/// <reference path="../util/Util.ts" />
/// <reference path="../display/Group.ts" />
namespace rocket.cmd {
	import display = rocket.display;
	import util = rocket.util;
	
	export class Context {
		private jqContext: JQuery;
		private _activeUrl: Url;
		private urls: Array<Url> = new Array<Url>();
		private layer: Layer;
		private callbackRegistery: util.CallbackRegistry<ContextCallback> = new util.CallbackRegistry<ContextCallback>();
		private additionalTabManager: AdditionalTabManager;
		private _menu: Menu;
		
		constructor(jqContext: JQuery, url: Url, layer: Layer) {
			this.jqContext = jqContext;
			this.urls.push(this._activeUrl = url);
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
		
		containsUrl(url: Url): boolean {
			for (var i in this.urls) {
				if (this.urls[i].equals(url)) return true;
			}
			
			return false;
		}
		
		registerUrl(url: Url) {
			if (this.containsUrl(url)) return;
			
			if (this.layer.containsUrl(url)) {
				throw new Error("Url already registered for another Context of the current Layer."); 
			}
			
			this.urls.push(url);
		}
		
		unregisterUrl(url: Url) {
			if (!this.activeUrl.equals(url)) {
				throw new Error("Cannot remove active url");
			}
			
			for (var i in this.urls) {
				if (this.urls[i].equals(url)) {
					this.urls.splice(parseInt(i), 1);
				}
			}
		}
		
		get activeUrl(): Url {
			return this._activeUrl;
		}
		
		set activeUrl(activeUrl: Url) {
			rocket.util.ArgUtils.valIsset(activeUrl !== null)
			
			if (this._activeUrl.equals(activeUrl)) {
				return;
			}
			
			if (this.containsUrl(activeUrl)) {
				this._activeUrl = activeUrl;
				this.fireEvent(Context.EventType.ACTIVE_URL_CHANGED);
				return;
			}
			
			throw new Error("Active url not available for this context.");
		}
		
		private fireEvent(eventType: Context.EventType) {
			var that = this;
			this.callbackRegistery.filter(eventType.toString()).forEach(function (callback: ContextCallback) {
				callback(that);
			});
		}
		
		private ensureNotClosed() {
			if (this.jqContext !== null) return;
			
			throw new Error("Context already closed.");
		}
		
		public close() {
			this.trigger(Context.EventType.CLOSE)
			
			this.jqContext.remove();
			this.jqContext = null;
		}
		
		public show() {
			this.trigger(Context.EventType.SHOW);
			
			this.jqContext.show();
		}
		
		public hide() {
			this.trigger(Context.EventType.HIDE);
			
			this.jqContext.hide();
		}
		
		private reset() {
			this.additionalTabManager = new AdditionalTabManager(this);
			this._menu = new Menu(this);
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
			this.trigger(Context.EventType.CONTENT_CHANGED);
		}
		
		private trigger(eventType: Context.EventType) {
			var context = this;
			this.callbackRegistery.filter(eventType.toString())
					.forEach(function (callback: ContextCallback) {
						callback(context);
					});
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
		
		get menu(): Menu {
			return this._menu;
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
		private _commandList: display.CommandList = null;
		private _partialCommandList: display.CommandList = null;
		
		
		constructor(context: Context) {
			this.context = context;
		}
		
		private getJqContextCommands() {
			var jqCommandList = this.context.getJQuery().find(".rocket-context-commands:first");
			if (jqCommandList.length == 0) {
				jqCommandList = $("<div />", {
					"class": "rocket-context-commands"
				});
				this.context.getJQuery().append(jqCommandList);
			}
			
			return jqCommandList;
		}
		
		get partialCommandList(): display.CommandList {
			if (this._partialCommandList !== null) {
				return this._partialCommandList;
			}
			
			var jqContextCommands = this.getJqContextCommands();
			
			var jqPartialCommands = jqContextCommands.children(".rocket-partial-commands:first");
			if (jqPartialCommands.length == 0) {
				jqPartialCommands = $("<div />", {"class": "rocket-partial-commands" }).prependTo(jqContextCommands);
			}
			
			return this._partialCommandList = new display.CommandList(jqPartialCommands);
		}
		
		get commandList(): display.CommandList {
			if (this._commandList !== null) {
				return this._commandList;
			}
			
			var jqContextCommands = this.getJqContextCommands();
			var jqCommands = jqContextCommands.children(":not(.rocket-partial-commands):first");
			if (jqCommands.length == 0) {
				jqCommands = $("<div />").appendTo(jqContextCommands);
			}
			
			return this._commandList = new display.CommandList(jqCommands);
		}
	}
	
	export class Url {
		protected urlStr: string;
		
		constructor(urlStr: string) {
			this.urlStr = urlStr;
		}
		
		public toString(): string {
			return this.urlStr;
		}
		
		public equals(url: Url): boolean {
			return this.urlStr == url.urlStr;
		}
		
		public extR(pathExt: string): Url {
			if (pathExt === null || pathExt === undefined) {
				return this;
			}
			
			return new Url(this.urlStr.replace(/\/+$/, "") + "/" + encodeURI(pathExt));
		}
		
		public static create(urlExpression: string|Url): Url {
			if (urlExpression instanceof Url) {
				return urlExpression;
			}
			
			return new Url(Url.absoluteStr(urlExpression));
		}
		
		public static absoluteStr(urlExpression: string|Url): string {
			if (urlExpression instanceof Url) {
				return urlExpression.toString();
			}
			
			var urlStr = <string> urlExpression;
			
			if (!/^(?:\/|[a-z]+:\/\/)/.test(urlStr)) {
				return window.location.toString().replace(/\/+$/, "") + "/" + urlStr;
			} 
			
			if (!/^(?:[a-z]+:)?\/\//.test(urlStr)) {
				return window.location.protocol + "//" + window.location.host + urlStr;				
			}
			
			return urlStr;
		}
	}
	
	export interface ContextCallback {
		(context: Context): any
	}
	
	export namespace Context {
		export enum EventType {
			SHOW = "show",
			HIDE = "hide",
			CLOSE = "close",
			CONTENT_CHANGED = "contentChanged",
			ACTIVE_URL_CHANGED = "activeUrlChanged"
		}
	}
}