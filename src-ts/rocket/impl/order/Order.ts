namespace Rocket.Impl.Order {

	export class Control {
		private entry: Display.Entry;
		private collection: Display.Collection;
		
		constructor(private elemJq: JQuery) {
			this.entry = Display.Entry.of(elemJq);
			this.collection = Display.Collection.of(this.elemJq);
			if (!this.collection) return;
			
			if (!this.collection.sortable) {
				this.collection.setupSelector(new Display.MultiEntrySelectorObserver());
			}
			
			this.collection.setupSortable();
			
			this.collection.whenSelectionChanged(() => {
				this.update();
			})
			this.update();
		}
		
		get jQuery(): JQuery {
			return this.elemJq;
		}
		
		private update() {
			if ((this.entry.selector && this.entry.selector.selected)
					|| this.collection.selectedIds.length == 0) {
				this.elemJq.hide();
			} else {
				this.elemJq.show();
			}
		}
	}
}