/// <reference path="..\..\rocket.ts" />
/// <reference path="..\common.ts" />
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
module spec.edit {
	$ = jQuery;

	class ToOneCurrent {
		private toOne: ToOne;
		private elem: JQuery;
		private elemContent: JQuery;
		private elemRemove: JQuery = null;
		private elemReplace: JQuery = null;
		private toOneNew: ToOneNew = null;
		private removable: boolean = null;
		private replaceItemLabel: string = null;
		private elemUlReplaceControlOptions: JQuery = null;
		private eiSpecId: string;
	
		public constructor(toOne: ToOne, elem: JQuery, 
				removable: boolean, toOneNew: ToOneNew = null, replaceItemLabel: string = null) {
			this.toOne = toOne;
			this.elem = elem;
			this.toOneNew = toOneNew;
			this.replaceItemLabel = replaceItemLabel;
			
			this.elemContent = elem.find(".rocket-to-one-content");
			this.eiSpecId = elem.data("ei-spec-id");
			
			(function(that: ToOneCurrent) {
				that.setRemovable(removable);
				if (null !== toOneNew) {
					if (toOneNew.isAvailabale()) {
						//form was sent and has errors
						that.remove();	
					} else {
						toOneNew.setToOneCurrent(that);
						toOneNew.getElemAdd().hide();
						toOneNew.applyAdd(replaceItemLabel, function() {
							that.resetControls();
							that.remove();
						});
					}
				}
			}).call(this, this);
		}
		
		public setLoading() {
			this.elemContent.hide();
			this.elem.append(rocketTs.createLoadingElem());
		}
		
		public getEiSpecId() {
			return this.eiSpecId;	
		}
	
		public resetControls() {
			if (null !== this.elemRemove) {
				this.elemRemove.parent().remove();
				this.elemRemove = null;
			}
			
			if (null !== this.elemReplace) {
				this.elemReplace.parent().remove();
				this.elemReplace = null;
			}
		}
		
		public getElemContent() {
			return this.elemContent;	
		}
		
		public setRemovable(removable) {
			if (removable === this.removable) return;
			
			var that = this;
			if (removable) {
				if (null !== this.toOneNew) {
					var replaceControl = new ToOneRecycleControl(that.toOne, function(eiSpecId: string) {
						that.setLoading();
						that.toOneNew.activate(eiSpecId);
					}, function() {
						that.setLoading();
						that.toOneNew.activate();
					});
					
					replaceControl.setConfirmMessage(this.elem.data("replace-confirm-msg"));
					replaceControl.setConfirmOkLabel(this.elem.data("replace-ok-label"));
					replaceControl.setConfirmCancelLabel(this.elem.data("replace-cancel-label"));
					
					this.elemReplace = replaceControl.getControlElem();
					
					if (this.toOneNew.isRemovable()) {
						this.elemRemove = this.toOne.addControl(this.elem.data("remove-item-label"), function() {
							that.remove();
							that.resetControls();
							that.toOneNew.getElemAdd().show();
						}, 'fa fa-times');
					}
				} else {
					this.elemRemove = this.toOne.addControl(this.elem.data("remove-item-label"), function() {
						that.remove();
					}, 'fa fa-times');
				}
			} else {
				if (null !== this.elemRemove) {
					this.elemRemove.parent().remove();
					this.elemRemove = null;
				}
				
				if (null !== this.elemReplace) {
					this.elemReplace.parent().remove();
					this.elemReplace = null;
				}
			}
			
			this.removable = removable;
		}
		
		private remove() {
			this.elem.remove();
		}
	}
	
	class ToOneSelectorStackedContent implements ui.StackedContent {
		private elemContent: JQuery;
		private toOneSelector: ToOneSelector;
		private stackedContentContainer: ui.StackedContentContainer = null;
		private overviewTools: spec.OverviewTools = null;
		private startObservingOnLoad: boolean = false;
		private idClassName: string;
		private static lastId: number = 0;
		
