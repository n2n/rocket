namespace Rocket.Impl.Relation {
	
	export class EmbeddedEntryRetriever {
		private urlStr: string;
		private propertyPath: string;
		private draftMode: boolean;
		private startKey: number;
		private keyPrefix: string;
		private preloadEnabled: boolean = false;
		private preloadedResponseObjects: Array<Jhtml.Snippet> = new Array<Jhtml.Snippet>();
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
			let snippet: Jhtml.Snippet = this.preloadedResponseObjects.shift();
			var embeddedEntry = new EmbeddedEntry($(snippet.elements), false);
			
			pendingLookup.doneCallback(embeddedEntry);
			snippet.markAttached();
		}
		
		private load() {
			let url = Jhtml.Url.create(this.urlStr).extR(null, {
				"propertyPath": this.propertyPath + (this.startKey !== null ? "[" + this.keyPrefix + (this.startKey++) + "]" : ""),
				"draft": this.draftMode ? 1 : 0
			});
			Jhtml.lookupModel(url)
				.then((model: Jhtml.Model) => {
					this.doneResponse(model.snippet);
				})
				.catch(e => {
					this.failResponse();
					throw e;
				});
		}
		
		private failResponse() {
			if (this.pendingLookups.length == 0) return;
			
			var pendingLookup = this.pendingLookups.shift();
			if (pendingLookup.failCallback !== null) {
				pendingLookup.failCallback();
			}
		}
		
		private doneResponse(snippet: Jhtml.Snippet) {
			this.preloadedResponseObjects.push(snippet);
			this.check();
		}
	}
	
	interface PendingLookup {
		doneCallback: (embeddedEntry: EmbeddedEntry) => any;
		failCallback: () => any;
	}
	
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
		
		constructor(jqEntry: JQuery, private readOnly: boolean) {
			this.entryGroup = Rocket.Display.StructureElement.from(jqEntry, true);
			
			this.bodyGroup = Rocket.Display.StructureElement.from(jqEntry.children(".rocket-impl-body"), true);
			 
			this.jqOrderIndex = jqEntry.children(".rocket-impl-order-index").hide();
			this.jqSummary = jqEntry.children(".rocket-impl-summary");
			
			this.jqPageCommands = this.bodyGroup.jQuery.children(".rocket-zone-commands");
			
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
			
			this.entryGroup.setGroup(true);
			
			if (asPartOfList) {
				this.jqPageCommands.hide();
			} else {
				this.jqPageCommands.show();
			}
			
			if (this.readOnly) return;
			
			if (asPartOfList) {
				this.jqExpMoveUpButton.show();
				this.jqExpMoveDownButton.show();
				this.jqExpRemoveButton.show();
				this.jqPageCommands.hide();
			} else {
				this.jqExpMoveUpButton.hide();
				this.jqExpMoveDownButton.hide();
				this.jqExpRemoveButton.hide();
				this.jqPageCommands.show();
			}
		}
		
		public reduce() {
			this.entryGroup.show();
			this.jqSummary.show();
			this.bodyGroup.hide();
			
			let jqContentType = this.jqSummary.find(".rocket-impl-content-type:first");
			jqContentType.children("span").text(this.entryForm.curGenericLabel);
			jqContentType.children("i").attr("class", this.entryForm.curGenericIconType);
			
			this.entryGroup.jQuery.removeClass("rocket-group");
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
		
		public dispose() {
			this.jQuery.remove();
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