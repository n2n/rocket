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
		public sortable: boolean = false;
		
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
		
		public lookupNew(doneCallback: (embeddedEntry: EmbeddedEntry, snippet: Jhtml.Snippet) => any, 
				failCallback: () => any = null) {
			this.pendingLookups.push({ "doneCallback": doneCallback, "failCallback": failCallback });
			
			this.check()
			this.load();
		}
		
		private check() {
			if (this.pendingLookups.length == 0 || this.preloadedResponseObjects.length == 0) return;
			
			var pendingLookup: PendingLookup = this.pendingLookups.shift();
			let snippet: Jhtml.Snippet = this.preloadedResponseObjects.shift();
			var embeddedEntry = new EmbeddedEntry($(snippet.elements), false, this.sortable);
			
			pendingLookup.doneCallback(embeddedEntry, snippet);
		}
		
		private load() {
			let url = Jhtml.Url.create(this.urlStr).extR(null, {
				"propertyPath": this.propertyPath + (this.startKey !== null ? "[" + this.keyPrefix + (this.startKey++) + "]" : ""),
				"draft": this.draftMode ? 1 : 0
			});
			Jhtml.lookupModel(url)
					.then((result) => {
						this.doneResponse(result.model.snippet);
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
		doneCallback: (embeddedEntry: EmbeddedEntry, snippet: Jhtml.Snippet) => any;
		failCallback: () => any;
	}
}