		public constructor(toOneSelector: ToOneSelector, elemContent: JQuery) {
			this.idClassName = "rocket-to-one-stacked-content-" + ++ToOneSelectorStackedContent.lastId;
			this.elemContent = $("<div />", {
				"class": "rocket-to-one-stacked-content "  + this.idClassName
			}).append($("<div />", {
				"class": "rocket-panel"
			}).append(elemContent));
			
			this.toOneSelector = toOneSelector;
			
			(function(that: ToOneSelectorStackedContent) {
				elemContent.on('overview.contentLoaded', function(e, overviewTools: spec.OverviewTools) {
					that.overviewTools = overviewTools;
					overviewTools.getFixedHeader().setApplyDefaultFixedContainer(false);
					if (null !== that.stackedContentContainer) {
						overviewTools.setElemFixedContainer(that.stackedContentContainer.getElem(), that.startObservingOnLoad);
					}
					
					that.overviewTools.setSelectable(false);
				});
				
				rocketTs.registerUiInitFunction("." + that.idClassName  + " .rocket-overview-content:first > tr > .rocket-entry-selector", function(elem: JQuery) {
					var id = elem.data("entry-id-rep"),
						identityString = elem.data("identity-string");
					rocketTs.creatControlElem(toOneSelector.getSelectLabel() + " (" + identityString + ")", function() {
						var identityStrings = {};
						identityStrings[id] = identityString;
						toOneSelector.applyIdentityStrings(identityStrings);
						that.stackedContentContainer.close();
					}).appendTo(elem);
					$(window).trigger('resize.overview');
				});
			}).call(this, this);
		}
		
		public getTitle() {
			return "test";
		};
		
		public getElemContent() {
			return this.elemContent;	
		}
		
		public setup(stackedContentContainer: ui.StackedContentContainer) {
			this.stackedContentContainer = stackedContentContainer;
			if (null !== this.overviewTools) {
				this.overviewTools.setElemFixedContainer(stackedContentContainer.getElem(), false);
			}
		}
		
		public onAnimationComplete() {
			if (null !== this.overviewTools) {
				this.overviewTools.getFixedHeader().startObserving();
			} else {
				this.startObservingOnLoad = false;
			}
 		}
		
		public onClose() {
			this.overviewTools.getFixedHeader().reset();
			this.overviewTools.getFixedHeader().stopObserving();
		}
		
		public onClosed() {
			this.stackedContentContainer.getElem().scrollTop(0);
			this.startObservingOnLoad = false;
		}
	}
	
	class ToOneSelector {
		private elem: JQuery;
		private overviewToolsUrl: string;
		private elemInput: JQuery;
		private elemLabel: JQuery;
		private elemLabelContainer: JQuery;
		private elemBtnSelect: JQuery;
		private identityStrings: Object;
		private originalIdRep: string;
		
		private elemSelect: JQuery = null;
		private elemReset: JQuery = null;
		private elemRemove: JQuery = null;
		private preloadedStackedContent: ToOneSelectorStackedContent = null;
		private selectLabel: string;
		private resetLabel: string;
		
		public constructor(elem: JQuery, removeItemLabel: string) {
			this.elem = elem;
			this.overviewToolsUrl = elem.data("overview-tools-url");
			this.elemInput = elem.find("input:first").hide();
			this.identityStrings = elem.data("identity-strings") || null;
			this.originalIdRep = elem.data("original-id-rep");
			this.selectLabel = elem.data("select-label");
			this.resetLabel = elem.data("reset-label");
			this.elemLabel = $("<span />");
			
			(function(that: ToOneSelector) {
				that.elemLabelContainer = $("<div />", {
					"class": "rocket-relation-label-container"	
				}).append(that.elemLabel).insertAfter(that.elemInput);
				
				that.elemRemove = rocketTs.creatControlElem(removeItemLabel, function() {
					that.applyIdentityStrings(null);
					that.elemLabelContainer.detach();
				}, "fa fa-times").appendTo(that.elemLabelContainer);
				
				that.applyIdentityStrings(that.getIdentityString(that.elemInput.val()));
				that.initControls();
				
			}).call(this, this);
		}
		
		private getIdentityString(idRep) {
			if (!idRep) return null;
			
			var identityString = {};
			identityString[idRep] = this.identityStrings[idRep];
			
			return identityString;
		}
		
		public getSelectLabel() {
			return this.selectLabel;	
		}
		
