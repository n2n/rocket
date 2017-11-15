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
		
		public registerEntry(entry: Entry) {
		    this.entryMap[entry.id] = entry;
		    
		    if (this.selectorObserver && entry.selector) {
		        this.selectorObserver.observeEntrySelector(entry.selector);
		    }
		    if (this.sortable && entry.selector) {
		    	this.applyHandle(entry.selector);
		    }
		    
			var onFunc = () => {
				if (this.entryMap[entry.id] !== entry) return;
			
				delete this.entryMap[entry.id];
			};
			entry.on(Display.Entry.EventType.DISPOSED, onFunc);
			entry.on(Display.Entry.EventType.REMOVED, onFunc);
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
			
			return this.selectorObserver.getSelectedIds()
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
		
		private _sortable = false;
		
		setupSortable() {
			if (this._sortable) return;
			
			this._sortable = true;
			this.elemJq.sortable({
				"handle": ".rocket-handle",
				"forcePlaceholderSize": true,
		      	"placeholder": "rocket-entry-placeholder",
				"start": function (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) {
//					var oldIndex = ui.item.index();
				},
				"update": function (event: JQueryEventObject, ui: JQueryUI.SortableUIParams) {
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