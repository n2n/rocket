/// <reference path="..\rocket.ts" />
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
module l10n {
	$ = jQuery; 
	
	class TranslationViewSwitch {
		private elemContainer: JQuery;
		private elemStandard: JQuery;
		private elemTranslationOnly: JQuery;
		private elemForClass: JQuery;
		private static CLASS_NAME_TRANSLATION_ONLY = "rocket-translation-only";
		private static CLASS_NAME_ACTIVE = "rocket-active";
		
		public constructor(defaultLabel: string, translationsOnlyLabel: string) {
			this.elemContainer = $("<ul />", {
				"class": "rocket-translation-view-switch"	
			});
			
			this.elemStandard = $("<li />", {
				"text": defaultLabel
			}).addClass(TranslationViewSwitch.CLASS_NAME_ACTIVE).appendTo(this.elemContainer);
			
			this.elemTranslationOnly = $("<li />", {
				"text": translationsOnlyLabel
			}).addClass("rocket-active").appendTo(this.elemContainer);
			
			this.elemForClass = $("#rocket-content-container");
			
			(function(that: TranslationViewSwitch) {
				that.elemStandard.click(function() {
					that.elemForClass.removeClass(TranslationViewSwitch.CLASS_NAME_TRANSLATION_ONLY);
					that.elemTranslationOnly.removeClass(TranslationViewSwitch.CLASS_NAME_ACTIVE);
					that.elemStandard.addClass(TranslationViewSwitch.CLASS_NAME_ACTIVE);
				});
				that.elemTranslationOnly.click(function() {
					that.elemForClass.addClass(TranslationViewSwitch.CLASS_NAME_TRANSLATION_ONLY);
					that.elemTranslationOnly.addClass(TranslationViewSwitch.CLASS_NAME_ACTIVE);
					that.elemStandard.removeClass(TranslationViewSwitch.CLASS_NAME_ACTIVE);
				});
			}).call(this, this);
		}
					
		public getElemContainer() {
			return this.elemContainer;	
		}
	}
	
	class TranslationEnabler {
		private elem: JQuery;
		private elemToolPanel: JQuery;
		private elemProperties
		private elemCbx: JQuery;
		private elemContainer: JQuery;
		private elemActivator: JQuery;
		private activationCallbacks: Array<(localeId: string) => void> = [];
		private deactivationCallbacks: Array<(localeId: string) => void> = [];
		
		public constructor (elem) {
			this.elem = elem;
			elem.parent(".rocket-controls").prev("label").hide();
			this.elemToolPanel = elem.parents(".rocket-tool-panel:first");
			this.elemProperties = this.elemToolPanel.next(".rocket-properties:first");
			
//			if (this.elem.children().length === 1) {
//				elem.parents("li:first").hide();
//				elem.find("input[type=checkbox]").prop("checked", true);
//				return;
//			}
			
			this.elemContainer = $("<div />").css({
				"position": "relative"
			}).insertBefore(elem);
			
			this.elemActivator = $("<a />", {
				"href": "#",
				"class": "rocket-translation-enabler-activator",
				"text": elem.data("active-locales-label")
			}).appendTo(this.elemContainer);
			
			this.elem.css({
				"position": "absolute",
				"left": "0",
				"top": this.elemActivator.outerHeight(true)
			}).appendTo(this.elemContainer).hide();
			
			(function(that: TranslationEnabler) {
				that.elemActivator.click(function(e) {
					e.preventDefault();
					e.stopPropagation();
					
					if (that.elemActivator.hasClass("rocket-open")) {
						that.hide();
					} else {
						that.show();
					}
				});
				
				that.elem.click(function(e) {
					e.stopPropagation();
				});
				
				this.elem.find("[data-locale-id]").each(function() {
					var elemLi = $(this),
						elemCheckbox = elemLi.children("input[type=checkbox]"),
						localeId = elemLi.data("locale-id");
					if (elemLi.data("mandatory")) {
						if (!elemCheckbox.prop("checked")) {
							elemCheckbox.prop("checked", true);
							that.triggerActivationCallbacks(localeId);	
						}
						
						elemCheckbox.clone().removeAttr("name").insertBefore(elemCheckbox).prop("disabled", true);
						elemCheckbox.removeAttr("id").hide();
					} else {
						elemCheckbox.change(function() {
							if (elemCheckbox.prop("checked")) {
								that.triggerActivationCallbacks(localeId);	
							} else {
								that.triggerDeactivationCallbacks(localeId);	
							}
						});
					}
				})
				
				that.elemProperties.addClass("rocket-translation-container");
				
				if (that.elemProperties.data("translation-enablers")) {
					that.elemProperties.data("translation-enablers").push(this);
				} else {
					that.elemProperties.data("translation-enablers", [this]);
				};
			}).call(this, this);
		}
		
