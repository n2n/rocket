namespace Rocket.Impl.Order {

	export class Control {
		private entry: Display.Entry;
		private collection: Display.Collection;
		
		constructor(private elemJq: JQuery, private insertMode: InsertMode) {
			this.entry = Display.Entry.of(elemJq);
			this.collection = Display.Collection.of(this.elemJq);
			if (!this.collection) return;
			
			if (!this.collection.selectable) {
				this.collection.setupSelector(new Display.MultiEntrySelectorObserver());
			}
			
			this.collection.setupSortable();
			
			this.collection.whenSelectionChanged(() => {
				this.update();
			})
			this.update();
			
			this.elemJq.click((evt) => {
				evt.preventDefault();
				this.exec();
				return false;
			});
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
		
		private exec() {
			let entries = this.collection.selectedEntries;
			
			if (this.insertMode == InsertMode.BEFORE) {
				this.collection.insertAfter(this.collection.findEntryBefore(this.entry), entries);
			} else {
				this.collection.insertAfter(this.entry, entries);
			}
			
			let newTreeLevel: number;
			if (this.insertMode == InsertMode.CHILD) {
				newTreeLevel = (this.entry.treeLevel || 0) + 1;
			} else {
				newTreeLevel = this.entry.treeLevel;
			}
			
			let idReps = [];
			for (let entry of entries) {
				entry.treeLevel = newTreeLevel;
				idReps.push(entry.id);
				entry.selector.selected = false;
			}
			
			$.get(this.elemJq.attr("href"), { "idReps": idReps });
		}
	}
	
	export enum InsertMode {
		BEFORE, AFTER, CHILD
	}
}