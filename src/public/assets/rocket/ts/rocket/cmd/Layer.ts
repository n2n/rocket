namespace rocket.cmd {
	import display = rocket.display;
	import util = rocket.util;
	
	export class Layer {
		private jqLayer: JQuery;
		private level: number;
		private container: Container;
		private contexts: Array<Context>;
		private historyUrls: Array<Url>;
		private currentHistoryIndex: number = null;
		private onNewContextCallbacks: Array<ContextCallback>;
		private onNewHistoryEntryCallbacks: Array<HistoryCallback>;
		private visible: boolean = true;
		
		constructor(jqContentGroup: JQuery, level: number, container: Container) {
			this.contexts = new Array<Context>();
			this.onNewContextCallbacks = new Array<ContextCallback>();
			this.onNewHistoryEntryCallbacks = new Array<HistoryCallback>();
			this.historyUrls = new Array<Url>();
			this.jqLayer = jqContentGroup;
			this.level = level;
			this.container = container;
			
			jqContentGroup.addClass("rocket-layer");
			jqContentGroup.data("rocketLayer", this);
			
			var jqContext = jqContentGroup.children(".rocket-context");
			if (jqContext.length > 0) {
				var context = new Context(jqContext, Url.create(window.location.href), this);
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
				if (this.contexts[i].getUrl().equals(url)) {
					return this.contexts[i];
				} 
			}
				
			return null;
		} 
		
		getContexts(): Array<Context> {
			return this.contexts;
		}
		
		public getCurrentHistoryIndex(): number {
			return this.currentHistoryIndex;
		}
		
		private addContext(context: Context) {
			this.contexts.push(context);
			var that = this;
			
			context.onClose(function (context: Context) {
				for (var i in that.contexts) {
					if (that.contexts[i] !== context) continue;
					
					that.contexts.splice(parseInt(i), 1);
					break;
				}
			});
			
			for (var i in this.onNewContextCallbacks) {
				this.onNewContextCallbacks[i](context);
			}
		}
		
		public pushHistoryEntry(url: string|Url) {
			url = Url.create(url);
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
		
		public go(historyIndex: number, urlExpr: string|Url) {
			var url = Url.create(urlExpr);
			
			if (this.historyUrls.length < (historyIndex + 1)) {
				throw new Error("Invalid history index: " + historyIndex);
			}
			
			if (this.historyUrls[historyIndex].equals(url)) {
				throw new Error("Url missmatch for history index " + historyIndex + ". Url: " + url + " History url: " 
						+ this.historyUrls[historyIndex]);
			}
			
			this.currentHistoryIndex = historyIndex;
			var context = this.getContextByUrl(this.historyUrls[historyIndex]);
			if (context === null) return false;
			
			this.switchToContext(context);
			return true;
		}
		
		public getHistoryUrlByIndex(historyIndex: number): Url {
			if (this.historyUrls.length <= historyIndex) return null;
			
			return this.historyUrls[historyIndex];
		}
		
		public getContextByUrl(urlExpr: string|Url): Context {
			var url = Url.create(urlExpr);
			
			for (var i in this.contexts) {
				if (this.contexts[i].getUrl().equals(url)) {
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
		
		public createContext(urlExpr: string|Url): Context {
			var url = Url.create(urlExpr);
			
			if (this.getContextByUrl(url)) {
				throw new Error("Context with url already available: " + url);
			}
			
			var jqContent = $("<div />");
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
			var context: Context;
			while (undefined !== (context = this.contexts.pop())) {
				context.close();
			}
				
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
	
	interface HistoryCallback {
		(index: number, context: Context): any
	}
}