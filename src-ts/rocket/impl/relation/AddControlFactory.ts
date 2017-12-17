namespace Rocket.Impl.Relation {
	
	export class AddControlFactory {
		
		constructor (public embeddedEntryRetriever: EmbeddedEntryRetriever, private addLabel: string,
				private replaceLabel: string = null) {
		}
		
		public createAdd(): AddControl {
			return AddControl.create(this.addLabel, this.embeddedEntryRetriever);
		}
		
		public createReplace(): AddControl {
			return AddControl.create(this.replaceLabel, this.embeddedEntryRetriever);
		}
	}
	
	export class AddControl {
		private embeddedEntryRetriever: EmbeddedEntryRetriever;
		private jqElem: JQuery;
		private jqButton: JQuery;
		private onNewEntryCallbacks: Array<(entry: EmbeddedEntry) => any> = [];
		private jqMultiTypeUl: JQuery;
		private multiTypeEmbeddedEntry: EmbeddedEntry; 
		private disposed: boolean = false;
		
		constructor(jqElem: JQuery, embeddedEntryRetriever: EmbeddedEntryRetriever) {
			this.embeddedEntryRetriever = embeddedEntryRetriever;
			
			this.jqElem = jqElem;
			this.jqButton = jqElem.children("button");
			
			this.jqButton.on("mouseenter", () => {
				this.embeddedEntryRetriever.setPreloadEnabled(true);
			});
			this.jqButton.on("click", () => {
				if (this.isLoading()) return;
				
				if (this.jqMultiTypeUl) {
					this.jqMultiTypeUl.toggle();
					return;
				}
				
				this.block(true);
				this.embeddedEntryRetriever.lookupNew(
						(embeddedEntry: EmbeddedEntry, snippet: Jhtml.Snippet) => {
							this.examine(embeddedEntry, snippet);
						},
						() => {
							this.block(false);
						});
			});
			
		}
		
		get jQuery(): JQuery {
			return this.jqElem;
		}
		
		private block(blocked: boolean) {
			if (blocked) {
				this.jqButton.prop("disabled", true);
				this.jqElem.addClass("rocket-impl-loading");
			} else {
				this.jqButton.prop("disabled", false);
				this.jqElem.removeClass("rocket-impl-loading");
			}
		}	
		
		private examine(embeddedEntry: EmbeddedEntry, snippet: Jhtml.Snippet) {
			this.block(false);
			
			if (!embeddedEntry.entryForm.multiEiType) {
				this.fireCallbacks(embeddedEntry);
				snippet.markAttached();
				return;
			}
			
			this.multiTypeEmbeddedEntry = embeddedEntry;
			
			this.jqMultiTypeUl = $("<ul />", { "class": "rocket-impl-multi-type-menu" });
			this.jqElem.append(this.jqMultiTypeUl);
			
			let typeMap = embeddedEntry.entryForm.typeMap;
			for (let typeId in typeMap) {
				this.jqMultiTypeUl.append($("<li />").append($("<button />", { 
					"type": "button", 
					"text": typeMap[typeId],
					"click": () => {
						embeddedEntry.entryForm.curEiTypeId = typeId;
						this.jqMultiTypeUl.remove();
						this.jqMultiTypeUl = null;
						this.multiTypeEmbeddedEntry = null;
						this.fireCallbacks(embeddedEntry);
						snippet.markAttached();
					}
				})));
			}
		}
		
		public dispose() {
			this.disposed = true;
			this.jqElem.remove();
			
			if (this.multiTypeEmbeddedEntry !== null) {
				this.fireCallbacks(this.multiTypeEmbeddedEntry);
				this.multiTypeEmbeddedEntry = null;
			}
		}
		
		public isLoading() {
			return this.jqElem.hasClass("rocket-impl-loading");
		}
		
		private fireCallbacks(embeddedEntry: EmbeddedEntry) {
			if (this.disposed) return;
			
			this.onNewEntryCallbacks.forEach(function (callback: (entry: EmbeddedEntry) => any) {
				callback(embeddedEntry);
			});
		}
		
		public onNewEmbeddedEntry(callback: (entry: EmbeddedEntry) => any) {
			this.onNewEntryCallbacks.push(callback);
		}
		
		public static create(label: string, embeddedEntryRetriever: EmbeddedEntryRetriever): AddControl {
			return new AddControl($("<div />", { "class": "rocket-impl-add-entry"})
							.append($("<button />", { "text": label, "type": "button", "class": "btn btn-block btn-secondary" })),
					embeddedEntryRetriever);
		} 
	}
}