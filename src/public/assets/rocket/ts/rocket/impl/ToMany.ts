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
namespace rocket.impl {
	import cmd = rocket.cmd;
	import display = rocket.display;
	
	var $ = jQuery;
	
	export class ToMany {
		constructor(private selector: ToManySelector = null, private embedded: ToManyEmbedded = null) {
		}
				
		public static from(jqToMany: JQuery): ToMany {
			var toMany: ToMany = jqToMany.data("rocketImplToMany");
			if (toMany instanceof ToMany) {
				return toMany;
			}
			
			var toManySelector = null;
			var jqSelector = jqToMany.children(".rocket-impl-selector");
			if (jqSelector.length > 0) {
				toManySelector = new ToManySelector(jqSelector, jqSelector.find("li.rocket-new-entry").detach());
				jqSelector.find("ul li").each(function () {
					var entry = new SelectedEntry($(this));
					entry.label = toManySelector.determineIdentityString(entry.idRep);
					toManySelector.addSelectedEntry(entry);
				});
			}
			
			var jqCurrents = jqToMany.children(".rocket-impl-currents");
			var jqNews = jqToMany.children(".rocket-impl-news");
			var addControlFactory = null;
			
			var toManyEmbedded = null;
			if (jqCurrents.length > 0 || jqNews.length > 0) {
				if (jqNews.length > 0) {
					var propertyPath = jqNews.data("property-path");
					
					var startKey: number = 0;
					var testPropertyPath = propertyPath + "[n";
					jqNews.find("input, textarea").each(function () {
						var name: string = <string> $(this).attr("name");
						if (0 == name.indexOf(testPropertyPath)) {
							name = name.substring(testPropertyPath.length);
							
							name.match(/^[0-9]+/).forEach(function (key) {
								var curKey: number = parseInt(key);
								if (curKey >= startKey) {
									startKey = curKey + 1;
								}
							});
						}
					});
				
					var entryFormRetriever = new EmbeddedEntryRetriever(jqNews.data("new-entry-form-url"), propertyPath, 
							jqNews.data("draftMode"), startKey, "n");
					addControlFactory = new AddControlFactory(entryFormRetriever, jqNews.data("add-item-label"));
				}
				
				toManyEmbedded = new ToManyEmbedded(jqToMany, addControlFactory);
				jqCurrents.children(".rocket-impl-entry").each(function () {
					toManyEmbedded.addEntry(new EmbeddedEntry($(this), toManyEmbedded.isReadOnly()));
				});
				jqNews.children(".rocket-impl-entry").each(function () {
					toManyEmbedded.addEntry(new EmbeddedEntry($(this), toManyEmbedded.isReadOnly()));
				});
			}
			
			var toMany = new ToMany(toManySelector, toManyEmbedded);
			jqToMany.data("rocketImplToMany", toMany);		
			
			
			
			return toMany;
		}
	}
	
	class ToManySelector {
		private jqUl: JQuery
		private entries: Array<SelectedEntry> = new Array<SelectedEntry>();
		private originalIdReps: Array<String>;
		private identityStrings: Array<String>;
		private browserLayer: cmd.Layer;
		
		constructor(private jqElem: JQuery, private jqNewEntrySkeleton: JQuery) {
			this.jqElem = jqElem;
			this.jqUl = jqElem.children("ul");
			
			this.originalIdReps = jqElem.data("original-id-reps");
			this.identityStrings = jqElem.data("identity-strings");
			
			this.init();
		}
		
		public determineIdentityString(idRep: string): string {
			return this.identityStrings[idRep];
		}
		
		private init() {
			var jqCommandList = $("<div />");
			this.jqElem.append(jqCommandList);
			
			var that = this;
			var commandList = new display.CommandList(jqCommandList);
			
			commandList.createJqCommandButton({ label: this.jqElem.data("select-label") }).click(function () {
				that.openBrowser();
			});
			
			commandList.createJqCommandButton({ label: this.jqElem.data("reset-label") }).click(function () {
				that.reset();
			});
			
			commandList.createJqCommandButton({ label: this.jqElem.data("clear-label") }).click(function () {
				that.clear();
			});
		}
		
