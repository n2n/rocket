namespace Rocket.Cmd {
	import display = Rocket.Display;
	import util = Rocket.util;
	
	export class Layer {
		private jqLayer: JQuery;
		private _level: number;
		private _container: Container;
		private _contexts: Array<Context>;
		private historyUrls: Array<Url>;
		private _currentHistoryIndex: number = null;
		private onNewContextCallbacks: Array<ContextCallback>;
		private onNewHistoryEntryCallbacks: Array<HistoryCallback>;
		private callbackRegistery: util.CallbackRegistry<LayerCallback> = new util.CallbackRegistry<LayerCallback>();
		private _visible: boolean = true;
		
		constructor(jqContentGroup: JQuery, level: number, container: Container) {
			this._contexts = new Array<Context>();
			this.onNewContextCallbacks = new Array<ContextCallback>();
			this.onNewHistoryEntryCallbacks = new Array<HistoryCallback>();
			this.historyUrls = new Array<Url>();
			this.jqLayer = jqContentGroup;
			this._level = level;
			this._container = container;
			
			jqContentGroup.addClass("rocket-layer");
			jqContentGroup.data("rocketLayer", this);
			
			var jqContext = jqContentGroup.children(".rocket-context");
			if (jqContext.length > 0) {
				var context = new Context(jqContext, Url.create(window.location.href), this);
				this.addContext(context);
				this.pushHistoryEntry(context.activeUrl);
			}
		}
		
		containsUrl(url: Url): boolean {
			for (var i in this._contexts) {
				if (this._contexts[i].containsUrl(url)) return true;
			}
			
			return false;
		}
		
		get container(): Container {
			return this._container;
		}
		
		get visible(): boolean {
			return this._visible;
		}
		
		private trigger(eventType: Layer.EventType) {
			var layer = this;
			this.callbackRegistery.filter(eventType.toString())
					.forEach(function (callback: LayerCallback) {
						callback(layer);
					});
		}
		
		on(eventType: Layer.EventType, callback: LayerCallback) {
			this.callbackRegistery.register(eventType.toString(), callback);
		}
		
		off(eventType: Layer.EventType, callback: LayerCallback) {
			this.callbackRegistery.unregister(eventType.toString(), callback);
		}		
		
		show() {
			this.trigger(Layer.EventType.SHOW);
			
			this._visible = true;
			this.jqLayer.show();
		}
		
		hide() {
			this.trigger(Layer.EventType.SHOW);
			
			this._visible = false;
			this.jqLayer.hide();
		}
		
		get level(): number {
			return this._level;
		}
		
		get empty(): boolean {
			return this._contexts.length == 0;
		}
		
		private hasCurrent(): boolean {
			return this._currentHistoryIndex !== null;
		}
		
		get currentContext(): Context {
			if (this.empty || !this.hasCurrent()) {
				return null;
			}
			
			var url = this.historyUrls[this._currentHistoryIndex];
			
			for (var i in this._contexts) {
				if (this._contexts[i].containsUrl(url)) {
					return this._contexts[i];
				} 
			}
				
			return null;
		} 
		
		get contexts(): Array<Context> {
			return this._contexts.slice();
		}
		
		public currentHistoryIndex(): number {
			return this._currentHistoryIndex;
		}
		
		private addContext(context: Context) {
			this._contexts.push(context);
			var that = this;
			
			context.on(Context.EventType.CLOSE, function (context: Context) {
				for (var i in that._contexts) {
					if (that._contexts[i] !== context) continue;
					
					that._contexts.splice(parseInt(i), 1);
					break;
				}
			});
			
			for (var i in this.onNewContextCallbacks) {
				this.onNewContextCallbacks[i](context);
			}
		}
		
		public pushHistoryEntry(urlExpr: string|Url) {
			var url: Url = Url.create(urlExpr);
			var context: Context = this.getContextByUrl(url);
			if (context === null) {
				throw new Error("Not context with this url found: " + url);
			}
			
			this._currentHistoryIndex = this.historyUrls.length;
			this.historyUrls.push(url);
			context.activeUrl = url;
			
			for (var i in this.onNewHistoryEntryCallbacks) {
				this.onNewHistoryEntryCallbacks[i](this._currentHistoryIndex, url, context);
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
			
			this._currentHistoryIndex = historyIndex;
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
			
			for (var i in this._contexts) {
				if (this._contexts[i].containsUrl(url)) {
					return this._contexts[i];
				}
			}

			return null;
		}
		
		private switchToContext(context: Context) {
			for (var i in this._contexts) {
				if (this._contexts[i] === context) {
					context.show();
				} else {
					this._contexts[i].hide();
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
			for (var i in this._contexts) {
				this._contexts[i].close();
			}
		}
		
		public close() {
			this.trigger(Layer.EventType.CLOSE);
				
			this._contexts = new Array<Context>();
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
		(index: number, url: Url, context: Context): any
	}
	
	
	export interface LayerCallback {
		(layer: Layer): any
	}
	
	export namespace Layer {
		export enum EventType {
			SHOW /*= "show"*/,
			HIDE /*= "hide"*/,
			CLOSE /*= "close"*/
		}
	}
}