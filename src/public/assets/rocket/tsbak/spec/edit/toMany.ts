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
 * 
 */
module spec.edit {
	$ = jQuery;
	
	class ToManyAdd {
		private toMany: ToMany;
		private toManyEntryForm: ToManyEntryForm = null;
		private elemDiv: JQuery;
		private elemButton: JQuery;
		private elemUlTypes: JQuery;
		
		public constructor(toMany: ToMany, toManyEntryForm: ToManyEntryForm = null) {
			this.toMany = toMany;
			this.elemDiv = $("<div />", {
				"class": "rocket-entry-form-command"
			});
			
			this.toManyEntryForm = toManyEntryForm;
			
			(function(that: ToManyAdd) {
				that.elemButton = $("<button />", {
					"text": toMany.getAddItemLabel(),
					"class": "rocket-control rocket-control-full"
				}).prepend($("<i />", {
					"class": "fa fa-plus"	
				})).click(function(e) {
					e.preventDefault();
					if (!toMany.hasTypes()) {
						that.requestNewEntryForm();
						return;
					}
					
					if (that.elemButton.hasClass("rocket-command-insert-open")) {
						that.elemUlTypes.remove();
						that.elemButton.removeClass("rocket-command-insert-open");
						return;
					}
					
					that.elemUlTypes = $("<ul />", {
						"class": "rocket-dd-menu-open"	
					}).insertAfter(that.elemButton);
					that.elemButton.addClass("rocket-command-insert-open");
					
					$.each(that.toMany.getTypes(), function(typeId, label) {
						$("<li />").append($("<a />", {
							"text": label
						}).click(function() {
							that.requestNewEntryForm(typeId);
							that.elemButton.removeClass("rocket-command-insert-open");
							that.elemUlTypes.remove();
						})).appendTo(that.elemUlTypes);
					});	
				}).appendTo(that.elemDiv);
				
				toMany.getElem().on('numEntriesChanged.toMany', function() {
					that.elemButton.prop("disabled", !toMany.areMoreEntriesAllowed());	
				}).on('loading.toMany', function() {
					that.elemButton.prop("disabled", true);
					that.elemButton.addClass("rocket-loading");
				}).on('loadingComplete.toMany', function() {
					that.elemButton.prop("disabled", false);	
					that.elemButton.removeClass("rocket-loading");
				});
			}).call(this, this);
		}
		
		private requestNewEntryForm(typeId: string = null) {
			var that = this;
			this.toMany.loading();
			this.toMany.requestNewEntryForm(function(toManyEntryForm: ToManyEntryForm) {
				that.toMany.loadingComplete();
				that.insertNewToManyEntryForm(toManyEntryForm);
				rocketTs.updateUi();
			}, typeId);	
		}
		
		private insertNewToManyEntryForm(toManyEntryForm: ToManyEntryForm) {
			if (null === this.toManyEntryForm) {
				this.toMany.getToManyEmbedded().addEntryForm(toManyEntryForm);
				this.elemDiv.trigger("numEntriesChanged.toMany");
				return;
			}
		
			toManyEntryForm.getElemLi().insertBefore(this.toManyEntryForm.getElemLi());
			this.toMany.getToManyEmbedded().updateOrderIndizes();
			this.elemDiv.trigger("numEntriesChanged.toMany");
		}
		
		public getElemDiv() {
			return this.elemDiv;
		}
	}
	
	export class ToManyEntryForm {
		private toMany: ToMany;
		private elemLi: JQuery;
		private elemContentContainer: JQuery;
		private elemInputOrderIndex: JQuery;
		private toManyAdd: ToManyAdd = null;
		private elemUp: JQuery;
		private elemDown: JQuery;
		private elemRemove: JQuery;
		private entryHeader: spec.EntryHeader;
		
