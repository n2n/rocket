namespace Rocket.Display {

	export class Collection {
		private entryMap: { [id: string]: Entry } = {};
		private sortedEntries: Entry[];
		private selectorObserver: SelectorObserver;
		private selectionChangedCallbacks: Array<() => any> = new Array<() => any>();
		private insertedCbr = new Jhtml.Util.CallbackRegistry<InsertedCallback>();
		
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
			this.selectionChangedCallbacks.forEach((callback) => {
				callback();
			});
		}
				
		whenSelectionChanged(callback: () => any) {
			this.selectionChangedCallbacks.push(callback);
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
			
			for (let entry of Entry.findAll(this.elemJq, false)) {
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
				"start": function (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) {
					console.log("start " + ui.item.index());
//					var oldIndex = ui.item.index();
				},
				"update": function (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) {
//					let entry = Entry.find(ui.item, true);
//					if (entry)
					
					console.log("update< " + ui.item.html());
					console.log("update> " + ui.sender.html());
					
//					var newIndex = ui.item.index();
//					
//					that.switchIndex(oldIndex, newIndex);
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
		
		insertAfter(aboveEntry: Entry|null, entries: Entry[]) {
			if (aboveEntry !== null) {
				this.valEntry(aboveEntry)
			}
			
			for (let entry of entries.reverse()) {
				if (aboveEntry) {
					entry.jQuery.insertAfter(aboveEntry.jQuery);
				} else {
					this.elemJq.prepend(entry.jQuery);
				}
			}
			
			this.insertedCbr.fire(entries, aboveEntry);
		}
		
		onInserted(callback: InsertedCallback) {
			this.insertedCbr.on(callback);
		}
		
		offInserted(callback: InsertedCallback) {
			this.insertedCbr.off(callback);
		}
		
		static readonly CSS_CLASS = "rocket-collection";
		
		static from(jqElem: JQuery, create: boolean = true): Collection {
			var collection = jqElem.data("rocketCollection");
			if (collection instanceof Collection) return collection;
		
			if (!create) return null;
			
			collection = new Collection(jqElem);
			jqElem.data("rocketCollection", collection);
			jqElem.addClass(Collection.CSS_CLASS);
			return collection;
		}
		
		static of(jqElem:JQuery) {
			jqElem = jqElem.closest("." + Collection.CSS_CLASS);
			if (jqElem.length == 0) return null;
			
			return Collection.from(jqElem, true);
		}
	}
	
	
	export interface InsertedCallback {
		(entries: Entry[], aboveEntry: Entry): any
	}
}