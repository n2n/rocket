/// <reference path="ui/common.ts" />
/// <reference path="ui/dialog.ts" />
/// <reference path="storage/cookie.ts" />
/// <reference path="storage/web.ts" />
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
class RocketTs {
	private elemContentContainer: JQuery;
	private contentStack: ui.ContentStack = null;
	private uiInitFunctions: Object = {};
	private readyCallbacks: Array<($: JQueryStatic) => void> = [];
	private additionalContent: ui.AdditionalContent = null;
	private stressWindow: ui.StressWindow = null;
	private unsavedFormManager: ui.UnsavedFormManager = null;
	private cookieStorage: storage.CookieStorage = null;
	private localStorage: storage.WebStorage = null;
	private finalEventTimers: Object = {};
	private confirmableManager: ui.ConfirmableManager;
	private initialized: boolean = false;
	
	public constructor() {
		var refreshPath = $("body").data("refresh-path");
		
		this.stressWindow = new ui.StressWindow();
		this.unsavedFormManager = new ui.UnsavedFormManager();
		this.cookieStorage = new storage.CookieStorage();
		this.localStorage = new storage.WebStorage(refreshPath, localStorage);
		
		(function(that: RocketTs) {
			
			jQuery(document).ready(function($) {
				
				that.confirmableManager = new ui.ConfirmableManager();
				that.onDomReady($)
				var refresh = function() {
					setTimeout(function() {
						$.get(refreshPath);
						refresh();
					}, 300000)
				}
				refresh();
				
				$(".rocket-paging select, select.rocket-paging").change(function() {
					window.location = this.value;
				});
				
				if (typeof $.fn.responsiveTable === 'function') {
					$(".rocket-list").responsiveTable();
				}
				
				$(".rocket-unsaved-check-form").each(function() {
					that.unsavedFormManager.registerForm($(this));
				});
				
				$(document).ajaxError(function(event, jqXhr, settings, thrownError) {
					if (jqXhr.status === 0) return;
					
					var w = window.open(settings.url);
					var newDoc = w.document.open("text/html", "replace");
					newDoc.write(jqXhr.responseText);
					newDoc.close();
				});
				
			});
			
		}).call(this, this);
	}
	
	public resetForm(elem: JQuery) {
		elem.find("input, textarea, select").each(function() {
			var jqElem = jQuery(this);
			if (this.defaultValue != undefined) {
				this.value = this.defaultValue; 
			}
		});
	}
	
	private onDomReady($: JQueryStatic) {
		var that = this;
		this.elemContentContainer = $("#rocket-content-container")
		this.contentStack = new ui.ContentStack(this.elemContentContainer);
		
		var elemAdditional = $("#rocket-additional");
		if (elemAdditional.length > 0) {
			this.additionalContent = new ui.AdditionalContent(this.elemContentContainer, elemAdditional);
		} 
		
		n2n.dispatch.registerCallback(function() {
			$.each(that.uiInitFunctions, function(selector, initFunction) {
				that.runInitFunction(selector, initFunction);
			});
		});
		
		this.readyCallbacks.forEach(function(callback) {
			callback($);
		});
		
		that.initialized = true;
	}
	
	public ready(callback) {
		if (this.initialized) {
			callback(jQuery);
			return;	
		}
		
		this.readyCallbacks.push(callback);
	}
	
	public getElemContentContainer() {
		return this.elemContentContainer;
	}
	
	public getContentStack() {
		return this.contentStack;
	}
	
	public getLocalStorage() {
		return this.localStorage;	
	}
	
	public getOrCreateAdditionalContent() {
		if (null === this.additionalContent) {
			this.additionalContent = new ui.AdditionalContent(this.elemContentContainer);	
		}
		
		return this.additionalContent;
	}
	
	public registerUiInitFunction(selector: string, initFunction: (elem: JQuery) => void) {
		this.uiInitFunctions[selector] = initFunction;	
		this.runInitFunction(selector, initFunction);
	}
	
	private runInitFunction(selector, initFunction) {
		var that = this;	
		jQuery(selector).each(function() {
			var elem = jQuery(this);
			if (that.isInitialized(selector, elem)) return;
			that.markAsInitialized(selector, elem);
			
			initFunction(elem);
		});
	}
	
	public isInitialized(selector, elem: JQuery) {
		return elem.data("initialized" + selector);
	}
	
	public markAsInitialized(selector, elem: JQuery): JQuery {
		return elem.data("initialized" + selector, true);	
	}
	
	public analyzeAjahData(data: Object): JQuery {
		return jQuery(jQuery.parseHTML(n2n.dispatch.analyze(data)));
	}
	
	public updateUi() {
		n2n.dispatch.update();	
	}
	
	public showDialog(dialog: ui.Dialog) {
		this.stressWindow.open(dialog);
	}
	
	public registerForm(form: JQuery) {
		this.unsavedFormManager.registerForm(form);
	}
	
	public setCookie(name: string, value: string, hours: number = null) {
		this.cookieStorage.setValue(name, value, hours);
	}

	public getCookie(name: string) {
	    return this.cookieStorage.getValue(name);
	};
	
	public waitForFinalEvent(callback, milliSeconds: number, uniqueId: string) {
	    if (this.finalEventTimers[uniqueId] ) {
			clearTimeout(this.finalEventTimers[uniqueId]);
	    }
		
	    this.finalEventTimers[uniqueId] = setTimeout(callback, milliSeconds);
	};
	
	public createLoadingElem() {
		return $("<div />", {
			"class": "rocket-control-group rocket-loading"	
		}).css({
			"text-align": "center"
		});
	}
	
	public objectify(obj) {
		if ($.isPlainObject(obj)) return obj;
		if ($.isArray(obj)) {
			var tmpObj = {};
			$.each(obj, function(key, value) {
				tmpObj[key] = value;
			})	
			return tmpObj
		}
		
		return {};
	}
	
	public creatControlElem(text: string, callback: () => void = null, iconClassName: string = null): JQuery {
		var aAttrs = {
			"href": "#",
			"class": "rocket-control"	
		}
		if (null !== iconClassName) {
			aAttrs["title"]	= text
		} else {
			aAttrs["text"] = text;	
		}
		
		var elemA = jQuery("<a />", aAttrs);
		if (null !== iconClassName) {
			elemA.append(jQuery("<i />", {
				"class": iconClassName	
			}));
		}
		
		if (null !== callback) {
			elemA.click(function(e) {
				e.preventDefault();
				callback();
			});
		}
		
		return elemA;
	}
	
	public getConfirmableManager(): ui.ConfirmableManager {
		return this.confirmableManager;
	}

}
var rocketTs = new RocketTs();
