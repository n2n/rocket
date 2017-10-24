/// <reference path="../util/Util.ts" />
/// <reference path="../display/Group.ts" />

namespace Rocket.Cmd {
	import display = Rocket.Display;
	import util = Rocket.util;
	
	export class Zone {
		private jqZone: JQuery;
		private _activeUrl: Jhtml.Url;
		private urls: Array<Jhtml.Url> = [];
		private _layer: Layer;
		private callbackRegistery: util.CallbackRegistry<PageCallback> = new util.CallbackRegistry<PageCallback>();
		private additionalTabManager: AdditionalTabManager;
		private _menu: Menu;
		private _blocked: boolean = false;
		
		constructor(jqZone: JQuery, url: Jhtml.Url, layer: Layer) {
			this.jqZone = jqZone;
			this.urls.push(this._activeUrl = url);
			this._layer = layer;
			
			jqZone.addClass("rocket-zone");
			jqZone.data("rocketPage", this);
			
			this.reset();
			this.hide();
		}
		
		get layer(): Layer {
			return this._layer;
		}
		
		get jQuery(): JQuery {
			return this.jqZone;
		}
		
		containsUrl(url: Jhtml.Url): boolean {
			for (var i in this.urls) {
				if (this.urls[i].equals(url)) return true;
			}
			
			return false;
		}
		
//		registerUrl(url: Url) {
//			if (this.containsUrl(url)) return;
//			
//			if (this._layer.containsUrl(url)) {
//				throw new Error("Url already registered for another Page of the current Layer."); 
//			}
//			
//			this.urls.push(url);
//		}
//		
//		unregisterUrl(url: Url) {
//			if (this.activeUrl.equals(url)) {
//				throw new Error("Cannot remove active url");
//			}
//			
//			for (var i in this.urls) {
//				if (this.urls[i].equals(url)) {
//					this.urls.splice(parseInt(i), 1);
//				}
//			}
//		}
		
		get activeUrl(): Jhtml.Url {
			return this._activeUrl;
		}
		
//		set activeUrl(activeUrl: Url) {
//			Rocket.util.ArgUtils.valIsset(activeUrl !== null)
//			
//			if (this._activeUrl.equals(activeUrl)) {
//				return;
//			}
//			
//			if (this.containsUrl(activeUrl)) {
//				this._activeUrl = activeUrl;
//				this.fireEvent(Page.EventType.ACTIVE_URL_CHANGED);
//				return;
//			}
//			
//			throw new Error("Active url not available for this context.");
//		}
		
		private fireEvent(eventType: Zone.EventType) {
			var that = this;
			this.callbackRegistery.filter(eventType.toString()).forEach(function (callback: PageCallback) {
				callback(that);
			});
		}
		
		private ensureNotClosed() {
			if (this.jqZone !== null) return;
			
			throw new Error("Page already closed.");
		}
		
		public close() {
			this.trigger(Zone.EventType.CLOSE)
			
			this.jqZone.remove();
			this.jqZone = null;
		}
		
		public show() {
			this.trigger(Zone.EventType.SHOW);
			
			this.jqZone.show();
		}
		
		public hide() {
			this.trigger(Zone.EventType.HIDE);
			
			this.jqZone.hide();
		}
		
		private reset() {
			this.additionalTabManager = new AdditionalTabManager(this);
			this._menu = new Menu(this);
		}
		
		
		public clear(showLoader: boolean = false) {
			this.jqZone.empty();
			if (showLoader) {
				this.jqZone.addClass("rocket-loading");
			}
			
			this.trigger(Zone.EventType.CONTENT_CHANGED);
		}
			
		public applyHtml(html: string) {
			this.endLoading();
			this.jqZone.html(html);
			
			this.reset();
			
			this.trigger(Zone.EventType.CONTENT_CHANGED);
		}
		
		public applyComp(comp: Jhtml.Comp) {
			this.endLoading();
			comp.attachTo(this.jqZone.get(0));
			
			this.reset();
			
			this.trigger(Zone.EventType.CONTENT_CHANGED);
		}
		
		public isLoading(): boolean {
			return this.jqZone.hasClass("rocket-loading");
		}
		
		public endLoading() {
			this.jqZone.removeClass("rocket-loading");
		}
		
		public applyContent(jqContent: JQuery) {
			this.endLoading();
			this.jqZone.append(jqContent);
			
			this.reset();
			this.trigger(Zone.EventType.CONTENT_CHANGED);
		}
		
		private trigger(eventType: Zone.EventType) {
			var context = this;
			this.callbackRegistery.filter(eventType.toString())
					.forEach(function (callback: PageCallback) {
						callback(context);
					});
		}
		
		public on(eventType: Zone.EventType, callback: PageCallback) {
			this.callbackRegistery.register(eventType.toString(), callback);
		}
		
		public off(eventType: Zone.EventType, callback: PageCallback) {
			this.callbackRegistery.unregister(eventType.toString(), callback);
		}
		
		public createAdditionalTab(title: string, prepend: boolean = false) {
			return this.additionalTabManager.createTab(title, prepend);
		} 
		
		get menu(): Menu {
			return this._menu;
		}
		
		get locked(): boolean {
			return this.locks.length > 0;
		}
		