		private initControls() {
			var that = this,
				elemControls = $("<div />", {
				"class": "rocket-to-one-controls"
			}).insertAfter(this.elemLabelContainer);
			
			this.elemBtnSelect = rocketTs.creatControlElem(this.selectLabel, function() {
				rocketTs.getContentStack().addStackedContent(that.preloadedStackedContent);
				rocketTs.updateUi();
			}).appendTo(elemControls).addClass("rocket-loading").prop("disabled", true);
			
			this.elemReset = rocketTs.creatControlElem(this.resetLabel, function() {
				that.applyIdentityStrings(that.getIdentityString(that.originalIdRep));
			}).appendTo(elemControls);
			
			that.loadOverlay();
		}
		
		public loadOverlay() {
			var that = this;
			$.getJSON(this.overviewToolsUrl, function(data) {
				that.preloadedStackedContent = new ToOneSelectorStackedContent(that, rocketTs.analyzeAjahData(data));
				that.preloadedStackedContent.getElemContent().appendTo($("body"));
				rocketTs.updateUi();
				that.preloadedStackedContent.getElemContent().detach();
				that.elemBtnSelect.removeClass("rocket-loading").prop("disabled", false);
			});
		}
		
		public applyIdentityStrings(identityObject: Object = null) {
			var that = this;
			this.elemLabelContainer.insertAfter(this.elemInput);
			
			if (null === identityObject) {
				this.elemInput.val(null);
				this.elemLabel.empty();
			} else {
				$.each(identityObject, function(id, label) {
					that.elemInput.val(id);
					that.elemLabel.text(label);
					return false;
				});
			}
			
			if (!this.elemInput.val()) {
				this.elemRemove.hide();
			} else {
				this.elemRemove.show();	
			}
		}
	}
	
	class ToOneNew {
		private toOne: ToOne;
		private elem: JQuery;
		private newEntryFormUrl: string;
		private propertyPath: string;
		private removeItemLabel: string;
		private addItemLabel: string;
		private elemRemove: JQuery = null;
		private elemEntryFormContainer: JQuery;
		private entryHeader: EntryHeader;
		private removable: boolean = null;
		private entryFormLoaded: boolean = false;
		private activated: boolean = false;
		private addCallback: () => void = null;
		private elemEntryForm: JQuery = null;
		private toOneCurrent: ToOneCurrent = null;
		private elemScriptTypeSelector: JQuery = null;
		private elemTypeSelection: JQuery = null;
		private elemUlTypes: JQuery = null;
		private entryFormCommandAdd: ui.EntryFormCommand = null;
		
		public constructor(toOne: ToOne, elem: JQuery, removable: boolean) {
			this.toOne = toOne;
			this.elem = elem;
			this.newEntryFormUrl = elem.data("new-entry-form-url");
			this.removeItemLabel = elem.data("remove-item-label");
			this.addItemLabel = elem.data("add-item-label");
			this.propertyPath = elem.data("property-path");
			
			var elemContents = elem.children();
			this.elemEntryFormContainer = $("<div />");
			
			(function(that: ToOneNew) {
				if (elemContents.length > 0) {
					that.initializeContent(elemContents);
					that.activated = true;
					that.entryFormLoaded = true;
					if (that.hasTypeSelection()) {
						if (!elem.data("prefilled")) {
							that.applyEiSpecId(that.elemTypeSelection.val());							
						} else {
							that.initializeAdd();
						}
					} else {
						that.elemEntryFormContainer.appendTo(elem);
					}
				} else {
					that.initializeAdd();
					that.elemEntryFormContainer.hide();
//					that.requestNewEntryForm(function() {
//						if (!that.removable && null === that.toOneCurrent) {
//							that.activate();
//							return;
//						}
//						
//						if (that.activated) {
//							that.elemEntryFormContainer.appendTo(elem);;
//						}
//					});
				}
				
				that.setRemovable(removable);
			}).call(this, this);
		}
		
		public getElem() {
			return this.elem;	
		}
		
		public getElemAdd() {
			return this.entryFormCommandAdd.getElemContainer();	
		}
		
		private initTypeSelction() {
			this.elemScriptTypeSelector = this.elemEntryForm.find("> .rocket-script-type-selector"),
			this.elemTypeSelection = this.elemScriptTypeSelector.find(".rocket-script-type-selection");
		}
		
