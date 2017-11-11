namespace Rocket.Display {

	export class Collection {
		private entryMap: { [id: string]: Entry } = {};
		private selectorObserver: SelectorObserver;
		
		constructor(private elemJq: JQuery) {
		}
		
		scan() {
		    let curEntries = this.entries;
			for (let entry of Entry.findAll(this.elemJq, false)) {
				if (this.entryMap[entry.id] && this.entryMap[entry.id] === entry) {
				    continue;
				}
				
				this.registerEntry(entry);
			}
		}
		
		private registerEntry(entry: Entry) {
		    this.entryMap[entry.id] = entry;
		    
		    if (this.selectorObserver && entry.selector) {
		        this.selectorObserver.observeEntrySelector(entry.selector);
		    }
		}
		
		setupSelector(selectorObserver: SelectorObserver) {
			for (let entry of this.entries) {
				if (!entry.selector) continue;
				
				selectorObserver.observeEntrySelector(entry.selector);
			}
		}
		
		get selectable(): boolean {
			return !!this.selectorObserver;
		}
		
		get jQuery(): JQuery {
			return this.elemJq;
		}
		
		private containsEntryId(id: string) {
			return this.entryMap[id] !== undefined;
		}
		
		get entries(): Array<Entry> {
			var OC: any = Object;
			return OC.values(this.entryMap);
		}
		
		get selectedEntries(): Array<Entry> {
			var entries = new Array<Entry>();
			
			for (let entry of this.entries) {
				if (!entry.selector || !entry.selector.selected) continue;
				
				entries.push(entry);
			}
			
			return entries;
		}
		

        
        
        
		
		static from(jqElem: JQuery, create: boolean = false): Collection {
			var collection = jqElem.data("rocketCollection");
			if (collection instanceof Collection) return collection;
		
			if (!create) return null;
			
			collection = new Collection(jqElem);
			jqElem.data("rocketCollection", collection);
			return collection;
		}
	}
	
}