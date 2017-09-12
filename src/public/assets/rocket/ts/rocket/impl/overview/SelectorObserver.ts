namespace rocket.impl.overview {
	import cmd = rocket.cmd;
	import display = rocket.display;

	export interface SelectorObserver {
		
		observeEntrySelector(entrySelector: display.EntrySelector);
		
		getSelectedIds(): Array<string>;
	}
	
	export class MultiEntrySelectorObserver implements SelectorObserver {
		private selectedIds: Array<string>;
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
			
			var id = selector.entry.id;
			selector.selected = this.containsSelectedId(id);
			this.selectors[id] = selector;
		}
		
		public containsSelectedId(id: string): boolean {
			return -1 < this.selectedIds.indexOf(id);
		}
		
		private chSelect(selected: boolean, idRep: string) {
			if (selected) {
				if (-1 < this.selectedIds.indexOf(idRep)) return;
				
				this.selectedIds.push(idRep);
				return;
			}
			
			var i;
			if (-1 < (i = this.selectedIds.indexOf(idRep))) {
				this.selectedIds.splice(i, 1);
			}
		}
		
		getSelectedIds(): Array<string> {
			return this.selectedIds;
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