		public addSelectedEntry(entry: SelectedEntry) {
			this.entries.push(entry);	
			
			var that = this;
			entry.commandList.createJqCommandButton({ iconType: "fa fa-times", label: this.jqElem.data("remove-entry-label") }).click(function () {
				that.removeSelectedEntry(entry);				
			});
		}
		
		public removeSelectedEntry(entry: SelectedEntry) {
			for (var i in this.entries) {
				if (this.entries[i] !== entry) continue;
			
				entry.jQuery.remove();
				this.entries.slice(parseInt(i), 1);
			}
		}
		
		public reset() {
		}
		
		public clear() {
			for (var i in this.entries) {
				this.entries[i].jQuery.remove();
			}
			
			this.entries.slice(0, this.entries.length);
		}
		
		public openBrowser() {
			var layer = rocket.getContainer().createLayer();
			rocket.exec(this.jqElem.data("overview-tools-url"), {
				showLoadingContext: true,
				currentLayer: layer
			});
		}
	}
	
	class SelectedEntry {
		private cmdList: display.CommandList;
		private jqLabel: JQuery;
		private jqInput: JQuery;
		
		constructor(private jqElem: JQuery) {
			jqElem.prepend(this.jqLabel = $("<span />"));
			
			this.cmdList = new display.CommandList($("<div />", true).appendTo(jqElem));			
			this.jqInput = jqElem.children("input").hide();
		}
		
		get jQuery(): JQuery {
			return this.jqElem;
		}
		
		get commandList(): display.CommandList {
			return this.cmdList;
		}
		
		get label(): string {
			return this.jqLabel.text();
		}
		
		set label(label: string) {
			this.jqLabel.text(label);
		}
		
		get idRep(): string {
			return this.jqInput.val();
		}
		
//		set idRep(idRep: string) {
//			this.jqInput.val(idRep);
//		}
	}
	
	
	export class ToManyEmbedded {
		private jqToMany: JQuery;
		private addControlFactory: AddControlFactory;
		private compact: boolean = true;
		private sortable: boolean = true;
		private entries: Array<EmbeddedEntry> = new Array<EmbeddedEntry>();
		private jqEmbedded: JQuery;
		private jqEntries: JQuery;
		private expandContext: cmd.Context = null;
		private dominantEntry: EmbeddedEntry = null;
		private closeLabel: string;
		private firstAddControl: AddControl = null;
		private lastAddControl: AddControl = null
		private entryAddControls: Array<AddControl> = new Array<AddControl>();
		
		constructor(jqToMany: JQuery, addButtonFactory: AddControlFactory = null) {
			this.jqToMany = jqToMany;
			this.addControlFactory = addButtonFactory;
			this.compact = (true == jqToMany.data("compact"));
			this.sortable = (true == jqToMany.data("sortable"))
			this.closeLabel = jqToMany.data("close-label");
			
			this.jqEmbedded = $("<div />", {
				"class": "rocket-impl-embedded"
			});
			this.jqToMany.append(this.jqEmbedded);
			
			this.jqEntries = $("<div />");
			this.jqEmbedded.append(this.jqEntries);
			
			if (this.compact) {
				var structureElement = rocket.display.StructureElement.findFrom(this.jqToMany);
				structureElement.setGroup(true);
				var toolbar = structureElement.getToolbar();
				if (toolbar !== null) {
					var jqButton = null;
					if (this.isReadOnly()) { 
						jqButton = toolbar.getCommandList().createJqCommandButton({ iconType: "fa fa-file", label: "Detail" });
					} else {
						jqButton = toolbar.getCommandList().createJqCommandButton({ iconType: "fa fa-pencil", label: "Edit", severity: display.Severity.WARNING });
					}
					let that = this;
					jqButton.click(function () {
						that.expand();
					});
				}
			}
			
			if (this.sortable) {
				this.initSortable();
			}
			
			this.changed();
		}
		