		private hide() {
			this.elem.hide();
			this.elemActivator.removeClass("rocket-open");
			
			$(window).off("click.translationEnabler");	
		}
		
		private show() {
			var that = this;
			this.elem.show();
			this.elemActivator.addClass("rocket-open");
			$(window).on("click.translationEnabler", function() {
				that.hide();	
			});	
		}
		
		public activate(localeId) {
			this.elem.find("[data-locale-id=" + localeId + "] > input[type=checkbox]").each(function() {
				$(this).prop("checked", true).change();
			});
		}
		
		public isActive(localeId) {
			return this.elem.find("[data-locale-id=" + localeId + "] > input[type=checkbox]").prop("checked");	
		}
		
		private triggerActivationCallbacks(localeId: string) {
			this.activationCallbacks.forEach(function(activationCallback: (localeId: string) => void) {
				activationCallback(localeId);
			});
		}
		
		public registerActivationCallback(activationCallback: (localeId: string) => void) {
			this.activationCallbacks.push(activationCallback);
		}
		
		public registerDeactivationCallback(deactivationCallback: (localeId: string) => void) {
			this.deactivationCallbacks.push(deactivationCallback);
		}
		
		public triggerDeactivationCallbacks(localeId: string) {
			this.deactivationCallbacks.forEach(function(deactivationCallback: (localeId: string) => void) {
				deactivationCallback(localeId);
			});	
		}
		
		public getElem() {
			return this.elem;	
		}
	}
	
	class TranslationEnablerManager {
		private translationEnablers: Array<TranslationEnabler> = [];
		
		public initializeElement(elemTranslationEnabler: JQuery) {
			var that = this;
			elemTranslationEnabler.each(function() {
				that.translationEnablers.push(new TranslationEnabler($(this)));	
			});
		}
//		
//		public activate(localeId) {
//			this.translationEnablers.forEach(function(translationEnabler) {
//				translationEnabler.activate(localeId);
//			});
//		}
    }
	
	class NotSelectedTag {
		private elem : JQuery;
		
		public constructor(localeId, localeSelector: LocaleSelector) {
			this.elem = $("<li />", {
				"class": "rocket-locale-not-selected-" + localeId	
			}).append($("<span />", {
				text: localeSelector.getLocaleLabel(localeId)	
			}));
			
			(function(that: NotSelectedTag) {
				that.elem.click(function(e) {
					e.preventDefault();
					e.stopPropagation();
					localeSelector.selectLocaleWithId(localeId);
					that.elem.remove();
				});
			}).call(this, this);
		}
		
		public getElem() {
			return this.elem;	
		}
	}

	class SelectedTag {
		private elem;
		private elemText;
		private elemRemove;
		
		public constructor(localeId, localeSelector: LocaleSelector) {
			this.elem = $("<li />");
			this.elemText = $("<span />", {
				"text": localeSelector.getLocaleLabel(localeId)	
			}).appendTo(this.elem);
			
			(function(that: SelectedTag) {
				this.elemRemove = rocketTs.creatControlElem("Text: Remove Language", function() {
					that.elem.remove();
					localeSelector.removeSelectedLocaleWithId(localeId);
				}, "fa fa-times").removeClass("rocket-control").appendTo(this.elem);
				
				localeSelector.getElemUlSelectedContainer().on("localeChange", function() {
					if ($(this).children().length === 1) {
						that.elemRemove.hide();
					} else {
						that.elemRemove.show();
					}
				})
			}).call(this, this);
		}
		
		public getElem() {
			return this.elem;	
		}
	}
	
	class TranslationEntry {
		private elem: JQuery;
		private elemActivate: JQuery;
		private elemLocaleControls: JQuery;
		private localeId;
		private translationEnablers: Array<TranslationEnabler>;
		private active: boolean = true;
		private error: boolean;
		
