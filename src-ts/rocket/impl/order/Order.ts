namespace Rocket.Impl.Order {

	export class Control {
		private entry: Display.Entry;
		private collection: Display.Collection;
		private executing: boolean = false;
		
		constructor(private elemJq: JQuery, private insertMode: InsertMode) {
			this.entry = Display.Entry.of(elemJq);
			this.collection = this.entry.collection;
			if (!this.collection || !this.entry.selector) {
				this.elemJq.hide();
				return;
			}
			
			if (!this.collection.selectable) {
				this.collection.setupSelector(new Display.MultiEntrySelectorObserver());
			}
			
			let onSelectionChanged = () => {
				this.update();
			};
			this.collection.onSelectionChanged(onSelectionChanged)
			this.entry.on(Display.Entry.EventType.DISPOSED, () => {
				this.collection.offSelectionChanged(onSelectionChanged);
			});
			
			this.update();
			
			this.elemJq.click((evt) => {
				evt.preventDefault();
				this.exec();
				return false;
			});
			
			this.setupSortable();
		}
		
		private setupSortable() {
			if (this.insertMode != InsertMode.AFTER && this.insertMode != InsertMode.BEFORE) {
				return;
			}
			
			this.collection.setupSortable();
			
			this.collection.onInserted((entries: Display.Entry[], aboveEntry: Display.Entry) => {
				if (this.executing) return;
				
				if ((this.insertMode == InsertMode.AFTER && this.entry === aboveEntry)
						|| (this.insertMode == InsertMode.BEFORE && aboveEntry === null
								&& this.entry === this.collection.entries[1])) {
					this.dingsel(entries);
				}
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
			this.executing = true;
			let entries = this.collection.selectedEntries;
			
			if (this.insertMode == InsertMode.BEFORE) {
				this.collection.insertAfter(this.collection.findEntryBefore(this.entry), entries);
			} else {
				this.collection.insertAfter(this.entry, entries);
			}
			
			this.dingsel(entries);
			this.executing = false;
		}
		
		private dingsel(entries: Display.Entry[]) {
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
			
			let url = new Jhtml.Url(this.elemJq.attr("href")).extR(null, { "idReps": idReps });
			Jhtml.Monitor.of(this.elemJq.get(0)).lookupModel(url);
		}
	}
	
	export enum InsertMode {
		BEFORE, AFTER, CHILD
	}
}