		public isReadOnly(): boolean {
			return this.addControlFactory === null;
		}
		
		private changed() {
			for (let i in this.entries) {
				let index = parseInt(i); 
				this.entries[index].setOrderIndex(index);
				
				if (this.isPartialExpaned()) continue;
				this.entries[index].setMoveUpEnabled(index > 0);
				this.entries[index].setMoveDownEnabled(index < this.entries.length - 1);
			}
			
			if (this.addControlFactory === null) return;
			
			if (this.entries.length === 0 && this.firstAddControl !== null) {
				this.firstAddControl.dispose();
				this.firstAddControl = null;
			}
			
			if (this.entries.length > 0 && this.firstAddControl === null) {
				this.firstAddControl = this.createFirstAddControl();
			}
				
			for (var i in this.entryAddControls) {
				this.entryAddControls[i].dispose();
			}
			
			if (this.isExpanded() && !this.isPartialExpaned()) {
				for (var i in this.entries) {
					if (parseInt(i) == 0) continue;
					
					this.entryAddControls.push(this.createEntryAddControl(this.entries[i]));
				}
			}
			
			if (this.lastAddControl === null) {
				this.lastAddControl = this.createLastAddControl();
			}
			
			if (this.isPartialExpaned()) {
				if (this.firstAddControl !== null) {
					this.firstAddControl.getJQuery().hide();
				}
				this.lastAddControl.getJQuery().hide();
			} else {
				if (this.firstAddControl !== null) {
					this.firstAddControl.getJQuery().show();
				}
				this.lastAddControl.getJQuery().show();
			}
		}
		
		private createFirstAddControl(): AddControl {
			var addControl = this.addControlFactory.create();
			var that = this;
				
			this.jqEmbedded.prepend(addControl.getJQuery());
			
			addControl.onNewEmbeddedEntry(function(newEntry: EmbeddedEntry) {
				that.insertEntry(newEntry);
				if (!that.isExpanded()) {
					that.expand(newEntry);
				}
			});
			return addControl;
		}
		
		private createEntryAddControl(entry: EmbeddedEntry): AddControl {
			var addControl = this.addControlFactory.create();
			var that = this;
			
			this.entryAddControls.push(addControl);
			addControl.getJQuery().insertBefore(entry.getJQuery());
			addControl.onNewEmbeddedEntry(function(newEntry: EmbeddedEntry) {
				that.insertEntry(newEntry, entry);
			});
			return addControl;
		}
		
		private createLastAddControl(): AddControl {
			var addControl = this.addControlFactory.create();
			var that = this;
			
			this.jqEmbedded.append(addControl.getJQuery());
			addControl.onNewEmbeddedEntry(function(newEntry: EmbeddedEntry) {
				that.addEntry(newEntry);
				if (!that.isExpanded()) {
					that.expand(newEntry);
				}
			});
			return addControl;
		}
		
		
		public insertEntry(entry: EmbeddedEntry, beforeEntry: EmbeddedEntry = null) {
			entry.getJQuery().detach();
			
			if (beforeEntry === null) {
				this.entries.unshift(entry);
				this.jqEntries.prepend(entry.getJQuery());
			} else {
				entry.getJQuery().insertBefore(beforeEntry.getJQuery());
				this.entries.splice(beforeEntry.getOrderIndex(), 0, entry);
			}
			
			this.initEntry(entry);
			this.changed();
		}
		
		public addEntry(entry: EmbeddedEntry) {
			entry.setOrderIndex(this.entries.length);
			this.entries.push(entry);
			this.jqEntries.append(entry.getJQuery());
		
			this.initEntry(entry);
			
			if (this.isReadOnly()) return;
			this.changed();
		}
		
