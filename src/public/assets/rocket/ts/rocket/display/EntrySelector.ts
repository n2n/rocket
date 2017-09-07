namespace rocket.display {
	
	export class EntrySelector {
		private control: EntrySelectorControl = null;
		
		constructor(private jqElem: JQuery) {
		}
		
		get jQuery(): JQuery {
			return this.jqElem;
		}
		
		get idRep(): string {
			return this.jqElem.data("entry-id-rep");
		}
		
		get draftId(): number {
			var draftId = parseInt(this.jqElem.data("draft-id-rep"));
			if (!isNaN(draftId)) {
				return draftId;
			}
			return null;
		}
		
		get entry(): Entry {
			return Entry.findFrom(this.jqElem);
		}
		
		applyControl(control: EntrySelectorControl) {
			this.control = control;
			control.setup(this);
		}
		
		get selected(): boolean {
			if (this.control === null) {
				return false;
			}
			
			return this.control.isSelected();
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
	
	export interface EntrySelectorControl {
		
		setup(entrySelector: EntrySelector);
		
		whenChanged(callback: () => any);
		
		isSelected(): boolean;
		
		setSelected(selected: boolean);
	}
	
	abstract class EntrySelectorControlAdapter implements EntrySelectorControl {
		private changedCallbacks: Array<() => any> = new Array<() => any>();
		
		setup(entrySelector: EntrySelector) {
			throw new Error("setup() not implemented.");
		}
		
		whenChanged(callback: () => any) {
			this.changedCallbacks.push(callback);
		}
		
		protected triggerChanged() {
			this.changedCallbacks.forEach(function (callback) {
				callback();
			});
		}
		
		isSelected(): boolean {
			throw new Error("isSelected() not implemented.");
		}
		
		setSelected(selected: boolean) {
			throw new Error("setSelected() not implemented.");
		}
		
	}
	
	export class CheckEntrySelectorControl extends EntrySelectorControlAdapter {
		private jqCheck: JQuery = null;
		
		setup(entrySelector: EntrySelector) {
			if (this.jqCheck !== null) {
				throw new Error("CheckEntrySelectorControl already setup.");
			}
			
			this.jqCheck = $("<input />", { "type": "checkbox" });
			
			var that;
			this.jqCheck.change(function () {
				that.triggerChanged();
			})
			
			entrySelector.jQuery.empty();
			entrySelector.jQuery.append(this.jqCheck);
		}
		
		isSelected(): boolean {
			return this.jqCheck.is(":checked");
		}
		
		setSelected(selected: boolean) {
			this.jqCheck.prop("checked", true);
			this.triggerChanged();
		}
	}
}