		private hasTypeSelection() {
			return this.elemScriptTypeSelector.length > 0 && this.elemScriptTypeSelector.length > 0;
		}
		
		public setToOneCurrent(toOneCurrent: ToOneCurrent) {
			this.toOneCurrent = toOneCurrent;	
		}
		
		public isAvailabale() {
			return this.activated;	
		}
		
		private initializeContent(elemEntryForm: JQuery) {
			this.elemEntryForm = elemEntryForm;
			this.initTypeSelction();
			this.elemEntryFormContainer.append(elemEntryForm);
			this.elemEntryFormContainer.children(".rocket-to-many-order-index").hide();
			
			var that = this;
			
			if (this.removable) {
				this.elemRemove = this.toOne.addControl(this.removeItemLabel, function() {
					that.elemEntryFormContainer.detach();
					that.initializeAdd();
				}, "fa fa-times");
			}
			
			if (this.hasTypeSelection()) {
				var toOneRecyleControl = new ToOneRecycleControl(that.toOne, function(eiSpecId: string) {
					that.applyEiSpecId(eiSpecId);
				});
				
				toOneRecyleControl.setExcludeEiSpecIdCallback(function(eiSpecId: string) {
					if (eiSpecId === that.elemTypeSelection.val()) return true;
					
					return false;
				});
			}
		}
		
		private initializeAdd() {
			var that = this;
			if (null === this.entryFormCommandAdd) {
				this.entryFormCommandAdd = new ui.EntryFormCommand(this.addItemLabel, function() {
					if (!that.toOne.isTypeSelectable()) {
						that.activate();
						return;
					}
					
					var elemButton = that.entryFormCommandAdd.getElemButton();
					if (elemButton.hasClass("rocket-command-insert-open")) {
						that.elemUlTypes.hide();
						elemButton.removeClass("rocket-command-insert-open");
						return;
					}
					
					if (null === that.elemUlTypes) {
						that.elemUlTypes = $("<ul />", {
							"class": "rocket-dd-menu-open"	
						}).insertAfter(elemButton);
					} else {
						that.elemUlTypes.empty();	
					}
					elemButton.addClass("rocket-command-insert-open");
					that.toOne.createTypeElemLis(function(eiSpecId: string) {
						that.activate(eiSpecId)
						elemButton.removeClass("rocket-command-insert-open");
						that.elemUlTypes.hide();
					}).forEach(function(elemLi) {
						elemLi.appendTo(that.elemUlTypes).children("a").removeClass();
					})
					
				}, "fa fa-plus");
			}
			
			this.entryFormCommandAdd.getElemContainer().appendTo(this.elem);
		}
		
		public setRemovable(removable: boolean) {
			if (this.removable === removable) return;
			
			var that = this;
			if (removable) {
				var that = this;
				this.initializeAdd();
			} else {
				if (this.activated) {
					this.activate();
				}
				
				if (null !== this.elemRemove) {
					this.elemRemove.parent().remove();
					this.elemRemove = null;
				} 
			}
			this.removable = removable;
		}
		
		public isRemovable() {
			return this.removable;	
		}
		
		public applyAdd(addItemLabel, callback: () => void) {
			this.addItemLabel = addItemLabel;
			this.entryFormCommandAdd.getElemButton().text(addItemLabel);
			
			this.addCallback = callback;
		}
		
		private applyEiSpecId(eiSpecId: string) {
			if (this.elemTypeSelection.children("option[value=" + eiSpecId + "]").length === 0) return;
			
			this.elemScriptTypeSelector.hide();
			this.toOne.setTypeSpecLabel(this.toOne.determineEiSpecLabel(eiSpecId));
			this.elemTypeSelection.val(eiSpecId).change();

			this.elem.trigger('applyEiSpecId.toOne', eiSpecId);
		}
		
		public activate(eiSpecId: string = null) {
			var that = this;
			this.requestNewEntryForm(function() {
				that.elemEntryFormContainer.appendTo(that.elem);
				rocketTs.updateUi();
				
				if (null !== eiSpecId && that.hasTypeSelection()) {
					that.applyEiSpecId(eiSpecId);
				}
				
				if (null !== that.entryFormCommandAdd) {
					that.entryFormCommandAdd.getElemContainer().detach();
				}
				
				if (null !== that.addCallback) {
					that.addCallback();	
				}
			});
			this.activated = true;
		}
		
