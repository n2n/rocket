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
		private jqToMany;
		private compact: boolean = true;
		private sortable: boolean = true;
		private entries: Array<EmbeddedEntry> = new Array<EmbeddedEntry>();
		private jqEmbedded;
		
		constructor(jqToMany: JQuery) {
			this.jqToMany = jqToMany;
			this.compact = (true == jqToMany.data("compact"));
			this.sortable = (true == jqToMany.data("sortable"))
			
			jqToMany.data("rocketToMany", this);
			
			this.jqEmbedded = $("<div />", {
				"class": "rocket-impl-embedded"
			});
			
			this.jqToMany.append(this.jqEmbedded);
		}
		
		public addEntry(entry: EmbeddedEntry) {
			this.entries.push(entry);
			
			entry.getJQuery().detach();
			this.jqEmbedded.append(entry.getJQuery());
			
			if (!this.compact) {
				entry.expand();
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
		
		constructor(jqEntry: JQuery) {
			this.jqEntry = jqEntry;
			this.jqOrderIndex = jqEntry.find(".rocket-impl-order-index").hide();
		}
		
		public getJQuery(): JQuery {
			return this.jqEntry;
		}
		
		public expand() {
			
		}
		
		public reduce() {
		}
	}
}