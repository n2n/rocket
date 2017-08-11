namespace rocket.cmd {
	import display = rocket.display;
	import util = rocket.util;
	
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
                this.jqErrorLayer.remove();
				history.replaceState(stateObj, "n2n Rocket", url);
			} else {
				history.pushState(stateObj, "n2n Rocket", url);
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
		
		public createLayer(dependentContext: Context = null): Layer {
			var jqLayer = $("<div />", {
				"class": "rocket-layer"
			});
			
			this.jqContainer.append(jqLayer);
			
			var layer = new Layer(jqLayer, this.layers.length, this);
			this.layers.push(layer);
			
			
			var jqToolbar = $("<div />", {
				"class": "rocket-layer-toolbar rocket-simple-commands"
			});
			jqLayer.append(jqToolbar);
			
			var jqButton = $("<button />", { 
				"class": "btn btn-danger"
			}).append($("<i />", {
				"class": "fa fa-times"
			})).click(function () {
				layer.close();
			});
			jqToolbar.append(jqButton);
			
			if (dependentContext === null) {
				return layer;
			}
			
			dependentContext.onClose(function () {
				layer.close();
			});
			dependentContext.onHide(function () {
				layer.hide();
			});
			dependentContext.onShow(function () {
				layer.show();
			});
			
			return layer;
		}
			
			
		public getAllContexts(): Array<Context> {
			var contexts = new Array<Context>();
			
			for (var i in this.layers) {
				var layerContexts = this.layers[i].getContexts(); 
				for (var j in layerContexts) {
					contexts.push(layerContexts[j]);
				}
			}
			
			return contexts;
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
				
				console.log(this.contexts[i].getUrl() + " - " + url);
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
		private onShowCallbacks: Array<ContextCallback> = new Array<ContextCallback>();
		private onHideCallbacks: Array<ContextCallback> = new Array<ContextCallback>();
		private onCloseCallbacks: Array<ContextCallback> = new Array<ContextCallback>();
		private whenContentChangedCallbacks: Array<ContextCallback> = new Array<ContextCallback>();
		private callbackRegistery: util.CallbackRegistry<ContextCallback> = new util.CallbackRegistry<ContextCallback>();
		private additionalTabManager: AdditionalTabManager;
		private menu: Menu;
		
		constructor(jqContext: JQuery, url: string, layer: Layer) {
			this.jqContext = jqContext;
			this.url = url;
			this.layer = layer;
			
			jqContext.addClass("rocket-context");
			jqContext.data("rocketContext", this);
			
			this.reset();
			this.hide();			
		}
		
		public getLayer(): Layer {
			return this.layer;
		}
		
		public getJQuery(): JQuery {
			return this.jqContext;
		}
		
		public getUrl(): string {
			return this.url;
		}
		
		private ensureNotClosed() {
			if (this.jqContext !== null) return;
			
			throw new Error("Context already closed.");
		}
		
		public close() {
			var callback;
			while (undefined !== (callback = this.onCloseCallbacks.shift())) {
				callback(this);
			}
			
			this.jqContext.remove();
			this.jqContext = null;
		}
		
		public show() {
			this.jqContext.show();
		
			var callback;
			while (undefined !== (callback = this.onShowCallbacks.shift())) {
				callback(this);
			}
		}
		
		public hide() {
			this.jqContext.hide();
			
			var callback;
			while (undefined !== (callback = this.onShowCallbacks.shift())) {
				callback(this);
			}
		}
		
		private reset() {
			this.additionalTabManager = new AdditionalTabManager(this);
			this.menu = new Menu(this);
		}
		
		public clear(loading: boolean = false) {
			this.jqContext.empty();
			this.jqContext.addClass("rocket-loading");
			
			this.reset();
		}
			
		public applyHtml(html: string) {
			this.endLoading();
			this.jqContext.html(html);
			
			this.reset();
		} 
		
		public isLoading(): boolean {
			return this.jqContext.hasClass("rocket-loading");
		}
		
		public endLoading() {
			this.jqContext.removeClass("rocket-loading");
		}
		
		public applyContent(jqContent: JQuery) {
			this.endLoading();
			this.jqContext.append(jqContent);
			
			this.reset();
			
			var context = this;
			this.callbackRegistery.filter(Context.EventType.CONTENT_CHANGED.toString()).forEach(function (callback: ContextCallback) {
				callback(context);
			});
		}
		
		public onShow(callback: ContextCallback) {
			this.onShowCallbacks.push(callback);
		}
		
		public onHide(callback: ContextCallback) {
			this.onHideCallbacks.push(callback);
		}
		
		public onClose(onCloseCallback: ContextCallback) {
			this.onCloseCallbacks.push(onCloseCallback);
		}
		
		public whenContentChanged(whenContentChangedCallback: ContextCallback) {
			this.whenContentChangedCallbacks.push(whenContentChangedCallback);
		}
		
		public on(eventType: Context.EventType, callback: ContextCallback) {
			this.callbackRegistery.register(eventType.toString(), callback);
		}
		
		public off(eventType: Context.EventType, callback: ContextCallback) {
			this.callbackRegistery.unregister(eventType.toString(), callback);
		}
		
		public createAdditionalTab(title: string, prepend: boolean = false) {
			return this.additionalTabManager.createTab(title, prepend);
		} 
		
		public getMenu(): Menu {
			return this.menu;
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
	
	export namespace Context {
		export enum EventType {
			CONTENT_CHANGED = "contentChanged"
		}
	}
	
	class AdditionalTabManager {
		private context: Context;
		private tabs: Array<AdditionalTab>;
		
		private jqAdditional: JQuery = null;
		
		public constructor(context: Context) {
			this.context = context;
			this.tabs = new Array<AdditionalTab>();
		}
		
		public createTab(title: string, prepend: boolean = false): AdditionalTab {
			this.setupAdditional();
			
			var jqNavItem = $("<li />", {
				"text": title
			});
			
			var jqContent = $("<div />", {
				"class": "rocket-additional-content"
			});
			
			if (prepend) {
				this.jqAdditional.find(".rocket-additional-nav").prepend(jqNavItem);
			} else {
				this.jqAdditional.find(".rocket-additional-nav").append(jqNavItem);
			}
			
			this.jqAdditional.find(".rocket-additional-container").append(jqContent);
			
			var tab = new AdditionalTab(jqNavItem, jqContent);
			this.tabs.push(tab);
			
			var that = this;
			
			tab.onShow(function () {
				for (var i in that.tabs) {
					if (that.tabs[i] === tab) continue;
					
					this.tabs[i].hide();
				}
			});
			
			tab.onDispose(function () {
				that.removeTab(tab);
			});
			
			if (this.tabs.length == 1) {
				tab.show();
			}
			
			return tab;
		}
		
		private removeTab(tab: AdditionalTab) {
			for (var i in this.tabs) {
				if (this.tabs[i] !== tab) continue;
				
				this.tabs.splice(parseInt(i), 1);
				
				if (this.tabs.length == 0) {
					this.setdownAdditional();
					return;
				}
			
				if (tab.isActive()) {
					this.tabs[0].show();
				}
				
				return;
			}
		}
		
		private setupAdditional() {
			if (this.jqAdditional !== null) return;
			
			var jqContext = this.context.getJQuery();
			
			jqContext.addClass("rocket-contains-additional")
			
			this.jqAdditional = $("<div />", {
				"class": "rocket-additional"
			});
			this.jqAdditional.append($("<ul />", { "class": "rocket-additional-nav" }));
			this.jqAdditional.append($("<div />", { "class": "rocket-additional-container" }));
			jqContext.append(this.jqAdditional);
		}
		
		private setdownAdditional() {
			if (this.jqAdditional === null) return;
			
			this.context.getJQuery().removeClass("rocket-contains-additional");
			
			this.jqAdditional.remove();
			this.jqAdditional = null;
		}
	}
	
	export class AdditionalTab {
		private jqNavItem: JQuery;
		private jqContent: JQuery;
		private active: boolean = false;
		
		private onShowCallbacks: Array<(AdditionalTab) => any> = new Array<(AdditionalTab) => any>();
		private onHideCallbacks: Array<(AdditionalTab) => any> = new Array<(AdditionalTab) => any>();
		private onDisposeCallbacks: Array<(AdditionalTab) => any> = new Array<(AdditionalTab) => any>();
		
		constructor(jqNavItem: JQuery, jqContent: JQuery) {
			this.jqNavItem = jqNavItem;
			this.jqContent = jqContent;
			
			this.jqNavItem.click(this.show);
			this.jqContent.hide();
		}
		
		public getJqNavItem(): JQuery {
			return this.jqNavItem;
		}
		
		public getJqContent(): JQuery {
			return this.jqContent;
		}
		
		public isActive(): boolean {
			return this.active;
		}
		
		public show() {
			this.active = true;
			this.jqNavItem.addClass("rocket-active");
			this.jqContent.show();
			
			for (var i in this.onShowCallbacks) {
				this.onShowCallbacks[i](this);
			}
		}
		
		public hide() {
			this.active = false;
			this.jqContent.hide();
			this.jqNavItem.removeClass("rocket-active");
			
			for (var i in this.onHideCallbacks) {
				this.onHideCallbacks[i](this);
			}
		}

		public dispose() {
			this.jqNavItem.remove();
			this.jqContent.remove();
			
			for (var i in this.onDisposeCallbacks) {
				this.onDisposeCallbacks[i](this);
			}
		}
		
		public onShow(callback: (AdditionalTab) => any) {
			this.onShowCallbacks.push(callback);
		}
		
		public onHide(callback: (AdditionalTab) => any) {
			this.onHideCallbacks.push(callback);
		}
		
		public onDispose(callback: (AdditionalTab) => any) {
			this.onDisposeCallbacks.push(callback);
		}
	}
	
	export class Menu {
		private context: Context;
		private commandList: display.CommandList = null;
		
		public constructor(context: Context) {
			this.context = context;
		}
		
		public getCommandList(): display.CommandList {
			if (this.commandList !== null) {
				return this.commandList;
			}
			
			var jqCommandList = this.context.getJQuery().find(".rocket-context-commands");
			if (jqCommandList.length == 0) {
				jqCommandList = $("<div />", {
					"class": "rocket-context-commands"
				});
				this.context.getJQuery().append(jqCommandList);
			}
			
			return this.commandList = new display.CommandList(jqCommandList);
		}
	}
}