		private requestNewEntryForm(callback: () => void) {
			var that = this;
			
			if (this.entryFormLoaded || this.activated) {
				if (null !== this.elemEntryForm) {
					this.elemEntryForm.appendTo(this.elemEntryFormContainer);
					callback();
				}
				return;	
			}
			
			this.entryFormLoaded = true;
			if (null !== this.entryFormCommandAdd) {
				this.entryFormCommandAdd.setLoading(true);
			}
			
			$.getJSON(this.newEntryFormUrl, {propertyPath: this.propertyPath}, function(data) {
				that.initializeContent(rocketTs.analyzeAjahData(data));
				rocketTs.updateUi();
				that.elemEntryFormContainer.show();
				if (null !== that.entryFormCommandAdd) {
					that.entryFormCommandAdd.setLoading(false);
				}
				callback();
			});
		}
	}
	
	class ToOneRecycleControl {
		private toOne: ToOne;
		private controlElem: JQuery;
		private elemUlReplaceControlOptions: JQuery = null;
		
		private typeCallback: (eiSpecId: string) => void;
		private defaultCallback: () => void;
		private excludeEiSpecIdCallback: (eiSpecId: string) => boolean = null;
		
		private confirmMessage: string = null;
		private confirmOkLabel: string = null;
		private confirmCancelLabel: string = null;
		
		public constructor(toOne: ToOne, typeCallback: (eiSpecId: string) => void, 
				defaultCallback: () => void = null) {
			this.toOne = toOne;
			this.typeCallback = typeCallback;
			this.defaultCallback = defaultCallback;
			
			(function(that: ToOneRecycleControl) {
				that.controlElem = this.toOne.addControl(this.replaceItemLabel, function() {
					if (!that.toOne.isTypeSelectable()) {
						if (null !== defaultCallback) {
							defaultCallback();
							return;
						}
					}
					
					if (null !== that.confirmMessage 
							&& (null === that.elemUlReplaceControlOptions 
									|| (null !== that.elemUlReplaceControlOptions 
											&& !that.elemUlReplaceControlOptions.hasClass("rocket-open")))) {
						var dialog = new ui.Dialog(that.confirmMessage);
						dialog.addButton(that.confirmOkLabel, function(e) {
							e.stopPropagation();
							that.initList();
						});
						
						dialog.addButton(that.confirmCancelLabel, function() {
							//defaultbehaviour is to close the dialog
						});
						rocketTs.showDialog(dialog);
						return;
					}
					
					that.initList();
				}, 'fa fa-recycle').addClass("rocket-control-danger");
			}).call(this, this);
		}
		
		private initList() {
			var that = this;
			if (null === this.elemUlReplaceControlOptions) {
				this.elemUlReplaceControlOptions = $("<ul />", {
					"class": "rocket-control-options"
				}).insertAfter(this.controlElem); 	
			} else {
				if (this.elemUlReplaceControlOptions.hasClass("rocket-open")) {
					this.hideList();
					return;	
				}
				this.elemUlReplaceControlOptions.empty();
			}
			console.log(this.elemUlReplaceControlOptions);
			this.showList();
			
			this.toOne.createTypeElemLis(function(eiSpecId: string) {
				that.typeCallback(eiSpecId);
				that.hideList();
			}, this.excludeEiSpecIdCallback).forEach(function(elemLi: JQuery) {
				elemLi.appendTo(that.elemUlReplaceControlOptions)
			});	
			
			var elemReplacePosition = this.controlElem.position();
			this.elemUlReplaceControlOptions.css({
				"position": "absolute",
				"zIndex": 2,
				"top": elemReplacePosition.top + this.controlElem.outerHeight(),
				"left": elemReplacePosition.left + this.controlElem.outerWidth() 
						- this.elemUlReplaceControlOptions.outerWidth()
			});
		}
		
		public setConfirmMessage(confirmMessage: string = null) {
			this.confirmMessage = confirmMessage;	
		}
		
