namespace rocket.cmd {
	
	export class Container {
		private jqContainer: JQuery;
		private layers: Array<Layer>;
		
		constructor(jqContainer: JQuery) {
			this.jqContainer = jqContainer;
			this.layers = new Array<Layer>();
			
			var layer = new Layer(this.jqContainer.find(".rocket-main-layer"), this.layers.length);
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
		
		public getMainLayer(): Layer {
			if (this.layers.length > 0) {
				return this.layers[0];
			}
			
			throw new Error("MainLayer ");
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
		private jqContentGroup: JQuery;
		private level: number;
		private contexts: Array<Context>;
		private historyUrls: Array<string>;
		private currentHistoryIndex: number = null;
		private onNewContextCallbacks: Array<ContextCallback>;
		private onNewHistoryEntryCallbacks: Array<HistoryCallback>;
		
		constructor(jqContentGroup: JQuery, level: number) {
			this.contexts = new Array<Context>();
			this.onNewContextCallbacks = new Array<ContextCallback>();
			this.onNewHistoryEntryCallbacks = new Array<HistoryCallback>();
			this.historyUrls = new Array<string>();
			this.jqContentGroup = jqContentGroup;
			this.level = level;
			
			jqContentGroup.addClass("rocket-layer");
			jqContentGroup.data("rocketLayer", this);
			
			var jqContext = jqContentGroup.children(".rocket-context");
			if (jqContext.length > 0) {
				var context = new Context(jqContext, window.location.href, this);
				this.addContext(context);
				this.createHistoryEntry(context);
			}
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
		
		private createHistoryEntry(context: Context) {
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
		
		private getContextByUrl(url: string): Context {
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
		
		public exec(url: string, config: ExecConfig = null) {
			var forceReload = false;
			var showLoadingContext = true;
			var doneCallback;
			
			if (config !== null) {
				forceReload = config.forceReload === true;
				showLoadingContext = config.showLoadingContext !== false
				doneCallback = config.done;
			}
			
			var context = this.getContextByUrl(url);
			
			if (context !== null) {
				if (this.getCurrentContext() !== context) {
					this.createHistoryEntry(context);
				}
				
				if (!forceReload) {
					if (doneCallback) {
						setTimeout(function () { doneCallback(new ExecResult(null, context)); }, 0);
					}
					
					return;
				}
			}
			
			if (context === null && showLoadingContext) {
				context = this.createContext(url);
				this.createHistoryEntry(context);
			}
			
			if (context !== null) {
				context.clear(true);
			}
			
		
			var that = this;
			$.ajax({
				"url": url,
				"dataType": "json"
			}).fail(function (data) {
				context.applyErrorHtml(data.responseText);
			}).done(function (data) {
				context.applyHtml(n2n.ajah.analyze(data));
				n2n.ajah.update();
				
				if (doneCallback) {
					doneCallback(new ExecResult(null, context));
				}
			});
		}
				
		private createContext(url: string): Context {
			var jqContent = $("<div/>");
			this.jqContentGroup.append(jqContent);
			var context = new Context(jqContent, url, this);
			
			this.addContext(context);
			
			return context;
		}
		
		public clear() {
			for (var i in this.contexts) {
				this.contexts[i].close();
			}
		}
		
		public dispose() {
			this.contexts = new Array<Context>();
			this.jqContentGroup.remove();
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
	
	interface ExecConfig {
		forceReload: boolean 
		showLoadingContext: boolean; 
		done: (ExecResult) => any;
	}
	
	class ExecResult {
		constructor(order, context: Context) {
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
		
		public applyErrorHtml(html: string) {
			this.jqContext.removeClass("rocket-loading");
			
			var iframe = document.createElement('iframe');
			this.jqContext.append(iframe);
			
			
			iframe.contentWindow.document.open();
			iframe.contentWindow.document.write(html);
			iframe.contentWindow.document.close();
			
			$(iframe).css({"width": "100%", "height": "100%"});
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