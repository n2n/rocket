namespace Rocket.Impl.Relation {
	
	export class EmbeddedEntry {
		private entryGroup: Rocket.Display.StructureElement;
		private jqOrderIndex: JQuery;
		private jqSummary: JQuery;
		
		private jqPageCommands: JQuery;
		private bodyGroup: Rocket.Display.StructureElement;
		private _entryForm: Rocket.Display.EntryForm;
		
		private jqExpMoveUpButton: JQuery;
		private jqExpMoveDownButton: JQuery;
		private jqExpRemoveButton: JQuery;
		private jqRedFocusButton: JQuery;
		private jqRedRemoveButton: JQuery;
		
		constructor(jqEntry: JQuery, private readOnly: boolean, sortable: boolean) {
			this.entryGroup = Rocket.Display.StructureElement.from(jqEntry, true);
			
			this.bodyGroup = Rocket.Display.StructureElement.from(jqEntry.children(".rocket-impl-body"), true);
			 
			this.jqOrderIndex = jqEntry.children(".rocket-impl-order-index")/*.hide()*/;
			this.jqSummary = jqEntry.children(".rocket-impl-summary");
			
			this.jqPageCommands = this.bodyGroup.jQuery.children(".rocket-zone-commands");
			
			if (readOnly) {
				var rcl = new Rocket.Display.CommandList(this.jqSummary.children(".rocket-simple-commands"), true);
				this.jqRedFocusButton = rcl.createJqCommandButton({iconType: "fa fa-file", label: "Detail", 
						severity: Rocket.Display.Severity.SECONDARY});
			} else {
				this._entryForm = Rocket.Display.EntryForm.firstOf(jqEntry);
				
				var ecl = this.bodyGroup.getToolbar().getCommandList();
				if (sortable) {
					this.jqExpMoveUpButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-up", label: "Move up" });
					this.jqExpMoveDownButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-down", label: "Move down"});
				} 
				this.jqExpRemoveButton = ecl.createJqCommandButton({ iconType: "fa fa-times", label: "Remove", 
						severity: Rocket.Display.Severity.DANGER }); 
				
				var rcl = new Rocket.Display.CommandList(this.jqSummary.children(".rocket-simple-commands"), true);
				this.jqRedFocusButton = rcl.createJqCommandButton({ iconType: "fa fa-pencil", label: "Edit", 
						severity: Rocket.Display.Severity.WARNING });
				this.jqRedRemoveButton = rcl.createJqCommandButton({ iconType: "fa fa-times", label: "Remove", 
						severity: Rocket.Display.Severity.DANGER });
				
				let formElemsJq = this.bodyGroup.jQuery.find("input, textarea, select, button");
				let changedCallback = () => { 
					this.changed();
					formElemsJq.off("change", changedCallback);
				};
				formElemsJq.on("change", changedCallback);
			}
			
			if (!sortable) {
				jqEntry.find(".rocket-impl-handle").css("visibility", "hidden");
			}
			
			this.reduce();
			
			jqEntry.data("rocketImplEmbeddedEntry", this);
		}
		
		get entryForm(): Rocket.Display.EntryForm {
			return this._entryForm;
		}
		
		public onMove(callback: (up: boolean) => any) {
			if (this.readOnly || !this.jqExpMoveUpButton) return;
			
			this.jqExpMoveUpButton.click(function () {
				callback(true);
			});
			this.jqExpMoveDownButton.click(function () {
				callback(false);
			});
		}
				
		public onRemove(callback: () => any) {
			if (this.readOnly) return;
			
			this.jqExpRemoveButton.click(function () {
				callback();
			});
			this.jqRedRemoveButton.click(function () {
				callback();
			});
		}
		
		public onFocus(callback: () => any) {
			this.jqRedFocusButton.click(function () {
				callback();
			});
			
			this.bodyGroup.onShow(function () {
				callback();
			});
		}
		
		get jQuery(): JQuery {
			return this.entryGroup.jQuery;
		}
		
		public getExpandedCommandList(): Rocket.Display.CommandList {
			return this.bodyGroup.getToolbar().getCommandList();
		}
		
		public expand(asPartOfList: boolean = true) {
			this.entryGroup.show();
			this.jqSummary.hide();
			this.bodyGroup.show();
			
			this.entryGroup.setGroup(true);
			
			if (asPartOfList) {
				this.jqPageCommands.hide();
			} else {
				this.jqPageCommands.show();
			}
			
			if (this.readOnly) return;
			
			if (asPartOfList) {
				if (this.jqExpMoveUpButton) this.jqExpMoveUpButton.show();
				if (this.jqExpMoveDownButton) this.jqExpMoveDownButton.show();
				this.jqExpRemoveButton.show();
				this.jqPageCommands.hide();
			} else {
				if (this.jqExpMoveUpButton) this.jqExpMoveUpButton.hide();
				if (this.jqExpMoveDownButton) this.jqExpMoveDownButton.hide();
				this.jqExpRemoveButton.hide();
				this.jqPageCommands.show();
			}
		}
		
		public reduce() {
			this.entryGroup.show();
			this.jqSummary.show();
			this.bodyGroup.hide();
			
			let jqContentType = this.jqSummary.find(".rocket-impl-content-type:first");
			if (this.entryForm) {
				jqContentType.children("span").text(this.entryForm.curGenericLabel);
				jqContentType.children("i").attr("class", this.entryForm.curGenericIconType);
			}
			
			this.entryGroup.setGroup(false);
		}
		
		public hide() {
			this.entryGroup.hide();
		}
		
		public setOrderIndex(orderIndex: number) {
			this.jqOrderIndex.val(orderIndex);
		}
	
		public getOrderIndex(): number {
			return parseInt(<string> this.jqOrderIndex.val());
		}
		
		public setMoveUpEnabled(enabled: boolean) {
			if (this.readOnly || !this.jqExpMoveUpButton) return;
			
			if (enabled) {
				this.jqExpMoveUpButton.show();
			} else {
				this.jqExpMoveUpButton.hide();
			}
		}
		
		public setMoveDownEnabled(enabled: boolean) {
			if (this.readOnly || !this.jqExpMoveDownButton) return;
			
			if (enabled) {
				this.jqExpMoveDownButton.show();
			} else {
				this.jqExpMoveDownButton.hide();
			}
		}
		
		public dispose() {
			this.jQuery.remove();
		}
		
		private changed() {
			let divJq = this.jqSummary.children(".rocket-impl-content").children("div:last");
			divJq.empty();
			divJq.append($("<div />", { "class": "rocket-impl-status", "text": this.jQuery.data("rocket-impl-changed-text") }));
		}
		
//		public static from(jqElem: JQuery, create: boolean = false): EmbeddedEntry {
//			var entry = jqElem.data("rocketImplEmbeddedEntry");
//			if (entry instanceof EmbeddedEntry) {
//				return entry;
//			}
//			
//			if (create) {
//				return new EmbeddedEntry(jqElem); 				
//			}
//			
//			return null;
//		}
	}
}