		public setConfirmOkLabel(confirmOkLabel: string = null) {
			this.confirmOkLabel = confirmOkLabel;	
		}
		
		public setConfirmCancelLabel(confirmCancelLabel: string = null) {
			this.confirmCancelLabel = confirmCancelLabel;	
		}
		
		private hideList() {
			this.elemUlReplaceControlOptions.removeClass("rocket-open");
			this.elemUlReplaceControlOptions.hide();
			$(window).off('off.toOneRecycle');
		}
		
		private showList() {
			this.elemUlReplaceControlOptions.addClass("rocket-open").show();
			var that = this;
			$(window).on('click.toOneRecycle', function(e) {
				if ($(e.target).is(that.controlElem) || $.contains(that.controlElem.get(0), e.target)) return;
				that.hideList();
			});
		}
		
		public setExcludeEiSpecIdCallback(excludeEiSpecIdCallback: (eiSpecId: string) => boolean = null) {
			this.excludeEiSpecIdCallback = excludeEiSpecIdCallback;	
		}
		
		public getControlElem(): JQuery {
			return this.controlElem;	
		}
	}
	
	class TypeConfig {
		private label: string;
		private callback: (toOne: ToOne) => void;
		
		public constructor(label, callback: (toOne: ToOne) => void) {
			this.label = label;
			this.callback = callback;
		}
		
		public getLabel() {
			return this.label;	
		}
		
		public getCallback() {
			return this.callback;	
		}
	}
	
	class EiSpecConfig {
		private specId: string;
		private additionalTypeConfigs: Array<TypeConfig> = [];
		
		public constructor(specId: string) {
			this.specId = specId;
		}
		
		public registerTypeConfig(label: string, callback: (toOne: ToOne) => void) {
			this.additionalTypeConfigs.push(new TypeConfig(label, callback));
		}
		
		public getAdditionalTypeConfigs(): Array<TypeConfig> {
			return this.additionalTypeConfigs;
		}
		
		public reset() {
			this.additionalTypeConfigs = [];	
		}
	}
	
	class ToOne {
		private elem: JQuery;
		private mandatory: boolean = false;
		private toOneNew: ToOneNew = null;
		private toOneCurrent: ToOneCurrent = null;
		private toOneSelector: ToOneSelector = null;
		private itemLabel: string;
		private replaceItemLabel: string;
		private removeItemLabel: string;
		private eiSpecLabels: Object;
		private typeSelectable: boolean;
		private elemLabel: JQuery;
		private defaultLabel: string;
		private elemUlControls: JQuery = null;
		private eiSpecConfigs: Object = {};
		
		public constructor(elem: JQuery) {
			this.elem = elem;
			this.mandatory = elem.data("mandatory") || false;
			this.itemLabel = elem.data("item-label");
			
			this.replaceItemLabel = elem.data("replace-item-label");
			this.removeItemLabel = elem.data("remove-item-label");
			this.eiSpecLabels = elem.data("ei-spec-labels");
			this.elemLabel = elem.parent(".rocket-controls:first").prev("label");
			this.defaultLabel = this.elemLabel.text();
			
			this.typeSelectable = false;
			
			(function(that: ToOne) {
				var numTypes = 0; 
				$.each(this.eiSpecLabels, function() {
					numTypes++;
				});
				
				that.typeSelectable = numTypes > 1;
				
				var elemNew = elem.children(".rocket-new:first");
				if (elemNew.length > 0) {
					that.toOneNew = new ToOneNew(that, elemNew, !that.mandatory);
				}
				
				var elemCurrent = elem.children(".rocket-current:first");
				if (elemCurrent.length > 0) {
					if (that.typeSelectable) {
						that.setTypeSpecLabel(elemCurrent.data("item-label"));
					}
					that.toOneCurrent = new ToOneCurrent(that, elemCurrent,
							null !== that.toOneNew || !that.mandatory, that.toOneNew, 
							that.replaceItemLabel);
				}
				
				var elemSelector = elem.children(".rocket-selector:first");
				if (elemSelector.length > 0) {
					new ToOneSelector(elemSelector, that.removeItemLabel);
				}
				
			}).call(this, this);
		}
		
