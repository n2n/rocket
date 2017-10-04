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
/// <reference path="../../display/Group.ts" />
namespace Rocket.Impl.Relation {
	import cmd = Rocket.Cmd;
	import display = Rocket.Display;
	
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
		private originalIdReps: Array<string>;
		private identityStrings: { [key: string]: string};
		private browserLayer: cmd.Layer = null;
		private browserSelectorObserver: Overview.MultiEntrySelectorObserver = null;
		
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
			
			commandList.createJqCommandButton({ label: this.jqElem.data("select-label") })
					.mouseenter(function () {
						that.loadBrowser();
					})
					.click(function () {
						that.openBrowser();
					});
			
			commandList.createJqCommandButton({ label: this.jqElem.data("reset-label") }).click(function () {
				that.reset();
			});
			
			commandList.createJqCommandButton({ label: this.jqElem.data("clear-label") }).click(function () {
				that.clear();
			});
		}
		
		public createSelectedEntry(idRep: string, identityString: string = null): SelectedEntry {
			var entry = new SelectedEntry(this.jqNewEntrySkeleton.clone().appendTo(this.jqUl));
			entry.idRep = idRep;
			if (identityString !== null) {
				entry.label = identityString;
			} else {
				entry.label = this.determineIdentityString(idRep);
			} 
			this.addSelectedEntry(entry);
			return entry;
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
				this.entries.splice(parseInt(i), 1);
			}
		}
		
		public reset() {
		}
		
		public clear() {
			for (var i in this.entries) {
				this.entries[i].jQuery.remove();
			}
			
			this.entries.splice(0, this.entries.length);
		}
		
		public loadBrowser() {
			if (this.browserLayer !== null) return;
			
			var that = this;
			
			this.browserLayer = Rocket.getContainer().createLayer(cmd.Context.findFrom(this.jqElem));
			this.browserLayer.hide();
			this.browserLayer.on(cmd.Layer.EventType.CLOSE, function () {
				that.browserLayer = null;
				that.browserSelectorObserver = null;				
			});
			
			Rocket.exec(this.jqElem.data("overview-tools-url"), {
				showLoadingContext: true,
				currentLayer: this.browserLayer,
				done: function (result: cmd.ExecResult) {
					that.iniBrowserContext(result.context);
				}
			});
		}
		
		private iniBrowserContext(context: cmd.Context) {
			if (this.browserLayer === null) return;
			
			var ocs = Impl.Overview.OverviewContext.findAll(context.jQuery);
			if (ocs.length == 0) return;
			
			ocs[0].initSelector(this.browserSelectorObserver = new Overview.MultiEntrySelectorObserver());
			
			var that = this;
			context.menu.partialCommandList.createJqCommandButton({ label: this.jqElem.data("select-label") }).click(function () {
				that.updateSelection();
				context.layer.hide();
			});
			context.menu.partialCommandList.createJqCommandButton({ label: this.jqElem.data("cancel-label") }).click(function () {
				context.layer.hide();
			});
			
			this.updateBrowser();
		}
		
		public openBrowser() {
			this.loadBrowser();
			
			this.updateBrowser();
			
			this.browserLayer.show();
		}
		
		private updateBrowser() {
			if (this.browserSelectorObserver === null) return;
			
			var selectedIds: Array<string> = new Array();
			this.entries.forEach(function (entry: SelectedEntry) {
				selectedIds.push(entry.idRep);
			});
			
			this.browserSelectorObserver.setSelectedIds(selectedIds);
		}
		
		private updateSelection() {
			if (this.browserSelectorObserver === null) return;
			
			this.clear();
			
			var that = this;
			this.browserSelectorObserver.getSelectedIds().forEach(function (id) {
				var identityString = that.browserSelectorObserver.getIdentityStringById(id);
				if (identityString !== null) {
					that.createSelectedEntry(id, identityString);
					return;
				}
				
				that.createSelectedEntry(id);
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
		
		set idRep(idRep: string) {
			this.jqInput.val(idRep);
		}
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
				var structureElement = Rocket.Display.StructureElement.findFrom(this.jqToMany);
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
					this.firstAddControl.jQuery.hide();
				}
				this.lastAddControl.jQuery.hide();
			} else {
				if (this.firstAddControl !== null) {
					this.firstAddControl.jQuery.show();
				}
				this.lastAddControl.jQuery.show();
			}
		}
		
		private createFirstAddControl(): AddControl {
			var addControl = this.addControlFactory.create();
			var that = this;
				
			this.jqEmbedded.prepend(addControl.jQuery);
			
			addControl.onNewEmbeddedEntry(function(newEntry: EmbeddedEntry) {
				that.insertEntry(newEntry);
//				if (!that.isExpanded()) {
//					that.expand(newEntry);
//				}
			});
			return addControl;
		}
		
		private createEntryAddControl(entry: EmbeddedEntry): AddControl {
			var addControl = this.addControlFactory.create();
			var that = this;
			
			this.entryAddControls.push(addControl);
			addControl.jQuery.insertBefore(entry.jQuery);
			addControl.onNewEmbeddedEntry(function(newEntry: EmbeddedEntry) {
				that.insertEntry(newEntry, entry);
			});
			return addControl;
		}
		
		private createLastAddControl(): AddControl {
			var addControl = this.addControlFactory.create();
			var that = this;
			
			this.jqEmbedded.append(addControl.jQuery);
			addControl.onNewEmbeddedEntry(function(newEntry: EmbeddedEntry) {
				that.addEntry(newEntry);
//				if (!that.isExpanded()) {
//					that.expand(newEntry);
//				}
			});
			return addControl;
		}
		
		
		public insertEntry(entry: EmbeddedEntry, beforeEntry: EmbeddedEntry = null) {
			entry.jQuery.detach();
			
			if (beforeEntry === null) {
				this.entries.unshift(entry);
				this.jqEntries.prepend(entry.jQuery);
			} else {
				entry.jQuery.insertBefore(beforeEntry.jQuery);
				this.entries.splice(beforeEntry.getOrderIndex(), 0, entry);
			}
			
			this.initEntry(entry);
			this.changed();
		}
		
		public addEntry(entry: EmbeddedEntry) {
			entry.setOrderIndex(this.entries.length);
			this.entries.push(entry);
			this.jqEntries.append(entry.jQuery);
		
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
					that.entries[oldIndex].jQuery.insertBefore(that.entries[newIndex].jQuery);
				} else {
					that.entries[oldIndex].jQuery.insertAfter(that.entries[newIndex].jQuery);
				}
				
				that.switchIndex(oldIndex, newIndex);
			});
			
			entry.onRemove(function () {
				that.entries.splice(entry.getOrderIndex(), 1);
				entry.jQuery.remove();
				
				that.changed();
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
			this.expandContext = Rocket.getContainer().createLayer().createContext(window.location.href);
			this.jqEmbedded.detach();
			this.expandContext.applyContent(this.jqEmbedded);
			this.expandContext.layer.pushHistoryEntry(window.location.href);
			
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
			
			var jqCommandButton = this.expandContext.menu.commandList
					.createJqCommandButton({ iconType: "fa fa-times", label: this.closeLabel, severity: display.Severity.WARNING} , true);
			jqCommandButton.click(function () {
				that.expandContext.layer.close();
			});
			
			this.expandContext.on(cmd.Context.EventType.CLOSE, function () {
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
}