namespace Rocket.Impl.Relation {
	
	export class EmbeddedEntryRetriever {
		private urlStr: string;
		private propertyPath: string;
		private draftMode: boolean;
		private startKey: number;
		private keyPrefix: string;
		private preloadEnabled: boolean = false;
		private preloadedResponseObjects: Array<Object> = new Array<Object>();
		private pendingLookups: Array<PendingLookup> = new Array<PendingLookup>();
		
		constructor (lookupUrlStr: string, propertyPath: string, draftMode: boolean, startKey: number = null, 
				keyPrefix: string = null) {
			this.urlStr = lookupUrlStr;
			this.propertyPath = propertyPath;
			this.draftMode = draftMode;
			this.startKey = startKey;
			this.keyPrefix = keyPrefix;
		}
		
		public setPreloadEnabled(preloadEnabled: boolean) {
			if (!this.preloadEnabled && preloadEnabled && this.preloadedResponseObjects.length == 0) {
				this.load();
			}
			
			this.preloadEnabled = preloadEnabled;
		}
		
		public lookupNew(doneCallback: (embeddedEntry: EmbeddedEntry) => any, failCallback: () => any = null) {
			this.pendingLookups.push({ "doneCallback": doneCallback, "failCallback": failCallback });
			
			this.check()
			this.load();
		}
		
		private check() {
			if (this.pendingLookups.length == 0 || this.preloadedResponseObjects.length == 0) return;
			
			var pendingLookup: PendingLookup = this.pendingLookups.shift();
			var embeddedEntry = new EmbeddedEntry($(n2n.ajah.analyze(this.preloadedResponseObjects.shift())), false);
			
			pendingLookup.doneCallback(embeddedEntry);
			n2n.ajah.update();
		}
		
		private load() {
			$.ajax({
				"url": this.urlStr,
				"data": {
					"propertyPath": this.propertyPath + "[" + this.keyPrefix + (this.startKey++) + "]",
					"draft": this.draftMode ? 1 : 0
				},
				"dataType": "json"
			}).fail((jqXHR, textStatus, data) => {
				if (jqXHR.status != 200) {
                    Rocket.handleErrorResponse(this.urlStr, jqXHR);
				}
				
				this.failResponse();
			}).done((data, textStatus, jqXHR) => {
				this.doneResponse(data);
			});
		}
		
		private failResponse() {
			if (this.pendingLookups.length == 0) return;
			
			var pendingLookup = this.pendingLookups.shift();
			if (pendingLookup.failCallback !== null) {
				pendingLookup.failCallback();
			}
		}
		
		private doneResponse(data: Object) {
			this.preloadedResponseObjects.push(data);
			this.check();
		}
	}
	
	interface PendingLookup {
		doneCallback: (embeddedEntry: EmbeddedEntry) => any;
		failCallback: () => any;
	}
	
	export class EmbeddedEntry {
		private entryGroup: Rocket.Display.StructureElement;
		private readOnly: boolean;
		private jqOrderIndex: JQuery;
		private jqSummary: JQuery;
		
		private jqContextCommands: JQuery;
		private bodyGroup: Rocket.Display.StructureElement;
		private _entryForm: Rocket.Display.EntryForm;
		
		private jqExpMoveUpButton: JQuery;
		private jqExpMoveDownButton: JQuery;
		private jqExpRemoveButton: JQuery;
		private jqRedFocusButton: JQuery;
		private jqRedRemoveButton: JQuery;
		
		constructor(jqEntry: JQuery, readOnly: boolean) {
			this.entryGroup = Rocket.Display.StructureElement.from(jqEntry, true);
			this.readOnly = readOnly;
			
			this.bodyGroup = Rocket.Display.StructureElement.from(jqEntry.children(".rocket-impl-body"), true);
			 
			this.jqOrderIndex = jqEntry.children(".rocket-impl-order-index").hide();
			this.jqSummary = jqEntry.children(".rocket-impl-summary");
			
			this.jqContextCommands = this.bodyGroup.jQuery.children(".rocket-context-commands");
			
			if (readOnly) {
				var rcl = new Rocket.Display.CommandList(this.jqSummary.children(".rocket-simple-commands"), true);
				this.jqRedFocusButton = rcl.createJqCommandButton({iconType: "fa fa-file", label: "Detail", 
						severity: Rocket.Display.Severity.SECONDARY});
			} else {
				this._entryForm = Rocket.Display.EntryForm.firstOf(jqEntry);
				
				var ecl = this.bodyGroup.getToolbar().getCommandList();
				this.jqExpMoveUpButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-up", label: "Move up" });
				this.jqExpMoveDownButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-down", label: "Move down"});
				this.jqExpRemoveButton = ecl.createJqCommandButton({ iconType: "fa fa-times", label: "Remove", 
						severity: Rocket.Display.Severity.DANGER }); 
				
				var rcl = new Rocket.Display.CommandList(this.jqSummary.children(".rocket-simple-commands"), true);
				this.jqRedFocusButton = rcl.createJqCommandButton({ iconType: "fa fa-pencil", label: "Edit", 
						severity: Rocket.Display.Severity.WARNING });
				this.jqRedRemoveButton = rcl.createJqCommandButton({ iconType: "fa fa-times", label: "Remove", 
						severity: Rocket.Display.Severity.DANGER });
			}
			
			
			
			this.reduce();
			
			jqEntry.data("rocketImplEmbeddedEntry", this);
		}
		
		get entryForm(): Rocket.Display.EntryForm {
			return this._entryForm;
		}
		
		public onMove(callback: (up: boolean) => any) {
			if (this.readOnly) return;
			
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
			
			this.entryGroup.jQuery.addClass("rocket-group");
			
			if (asPartOfList) {
				this.jqContextCommands.hide();
			} else {
				this.jqContextCommands.show();
			}
			
			if (this.readOnly) return;
			
			if (asPartOfList) {
				this.jqExpMoveUpButton.show();
				this.jqExpMoveDownButton.show();
				this.jqExpRemoveButton.show();
				this.jqContextCommands.hide();
			} else {
				this.jqExpMoveUpButton.hide();
				this.jqExpMoveDownButton.hide();
				this.jqExpRemoveButton.hide();
				this.jqContextCommands.show();
			}
		}
		
		public reduce() {
			this.entryGroup.show();
			this.jqSummary.show();
			this.bodyGroup.hide();
			
			this.entryGroup.jQuery.removeClass("rocket-group");
		}
		
		public hide() {
			this.entryGroup.hide();
		}
		
		public setOrderIndex(orderIndex: number) {
			this.jqOrderIndex.val(orderIndex);
		}
	
		public getOrderIndex(): number {
			return parseInt(this.jqOrderIndex.val());
		}
		
		public setMoveUpEnabled(enabled: boolean) {
			if (this.readOnly) return;
			
			if (enabled) {
				this.jqExpMoveUpButton.show();
			} else {
				this.jqExpMoveUpButton.hide();
			}
		}
		
		public setMoveDownEnabled(enabled: boolean) {
			if (this.readOnly) return;
			
			if (enabled) {
				this.jqExpMoveDownButton.show();
			} else {
				this.jqExpMoveDownButton.hide();
			}
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