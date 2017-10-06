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
		constructor(private embedded: ToOneEmbedded = null) {
		}
				
		public static from(jqToOne: JQuery): ToOne {
			let toOne: ToOne = jqToOne.data("rocketImplToOne");
			if (toOne instanceof ToOne) {
				return toOne;
			}
			
			var jqCurrent = jqToOne.children(".rocket-impl-current");
			var jqNew = jqToOne.children(".rocket-impl-new");
			var addControlFactory = null;
			
			var toOneEmbedded = null;
			if (jqCurrent.length > 0 || jqNew.length > 0) {
				if (jqNew.length > 0) {
					var propertyPath = jqNew.data("property-path");
					
					var startKey: number = 0;
					var testPropertyPath = propertyPath + "[n";
					jqNew.find("input, textarea").each(function () {
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
				
					var entryFormRetriever = new EmbeddedEntryRetriever(jqNew.data("new-entry-form-url"), propertyPath, 
							jqNew.data("draftMode"), startKey, "n");
					addControlFactory = new AddControlFactory(entryFormRetriever, jqNew.data("add-item-label"));
				}
				
				toOneEmbedded = new ToOneEmbedded(jqToOne, addControlFactory);
				jqCurrent.children(".rocket-impl-entry").each(function () {
					toOneEmbedded.currentEntry = new EmbeddedEntry($(this), toOneEmbedded.isReadOnly());
				});
				jqNew.children(".rocket-impl-entry").each(function () {
					toOneEmbedded.newEntry = new EmbeddedEntry($(this), toOneEmbedded.isReadOnly());
				});
			}
			
			toOne = new ToOne(toOneEmbedded);
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
		private expandContext: cmd.Context = null;
		private closeLabel: string;
		
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
		}
		
		private createReplaceControl(prepend: boolean): AddControl {
			var addControl = this.addControlFactory.create();
				
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
			var addControl = this.addControlFactory.create();
			
			this.jqEmbedded.append(addControl.jQuery);
			addControl.onNewEmbeddedEntry(function(newEntry: EmbeddedEntry) {
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
				entry.expand();
			} else {
				entry.reduce();
			}
			
			entry.onFocus(() => {
				this.expand();
			});
		}
		
		public isExpanded(): boolean {
			return this.expandContext !== null;
		}
		
		public expand() {
			if (this.isExpanded()) return;
			
			this.expandContext = Rocket.getContainer().createLayer().createContext(window.location.href);
			this.jqEmbedded.detach();
			this.expandContext.applyContent(this.jqEmbedded);
			this.expandContext.layer.pushHistoryEntry(window.location.href);

			if (this.newEntry) {
				this.newEntry.expand(false);
			}
			
			if (this.currentEntry) {
				this.currentEntry.expand(false);
			}
			
			var jqCommandButton = this.expandContext.menu.commandList
					.createJqCommandButton({ iconType: "fa fa-times", label: this.closeLabel, severity: display.Severity.WARNING} , true);
			jqCommandButton.click(() => {
				this.expandContext.layer.close();
			});
			
			this.expandContext.on(cmd.Context.EventType.CLOSE, () => {
				this.reduce();
			});
			
			this.changed();
			n2n.ajah.update();
		}
		
		public reduce() {
			if (!this.isExpanded()) return;
			
			this.expandContext = null;
			
			this.jqEmbedded.detach();
			this.jqToOne.append(this.jqEmbedded);
			
			if (this.newEntry) {
				this.newEntry.reduce();
			}
			
			if (this.currentEntry) {
				this.currentEntry.reduce();
			}
			
			this.changed();
			n2n.ajah.update();
		}
	}
}