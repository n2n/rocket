namespace rocket.impl.overview {
	import cmd = rocket.cmd;
	import display = rocket.display;

	export interface SelectorObserver {
		
		observeEntrySelector(entrySelector: display.EntrySelector);
		
		getSelectedIdReps(): Array<string>;
	}
	
	export class MultiEntrySelectorObserver implements SelectorObserver {
		private _selectedIdReps: Array<string>;
		
		constructor(private originalIdReps: Array<string>) {
			this._selectedIdReps = originalIdReps;
		}
		
		observerEntrySelector(entrySelector: display.EntrySelector) {
			var control = new display.CheckEntrySelectorControl();
			entrySelector.applyControl(control);
			var that = this;
			control.whenChanged(function () {
				that.chSelect(control.isSelected(), entrySelector.idRep);
			}); 
		}
		
		private chSelect(selected: boolean, idRep: string) {
			if (selected) {
				if (-1 < this._selectedIdReps.indexOf(idRep)) return;
				
				this._selectedIdReps.push(idRep);
				return;
			}
			
			var i;
			if (-1 < (i = this._selectedIdReps.indexOf(idRep))) {
				this._selectedIdReps.splice(i, 1);
			}
		}
		
		getSelectedIdReps(): Array<string> {
			return this._selectedIdReps;
		}
	}
	
	
}