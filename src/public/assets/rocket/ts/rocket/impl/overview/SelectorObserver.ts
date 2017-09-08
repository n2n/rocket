namespace rocket.impl.overview {
	import cmd = rocket.cmd;
	import display = rocket.display;

	export interface SelectorObserver {
		
		observeEntrySelector(entrySelector: display.EntrySelector);
		
		getSelectedIds(): Array<string>;
	}
	
	export class MultiEntrySelectorObserver implements SelectorObserver {
		private _selectedIds: Array<string>;
		
		constructor(private originalIdReps: Array<string>) {
			this._selectedIds = originalIdReps;
		}
		
		observerEntrySelector(selector: display.EntrySelector) {
			var control = new display.CheckEntrySelectorControl();
			selector.applyControl(control);
			var that = this;
			control.whenChanged(function () {
				that.chSelect(control.isSelected(), selector.entry.id);
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
	}
}