		private switchIndex(oldIndex: number, newIndex: number) {
			var entry = this.entries[oldIndex];
			this.entries[oldIndex] = this.entries[newIndex];
			this.entries[newIndex] = entry;
			
			this.changed();
		}
			
		private initEntry(entry: EmbeddedEntry) {
			if (this.isExpanded()) {
				entry.expand();
			} else {
				entry.reduce();
			}
			
			var that = this;
			
			entry.onMove(function (up: boolean) {
				var oldIndex: number = entry.getOrderIndex();
				var newIndex: number = up ? oldIndex - 1 : oldIndex + 1;
			
				if (newIndex < 0 || newIndex >= that.entries.length) {
					return;
				}

				if (up) {
					that.entries[oldIndex].getJQuery().insertBefore(that.entries[newIndex].getJQuery());
				} else {
					that.entries[oldIndex].getJQuery().insertAfter(that.entries[newIndex].getJQuery());
				}
				
				that.switchIndex(oldIndex, newIndex);
			});
			
			entry.onRemove(function () {
				that.entries.splice(entry.getOrderIndex(), 1);
				entry.getJQuery().remove();
				
				this.update();
			});
			
			entry.onFocus(function () {
				that.expand(entry);
			});
		}
		
		private initSortable() {
			var that = this;
			var oldIndex: number = 0;
			this.jqEntries.sortable({
				"handle": ".rocket-impl-handle",
				"forcePlaceholderSize": true,
		      	"placeholder": "rocket-impl-entry-placeholder",
				"start": function (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) {
					var oldIndex = ui.item.index();
				},
				"update": function (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) {
					var newIndex = ui.item.index();
					
					that.switchIndex(oldIndex, newIndex);
				}
		    }).disableSelection();
		}
		
		
		private enabledSortable() {
			this.jqEntries.sortable("enable");
			this.jqEntries.disableSelection();
		}
		
		private disableSortable() {
			this.jqEntries.sortable("disable");
			this.jqEntries.enableSelection();
		}
		
		public isExpanded(): boolean {
			return this.expandContext !== null;
		}
		
		public isPartialExpaned() {
			return this.dominantEntry !== null;
		}	 
		
		public expand(dominantEntry: EmbeddedEntry = null) {
			if (this.isExpanded()) return;
			
			if (this.sortable) {
				this.disableSortable();
			}
			
			this.dominantEntry = dominantEntry;
			this.expandContext = rocket.getContainer().createLayer().createContext(window.location.href);
			this.jqEmbedded.detach();
			this.expandContext.applyContent(this.jqEmbedded);
			this.expandContext.getLayer().pushHistoryEntry(window.location.href);
			
			for (let i in this.entries) {
				if (dominantEntry === null) {
					this.entries[i].expand(true);
				} else if (dominantEntry === this.entries[i]) {
					this.entries[i].expand(false);
				} else {
					this.entries[i].hide();
				}
			}
			
			var that = this;
			
			var jqCommandButton = this.expandContext.getMenu().getCommandList()
					.createJqCommandButton({ iconType: "fa fa-times", label: this.closeLabel, severity: display.Severity.WARNING} , true);
			jqCommandButton.click(function () {
				that.expandContext.getLayer().close();
			});
			
			this.expandContext.onClose(function () {
				that.reduce();
			});
			
			this.changed();
			n2n.ajah.update();
		}
		
		public reduce() {
			if (!this.isExpanded()) return;
			
			this.dominantEntry = null;
			this.expandContext = null;
			
			this.jqEmbedded.detach();
			this.jqToMany.append(this.jqEmbedded);
			
			for (let i in this.entries) {
				this.entries[i].reduce();
			}
			
			if (this.sortable) {
				this.enabledSortable();
			}
			
			this.changed();
			n2n.ajah.update();
		}
	}
	
	
	class EmbeddedEntry {
		private entryGroup: display.StructureElement;
		private readOnly: boolean;
		private jqOrderIndex: JQuery;
		private jqSummary: JQuery;
		
