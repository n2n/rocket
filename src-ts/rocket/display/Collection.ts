namespace Rocket.Display {

	export class Collection {
		private entryMap: { [id: string]: Entry } = {};
		private sortedEntries: Entry[];
		private selectorObserver: SelectorObserver;
		private selectionChangedCbr = new Jhtml.Util.CallbackRegistry<() => any>();
		private insertCbr = new Jhtml.Util.CallbackRegistry<InsertCallback>();
		private insertedCbr = new Jhtml.Util.CallbackRegistry<InsertedCallback>();
		
		constructor(private elemJq: JQuery) {
		}
		
		scan() {
		    this.sortedEntries = null;
		    
			for (let entry of Entry.children(this.elemJq)) {
				if (this.entryMap[entry.id] && this.entryMap[entry.id] === entry) {
				    continue;
				}
				
				this.registerEntry(entry);
			}
		}
		
		public registerEntry(entry: Entry) {
		    this.entryMap[entry.id] = entry;
		    
		    if (this.selectorObserver && entry.selector) {
		        this.selectorObserver.observeEntrySelector(entry.selector);
		    }
		    if (this.sortable && entry.selector) {
		    	this.applyHandle(entry.selector);
		    }
		    
			entry.selector.whenChanged(() => {
				this.triggerChanged();
			});
		    
			var onFunc = () => {
				if (this.entryMap[entry.id] !== entry) return;
			
				delete this.entryMap[entry.id];
			};
			entry.on(Display.Entry.EventType.DISPOSED, onFunc);
			entry.on(Display.Entry.EventType.REMOVED, onFunc);
			
//			entry.jQuery.on("DOMNodeInserted", () => {
//				
//			});
		}
		
		private triggerChanged() {
			this.selectionChangedCbr.fire();
		}
				
		onSelectionChanged(callback: () => any) {
			this.selectionChangedCbr.on(callback);
		}
		
		offSelectionChanged(callback: () => any) {
			this.selectionChangedCbr.off(callback);
		}
		
		setupSelector(selectorObserver: SelectorObserver) {
			this.selectorObserver = selectorObserver;
			for (let entry of this.entries) {
				if (!entry.selector) continue;
				
				selectorObserver.observeEntrySelector(entry.selector);
			}
		}
		
		get selectedIds(): string[] {
			if (!this.selectorObserver) return [];
			
			return this.selectorObserver.getSelectedIds();
		}
		
		get selectable(): boolean {
			return !!this.selectorObserver;
		}
		
		get jQuery(): JQuery {
			return this.elemJq;
		}
		
		containsEntryId(id: string): boolean {
			return this.entryMap[id] !== undefined;
		}
		
		get entries(): Array<Entry> {
			if (this.sortedEntries) {
				return this.sortedEntries;
			}
			
			this.sortedEntries = new Array<Entry>();
			
			for (let entry of Entry.children(this.elemJq)) {
				if (!this.entryMap[entry.id] || this.entryMap[entry.id] !== entry) {
					continue;
				}

				this.sortedEntries.push(entry);
			}
			
			return this.sortedEntries.slice();
		}
		
		get selectedEntries(): Array<Entry> {
			var entries = new Array<Entry>();
			
			for (let entry of this.entries) {
				if (!entry.selector || !entry.selector.selected) continue;
				
				entries.push(entry);
			}
			
			return entries;
		}
		
		private _sortable = false;
		
		setupSortable() {
			if (this._sortable) return;
			
			this._sortable = true;
			this.elemJq.sortable({
				"handle": ".rocket-handle",
				"forcePlaceholderSize": true,
		      	"placeholder": "rocket-entry-placeholder",
				"start": (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) => {
					let entry = Entry.find(ui.item, true);
					this.insertCbr.fire([entry]);
				},
				"update": (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) => {
					this.sortedEntries = null;
					let entry = Entry.find(ui.item, true);
					this.insertedCbr.fire([entry], this.findEntryBefore(entry));
				}
		    })/*.disableSelection()*/;
			
			for (let entry of this.entries) {
				if (!entry.selector) continue;
				
				this.applyHandle(entry.selector);
			}
		}
		
		get sortable(): boolean {
			return this._sortable;
		}
		
		private applyHandle(selector: EntrySelector) {
			selector.jQuery.append($("<div />", { "class": "rocket-handle" })
					.append($("<i></i>", { "class": "fa fa-bars" })));
		}
				
		private enabledSortable() {
			this._sortable = true;
			this.elemJq.sortable("enable");
			this.elemJq.disableSelection();
		}
		
		private disableSortable() {
			this._sortable = false;
			this.elemJq.sortable("disable");
			this.elemJq.enableSelection();
		}
		
		private valEntry(entry: Entry) {
			let id = entry.id;
			if (!this.entryMap[id]) {
				throw new Error("Unknown entry with id " + id);
			}
			
			if (this.entryMap[id] !== entry) {
				throw new Error("Collection contains other entry with same id: " + id);
			}
		}
		
		findEntryBefore(belowEntry: Entry): Entry|null {
			this.valEntry(belowEntry);
			
			let aboveEntry: Entry = null;
			for (let entry of this.entries) {
				if (entry === belowEntry) return aboveEntry;
				
				aboveEntry = entry;
			}
			
			return null;
		}
		
		findNextEntries(beforeEntry: Entry): Entry[] {
			this.valEntry(beforeEntry);
			
			let nextEntries: Entry[] = [];
			for (let entry of this.entries) {
				if (!beforeEntry) {
					nextEntries.push(entry);
				}
				
				if (entry === beforeEntry) {
					beforeEntry = null;
				}
				continue;
			}
			
			return null;
		}
		
		insertAfter(aboveEntry: Entry|null, entries: Entry[]) {
			if (aboveEntry !== null) {
				this.valEntry(aboveEntry)
			}
			
			this.insertCbr.fire(entries);
			
			for (let entry of entries.reverse()) {
				if (aboveEntry) {
					entry.jQuery.insertAfter(aboveEntry.jQuery);
				} else {
					this.elemJq.prepend(entry.jQuery);
				}
			}
			
			this.sortedEntries = null;
			this.insertedCbr.fire(entries, aboveEntry);
		}
		
//		onInsert(callback: InsertCallback) {
//			this.insertCbr.on(callback);
//		}
//		
//		offInsert(callback: InsertCallback) {
//			this.insertCbr.off(callback);
//		}
		
		onInserted(callback: InsertedCallback) {
			this.insertedCbr.on(callback);
		}
		
		offInserted(callback: InsertedCallback) {
			this.insertedCbr.off(callback);
		}
		
		static readonly CSS_CLASS = "rocket-collection";
		
		static test(jqElem: JQuery) {
			if (jqElem.hasClass(Collection.CSS_CLASS)) {
				return Collection.from(jqElem);
			}
			
			return null;
		}
		
		static from(jqElem: JQuery): Collection {
			var collection = jqElem.data("rocketCollection");
			if (collection instanceof Collection) return collection;
		
			collection = new Collection(jqElem);
			jqElem.data("rocketCollection", collection);
			jqElem.addClass(Collection.CSS_CLASS);
			return collection;
		}
		
		static of(jqElem:JQuery) {
			jqElem = jqElem.closest("." + Collection.CSS_CLASS);
			if (jqElem.length == 0) return null;
			
			return Collection.from(jqElem);
		}
	}
	

	export interface InsertCallback {
		(entries: Entry[]): any
	}
	
	export interface InsertedCallback {
		(entries: Entry[], aboveEntry: Entry): any
	}
}