		private locks: Array<Lock> = new Array();
		
		private releaseLock(lock: Lock) {
			let i = this.locks.indexOf(lock);
			if (i == -1) return; 
			
			this.locks.splice(i, 1);
			this.trigger(Zone.EventType.BLOCKED_CHANGED);
		}
		
		createLock(): Lock {
			var that = this;
			var lock = new Lock(function (lock: Lock) {
				that.releaseLock(lock);
			});
			this.locks.push(lock);
			this.trigger(Zone.EventType.BLOCKED_CHANGED);
			return lock;
		}
		
		public static of(jqElem: JQuery): Zone {
			if (!jqElem.hasClass(".rocket-zone")) {
				jqElem = jqElem.parents(".rocket-zone");
			}
			
			var context = jqElem.data("rocketPage");
			if (context instanceof Zone) return context;
			
			return null;
		}
	}
	
	export class Lock {
		constructor(private releaseCallback: (lock: Lock) => any) {
		}
		
		release() {
			this.releaseCallback(this);
		}
	}
	
	class AdditionalTabManager {
		private context: Zone;
		private tabs: Array<AdditionalTab>;
		
		private jqAdditional: JQuery = null;
		
		public constructor(context: Zone) {
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
			
			var jqPage = this.context.jQuery;
			
			jqPage.addClass("rocket-contains-additional")
			
			this.jqAdditional = $("<div />", {
				"class": "rocket-additional"
			});
			this.jqAdditional.append($("<ul />", { "class": "rocket-additional-nav" }));
			this.jqAdditional.append($("<div />", { "class": "rocket-additional-container" }));
			jqPage.append(this.jqAdditional);
		}
		
		private setdownAdditional() {
			if (this.jqAdditional === null) return;
			
			this.context.jQuery.removeClass("rocket-contains-additional");
			
			this.jqAdditional.remove();
			this.jqAdditional = null;
		}
	}
	
	export class AdditionalTab {
		private jqNavItem: JQuery;
		private jqContent: JQuery;
		private active: boolean = false;
		
		private onShowCallbacks: Array<(tab: AdditionalTab) => any> = [];
		private onHideCallbacks: Array<(tab: AdditionalTab) => any> = [];
		private onDisposeCallbacks: Array<(tab: AdditionalTab) => any> = [];
		
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
		
		public onShow(callback: (tab: AdditionalTab) => any) {
			this.onShowCallbacks.push(callback);
		}
		
		public onHide(callback: (tab: AdditionalTab) => any) {
			this.onHideCallbacks.push(callback);
		}
		
		public onDispose(callback: (tab: AdditionalTab) => any) {
			this.onDisposeCallbacks.push(callback);
		}
	}
	
	export class Menu {
		private context: Zone;
		private _toolbar: display.Toolbar = null;
		private _commandList: display.CommandList = null;
		private _partialCommandList: display.CommandList = null;
		
		
		constructor(context: Zone) {
			this.context = context;
		}
		
		get toolbar(): display.Toolbar {
			if (this._toolbar) {
				return this._toolbar;
			}
			
			let jqToolbar = this.context.jQuery.find(".rocket-zone-toolbar:first");
			if (jqToolbar.length == 0) {
				jqToolbar = $("<div />", { "class": "rocket-zone-toolbar"}).prependTo(this.context.jQuery);
			}
			
			
			return this._toolbar = new display.Toolbar(jqToolbar);
		}
		
		private getJqPageCommands() {
			var jqCommandList = this.context.jQuery.find(".rocket-zone-commands:first");
			if (jqCommandList.length == 0) {
				jqCommandList = $("<div />", {
					"class": "rocket-zone-commands"
				});
				this.context.jQuery.append(jqCommandList);
			}
			
			return jqCommandList;
		}
		
		get partialCommandList(): display.CommandList {
			if (this._partialCommandList !== null) {
				return this._partialCommandList;
			}
			
			var jqPageCommands = this.getJqPageCommands();
			
			var jqPartialCommands = jqPageCommands.children(".rocket-partial-commands:first");
			if (jqPartialCommands.length == 0) {
				jqPartialCommands = $("<div />", {"class": "rocket-partial-commands" }).prependTo(jqPageCommands);
			}
			
			return this._partialCommandList = new display.CommandList(jqPartialCommands);
		}
		
		get commandList(): display.CommandList {
			if (this._commandList !== null) {
				return this._commandList;
			}
			
			var jqPageCommands = this.getJqPageCommands();
			var jqCommands = jqPageCommands.children(":not(.rocket-partial-commands):first");
			if (jqCommands.length == 0) {
				jqCommands = $("<div />").appendTo(jqPageCommands);
			}
			
			return this._commandList = new display.CommandList(jqCommands);
		}
	}
	
	export interface PageCallback {
		(context: Zone): any
	}
	
	export namespace Zone {
		export enum EventType {
			SHOW /*= "show"*/,
			HIDE /*= "hide"*/,
			CLOSE /*= "close"*/,
			CONTENT_CHANGED /*= "contentChanged"*/,
			ACTIVE_URL_CHANGED /*= "activeUrlChanged"*/,
			BLOCKED_CHANGED /*= "stateChanged"*/ 
		}
	}
}