		private jqContextCommands: JQuery;
		private bodyGroup: display.StructureElement;
		private entryForm: display.EntryForm;
		
		private jqExpMoveUpButton: JQuery;
		private jqExpMoveDownButton: JQuery;
		private jqExpRemoveButton: JQuery;
		private jqRedFocusButton: JQuery;
		private jqRedRemoveButton: JQuery;
		
		constructor(jqEntry: JQuery, readOnly: boolean) {
			this.entryGroup = display.StructureElement.from(jqEntry, true);
			this.readOnly = readOnly;
			
			this.bodyGroup = display.StructureElement.from(jqEntry.children(".rocket-impl-body"), true);
			 
			this.jqOrderIndex = jqEntry.children(".rocket-impl-order-index").hide();
			this.jqSummary = jqEntry.children(".rocket-impl-summary");
			
			this.jqContextCommands = this.bodyGroup.getJQuery().children(".rocket-context-commands");
			
			if (readOnly) {
				var rcl = new display.CommandList(this.jqSummary.children(".rocket-simple-commands"), true);
				this.jqRedFocusButton = rcl.createJqCommandButton({iconType: "fa fa-file", label: "Detail", severity: display.Severity.SECONDARY});
			} else {
				this.entryForm = display.EntryForm.from(jqEntry, true);
				
				var ecl = this.bodyGroup.getToolbar().getCommandList();
				this.jqExpMoveUpButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-up", label: "Move up" });
				this.jqExpMoveDownButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-down", label: "Move down"});
				this.jqExpRemoveButton = ecl.createJqCommandButton({ iconType: "fa fa-times", label: "Remove", severity: display.Severity.DANGER }); 
				
				var rcl = new display.CommandList(this.jqSummary.children(".rocket-simple-commands"), true);
				this.jqRedFocusButton = rcl.createJqCommandButton({ iconType: "fa fa-pencil", label: "Edit", severity: display.Severity.WARNING });
				this.jqRedRemoveButton = rcl.createJqCommandButton({ iconType: "fa fa-times", label: "Remove", severity: display.Severity.DANGER });
			}
			
			
			
			this.reduce();
			
			jqEntry.data("rocketImplEmbeddedEntry", this);
		}
		
		public getEntryForm(): display.EntryForm {
			return this.entryForm;
		}
		
		public onMove(callback: (up: boolean) => any) {
			if (this.readOnly) return;
			
			this.jqExpMoveUpButton.click(function () {
				callback(true);
			});
			this.jqExpMoveDownButton.click(function () {
				callback(false);
			});
		}
				
		public onRemove(callback: () => any) {
			if (this.readOnly) return;
			
			this.jqExpRemoveButton.click(function () {
				callback();
			});
			this.jqRedRemoveButton.click(function () {
				callback();
			});
		}
		
		public onFocus(callback: () => any) {
			this.jqRedFocusButton.click(function () {
				callback();
			});
			
			this.bodyGroup.onShow(function () {
				callback();
			});
		}
		
		public getJQuery(): JQuery {
			return this.entryGroup.getJQuery();
		}
		
		public getExpandedCommandList(): display.CommandList {
			return this.bodyGroup.getToolbar().getCommandList();
		}
		
		public expand(asPartOfList: boolean = true) {
			this.entryGroup.show();
			this.jqSummary.hide();
			this.bodyGroup.show();
			
			this.entryGroup.getJQuery().addClass("rocket-group");
			
			if (asPartOfList) {
				this.jqContextCommands.hide();
			} else {
				this.jqContextCommands.show();
			}
			
			if (this.readOnly) return;
			
			if (asPartOfList) {
				this.jqExpMoveUpButton.show();
				this.jqExpMoveDownButton.show();
				this.jqExpRemoveButton.show();
				this.jqContextCommands.hide();
			} else {
				this.jqExpMoveUpButton.hide();
				this.jqExpMoveDownButton.hide();
				this.jqExpRemoveButton.hide();
				this.jqContextCommands.show();
			}
		}
		
