namespace rocket.display {
	
	export class Entry {
		
		constructor(private jqElem: JQuery) {
		}
		
		get jqQuery(): JQuery {
			return this.jqElem;
		}
		
		show() {
			this.jqElem.show();
		}
		
		hide() {
			this.jqElem.hide();
		}
		
		get generalId(): string {
			return this.jqElem.data("rocket-general-id");
		}
		
		get id(): string {
			if (this.draftId !== null) {
				return this.draftId.toString();
			}
			
			return this.idRep;
		}
		
		get idRep(): string {
			return this.jqElem.data("rocket-id-rep");
		}
		
		get draftId(): number {
			var draftId = parseInt(this.jqElem.data("rocket-draft-id"));
			if (!isNaN(draftId)) {
				return draftId;
			}
			return null;
		}
		
		get identityString(): string {
			return this.jqElem.data("rocket-identity-string");
		}
		
		get entrySelector(): EntrySelector {
			var entrySelectors = EntrySelector.findAll(this.jqElem);
			for (var i in entrySelectors) {
				if (entrySelectors[i].entry === this) {
					return entrySelectors[i];
				}
			}
			return null;
		}
		
		private static from(jqElem: JQuery): Entry {
			var entry = jqElem.data("rocketEntry");
			if (entry instanceof Entry) {
				return entry;
			}
			
			entry = new Entry(jqElem); 
			jqElem.data("rocketEntry", entry);
			
			return entry;
		}
		
		static findFrom(jqElem: JQuery): Entry {
			var jqElem = jqElem.closest(".rocket-entry");
			
			if (jqElem.length == 0) return null;
			
			return Entry.from(jqElem);
		}
		
		static findAll(jqElem: JQuery): Array<Entry> {
			var entries = new Array<Entry>();
			
			jqElem.find(".rocket-entry").each(function () {
				entries.push(Entry.from($(this)));
			});
			
			return entries;
		}
	}
}