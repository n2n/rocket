namespace Rocket.Cmd {
	import display = Rocket.Display;
	
	export class Layer {
		private _contexts: Array<Page> = new Array<Page>();
		private onNewPageCallbacks: Array<PageCallback>;
		private onNewHistoryEntryCallbacks: Array<HistoryCallback>;
		private callbackRegistery: Rocket.util.CallbackRegistry<LayerCallback> = new Rocket.util.CallbackRegistry<LayerCallback>();
		private _visible: boolean = true;
		
		constructor(private jqLayer: JQuery, private _level: number, private _container: Container, 
				private _monitor: Jhtml.Monitor) {
			this.onNewPageCallbacks = new Array<PageCallback>();
			this.onNewHistoryEntryCallbacks = new Array<HistoryCallback>();

			var jqPage = jqLayer.children(".rocket-context:first");
			if (jqPage.length > 0) {
				var page = new Page(jqPage, Jhtml.Url.create(window.location.href), this);
				this.addPage(page);
			}

			this._monitor.history.onChanged(() => this.historyChanged() );
			
			this.historyChanged();
		}
		
		get monitor(): Jhtml.Monitor {
			return this._monitor;
		}
		
		containsUrl(url: Jhtml.Url): boolean {
			for (var i in this._contexts) {
				if (this._contexts[i].containsUrl(url)) return true;
			}
			
			return false;
		}
		
		public getPageByUrl(urlExpr: string|Jhtml.Url): Page {
			var url = Jhtml.Url.create(urlExpr);
			
			for (var i in this._contexts) {
				if (this._contexts[i].containsUrl(url)) {
					return this._contexts[i];
				}
			}
	
			return null;
		}
		
		private historyChanged() {
			let currentEntry: Jhtml.History.Entry;
		
			if (!currentEntry) return;
			
			let page: Page = this.getPageByUrl(currentEntry.page.url);
			if (!page) {
				this.addPage(page = this.createPage(currentEntry.page.url))
			}
			
			this.switchToPage(page);
		}
		

		public createPage(urlExpr: string|Jhtml.Url): Page {
			let url = Jhtml.Url.create(urlExpr);
			
			if (this.containsUrl(url)) {
				throw new Error("Page with url already available: " + url);
			}
			
			var jqContent = $("<div />");
			this.jqLayer.append(jqContent);
			var context = new Page(jqContent, url, this);
			
			this.addPage(context);
			
			return context;
		}

		get currentPage(): Page {
			if (this.empty || !this._monitor.history.currentEntry) {
				return null;
			}

			var url = this._monitor.history.currentPage.url;
			
			for (var i in this._contexts) {
				if (this._contexts[i].containsUrl(url)) {
					return this._contexts[i];
				} 
			}
				
			return null;
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
		
		get contexts(): Array<Page> {
			return this._contexts.slice();
		}
				
		private addPage(page: Page) {
			this._contexts.push(page);
			var that = this;
			
			page.on(Page.EventType.CLOSE, function (context: Page) {
				for (var i in that._contexts) {
					if (that._contexts[i] !== context) continue;
					
					that._contexts.splice(parseInt(i), 1);
					break;
				}
			});
			
			for (var i in this.onNewPageCallbacks) {
				this.onNewPageCallbacks[i](page);
			}
		}
		
		public onNewPage(onNewPageCallback: PageCallback) {
			this.onNewPageCallbacks.push(onNewPageCallback);
		}
		
		public clear() {
			for (var i in this._contexts) {
				this._contexts[i].close();
			}
		}
		
		public close() {
			this.trigger(Layer.EventType.CLOSE);
			
			let context = null;
			while (context = this._contexts.pop()) {
				context.close();
			}
				
			this._contexts = new Array<Page>();
			this.jqLayer.remove();
		}
		
		private switchToPage(context: Page) {
			for (var i in this._contexts) {
				if (this._contexts[i] === context) {
					context.show();
				} else {
					this._contexts[i].hide();
				}
			}
		}
		
//		public currentHistoryIndex(): number {
//			return this._currentHistoryIndex;
//		}
		
//		public pushHistoryEntry(urlExpr: string|Url) {
//			var url: Url = Url.create(urlExpr);
//			var context: Page = this.getPageByUrl(url);
//			if (context === null) {
//				throw new Error("Not context with this url found: " + url);
//			}
//			
//			this._currentHistoryIndex = this.historyUrls.length;
//			this.historyUrls.push(url);
//			context.activeUrl = url;
//			
//			for (var i in this.onNewHistoryEntryCallbacks) {
//				this.onNewHistoryEntryCallbacks[i](this._currentHistoryIndex, url, context);
//			}
//			
//			this.switchToPage(context);
//		}
		
//		get currentHistoryEntryUrl(): Url {
//			return this.historyUrls[this._currentHistoryIndex];
//		}
//		
//		public go(historyIndex: number, urlExpr: string|Url) {
//			var url = Url.create(urlExpr);
//			
//			if (this.historyUrls.length < (historyIndex + 1)) {
//				throw new Error("Invalid history index: " + historyIndex);
//			}
//			
//			if (this.historyUrls[historyIndex].equals(url)) {
//				throw new Error("Url missmatch for history index " + historyIndex + ". Url: " + url + " History url: " 
//						+ this.historyUrls[historyIndex]);
//			}
//			
//			this._currentHistoryIndex = historyIndex;
//			var context = this.getPageByUrl(this.historyUrls[historyIndex]);
//			if (context === null) return false;
//			
//			this.switchToPage(context);
//			return true;
//		}
//		
//		public getHistoryUrlByIndex(historyIndex: number): Url {
//			if (this.historyUrls.length <= historyIndex) return null;
//			
//			return this.historyUrls[historyIndex];
//		}
//		
//		
//		
//		
//		public onNewHistoryEntry(onNewHistoryEntryCallback: HistoryCallback) {
//			this.onNewHistoryEntryCallbacks.push(onNewHistoryEntryCallback);
//		}

		public static create(jqLayer: JQuery, _level: number, _container: Container, history: Jhtml.History) {
			if (Layer.test(jqLayer)) {
				throw new Error("Layer already bound to this element.");
			}
			
			jqLayer.addClass("rocket-layer");
			jqLayer.data("rocketLayer", this);
		}
		
		private static test(jqLayer: JQuery): Layer {
			var layer = jqLayer.data("rocketLayer");
			if (layer instanceof Layer) {
				return layer;
			}
			
			return null;
		}
		
		public static of(jqElem: JQuery): Layer {
			if (!jqElem.hasClass(".rocket-layer")) {
				jqElem = jqElem.closest(".rocket-layer");
			}
			
			var layer = Layer.test(jqElem);
			if (layer === undefined) {
				return null;
			}
			
			return layer;
		}
	}
	
	interface HistoryCallback {
		(index: number, url: Jhtml.Url, context: Page): any
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