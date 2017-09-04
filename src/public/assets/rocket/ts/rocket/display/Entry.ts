namespace rocket.display {
	
	export class Entry {
		
		constructor(private jqElem: JQuery) {
		}
		
		get jqQuery(): JQuery {
			return this.jqElem;
		}
		
		static findFrom(jqElem: JQuery): Entry {
			var jqElem = jqElem.closest(".rocket-entry");
			
			if (jqElem.length == 0) return null;
			
			var entry = jqElem.data("rocketEntry");
			if (entry instanceof Entry) {
				return entry;
			}
			
			entry = new Entry(jqElem); 
			jqElem.data("rocketEntry", entry);
			
			return entry;
		}
	}
}