		public reduce() {
			this.entryGroup.show();
			this.jqSummary.show();
			this.bodyGroup.hide();
			
			this.entryGroup.getJQuery().removeClass("rocket-group");
		}
		
		public hide() {
			this.entryGroup.hide();
		}
		
		public setOrderIndex(orderIndex: number) {
			this.jqOrderIndex.val(orderIndex);
		}
	
		public getOrderIndex(): number {
			return parseInt(this.jqOrderIndex.val());
		}
		
		public setMoveUpEnabled(enabled: boolean) {
			if (this.readOnly) return;
			
			if (enabled) {
				this.jqExpMoveUpButton.show();
			} else {
				this.jqExpMoveUpButton.hide();
			}
		}
		
		public setMoveDownEnabled(enabled: boolean) {
			if (this.readOnly) return;
			
			if (enabled) {
				this.jqExpMoveDownButton.show();
			} else {
				this.jqExpMoveDownButton.hide();
			}
		}
		
//		public static from(jqElem: JQuery, create: boolean = false): EmbeddedEntry {
//			var entry = jqElem.data("rocketImplEmbeddedEntry");
//			if (entry instanceof EmbeddedEntry) {
//				return entry;
//			}
//			
//			if (create) {
//				return new EmbeddedEntry(jqElem); 				
//			}
//			
//			return null;
//		}
	}
	
	class EmbeddedEntryRetriever {
		private urlStr: string;
		private propertyPath: string;
		private draftMode: boolean;
		private startKey: number;
		private keyPrefix: string;
		private preloadEnabled: boolean = false;
		private preloadedResponseObjects: Array<Object> = new Array<Object>();
		private pendingLookups: Array<PendingLookup> = new Array<PendingLookup>();
		
		constructor (lookupUrlStr: string, propertyPath: string, draftMode: boolean, startKey: number = null, 
				keyPrefix: string = null) {
			this.urlStr = lookupUrlStr;
			this.propertyPath = propertyPath;
			this.draftMode = draftMode;
			this.startKey = startKey;
			this.keyPrefix = keyPrefix;
		}
		
		public setPreloadEnabled(preloadEnabled: boolean) {
			if (!this.preloadEnabled && preloadEnabled && this.preloadedResponseObjects.length == 0) {
				this.load();
			}
			
			this.preloadEnabled = preloadEnabled;
		}
		
		public lookupNew(doneCallback: (embeddedEntry: EmbeddedEntry) => any, failCallback: () => any = null) {
			this.pendingLookups.push({ "doneCallback": doneCallback, "failCallback": failCallback });
			
			this.check()
			this.load();
		}
		
		private check() {
			if (this.pendingLookups.length == 0 || this.preloadedResponseObjects.length == 0) return;
			
			var pendingLookup: PendingLookup = this.pendingLookups.shift();
			var embeddedEntry = new EmbeddedEntry($(n2n.ajah.analyze(this.preloadedResponseObjects.shift())), false);
			
			pendingLookup.doneCallback(embeddedEntry);
			n2n.ajah.update();
		}
		
		private load() {
			var that = this;
			$.ajax({
				"url": this.urlStr,
				"data": {
					"propertyPath": this.propertyPath + "[" + this.keyPrefix + (this.startKey++) + "]",
					"draft": this.draftMode ? 1 : 0
				},
				"dataType": "json"
			}).fail(function (jqXHR, textStatus, data) {
				if (jqXHR.status != 200) {
                    rocket.handleErrorResponse(this.urlStr, jqXHR);
				}
				
				that.failResponse();
			}).done(function (data, textStatus, jqXHR) {
				that.doneResponse(data);
			});
		}
		
