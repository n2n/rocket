namespace rocket.cmd {
	
	export class Container {
		private jqContainer: JQuery;
		private layers: Array<Layer>;
		
		private jqErrorLayer: JQuery = null;
		
		constructor(jqContainer: JQuery) {
			this.jqContainer = jqContainer;
			this.layers = new Array<Layer>();
			
			var layer = new Layer(this.jqContainer.find(".rocket-main-layer"), this.layers.length, this);
			this.layers.push(layer);
			
			var that = this;
			
			layer.onNewHistoryEntry(function (historyIndex: number, context: Context) {
				var stateObj = { 
					"type": "rocketContext",
					"level": layer.getLevel(),
					"url": context.getUrl(),
					"historyIndex": historyIndex
				};
				history.pushState(stateObj, "seite 2", context.getUrl());
			});
						
			$(window).bind("popstate", function(e) {
				if (that.jqErrorLayer) {
					that.jqErrorLayer.remove();
					that.jqErrorLayer = null;
				}
				
				if (!history.state) {
					layer.go(0, window.location.href);
					return;
				}
				
				if (history.state.type != "rocketContext" || history.state.level != 0) {
					return;
				}
				
				if (!layer.go(history.state.historyIndex, history.state.url)) {
//					history.back();
				}
			});
		}
		
		public handleError(url: string, html: string) {
			var stateObj = { 
				"type": "rocketErrorContext",
				"url": url
			};
			
			if (this.jqErrorLayer) {
				history.replaceState(stateObj, "seite 2", url);
			} else {
				history.pushState(stateObj, "seite 2", url);
			}
			
			this.jqErrorLayer = $("<div />", { "class": "rocket-error-layer" });
			this.jqErrorLayer.css({ "position": "fixed", "top": 0, "left": 0, "right": 0, "bottom": 0 });
			this.jqContainer.append(this.jqErrorLayer);
			
			var iframe = document.createElement("iframe");
			this.jqErrorLayer.append(iframe);
			
			iframe.contentWindow.document.open();
			iframe.contentWindow.document.write(html);
			iframe.contentWindow.document.close();
			
			$(iframe).css({ "width": "100%", "height": "100%" });
		}
	
		public getMainLayer(): Layer {
			if (this.layers.length > 0) {
				return this.layers[0];
			}
			
			throw new Error("Container empty.");
		}
		
		public getCurrentLayer() {
			if (this.layers.length == 0) {
				throw new Error("Container empty.");
			}
			
			var layer = null;
			for (var i in this.layers) {
				if (this.layers[i].isVisible()) {
					layer = this.layers[i];
				}
			}
			
			if (layer !== null) return layer;
			
			return this.layers[this.layers.length - 1];
		}
			
		
//		public createContext(html: string, newGroup: boolean = false): Context {
////			if (newGroup) {
////				this.currentContentGroup = new ContentGroup();
////				this.additonalContentGroups.push(this.currentContentGroup);
////			}
//			
//			return this.currentLayer.createContext(html, bla);
//		}
		
		
	}
	
	export class Layer {
		private jqLayer: JQuery;
		private level: number;
		private container: Container;
		private contexts: Array<Context>;
		private historyUrls: Array<string>;
		private currentHistoryIndex: number = null;
		private onNewContextCallbacks: Array<ContextCallback>;
		private onNewHistoryEntryCallbacks: Array<HistoryCallback>;
		private visible: boolean = true;
		
		constructor(jqContentGroup: JQuery, level: number, container: Container) {
			this.contexts = new Array<Context>();
			this.onNewContextCallbacks = new Array<ContextCallback>();
			this.onNewHistoryEntryCallbacks = new Array<HistoryCallback>();
			this.historyUrls = new Array<string>();
			this.jqLayer = jqContentGroup;
			this.level = level;
			this.container = container;
			
			jqContentGroup.addClass("rocket-layer");
			jqContentGroup.data("rocketLayer", this);
			
			var jqContext = jqContentGroup.children(".rocket-context");
			if (jqContext.length > 0) {
				var context = new Context(jqContext, window.location.href, this);
				this.addContext(context);
				this.pushHistoryEntry(context.getUrl());
			}
		}
		
		public getContainer(): Container {
			return this.container;
		}
		
		public isVisible(): boolean {
			return this.visible;
		}
		
		public show() {
			this.visible = true;
			this.jqLayer.show();	
		}
		
		public hide() {
			this.visible = false;
			this.jqLayer.hide();	
		}
		
		public getLevel() {
			return this.level;
		}
		
		public getCurrentContext(): Context {
			if (this.contexts.length == 0) {
				throw new Error("no context avaialble");
			}
			
			var url = this.historyUrls[this.currentHistoryIndex];
			
			for (var i in this.contexts) {
				if (this.contexts[i].getUrl() == url) {
					return this.contexts[i];
				} 
			}
				
			return null;
		} 
		
		public getCurrentHistoryIndex() {
			return this.currentHistoryIndex;
		}
		
		private addContext(context: Context) {
			this.contexts.push(context);
			var that = this;
			
			context.onClose(function (context: Context) {
				for (var i in that.contexts) {
					if (that.contexts[i] !== context) continue;
					
					delete that.contexts[i];
					break;
				}
			});
			
			for (var i in this.onNewContextCallbacks) {
				this.onNewContextCallbacks[i](context);
			}
		}
		
		public pushHistoryEntry(url: string) {
			var context: Context = this.getContextByUrl(url);
			if (context === null) {
				throw new Error("Not context with this url found: " + url);
			}
			
			this.currentHistoryIndex = this.historyUrls.length;
			this.historyUrls.push(context.getUrl());
			
			for (var i in this.onNewHistoryEntryCallbacks) {
				this.onNewHistoryEntryCallbacks[i](this.currentHistoryIndex, context);
			}
			
			this.switchToContext(context);
		}
		
		public go(historyIndex: number, url: string) {
			if (this.historyUrls.length < (historyIndex + 1)) {
				throw new Error("Invalid history index: " + historyIndex);
			}
			
			if (this.historyUrls[historyIndex] != url) {
				throw new Error("Url missmatch for history index " + historyIndex + ". Url: " + url + " History url: " 
						+ this.historyUrls[historyIndex]);
			}
			
			this.currentHistoryIndex = historyIndex;
			var context = this.getContextByUrl(this.historyUrls[historyIndex]);
			if (context === null) return false;
			
			this.switchToContext(context);
			return true;
		}
		
		public getHistoryUrlByIndex(historyIndex: number): string {
			if (this.historyUrls.length <= historyIndex) return null;
			
			return this.historyUrls[historyIndex];
		}
		
		public getContextByUrl(url: string): Context {
			for (var i in this.contexts) {
				if (this.contexts[i].getUrl() == url) {
					return this.contexts[i];
				}
			}

			return null;
		}
		
		private switchToContext(context: Context) {
			for (var i in this.contexts) {
				if (this.contexts[i] === context) {
					context.show();
				} else {
					this.contexts[i].hide();
				}
			}
		}
		
		public createContext(url: string): Context {
			if (this.getContextByUrl(url)) {
				throw new Error("Context with url already available: " + url);
			}
			
			var jqContent = $("<div/>");
			this.jqLayer.append(jqContent);
			var context = new Context(jqContent, url, this);
			
			this.addContext(context);
			
			return context;
		}
		
		public clear() {
			for (var i in this.contexts) {
				this.contexts[i].close();
			}
		}
		
		public close() {
			throw new Error("layer close not yet implemented.");
		}
		
		public dispose() {
			this.contexts = new Array<Context>();
			this.jqLayer.remove();
		}
		
		public onNewContext(onNewContextCallback: ContextCallback) {
			this.onNewContextCallbacks.push(onNewContextCallback);
		}
		
		public onNewHistoryEntry(onNewHistoryEntryCallback: HistoryCallback) {
			this.onNewHistoryEntryCallbacks.push(onNewHistoryEntryCallback);
		}
		
		public static findFrom(jqElem: JQuery): Layer {
			if (!jqElem.hasClass(".rocket-layer")) {
				jqElem = jqElem.parents(".rocket-layer");
			}
			
			var layer = jqElem.data("rocketLayer");
			if (layer === undefined) {
				return null;
			}
			
			return layer;
		}
	}
	
	interface ContextCallback {
		(context: Context): any
	}
	
	interface HistoryCallback {
		(index: number, context: Context): any
	}
	
	export class Context {
		private jqContext: JQuery;
		private url: string;
		private layer: Layer;
		private onCloseCallbacks: Array<ContextCallback>
		
		constructor(jqContext: JQuery, url: string, layer: Layer) {
			this.jqContext = jqContext;
			this.url = url;
			this.layer = layer;
			this.onCloseCallbacks = new Array<ContextCallback>();
			jqContext.addClass("rocket-context");
			jqContext.data("rocketContext", this);
			
			this.hide();
		}
		
		public getLayer(): Layer {
			return this.layer;
		}
		
		public getUrl(): string {
			return this.url;
		}
		
		private ensureNotClosed() {
			if (this.jqContext !== null) return;
			
			throw new Error("Context already closed.");
		}
		
		public close() {
			this.jqContext.remove();
			this.jqContext = null;
			
			var callback;
			while (undefined !== (callback = this.onCloseCallbacks.shift())) {
				callback(this);
			}
		}
		
		public show() {
			this.jqContext.show();
		
//			var callback;
//			while (undefined !== (callback = this.onShowCallbacks.shift())) {
//				callback(this);
//			}
		}
		
		public hide() {
			this.jqContext.hide();
		}
		
		public clear(loading: boolean = false) {
			this.jqContext.empty();
			this.jqContext.addClass("rocket-loading");
		}
			
		public applyHtml(html: string) {
			this.jqContext.removeClass("rocket-loading");
			this.jqContext.html(html);
		} 
		
		public onClose(onCloseCallback: ContextCallback) {
			this.onCloseCallbacks.push(onCloseCallback);
		}
		
		public static findFrom(jqElem: JQuery): Context {
			if (!jqElem.hasClass(".rocket-context")) {
				jqElem = jqElem.parents(".rocket-context");
			}
			
			var context = jqElem.data("rocketContext");
			if (context) return context;
			
			return null;
		}
		
	}
	
	export class Entry {
		
	}
}