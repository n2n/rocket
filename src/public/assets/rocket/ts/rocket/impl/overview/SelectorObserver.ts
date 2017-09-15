namespace rocket.impl.overview {
	import cmd = rocket.cmd;
	import display = rocket.display;

	export interface SelectorObserver {
		
		observeEntrySelector(entrySelector: display.EntrySelector);
		
		getSelectedIds(): Array<string>;
	}
	
	export class MultiEntrySelectorObserver implements SelectorObserver {
		private selectedIds: Array<string>;
		private identityStrings: { [key: string]: string } = {};
		private selectors: { [key: string]: display.EntrySelector } = {};
		
		constructor(private originalIdReps: Array<string> = new Array<string>()) {
			this.selectedIds = originalIdReps;
		}
		
		observeEntrySelector(selector: display.EntrySelector) {
			var that = this;
			
			var jqCheck = $("<input />", { "type": "checkbox" });
			selector.jQuery.empty();
			selector.jQuery.append(jqCheck);
			
			jqCheck.change(function () {
				selector.selected = jqCheck.is(":checked");
			});
			selector.whenChanged(function () {
				jqCheck.prop("checked", selector.selected);
				that.chSelect(selector.selected, selector.entry.id);
			});
			
			var entry = selector.entry;
			var id = entry.id;
			selector.selected = this.containsSelectedId(id);
			this.selectors[id] = selector;
			this.identityStrings[id] = entry.identityString;
			
			entry.on(display.Entry.EventType.DISPOSED, function () {
				delete that.selectors[id];
			});
			entry.on(display.Entry.EventType.REMOVED, function () {
				that.chSelect(false, id);
			});
		}
		
		public containsSelectedId(id: string): boolean {
			return -1 < this.selectedIds.indexOf(id);
		}
		
		private chSelect(selected: boolean, id: string) {
			if (selected) {
				if (-1 < this.selectedIds.indexOf(id)) return;
				
				this.selectedIds.push(id);
				return;
			}
			
			var i;
			if (-1 < (i = this.selectedIds.indexOf(id))) {
				this.selectedIds.splice(i, 1);
			}
		}
		
		getSelectedIds(): Array<string> {
			return this.selectedIds;
		}
		
		getIdentityStringById(id: string): string {
			if (this.identityStrings[id] !== undefined) {
				return this.identityStrings[id];
			}
			
			return null;
		}
		
		getSelectorById(id: string): display.EntrySelector {
			if (this.selectors[id] !== undefined) {
				return this.selectors[id];
			}
			
			return null;
		}
		
		setSelectedIds(selectedIds: Array<string>) {
			this.selectedIds = selectedIds;
			
			var that = this;
			for (var id in this.selectors) {
				this.selectors[id].selected = that.containsSelectedId(id);
			}
		}
	}
}