		private failResponse() {
			if (this.pendingLookups.length == 0) return;
			
			var pendingLookup = this.pendingLookups.shift();
			if (pendingLookup.failCallback !== null) {
				pendingLookup.failCallback();
			}
		}
		
		private doneResponse(data: Object) {
			this.preloadedResponseObjects.push(data);
			this.check();
		}
	}
	
	interface PendingLookup {
		doneCallback: (embeddedEntry: EmbeddedEntry) => any;
		failCallback: () => any;
	}
	
	
	class AddControlFactory {
		private embeddedEntryRetriever: EmbeddedEntryRetriever;
		private label: string;
		
		constructor (embeddedEntryRetriever: EmbeddedEntryRetriever, label: string) {
			this.embeddedEntryRetriever = embeddedEntryRetriever;
			this.label = label;
		}
		
		public create(): AddControl {
			return AddControl.create(this.label, this.embeddedEntryRetriever);
		}
	}
	
	class AddControl {
		private embeddedEntryRetriever: EmbeddedEntryRetriever;
		private jqElem: JQuery;
		private jqButton: JQuery;
		private onNewEntryCallbacks: Array<(EmbeddedEntry) => any> = new Array<(EmbeddedEntry) => any>();
		private examinedEmbeddedEntry: EmbeddedEntry; 
		private disposed: boolean = false;
		
		constructor(jqElem: JQuery, embeddedEntryRetriever: EmbeddedEntryRetriever) {
			this.embeddedEntryRetriever = embeddedEntryRetriever;
			
			this.jqElem = jqElem;
			this.jqButton = jqElem.children("button");
			
			var that = this;
			this.jqButton.on("mouseenter", function () {
				that.embeddedEntryRetriever.setPreloadEnabled(true);
			});
			this.jqButton.on("click", function () {
				if (that.isLoading()) return;
				that.block(true);
				that.embeddedEntryRetriever.lookupNew(
						function (embeddedEntry: EmbeddedEntry) {
							that.examine(embeddedEntry);
						},
						function () {
							that.block(false);
						});
			});
			
		}
		
		public getJQuery(): JQuery {
			return this.jqElem;
		}
		
		private block(blocked: boolean) {
			if (blocked) {
				this.jqButton.prop("disabled", true);
				this.jqElem.addClass("rocket-impl-loading");
			} else {
				this.jqButton.prop("disabled", false);
				this.jqElem.removeClass("rocket-impl-loading");
			}
		}	
		
		private examine(embeddedEntry: EmbeddedEntry) {
			this.block(false);
			
			if (!embeddedEntry.getEntryForm().hasTypeSelector()) {
				this.fireCallbacks(embeddedEntry);
				return;
			}
			
			this.examinedEmbeddedEntry = embeddedEntry;
		}
		
		public dispose() {
			this.disposed = true;
			this.jqElem.remove();
			
			if (this.examinedEmbeddedEntry !== null) {
				this.fireCallbacks(this.examinedEmbeddedEntry);
				this.examinedEmbeddedEntry = null;
			}
		}
		
		public isLoading() {
			return this.jqElem.hasClass("rocket-impl-loading");
		}
		
		private fireCallbacks(embeddedEntry: EmbeddedEntry) {
			if (this.disposed) return;
			
			this.onNewEntryCallbacks.forEach(function (callback: (EmbeddedEntry) => any) {
				callback(embeddedEntry);
			});
		}
		
		public onNewEmbeddedEntry(callback: (EmbeddedEntry) => any) {
			this.onNewEntryCallbacks.push(callback);
		}
		
		public static create(label: string, embeddedEntryRetriever: EmbeddedEntryRetriever): AddControl {
			return new AddControl($("<div />").append($("<button />", { "text": label, "type": "button" })),
					embeddedEntryRetriever);
		} 
	}
}