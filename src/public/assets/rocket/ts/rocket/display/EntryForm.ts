namespace Rocket.Display {
	export class EntryForm {
		private jqElem: JQuery;
		private jqTypeSelect: JQuery = null;
		private inited: boolean = false;
		
		constructor (jqElem: JQuery) {
			this.jqElem = jqElem;
		}
		
		public init() {
			if (this.inited) {
				throw new Error("EntryForm already initialized:");
			}
			this.inited = true;
			
			if (!this.jqElem.hasClass("rocket-multi-type")) return;
			
			this.jqTypeSelect = this.jqElem.children(".rocket-type-selector").find("select");
			this.updateDisplay();
			
			this.jqTypeSelect.change(() => {
				this.updateDisplay();
			});
		}
		
		private updateDisplay() {
			if (!this.jqTypeSelect) return;
			
			this.jqElem.children(".rocket-type-entry-form").hide();
			this.jqElem.children(".rocket-type-" + this.jqTypeSelect.val()).show();
		}
		
		get jQuery(): JQuery {
			return this.jqElem;
		}
		
		get multiType(): boolean {
			return this.jqTypeSelect ? true : false;
		}
		
		get curTypeId(): string {
			if (!this.multiType) {
				return this.jqElem.data("rocket-type-id");
			}
			
			throw new Error();
		}
		
		get curGenericLabel(): string {
			if (!this.multiType) {
				return this.jqElem.data("rocket-generic-label");
			}
			
			throw new Error();
		}
		
		get typeMap(): { [typeId: string]: string } {
			let typeMap: { [typeId: string]: string } = {};
			if (!this.multiType) {
				typeMap[this.curTypeId] = this.curGenericLabel;
				return typeMap;  
			}
			
		}
		
		public static from(jqElem: JQuery, create: boolean = true): EntryForm {
			var entryForm = jqElem.data("rocketEntryForm");
			if (entryForm instanceof EntryForm) return entryForm;
		
			if (!create) return null;
			
			entryForm = new EntryForm(jqElem);
			entryForm.init();
			jqElem.data("rocketEntryForm", entryForm);
			return entryForm;
		}
		
		public static findFirst(jqElem: JQuery): EntryForm {
			let jqEntryForm = jqElem.find(".rocket-entry-form:first");
			if (jqEntryForm.length == 0) return null;
			
			return EntryForm.from(jqEntryForm);
		}
		
		public static find(jqElem: JQuery, mulitTypeOnly: boolean = false): Array<EntryForm> {
			let entryForms: Array<EntryForm> = [];
			jqElem.find(".rocket-entry-form" + (mulitTypeOnly ? ".rocket-multi-type": "")).each(function() {
				entryForms.push(EntryForm.from($(this)));	
			});
			return entryForms;
		}
	}	
}