namespace rocket.impl.overview {
	import cmd = rocket.cmd;
	import display = rocket.display;

	export interface SelectorObserver {
		
		observeEntrySelector(entrySelector: display.EntrySelector);
		
		getSelectedIds(): Array<string>;
	}
	
	export class MultiEntrySelectorObserver implements SelectorObserver {
		private _selectedIds: Array<string>;
		
		constructor(private originalIdReps: Array<string> = new Array<string>()) {
			this._selectedIds = originalIdReps;
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
		}
		
		private chSelect(selected: boolean, idRep: string) {
			if (selected) {
				if (-1 < this._selectedIds.indexOf(idRep)) return;
				
				this._selectedIds.push(idRep);
				return;
			}
			
			var i;
			if (-1 < (i = this._selectedIds.indexOf(idRep))) {
				this._selectedIds.splice(i, 1);
			}
		}
		
		getSelectedIds(): Array<string> {
			return this._selectedIds;
		}
		
		setSelectedIds(selectedIds: Array<string>) {
			this._selectedIds = selectedIds;
			
			selectedIds.forEach(function (id: string) {
			});
		}
	}
}