		public constructor(localeSelector: LocaleSelector, elem: JQuery) {
			this.elem = elem;
			this.localeId = elem.data("locale-id");
			this.error = elem.hasClass("rocket-has-error")
			this.translationEnablers = elem.parents(".rocket-translation-container:first").data("translation-enablers") || null;
			
			(function(that: TranslationEntry) {
				
				localeSelector.registerSelectionCallback(function(localeId: string) {
					if (localeId !== that.localeId) return;
					that.show();
				});
				
				localeSelector.registerRemoveSelectionCallback(function(localeId: string) {
					if (localeId !== that.localeId) return;
					that.hide();
				});
				
				if (null !== this.translationEnabler) {
					that.elemLocaleControls = elem.find(".rocket-locale-controls:first");
					var entryFormCommand = new ui.EntryFormCommand("Activate " + localeSelector.getLocaleLabel(that.localeId), function() {
						that.translationEnablers.forEach(function(translationEnabler) {
							translationEnabler.activate(that.localeId);
						});
					}, "fa fa-language");
					
					that.elemActivate = entryFormCommand.getElemContainer().addClass("rocket-translation-activator");
					var active = false;
					that.translationEnablers.forEach(function(translationEnabler) {
						active = active || translationEnabler.isActive(that.localeId);
					});
					if (!active) {
						that.deactivate();
					}
					
					that.translationEnablers.forEach(function(translationEnabler) {
						translationEnabler.registerActivationCallback(function(localeId: string) {
							if (localeId !== that.localeId) return;
							that.activate();
						});
						
						translationEnabler.registerDeactivationCallback(function(localeId: string) {
							if (localeId !== that.localeId) return;
							that.deactivate();	
						});
						
					});
				}
			}).call(this, this);
		}
		
		public hasError() {
			return this.error;	
		}
		
		public show() {
			this.elem.show();
			//this.elemActivate.show();
		}
		
		public hide() {
			this.elem.hide();
			//this.elemActivate.hide();
		}
		
		private activate() {
			if (this.active) return;
			
			this.elemActivate.detach();
			this.elemLocaleControls.children().show();
			
			rocketTs.updateUi();
			this.active = true; 
		}
		
		private deactivate() {
			if (!this.active) return;
			
			this.elemLocaleControls.children().hide();
			this.elemActivate.prependTo(this.elemLocaleControls);
			this.active = false;
		}
	}
	
	class LocaleSelector {
		public static COOKIE_NAME_SELECTED_LOCALE_IDS = "selectedLocaleIds";
		
		private elemToolbar: JQuery;
		private localeLabels = {};
		private selectedLocaleIds: Array<string> = [];
		private notSelectedLocaleIds: Array<string> = [];
		private elemContainer: JQuery;
		private elemUlSelectedContainer: JQuery;
		private elemUlNotSelectedContainer: JQuery;
		private elemOpen: JQuery;
		private elemLabel: JQuery;
		private tem: TranslationEnablerManager;
		private initialized: boolean = false;
		
		private selectionCallbacks: Array<(localeId: string) => void> = [];
		private removeSelectionCallbacks: Array<(localeId: string) => void> = [];
		
		public constructor(tem: TranslationEnablerManager, languagesLabel: string,
				defaultLabel: string, translationsOnlyLabel: string) {
			this.tem = tem;
			this.elemToolbar = $("#rocket-toolbar");
			
			if (this.elemToolbar.length === 0) {
				this.elemToolbar = $("<div />", {"id": "rocket-toolbar"})
						.insertAfter($(".rocket-panel:first > h3:first"));	
			}
			
			this.elemContainer = $("<div />", {
				"class": "rocket-locale-selection"	
			}).appendTo(this.elemToolbar);
			
			this.elemUlSelectedContainer = $("<ul />", {
				"class": "rocket-selected-locales"	
			}).appendTo(this.elemContainer);

			this.elemLabel = $("<a />", {
				"text": languagesLabel,
				"href": "#"
			}).appendTo(this.elemContainer);
			
			this.elemUlNotSelectedContainer = $("<ul />", {
				"class": "rocket-not-selected-locales"	
			}).appendTo(this.elemContainer).hide();
			
			(function(that: LocaleSelector) {
				that.elemLabel.click(function(e) {
					e.preventDefault();
					e.stopPropagation();
					if (that.elemContainer.hasClass("open")) {
						that.close();
					} else { 
						that.open();
					}
				});
				
				var translationSwitch = new TranslationViewSwitch(defaultLabel, translationsOnlyLabel);
				translationSwitch.getElemContainer().prependTo(this.elemToolbar);
			}).call(this, this);
		}
		
