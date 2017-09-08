namespace rocket.display {
	
	export class EntrySelector {
		private changedCallbacks: Array<() => any> = new Array<() => any>();
		private _selected: boolean = false;
		
		constructor(private jqElem: JQuery) {
		}
		
		get jQuery(): JQuery {
			return this.jqElem;
		}
		
		get entry(): Entry {
			return Entry.findFrom(this.jqElem);
		}
		
		get selected(): boolean {
			return this._selected;
		}
		
		set selected(selected: boolean) {
			if (this._selected == selected) return;
			
			this._selected = selected;
			this.triggerChanged();
		}
				
		whenChanged(callback: () => any) {
			this.changedCallbacks.push(callback);
		}
		
		protected triggerChanged() {
			this.changedCallbacks.forEach(function (callback) {
				callback();
			});
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
}