		public constructor(toMany: ToMany, elemLi: JQuery, addAllowed: boolean, headerLabel: string) {
			this.elemLi = elemLi;
			this.elemLi.data("to-many-entry-form", this);
			this.elemContentContainer = $("<div />", {
				"class": "rocket-embedded"
			}).append(elemLi.children()).appendTo(this.elemLi);
			
			this.entryHeader = new spec.EntryHeader(headerLabel, this.elemContentContainer);
			this.elemInputOrderIndex = this.elemContentContainer.children(".rocket-to-many-order-index").hide();
			
			if (addAllowed) {
				this.toManyAdd = new ToManyAdd(toMany, this);
				this.elemLi.prepend(this.toManyAdd.getElemDiv());
			}
			
			(function(that: ToManyEntryForm) {				
				that.elemUp = $("<li />").append(rocketTs.creatControlElem(toMany.getMoveUpLabel(), function() {
					var elemLiPrev = elemLi.prev();
					if (elemLiPrev.length === 0) return;
					that.ckHack(function() {
						elemLi.insertBefore(elemLiPrev);
					});
					toMany.getToManyEmbedded().updateOrderIndizes();
				}, "fa fa-arrow-up"));
				that.entryHeader.addControl(that.elemUp);
				
				that.elemDown = $("<li />").append(rocketTs.creatControlElem(toMany.getMoveDownLabel(), function() {
					var elemLiNext = elemLi.next();
					if (elemLiNext.length === 0) return;
					
					that.ckHack(function() {
						elemLi.insertAfter(elemLiNext);
					});
					toMany.getToManyEmbedded().updateOrderIndizes();
				}, "fa fa-arrow-down"));
				that.entryHeader.addControl(that.elemDown);
				
				that.elemRemove = rocketTs.creatControlElem(elemLi.data("remove-item-label") || toMany.getRemoveItemLabel(), function() {
					elemLi.remove();
					toMany.getElem().trigger("numEntriesChanged.toMany");
					toMany.getToManyEmbedded().updateOrderIndizes();
					
				}, "fa fa-times").appendTo($("<li />"));
				that.entryHeader.addControl(that.elemRemove);
				 
				toMany.getElem().on("numEntriesChanged.toMany", function() {
					if (toMany.areLessEntriesAllowed()) {
						that.elemRemove.show();	
					} else {
						that.elemRemove.hide();	
					}
				});
				
			}).call(this, this);
		}
		
		public ckHack(callback: () => void) {
			if (typeof Wysiwyg === 'undefined') {
				callback();
				return;
			}
			
			Wysiwyg.ckHack(this.elemLi, function() {
				callback();	
			});
		}
		
		public setHeaderLabel(headerLabel: string) {
			this.entryHeader.setLabel(headerLabel);
		}
		
		public isOrderable() {
			return this.elemInputOrderIndex.length > 0;	
		}
		
		public setOrderIndex(orderIndex) {
			if (!this.isOrderable()) return;
			
			this.elemInputOrderIndex.val(orderIndex).change();
		}
		
		public enableUp() {
			this.elemUp.show();	
		}
		
		public disableUp() {
			this.elemUp.hide();	
		}
		
		public enableDown() {
			this.elemDown.show();	
		}
		
		public disableDown() {
			this.elemDown.hide();	
		}
		
		public getOrderIndex() {
			if (!this.isOrderable()) return 0;
			
			return parseInt(this.elemInputOrderIndex.val());
		}
		
		public getElemInputOrderIndex() {
			return this.elemInputOrderIndex;	
		}
		
		public getElemLi() {
			return this.elemLi;
		}
	}
	
	class ToManyEmbedded {
		private elemContainer: JQuery;
		private elem: JQuery;
		private toMany: ToMany;
		private toManyAdd: ToManyAdd = null;
	
		public constructor(toMany: ToMany) {
			this.elemContainer = $("<div />");
			this.elem = $("<div />").appendTo(this.elemContainer);
			this.toMany = toMany;
		}
		
		public activate(enableAdd) {
			if (enableAdd) {
				this.toManyAdd = new ToManyAdd(this.toMany);
				this.toManyAdd.getElemDiv().appendTo(this.elemContainer);
			}
			
			this.elemContainer.appendTo(this.toMany.getElem());
			this.updateOrderIndizes();
			
		}
		
		public getElemUl() {
			return this.elem;
				
		}
		
		public getNumNewEntryForms() {
			return this.elem.children(".rocket-new").length;
		}
		
