namespace rocket.display {
	
	export class EntrySelector {
		constructor(private jqElem: JQuery) {
		}
		
		get jQuery(): JQuery {
			return this.jqElem;
		}
		
		get idRep(): string {
			return this.jqElem.data("entry-id-rep");
		}
		
		get draftId(): number {
			var draftId = parseInt(this.jqElem.data("draft-id-rep"));
			if (!isNaN(draftId)) {
				return draftId;
			}
			return null;
		}
		
		get entry(): Entry {
			return Entry.findFrom(this.jqElem);
		}
		
		
		static findAll(jqElem: JQuery): Array<EntrySelector> {
			var entrySelectors = new Array<EntrySelector>();
			
			jqElem.find(".rocket-entry-selector").each(function () {
				entrySelectors.push(EntrySelector.from($(this)));
			});
			
			return entrySelectors;
		}
		
		static findFrom(jqElem: JQuery): EntrySelector {
			var jqElem = jqElem.closest(".rocket-entry-selector");
			
			if (jqElem.length == 0) return null;
			
			return EntrySelector.findFrom(jqElem);
		}
		
		private static from(jqElem: JQuery): EntrySelector {
			var entrySelector = jqElem.data("rocketEntrySelector");
			if (entrySelector instanceof EntrySelector) {
				return entrySelector;
			}
			
			entrySelector = new Entry(jqElem); 
			jqElem.data("rocketEntrySelector", entrySelector);
			
			return entrySelector;
		}
	}
	
	interface EntrySelectorControl {
		
		
	}
}