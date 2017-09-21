namespace Rocket.Display {
	export class EntryForm {
		private jqEntryForm: JQuery;
		
		constructor (jqEntryForm: JQuery) {
			this.jqEntryForm = jqEntryForm;
		}
		
		public getJQuery(): JQuery {
			return this.jqEntryForm;
		}
		
		public hasTypeSelector(): boolean {
			return this.jqEntryForm.find(".rocket-type-dependent-entry-form").length > 0;
		}	
		
		public static from(jqElem: JQuery, create: boolean = false): EntryForm {
			var entryForm = jqElem.data("rocketEntryForm");
			if (entryForm instanceof EntryForm) return entryForm;
		
			if (!create) return null;
			
			entryForm = new EntryForm(jqElem);
			jqElem.data("rocketEntryForm", entryForm);
			return entryForm;
		}
	}	
}