		public initialize() {
			this.initialized = true;
			this.initSelectedLocales();
			this.initNotSelectedLocales();
		}
				
		private initSelectedLocales() {
			var that = this;
			this.getSavedLocaleIds().forEach(function(localeId) {
				that.selectLocaleWithId(localeId);
			});
		}
		
		private initNotSelectedLocales() {
			var selectedLocaleId = null, 
				that = this;
			this.notSelectedLocaleIds.forEach(function(localeId) {
				if (that.selectedLocaleIds.length === 0 
						&& null === selectedLocaleId) {
					//If no locale is selected then select the first one					
					
					//need to remember it here, if you push it directly, the array 
					//will change internaly and the element after will be ignored
					selectedLocaleId = localeId;
				} else {
					that.addNotSelectedLocaleWithId(localeId);
				}
			});
			
			if (null !== selectedLocaleId) {
				that.selectLocaleWithId(selectedLocaleId);
			}
		}
		
		public hasLocaleId(localeId) {
			return this.isLocaleIdSelected(localeId) || (this.notSelectedLocaleIds.indexOf(localeId) >= 0);
		}
		
		public getLocaleLabel(localeId: string) {
			if (!this.localeLabels.hasOwnProperty(localeId)) return localeId;
			
			return this.localeLabels[localeId];
		}
		
		public initializeLocalizedElems(localizedElem: JQuery) {
			var that = this;
			
			localizedElem.each(function() {
				var elem = $(this),
					localeId = elem.data("locale-id");
				
				if (!that.hasLocaleId(localeId)) {
					that.notSelectedLocaleIds.push(localeId);
					that.localeLabels[localeId] = elem.data("pretty-locale-id");
				}
				var translationEntry = new TranslationEntry(that, elem);
				
				if (!that.isLocaleIdSelected(localeId) && !translationEntry.hasError()) {
					translationEntry.hide()
				}
				
				if (that.initialized) return;
				
				that.initSelectedLocales();
				that.initNotSelectedLocales();	
			});
			
//			if (that.notSelectedLocaleIds.length <= 1) {
//				//just one locale is available -> show elements like not translatable 	
//				$(".rocket-properties [data-locale-id]").each(function() {
//					var elem = $(this).show();	
//					//elem.show().children("label:first").remove();
//					//elem.parent().replaceWith(elem.children("div.rocket-controls").contents());
//				});
				
//				//that.elemContainer.remove();
//				if (that.elemToolbar.children().length === 1) {
//					this.elemToolbar.remove();	
//				}
//				return;
//			}
		}
		
		public open() {
			if (this.notSelectedLocaleIds.length === 0) return;
			
			this.elemContainer.addClass("open");
			this.elemUlNotSelectedContainer.show();
			
			var that = this;
			$(window).off("click.localeSelector").on("click.localeSelector", function() {
				that.close();
			});
		}
		
		public close() {
			$(window).off("click.localeSelector")	
			this.elemContainer.removeClass("open");
			this.elemUlNotSelectedContainer.hide();
		}
		
		public getElemUlSelectedContainer() {
			return this.elemUlSelectedContainer;	
		}
		
		private getSavedLocaleIds() {
			var	cookieValue = rocketTs.getCookie(LocaleSelector.COOKIE_NAME_SELECTED_LOCALE_IDS);
			if (!cookieValue) return [];
			
			return cookieValue.split(",");
		}
		
		private saveState() {
			var savedLocaleIds = this.getSavedLocaleIds();
			
			this.selectedLocaleIds.forEach(function(value) {
				if (savedLocaleIds.indexOf(value) !== -1) return;
				savedLocaleIds.push(value);
			});
			
			this.notSelectedLocaleIds.forEach(function(value) {
				if (savedLocaleIds.indexOf(value) === -1) return;
				savedLocaleIds.splice(savedLocaleIds.indexOf(value), 1);
			});
			
			rocketTs.setCookie(LocaleSelector.COOKIE_NAME_SELECTED_LOCALE_IDS, savedLocaleIds.join(","));
			
			this.elemUlSelectedContainer.trigger("localeChange");
		}
		
