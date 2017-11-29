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
	
	export class ToOne {
		constructor(private toOneSelector: ToOneSelector = null, private embedded: ToOneEmbedded = null) {
			
			if (toOneSelector && embedded) {
				embedded.whenChanged(function () {
					if (embedded.currentEntry || embedded.newEntry) {
						toOneSelector.jQuery.hide();
					} else {
						toOneSelector.jQuery.show();
					}
				});
			}
		}
				
		public static from(jqToOne: JQuery): ToOne {
			let toOne: ToOne = jqToOne.data("rocketImplToOne");
			if (toOne instanceof ToOne) {
				return toOne;
			}
			
			let toOneSelector: ToOneSelector = null;
			let jqSelector = jqToOne.children(".rocket-impl-selector");
			if (jqSelector.length > 0) {
				toOneSelector = new ToOneSelector(jqSelector);
			}
			
			
			let jqCurrent = jqToOne.children(".rocket-impl-current");
			let jqNew = jqToOne.children(".rocket-impl-new");
			let jqDetail = jqToOne.children(".rocket-impl-detail");
			let addControlFactory = null;
			
			let toOneEmbedded: ToOneEmbedded = null;
			if (jqCurrent.length > 0 || jqNew.length > 0 || jqDetail.length > 0) {
				if (jqNew.length > 0) {
					var propertyPath = jqNew.data("property-path");
				
					var entryFormRetriever = new EmbeddedEntryRetriever(jqNew.data("new-entry-form-url"), propertyPath, 
							jqNew.data("draftMode"));
					addControlFactory = new AddControlFactory(entryFormRetriever, jqNew.data("add-item-label"), 
							jqNew.data("replace-item-label"));
				}
				
				toOneEmbedded = new ToOneEmbedded(jqToOne, addControlFactory);
				jqCurrent.children(".rocket-impl-entry").each(function () {
					toOneEmbedded.currentEntry = new EmbeddedEntry($(this), toOneEmbedded.isReadOnly());
				});
				jqNew.children(".rocket-impl-entry").each(function () {
					toOneEmbedded.newEntry = new EmbeddedEntry($(this), toOneEmbedded.isReadOnly());
				});
				jqDetail.children(".rocket-impl-entry").each(function () {
					toOneEmbedded.currentEntry = new EmbeddedEntry($(this), true);
				});
				
			}
			
			toOne = new ToOne(toOneSelector, toOneEmbedded);
			jqToOne.data("rocketImplToOne", toOne);		
			
			return toOne;
		}
	}
	
	
	class ToOneEmbedded {
		private jqToOne: JQuery;
		private addControlFactory: AddControlFactory;
		private compact: boolean = true;
		private _currentEntry: EmbeddedEntry;
		private _newEntry: EmbeddedEntry;
		private jqEmbedded: JQuery;
		private jqEntries: JQuery;
		private expandZone: cmd.Zone = null;
		private closeLabel: string;
		private changedCallbacks: Array<() => any> = new Array<() => any>();
		
		constructor(jqToOne: JQuery, addButtonFactory: AddControlFactory = null) {
			this.jqToOne = jqToOne;
			this.addControlFactory = addButtonFactory;
			this.compact = (true == jqToOne.data("compact"));
			this.closeLabel = jqToOne.data("close-label");
			
			this.jqEmbedded = $("<div />", {
				"class": "rocket-impl-embedded"
			});
			this.jqToOne.append(this.jqEmbedded);
			
			this.jqEntries = $("<div />");
			this.jqEmbedded.append(this.jqEntries);
			
			this.changed();
		}
		
		public isReadOnly(): boolean {
			return this.addControlFactory === null;
		}
		
		private addControl: AddControl;
		private firstReplaceControl: AddControl;
		private secondReplaceControl: AddControl;
		
		private changed() {
			if (this.addControlFactory === null) return;
			
			if (!this.addControl) {
				this.addControl = this.createAddControl();
			}
			
			if (!this.firstReplaceControl) {
				this.firstReplaceControl = this.createReplaceControl(true);
			}
			
			if (!this.secondReplaceControl) {
				this.secondReplaceControl = this.createReplaceControl(false);
			}
			
			if (this.currentEntry || this.newEntry) {
				this.addControl.jQuery.hide();
				this.firstReplaceControl.jQuery.show();
				this.secondReplaceControl.jQuery.show();
			} else {
				this.addControl.jQuery.show();
				this.firstReplaceControl.jQuery.hide();
				this.secondReplaceControl.jQuery.hide();
			}
			
			this.triggerChanged();

			Rocket.scan();
		}
		
		private createReplaceControl(prepend: boolean): AddControl {
			var addControl = this.addControlFactory.createReplace();
				
			if (prepend) {
				this.jqEmbedded.prepend(addControl.jQuery);
			} else {
				this.jqEmbedded.append(addControl.jQuery);
			}
			
			addControl.onNewEmbeddedEntry((newEntry: EmbeddedEntry) => {
				this.newEntry = newEntry;
			});
			return addControl;
		}
		
		private createAddControl(): AddControl {
			var addControl = this.addControlFactory.createAdd();
			
			this.jqEmbedded.append(addControl.jQuery);
			addControl.onNewEmbeddedEntry((newEntry: EmbeddedEntry) => {
				this.newEntry = newEntry;
			});
			return addControl;
		}
		
		get currentEntry(): EmbeddedEntry {
			return this._currentEntry;
		}
		
		set currentEntry(entry: EmbeddedEntry) {
			if (this._currentEntry === entry) return;
			
			if (this._currentEntry) {
				this._currentEntry.dispose();
			}
				
			this._currentEntry = entry;
			
			if (!entry) return;
			
			if (this.newEntry) {
				this._currentEntry.jQuery.detach();
			}
		
			entry.onRemove(() => {
				this._currentEntry.dispose();
				this._currentEntry = null;
				this.changed();
			});
			
			this.initEntry(entry);
			this.changed();
		}
	
		get newEntry(): EmbeddedEntry {
			return this._newEntry;
		}
		
		set newEntry(entry: EmbeddedEntry) {
			if (this._newEntry === entry) return;
			
			if (this._newEntry) {
				this._newEntry.dispose();
			}
				
			this._newEntry = entry;
			
			if (!entry) return;
			
			if (this.currentEntry) {
				this.currentEntry.jQuery.detach();
			}
	
			entry.onRemove(() => {
				this._newEntry.dispose();
				this._newEntry = null;
				
				if (this.currentEntry) {
					this.currentEntry.jQuery.appendTo(this.jqEntries);
				}
				
				this.changed();
			});
			
			this.initEntry(entry);
			this.changed();
		}
	
		private initEntry(entry: EmbeddedEntry) {
			this.jqEntries.append(entry.jQuery);
			
			if (this.isExpanded()) {
				entry.expand(false);
			} else {
				entry.reduce();
			}
			
			entry.onFocus(() => {
				this.expand();
			});
		}
		
		public isExpanded(): boolean {
			return this.expandZone !== null;
		}
		
		public expand() {
			if (this.isExpanded()) return;
			
			this.expandZone = Rocket.getContainer().createLayer().createZone(window.location.href);
			this.jqEmbedded.detach();

			let contentJq = $("<div />", { "class": "rocket-content" }).append(this.jqEmbedded);
			this.expandZone.applyContent(contentJq);
			$("<header></header>").insertBefore(contentJq);
			
			this.expandZone.layer.pushHistoryEntry(window.location.href);

			if (this.newEntry) {
				this.newEntry.expand(false);
			}
			
			if (this.currentEntry) {
				this.currentEntry.expand(false);
			}
			
			var jqCommandButton = this.expandZone.menu.commandList
					.createJqCommandButton({ iconType: "fa fa-times", label: this.closeLabel, severity: display.Severity.WARNING} , true);
			jqCommandButton.click(() => {
				this.expandZone.layer.close();
			});
			
			this.expandZone.on(cmd.Zone.EventType.CLOSE, () => {
				this.reduce();
			});
			
			this.changed();
		}
		
		public reduce() {
			if (!this.isExpanded()) return;
			
			this.expandZone = null;
			
			this.jqEmbedded.detach();
			this.jqToOne.append(this.jqEmbedded);
			
			if (this.newEntry) {
				this.newEntry.reduce();
			}
			
			if (this.currentEntry) {
				this.currentEntry.reduce();
			}
			
			this.changed();
		}
		
		private triggerChanged() {
			for (let callback of this.changedCallbacks) {
				callback();
			}
		}
		
		public whenChanged(callback: () => any) {
			this.changedCallbacks.push(callback);
		}
	}
	
	
	
	class ToOneSelector {
		private jqInput: JQuery;
		private originalIdRep: string;
		private identityStrings: { [key: string]: string};
		private jqSelectedEntry: JQuery;
		private jqEntryLabel: JQuery;
		private browserLayer: cmd.Layer = null;
		private browserSelectorObserver: Display.SingleEntrySelectorObserver = null;
		
		constructor(private jqElem: JQuery) {
			this.jqElem = jqElem;
			this.jqInput = jqElem.children("input").hide();
			
			this.originalIdRep = jqElem.data("original-id-rep");
			this.identityStrings = jqElem.data("identity-strings");
			
			this.init();
			this.selectEntry(this.selectedIdRep);
		}
		
		get jQuery(): JQuery {
			return this.jqElem;
		}
		
		get selectedIdRep(): string {
			let idRep: string = this.jqInput.val().toString();
			if (idRep.length == 0) return null;
			
			return idRep;
		}	
		
		private init() {
			this.jqSelectedEntry = $("<div />")
			this.jqSelectedEntry.append(this.jqEntryLabel = $("<span />", { "text": this.identityStrings[this.originalIdRep] }));
			new display.CommandList($("<div />").appendTo(this.jqSelectedEntry), true)
					.createJqCommandButton({ iconType: "fa fa-times", label: this.jqElem.data("remove-entry-label") })
					.click(() => {
						this.clear();				
					});
			this.jqElem.append(this.jqSelectedEntry);
			
			var jqCommandList = $("<div />");
			this.jqElem.append(jqCommandList);
			
			var commandList = new display.CommandList(jqCommandList);
			
			commandList.createJqCommandButton({ label: this.jqElem.data("select-label") })
					.mouseenter(() => {
						this.loadBrowser();
					})
					.click(() => {
						this.openBrowser();
					});
			
			commandList.createJqCommandButton({ label: this.jqElem.data("reset-label") })
					.click(() => {
						this.reset();
					});
		}
		
		private selectEntry(idRep: string, identityString: string = null) {
			this.jqInput.val(idRep);
			
			if (idRep === null) {
				this.jqSelectedEntry.hide();
				return;
			}
			
			this.jqSelectedEntry.show();
			
			if (identityString === null) {
				identityString = this.identityStrings[idRep];
			}
			this.jqEntryLabel.text(identityString);
		}
		
		public reset() {
			this.selectEntry(this.originalIdRep);
		}
		
		public clear() {
			this.selectEntry(null);
		}
		
		public loadBrowser() {
			if (this.browserLayer !== null) return;
			
			var that = this;
			
			this.browserLayer = Rocket.getContainer().createLayer(cmd.Zone.of(this.jqElem));
			this.browserLayer.hide();
			this.browserLayer.on(cmd.Layer.EventType.CLOSE, function () {
				that.browserLayer = null;
				that.browserSelectorObserver = null;				
			});
			
			let url = this.jqElem.data("overview-tools-url");
			this.browserLayer.monitor.exec(url).then(() => {
				that.iniBrowserPage(this.browserLayer.getZoneByUrl(url));
			});
		}
		
		private iniBrowserPage(context: cmd.Zone) {
			if (this.browserLayer === null) return;
			
			var ocs = Impl.Overview.OverviewPage.findAll(context.jQuery);
			if (ocs.length == 0) return;
			
			ocs[0].initSelector(this.browserSelectorObserver = new Display.SingleEntrySelectorObserver());
			
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
			
			this.browserSelectorObserver.setSelectedId(this.selectedIdRep);
		}
		
		private updateSelection() {
			if (this.browserSelectorObserver === null) return;
			
			this.clear();
			
			this.browserSelectorObserver.getSelectedIds().forEach((id) => {
				var identityString = this.browserSelectorObserver.getIdentityStringById(id);
				if (identityString !== null) {
					this.selectEntry(id, identityString);
					return;
				}
				
				this.selectEntry(id);
			});
		}
	}
}