		public isTypeSelectable() {
			return this.typeSelectable;
		}
		
		public getEiSpecLabels() {
			return this.eiSpecLabels;	
		}
		
		public setEiSpecLabels(eiSpecLabels: Object) {
			this.eiSpecLabels = eiSpecLabels;	
		}
		
		public determineEiSpecLabel(eiSpecId) {
			return this.eiSpecLabels[eiSpecId];	
		}
		
		public setTypeSpecLabel(typeSpecLabel: string) {
			this.elemLabel.text(this.defaultLabel + ": " + typeSpecLabel);
		}
		
		public getToOneCurrent() {
			return this.toOneCurrent;	
		}
		
		public getToOneNew() {
			return this.toOneNew;	
		}
		
		public addControl(text: string, callback: () => void, iconClassName: string) {
			if (null === this.elemUlControls) {
				this.elemUlControls = $("<ul />", {
					"class": "rocket-simple-commands"	
				}).css({
					"position": "absolute",
					"top": "0",
					"right": "0"	
				}).insertAfter(this.elemLabel);
			}
			
			var elemControl = rocketTs.creatControlElem(text, callback, iconClassName)
			$("<li />").append(elemControl)
					.appendTo(this.elemUlControls);
			
			return elemControl;
		}
		
		public setMandatory(mandatory: boolean) {
			this.mandatory = mandatory;
			
			if (null !== this.toOneCurrent) {
				this.toOneCurrent.setRemovable(null !== this.toOneNew || !mandatory);
			}
			
			if (null !== this.toOneNew) {
				this.toOneNew.setRemovable(!mandatory);	
			}
		}
		
		public getElem() {
			return this.elem;	
		}
		
		public hasEiSpecConfig(eiSpecId: string) {
			return this.eiSpecConfigs.hasOwnProperty(eiSpecId);
		}
		
		public getOrCreateEiSpecConfig(eiSpecId: string): EiSpecConfig {
			if (!this.eiSpecLabels.hasOwnProperty(eiSpecId)) {
				throw new Error("Invalid ei spec id: " + eiSpecId);	
			}
			
			if (!this.eiSpecConfigs.hasOwnProperty(eiSpecId)) {
				this.eiSpecConfigs[eiSpecId] = new EiSpecConfig(eiSpecId);
			}
			
			return this.eiSpecConfigs[eiSpecId];
		}
		
		public createTypeElemLis(typeCallback: (eiSpecId: string) => void, 
				excludeEiSpecIdCallback: (eiSpecId: string) => boolean = null) : Array<JQuery> {
			var lis = [], 
					that = this;
					
			$.each(that.getEiSpecLabels(), function(eiSpecId, label) {
				if (that.hasEiSpecConfig(eiSpecId)) {
					var eiSpecConfig = that.getOrCreateEiSpecConfig(eiSpecId); 
					eiSpecConfig.getAdditionalTypeConfigs().forEach(function(typeConfig: TypeConfig) {
						lis.push(that.createTypeElemLi(typeConfig.getLabel(), function() {
							typeConfig.getCallback()(that);
							typeCallback(eiSpecId);
						}));
					});
					return;
				}
				
				if (null !== excludeEiSpecIdCallback) {
					if (excludeEiSpecIdCallback(eiSpecId)) return;	
				}
				
				lis.push(that.createTypeElemLi(label, function() {
					typeCallback(eiSpecId);
				}));
			});
			
			lis.sort(function(elemLiA: JQuery, elemLiB: JQuery) {
				if (elemLiA.data("sort") < elemLiB.data("sort")) return -1;
				if (elemLiA.data("sort") == elemLiB.data("sort")) return 0;
				
				return 1;
			});
			
			return lis;
		}
		
				
		private createTypeElemLi(label: string, callback: () => void) {
			return $("<li />").append(rocketTs.creatControlElem(label, function() {
				callback();	
			})).data("sort", label);
		}
	}
	
	$(document).ready(function() {
		rocketTs.registerUiInitFunction("form .rocket-to-one", function(elem: JQuery) {
			elem.data('rocket-to-one', new ToOne(elem));
			elem.trigger('initialized.toOne', elem.data('rocket-to-one'));
		});
	});
}