		public addEntryForm(toManyEntryForm: ToManyEntryForm, inspectOrder: boolean = false, updateOrderIndizes: boolean = true) {
			if (!inspectOrder) {
				this.elem.append(toManyEntryForm.getElemLi());
				if (updateOrderIndizes) {
					this.updateOrderIndizes();
				}
				return;	
			}
			
			var orderIndex = toManyEntryForm.getOrderIndex(),
				added = false;
			this.elem.children().each(function(index) {
				var tmpToManyEntryForm = <ToManyEntryForm> $(this).data("to-many-entry-form");
				if (tmpToManyEntryForm.getOrderIndex() <= orderIndex) return;
				
				toManyEntryForm.getElemLi().insertBefore(tmpToManyEntryForm.getElemLi());
				added = true;
				return false;
			});
			
			if (!added) {
				this.elem.append(toManyEntryForm.getElemLi());
			}
			
			if (updateOrderIndizes) {
				this.updateOrderIndizes();
			}
		}
		
		public updateOrderIndizes() {
			var children = this.elem.children();
			children.each(function(index) {
				var toManyEntryForm = <ToManyEntryForm> $(this).data('to-many-entry-form');
				toManyEntryForm.setOrderIndex(index);
				if (index === 0) {
					toManyEntryForm.disableUp();
				} else {
					toManyEntryForm.enableUp();
				}

				if (index === (children.length - 1)) {
					toManyEntryForm.disableDown();
				} else {
					toManyEntryForm.enableDown();
				}
			});
		}
		
		public getEntryForms() {
			var entryForms = []
			this.elem.children().each(function(index) {
				entryForms.push(<ToManyEntryForm> $(this).data('to-many-entry-form'));
			});
			
			return entryForms;
		}
		
		public getNumEntryForms() {
			return this.elem.children().length;	
		}
	}
	
	class ToManySelectorStackedContent implements ui.StackedContent {
		private elemContent: JQuery;
		private toManySelector: ToManySelector;
		private stackedContentContainer: ui.StackedContentContainer = null;
		private overviewTools: spec.OverviewTools = null;
		private observeOnContentLoad: boolean = false;
		
