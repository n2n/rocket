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
		private jqToMany: JQuery;
		private addButtonFactory: AddButtonFactory;
		private compact: boolean = true;
		private sortable: boolean = true;
		private entries: Array<EmbeddedEntry> = new Array<EmbeddedEntry>();
		private jqEmbedded: JQuery;
		private expandContext: cmd.Context = null;
		private closeLabel: string;
		
		
		constructor(jqToMany: JQuery, addButtonFactory: AddButtonFactory = null) {
			this.jqToMany = jqToMany;
			this.addButtonFactory = addButtonFactory;
			this.compact = (true == jqToMany.data("compact"));
			this.sortable = (true == jqToMany.data("sortable"))
			this.closeLabel = jqToMany.data("close-label");
			
			this.jqEmbedded = $("<div />", {
				"class": "rocket-impl-embedded"
			});
			
			this.jqToMany.append(this.jqEmbedded);
			
			if (this.compact) {
				var structureElement = rocket.display.StructureElement.findFrom(this.jqToMany);
				var toolbar = structureElement.getToolbar();
				if (toolbar !== null) {
					var jqButton = toolbar.getCommandList().createJqCommandButton("fa fa-pencil", "Edit", display.Severity.WARNING);
					let that = this;
					jqButton.click(function () {
						that.expand();
					});
				}
			}
			
			if (this.sortable) {
				this.initSortable();
			}
			
			if (this.addButtonFactory !== null) {
				var lastButton = this.addButtonFactory.create();
				this.jqToMany.append(lastButton.getJQuery());
				let that = this;
				lastButton.onNewEmbeddedEntry(function(embeddedEntry: EmbeddedEntry) {
					that.addEntry(embeddedEntry);
					if (!that.isExpanded()) {
						that.expand(embeddedEntry);
					}
				});
			}
		}
		
		public addEntry(entry: EmbeddedEntry) {
			entry.setOrderIndex(this.entries.length);
			this.entries.push(entry);
			
			entry.getJQuery().detach();
			this.jqEmbedded.append(entry.getJQuery());
			
			if (this.isExpanded()) {
				entry.expand();
			} else {
				entry.reduce();
			}
			
			this.moveConf(this.entries.length - 1);
			
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
				
				that.reIndex(oldIndex, newIndex);
			});
			
			entry.onRemove(function () {
				that.entries.splice(entry.getOrderIndex(), 1);
				entry.getJQuery().remove();
				
				var index = 0;
				for (var i in that.entries) {
					that.entries[i].setOrderIndex(index);
					that.entries[index] = that.entries[i];
					index++;
				}	
				
				if (that.entries.length > 0) {
					that.moveConf(0);
					that.moveConf(that.entries.length - 1);
				}
			});
			
			entry.onEdit(function () {
				that.expand(entry);
			});
		}
		
		private reIndex(oldIndex: number, newIndex: number) {
			this.entries[oldIndex].setOrderIndex(newIndex);
			this.entries[newIndex].setOrderIndex(oldIndex);
			
			var entry = this.entries[oldIndex];
			this.entries[oldIndex] = this.entries[newIndex];
			this.entries[newIndex] = entry;
			
			this.moveConf(oldIndex);
			this.moveConf(newIndex);
		}
		
		private moveConf(index: number) {
			this.entries[index].setMoveUpEnabled(index > 0);
			this.entries[index].setMoveDownEnabled(index < this.entries.length - 1);
		}	
		
		private initSortable() {
			var that = this;
			var oldIndex: number = 0;
			this.jqEmbedded.sortable({
				"handle": ".rocket-impl-handle",
				"forcePlaceholderSize": true,
		      	"placeholder": "rocket-impl-entry-placeholder",
				"start": function (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) {
					var oldIndex = ui.item.index();
				},
				"update": function (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) {
					var newIndex = ui.item.index();
					
					that.reIndex(oldIndex, newIndex);
				}
		    }).disableSelection();
		}
		
		
		private enabledSortable() {
			this.jqEmbedded.sortable("enable");
			this.jqEmbedded.disableSelection();
		}
		
		private disableSortable() {
			this.jqEmbedded.sortable("disable");
			this.jqEmbedded.enableSelection();
		}
		
		public isExpanded(): boolean {
			return this.expandContext !== null;
		}
		
		public expand(dominantEntry: EmbeddedEntry = null) {
			if (this.isExpanded()) return;
			
			if (this.sortable) {
				this.disableSortable();
			}
			
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
					.createJqCommandButton("fa fa-times", this.closeLabel, display.Severity.WARNING);
			jqCommandButton.click(function () {
				that.expandContext.getLayer().close();
			});
			
			this.expandContext.onClose(function () {
				that.reduce();
			});
			
			n2n.ajah.update();
		}
		
		public reduce() {
			if (!this.isExpanded()) return;
			
			this.expandContext = null;
			
			this.jqEmbedded.detach();
			this.jqToMany.append(this.jqEmbedded);
			
			for (let i in this.entries) {
				this.entries[i].reduce();
			}
			
			if (this.sortable) {
				this.enabledSortable();
			}
			
			n2n.ajah.update();
		}
		
		public static from(jqToMany: JQuery): ToMany {
			var toMany: ToMany = jqToMany.data("rocketImplToMany");
			if (toMany instanceof ToMany) {
				return toMany;
			}
			
			var jqNews = jqToMany.children(".rocket-impl-news");
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
							jqNews.data("draftMode"), startKey, "n")
			toMany = new ToMany(jqToMany, new AddButtonFactory(entryFormRetriever, jqNews.data("add-item-label")));	
			jqToMany.data("rocketImplToMany", toMany);		
			
			jqToMany.find(".rocket-impl-entry").each(function () {
				toMany.addEntry(new EmbeddedEntry($(this)));
			});
			
			return toMany;
		}
	}
	
	class AddButtonFactory {
		private embeddedEntryRetriever: EmbeddedEntryRetriever;
		private label: string;
		
		constructor (embeddedEntryRetriever: EmbeddedEntryRetriever, label: string) {
			this.embeddedEntryRetriever = embeddedEntryRetriever;
			this.label = label;
		}
		
		public create() {
			return AddControl.create(this.label, this.embeddedEntryRetriever);
		}
	}
	
	class AddControl {
		private embeddedEntryRetriever: EmbeddedEntryRetriever;
		private jqElem: JQuery;
		private jqButton: JQuery;
		private onNewEntryCallbacks: Array<(EmbeddedEntry) => any> = new Array<(EmbeddedEntry) => any>();
		
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
			
			alert("todo");
		}
		
		public isLoading() {
			return this.jqElem.hasClass("rocket-impl-loading");
		}
		
		private fireCallbacks(embeddedEntry: EmbeddedEntry) {
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
	
	class EmbeddedEntry {
		private entryGroup: display.StructureElement;
		private jqOrderIndex: JQuery;
		private jqSummary: JQuery;
		
		private bodyGroup: display.StructureElement;
		private entryForm: display.EntryForm;
		
		private jqExpMoveUpButton: JQuery;
		private jqExpMoveDownButton: JQuery;
		private jqExpRemoveButton: JQuery;
		private jqRedEditButton: JQuery;
		private jqRedRemoveButton: JQuery;
		
		constructor(jqEntry: JQuery) {
			this.entryGroup = display.StructureElement.from(jqEntry, true);
			this.jqOrderIndex = jqEntry.children(".rocket-impl-order-index").hide();
			this.jqSummary = jqEntry.children(".rocket-impl-summary");
			this.bodyGroup = display.StructureElement.from(jqEntry.children(".rocket-impl-body"), true); 
			this.entryForm = display.EntryForm.from(jqEntry, true);
			
			var ecl = this.bodyGroup.getToolbar().getCommandList();
			this.jqExpMoveUpButton = ecl.createJqCommandButton("fa fa-arrow-up", "Move up");
			this.jqExpMoveDownButton = ecl.createJqCommandButton("fa fa-arrow-down", "Move down");
			this.jqExpRemoveButton = ecl.createJqCommandButton("fa fa-times", "Remove", display.Severity.DANGER); 
			
			var rcl = new display.CommandList(this.jqSummary.find(".rocket-simple-commands"), true);
			this.jqRedEditButton = rcl.createJqCommandButton("fa fa-pencil", "Edit", display.Severity.WARNING);
			this.jqRedRemoveButton = rcl.createJqCommandButton("fa fa-times", "Remove", display.Severity.DANGER);
			
			this.reduce();
			
			jqEntry.data("rocketImplEmbeddedEntry", this);
		}
		
		public getEntryForm(): display.EntryForm {
			return this.entryForm;
		}
		
		public onMove(callback: (up: boolean) => any) {
			this.jqExpMoveUpButton.click(function () {
				callback(true);
			});
			this.jqExpMoveDownButton.click(function () {
				callback(false);
			});
		}
				
		public onRemove(callback: () => any) {
			this.jqExpRemoveButton.click(function () {
				callback();
			});
			this.jqRedRemoveButton.click(function () {
				callback();
			});
		}
		
		public onEdit(callback: () => any) {
			this.jqRedEditButton.click(function () {
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
		
		public expand(showCommands: boolean = true) {
			this.entryGroup.show();
			this.jqSummary.hide();
			this.bodyGroup.show();
			
			this.entryGroup.getJQuery().addClass("rocket-group");
			
			if (showCommands) {
				this.jqExpMoveUpButton.show();
				this.jqExpMoveDownButton.show();
				this.jqExpRemoveButton.show();
			} else {
				this.jqExpMoveUpButton.hide();
				this.jqExpMoveDownButton.hide();
				this.jqExpRemoveButton.hide();
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
			if (enabled) {
				this.jqExpMoveUpButton.show();
			} else {
				this.jqExpMoveUpButton.hide();
			}
		}
		
		public setMoveDownEnabled(enabled: boolean) {
			if (enabled) {
				this.jqExpMoveDownButton.show();
			} else {
				this.jqExpMoveDownButton.hide();
			}
		}
		
		public static from(jqElem: JQuery, create: boolean = false): EmbeddedEntry {
			var entry = jqElem.data("rocketImplEmbeddedEntry");
			if (entry instanceof EmbeddedEntry) {
				return entry;
			}
			
			if (create) {
				return new EmbeddedEntry(jqElem); 				
			}
			
			return null;
		}
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
			var embeddedEntry = new EmbeddedEntry($(n2n.ajah.analyze(this.preloadedResponseObjects.shift())));
			
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
}