		public isLocaleIdSelected(localeId) {
			return this.selectedLocaleIds.indexOf(localeId) >= 0;
		}
		
		public registerSelectionCallback(selectionCallback: (localId: string) => void) {
			this.selectionCallbacks.push(selectionCallback);
		}
		
		public triggerSelectionCallbacks(localeId) {
			this.selectionCallbacks.forEach(function(selectionCallback) {
				selectionCallback(localeId);
			});
		}
		
		public selectLocaleWithId(localeId) {
			if (this.notSelectedLocaleIds.indexOf(localeId) === -1) return;
			
			var selectedTag = new SelectedTag(localeId, this);
			this.elemUlSelectedContainer.append(selectedTag.getElem());
			this.selectedLocaleIds.push(localeId);
			this.notSelectedLocaleIds.splice(this.notSelectedLocaleIds.indexOf(localeId), 1);
			
			// this.tem.activate(localeId);
			this.triggerSelectionCallbacks(localeId);
			
			
			this.saveState();
			if (this.notSelectedLocaleIds.length === 0) {
				this.close();	
			}
		}
		
			
		public registerRemoveSelectionCallback(removeSelectionCallback: (localId: string) => void) {
			this.removeSelectionCallbacks.push(removeSelectionCallback);
		}
		
		public triggerRemoveSelectionCallbacks(localeId) {
			this.removeSelectionCallbacks.forEach(function(removeSelectionCallback) {
				removeSelectionCallback(localeId);
			});
		}
		
		public removeSelectedLocaleWithId(localeId) {
			if (this.selectedLocaleIds.indexOf(localeId) === -1) return;
			
			this.notSelectedLocaleIds.push(localeId);
			this.selectedLocaleIds.splice(this.selectedLocaleIds.indexOf(localeId), 1);
			this.addNotSelectedLocaleWithId(localeId);
			
			this.triggerRemoveSelectionCallbacks(localeId);
			//this.tem.deactivate(localeId);
			
			this.saveState();
		}
		
		private addNotSelectedLocaleWithId(localeId) {
			if (this.elemUlNotSelectedContainer.children("li.rocket-locale-not-selected-" + localeId).length > 0) return;
			
			var selectedTag = new NotSelectedTag(localeId, this);
			this.elemUlNotSelectedContainer.append(selectedTag.getElem());
		}
	}
	rocketTs.ready(function() {
		var localeSelector: LocaleSelector = null,
			tem = new TranslationEnablerManager();
		
		rocketTs.registerUiInitFunction(".rocket-translation-enabler", function(elem: JQuery) {
			tem.initializeElement(elem);
		});
		
		rocketTs.registerUiInitFunction(".rocket-properties > [data-locale-id]", function(localizedElem: JQuery) {
			if (null === localeSelector) {
				var languagesLabel = null,
					translationsOnlyLabel = null,
					defaultLabel = null,
					elemTranslatableContent = $(".rocket-translatable-content:first");
				if (elemTranslatableContent.length > 0) {
					languagesLabel = elemTranslatableContent.data("languages-label");
					defaultLabel = elemTranslatableContent.data("standard-label");
					translationsOnlyLabel = elemTranslatableContent.data("translations-only-label"); 
				}
				
				if (null === languagesLabel) {
					var elemTranslationEnabler = $(".rocket-translation-enabler:first");
					languagesLabel = elemTranslationEnabler.data("languages-label");
					defaultLabel = elemTranslationEnabler.data("standard-label");
					translationsOnlyLabel = elemTranslationEnabler.data("translations-only-label"); 
				}
				
				if (null !== languagesLabel) {
					localeSelector = new LocaleSelector(tem, languagesLabel, defaultLabel, translationsOnlyLabel);
				} else {
					throw new Error("no languages label found");	
				}
			}
			
			if (null === localeSelector) return;
			localeSelector.initializeLocalizedElems(localizedElem);
		});
		
		if (null !== localeSelector) {
			localeSelector.initialize();
		}
	});
}