		public constructor(toManySelector: ToManySelector, elemContent: JQuery) {
			this.elemContent = $("<div />", {
				"class": "rocket-to-one-stacked-content"	
			}).append($("<div />", {
				"class": "rocket-panel"
			}).append(elemContent));
			
			this.toManySelector = toManySelector;
			
			(function(that: ToManySelectorStackedContent) {
				elemContent.on('overview.contentLoaded', function(e, overviewTools: OverviewTools) {
					that.overviewTools = overviewTools;
					that.overviewTools.getFixedHeader().setApplyDefaultFixedContainer(false);
					if (null !== that.stackedContentContainer) {
						overviewTools.setElemFixedContainer(that.stackedContentContainer.getElem(), that.observeOnContentLoad);
					}
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
			var that = this;
			this.stackedContentContainer = stackedContentContainer;
			stackedContentContainer.addControl("fa fa-save", this.toManySelector.getAddLabel(), function() {
				that.toManySelector.addByIdentityStrings(that.overviewTools.getOverviewContent().getSelectedIdentityStrings());
				stackedContentContainer.close();
			});
			
			if (null !== this.overviewTools) {
				this.overviewTools.setElemFixedContainer(stackedContentContainer.getElem(), false);
			}
		}
		
		public onAnimationComplete() {
			if (null !== this.overviewTools) {
				this.overviewTools.getFixedHeader().startObserving();
			} else {
				this.observeOnContentLoad = true;
			}
 		}
		
		public onClose() {
			this.overviewTools.getFixedHeader().reset();
			this.overviewTools.getFixedHeader().stopObserving();
			this.overviewTools.getOverviewContent().removeSelection();
		}
		
		public onClosed() {
			this.stackedContentContainer.getElem().scrollTop(0);
			this.observeOnContentLoad = false;
		}
	}
	
	class ToManySelectorEntry {
		private toManySelector: ToManySelector;
		private elemLi: JQuery;
		private elemInput: JQuery;
		private elemLabel: JQuery;
		private elemLabelContainer: JQuery;
		
		public constructor(toManySelector: ToManySelector, elemLi: JQuery, id: number = null) {
			this.toManySelector = toManySelector;
			this.elemInput = elemLi.find("input:first").hide();
			if (null !== id) {
				this.elemInput.val(id);	
			}
			this.elemLi = elemLi.addClass("rocket-to-many-" + this.elemInput.val());
			this.elemLabel = $("<span />", {
				"text": toManySelector.getIdentityString(this.elemInput.val())
			});
			
			(function(that: ToManySelectorEntry) {
				that.elemLabelContainer = $("<div />", {
					"class": "rocket-relation-label-container"	
				}).append(that.elemLabel).insertAfter(that.elemInput);

				rocketTs.creatControlElem(toManySelector.getRemoveItemLabel(), function() {
					elemLi.remove();
				}, "fa fa-times").appendTo(that.elemLabelContainer);
			}).call(this, this);
		}
		
		public getElemInput(): JQuery {
			return this.elemInput;
		}
	}
	
	class ToManySelector {
		private elem: JQuery;
		private elemUl: JQuery = null;
		private elemUlClone: JQuery;
		private overviewToolsUrl: string;
		private elemInput: JQuery;
		private elemLiNew: JQuery;
		
		private identityStrings: Object;
		private originalIdReps: Array<string>;
		
		private elemSelect: JQuery = null;
		private elemReset: JQuery = null;
		private preloadedStackedContent: ToManySelectorStackedContent = null;
		private addLabel: string;
		private resetLabel: string;
		private clearLabel: string;
		private genericEntryLabel: string;
		private removeItemLabel: string;
		private basePropertyName: string;
		
		public constructor(elem: JQuery, removeItemLabel: string) {
			this.elem = elem;
			this.overviewToolsUrl = elem.data("overview-tools-url");
			this.identityStrings = rocketTs.objectify(elem.data("identity-strings"));
			
			this.originalIdReps = elem.data("original-id-reps");

			this.addLabel = elem.data("add-label");
			this.resetLabel = elem.data("reset-label");
			this.clearLabel = elem.data("clear-label");
			this.genericEntryLabel = elem.data("generic-entry-label");
			this.removeItemLabel = removeItemLabel;
			this.basePropertyName = elem.data('base-property-name');
			
			this.setup(elem.children("ul:first").addClass("rocket-to-many-selected-entries"));
			this.initControls();
		}
		
		public getElem() {
			return this.elem;	
		}
		
		public getAddLabel() {
			return this.addLabel;	
		}
		
		public getRemoveItemLabel() {
			return this.removeItemLabel;
		}
		
		private setup(elemUl) {
			var that = this;
			
			this.elemLiNew = elemUl.children(".rocket-new-entry:last").detach();
			this.elemUlClone = elemUl.clone();
			
			this.elemUl = elemUl;
			this.elemUl.prependTo(this.elem);
			
			this.elemUl.children().each(function() {
				new ToManySelectorEntry(that, $(this));	
			});
		}
		
		private initControls() {
			var that = this,
				elemControls = $("<ul />", {
				"class": "rocket-to-many-controls"
			}).insertAfter(this.elemUl);
			
			rocketTs.creatControlElem(this.addLabel, function() {
				rocketTs.getContentStack().addStackedContent(that.preloadedStackedContent);
				rocketTs.updateUi();
			}).appendTo($("<li />").appendTo(elemControls));
			
			rocketTs.creatControlElem(this.resetLabel, function() {
				that.reset();
				that.elem.trigger("numEntriesChanged.toMany");
			}).appendTo($("<li />").appendTo(elemControls));
			
			rocketTs.creatControlElem(this.clearLabel, function() {
				that.elemUl.empty();
				that.elem.trigger("numEntriesChanged.toMany");
			}).appendTo($("<li />").appendTo(elemControls));
			
			that.loadOverlay();
		}
		
		private reset() {
			var that = this
			this.elemUl.empty();
			this.originalIdReps.forEach(function(idRep) {
				that.addEntry(idRep, false);
			});
		}
		
		public loadOverlay() {
			var that = this;
			$.getJSON(this.overviewToolsUrl, function(data) {
				that.preloadedStackedContent = new ToManySelectorStackedContent(that, rocketTs.analyzeAjahData(data));
				that.preloadedStackedContent.getElemContent().appendTo($("body"));
				rocketTs.updateUi();
				that.preloadedStackedContent.getElemContent().detach();
			});
		}
		
		public addByIdentityStrings(identityStrings: Object) {
			var that = this;

			
			$.each(identityStrings, function(idRep, identityString) {
				if (that.elemUl.children(".rocket-to-many-" + idRep).length > 0) return;
				that.identityStrings[idRep] = identityString;
				
				that.addEntry(idRep);
			});
		}
		
		public addEntry(idRep, prepend: boolean = true) {
			var elemLi = this.elemLiNew.clone(),
				toManySelctorEntry = new ToManySelectorEntry(this, elemLi, idRep);
			toManySelctorEntry.getElemInput().attr("name", this.basePropertyName + "[]");
			
			if (prepend) {
				elemLi.prependTo(this.elemUl);	
			} else {
				elemLi.appendTo(this.elemUl);	
			}
			this.elem.trigger("numEntriesChanged.toMany");
		}
		
		public getIdentityString(id: string) {
			var identiyString = id;
			$.each(this.identityStrings, function(tmpId, tmpIdentiyString) {
				if (id !== tmpId) return;
				
				identiyString = tmpIdentiyString;
				return false;
			});
			
			return identiyString;
		}
		
		public getNumSelectedEntries() {
			return this.elemUl.children().length;	
		}
	}
	
	export class ToMany {
		private elem: JQuery;
		private min: number;
		private max: number;
		private newEntryFormUrl: string = null;
		private newEntryFormPropertyPath: string;
		private addItemLabel: string;
		private genericRemoveItemLabel: string;
		private toManyEmbedded: ToManyEmbedded;
		private toManySelector: ToManySelector = null;
		private types: Object = null;
		private entryFormPreparationCallback: (entryForm: ToManyEntryForm) => void = null;
		private moveUpLabel: string;
		private moveDownLabel: string;
		private removeItemLabel: string;
		private itemLabel: string;
		private nextPropertyPathIndex: number = 0;
		
		public constructor(elem: JQuery) {
			this.elem = elem;
			this.min = elem.data("min");
			this.max = elem.data("max") || null;
			this.moveUpLabel = elem.data("move-up-label");
			this.moveDownLabel = elem.data("move-down-label");
			this.removeItemLabel = elem.data("remove-item-label");
			this.itemLabel = elem.data("item-label");
			
			this.toManyEmbedded = new ToManyEmbedded(this);
			elem.data("to-many", this);
			
			(function(that: ToMany) {
				var elemCurrent = elem.children("div.rocket-current"),
					elemNew = elem.children("div.rocket-new"),
					elemSelector = elem.children(".rocket-selector"),
					currentAvailable = elemCurrent.length > 0,
					newAvailable = elemNew.length > 0;
				
				if (newAvailable) {
					that.newEntryFormUrl = elemNew.data("new-entry-form-url");
					that.newEntryFormPropertyPath = elemNew.data("property-path");
					that.addItemLabel = elemNew.data("add-item-label");
					
					elemNew.children(".rocket-new").each(function() {
						that.toManyEmbedded.addEntryForm(new ToManyEntryForm(that, $(this), true, that.getItemLabel()), true, false);
					});
				}
				
				if (elemCurrent.length > 0) {
					elemCurrent.children(".rocket-current").each(function() {
						var elemLi = $(this);
						that.toManyEmbedded.addEntryForm(new ToManyEntryForm(that, elemLi, newAvailable, elemLi.data("item-label")), true, false);
					});
					
					elemCurrent.remove();
				}
				
				if (elemSelector.length > 0) {
					that.toManySelector = new ToManySelector(elemSelector, that.removeItemLabel);
				}
				
				if (currentAvailable || newAvailable) {
					that.toManyEmbedded.activate(newAvailable);
				}
				
				var eiSpecLabels = elem.data("ei-spec-labels"),
					numEiSpecLabels = 0;
				console.log(eiSpecLabels);
				$.each(eiSpecLabels, function() {
					numEiSpecLabels++;
				});
				
				if (numEiSpecLabels > 1) {
					that.setTypes(eiSpecLabels);	
				}
			}).call(this, this);
		}
		
		public getMoveUpLabel() {
			return this.moveUpLabel;	
		}
		
		public getMoveDownLabel() {
			return this.moveDownLabel;	
		}
		
		public getRemoveItemLabel() {
			return this.removeItemLabel;	
		}
		
		public getItemLabel() {
			return this.itemLabel;	
		}
		
		public setEntryFormPreperationCallback(entryFormPreperationCallback :(entryForm: ToManyEntryForm) => void) {
			this.entryFormPreparationCallback = entryFormPreperationCallback;
			
			this.toManyEmbedded.getEntryForms().forEach(function(entryForm: ToManyEntryForm) {
				entryFormPreperationCallback(entryForm);
			});
		}
		
		public getElem() {
			return this.elem;	
		}
		
		public requestNewEntryForm(callback: (newEntryForm: ToManyEntryForm) => void, typeId: string = null) {
			var params = {
				"propertyPath": this.newEntryFormPropertyPath + "[n" + this.nextPropertyPathIndex++ + "]"
			}, that = this;
			
			$.getJSON(this.newEntryFormUrl, params, function(data) {
				var elemLiEntryForm = $("<div />", {
					"class": "rocket-new"
				}).append(rocketTs.analyzeAjahData(data)),
					headerLabel = that.itemLabel;
				
				if (null !== typeId) {
					var elemTypeSelector = elemLiEntryForm.find(".rocket-script-type-selector:first").hide();
					elemTypeSelector.find(".rocket-script-type-selection:first").val(typeId).change();
					headerLabel = that.types[typeId];
				}
				
				var newEntryForm = new ToManyEntryForm(that, elemLiEntryForm, true, headerLabel);
				
				if (null !== that.entryFormPreparationCallback) {
					that.entryFormPreparationCallback(newEntryForm);
				}
				
				callback(newEntryForm);
			});
		}
		
		public getAddItemLabel() {
			return this.addItemLabel;
		}
		
		public hasTypes() {
			return null !== this.types;
		}
		
		public getTypes() {
			return this.types;
		}
		
		public setTypes(types: Object) {
			this.types = types;
			
			this.toManyEmbedded.getEntryForms().forEach(function(entryForm: ToManyEntryForm) {
				var elemTypeSelector = entryForm.getElemLi().find(".rocket-script-type-selector:first").hide(),
					headerLabel = types[elemTypeSelector.find(".rocket-script-type-selection:first").val()];
				
				entryForm.setHeaderLabel(headerLabel);
			});
			
		}
		
		private determineNumEntries() {
			var numEntries = this.toManyEmbedded.getNumEntryForms();
			if (null !== this.toManySelector) {
				numEntries = this.toManySelector.getNumSelectedEntries();
			}
			
			return numEntries;
		}
		
		public determineNumMore() {
			return this.max - this.determineNumEntries();
		}
		
		public areMoreEntriesAllowed() {
			if (this.max === null) return true;
			
			return this.determineNumEntries() < this.max;
		}
		
		public areLessEntriesAllowed() {
			if (this.min === null) return true;
			
			return this.determineNumEntries() > this.min;
		}
		
		public getGenericAddItemLabel() {
			return this.addItemLabel;	
		}
		
		public getToManyEmbedded() {
			return this.toManyEmbedded;
		}
		
		public loading() {
			this.elem.trigger('loading.toMany');
		}
		
		public loadingComplete() {
			this.elem.trigger('loadingComplete.toMany');
		}
	}
	
	rocketTs.ready(function() {
		rocketTs.registerUiInitFunction("form .rocket-to-many", function(elem: JQuery) {
			new ToMany(elem);
		});
		
		rocketTs.registerUiInitFunction(".rocket-selector-mag", function(elem: JQuery) {
			new ToManySelector(elem, "Text: remove Item");
		});
	});
}
