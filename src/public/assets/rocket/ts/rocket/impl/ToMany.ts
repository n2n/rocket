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
	var $ = jQuery;
	
	export class ToMany {
		private jqToMany: JQuery;
		private compact: boolean = true;
		private sortable: boolean = true;
		private entries: Array<EmbeddedEntry> = new Array<EmbeddedEntry>();
		private jqEmbedded: JQuery;
		private expanded: boolean = false;
		
		constructor(jqToMany: JQuery) {
			this.jqToMany = jqToMany;
			this.compact = (true == jqToMany.data("compact"));
			this.expanded = !this.compact;
			this.sortable = (true == jqToMany.data("sortable"))
			
			jqToMany.data("rocketToMany", this);
			
			this.jqEmbedded = $("<div />", {
				"class": "rocket-impl-embedded"
			});
			
			this.jqToMany.append(this.jqEmbedded);
		}
		
		public addEntry(entry: EmbeddedEntry) {
			entry.setOrderIndex(this.entries.length);
			this.entries.push(entry);
			
			entry.getJQuery().detach();
			this.jqEmbedded.append(entry.getJQuery());
			
			if (this.expanded) {
				entry.expand();
				this.expand();
			} else {
				entry.reduce();
				this.reduce();
			}
		}
		
		public expand() {
			if (this.sortable) {
				this.jqEmbedded.sortable("disable");
				this.jqEmbedded.enableSelection();
			}
		}
		
		public reduce() {
			if (this.sortable) {
				var that = this;
				var oldIndex: number = 0;
				this.jqEmbedded.sortable({
					"forcePlaceholderSize": true,
			      	"placeholder": "rocket-impl-entry-placeholder",
					"start": function (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) {
						var oldIndex = ui.item.index();
					},
					"update": function (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) {
						var newIndex = ui.item.index();
						
						that.entries[oldIndex].setOrderIndex(newIndex);
						that.entries[newIndex].setOrderIndex(oldIndex);
						
						var entry = that.entries[oldIndex];
						that.entries[oldIndex] = that.entries[newIndex];
						that.entries[newIndex] = entry;
					}
			    }).disableSelection();
			}
		}
		
		public static from(jqToMany: JQuery): ToMany {
			var toMany: ToMany = jqToMany.data("rocketToMany");
			if (toMany instanceof ToMany) {
				return toMany;
			}
			
			toMany = new ToMany(jqToMany);			
			
			jqToMany.find(".rocket-impl-entry").each(function () {
				toMany.addEntry(new EmbeddedEntry($(this)));
			});
			
			return toMany;
		}
	}
	
	class EmbeddedEntry {
		private jqEntry: JQuery;
		private jqOrderIndex: JQuery;
		private jqSummary: JQuery;
		private jqBody: JQuery;
		
		constructor(jqEntry: JQuery) {
			this.jqEntry = jqEntry;
			this.jqOrderIndex = jqEntry.children(".rocket-impl-order-index").hide();
			this.jqSummary = jqEntry.children(".rocket-impl-summary");
			this.jqBody = jqEntry.children(".rocket-impl-body");
			
			this.reduce();
			
			jqEntry.data("rocketImplEmbeddedEntry", this);
		}
		
		public getJQuery(): JQuery {
			return this.jqEntry;
		}
		
		public expand() {
			this.jqSummary.hide();
			this.jqBody.show();
		}
		
		public reduce() {
			this.jqSummary.show();
			this.jqBody.hide();
		}
		
		public setOrderIndex(orderIndex: number) {
			this.jqOrderIndex.val(orderIndex);
		}
	
		public getOrderIndex(): number {
			return parseInt(this.jqOrderIndex.val());
		}
		
		public static from(jqElem: JQuery, create: boolean = false) {
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
}