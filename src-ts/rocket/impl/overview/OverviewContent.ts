namespace Rocket.Impl.Overview {
	import cmd = Rocket.Cmd;
	
	var $ = jQuery;
	
	export class OverviewContent {
		private collection: Display.Collection;
		private pages: Array<Page> = new Array<Page>();
		private fakePage: Page = null;
		private selectorState: SelectorState;
		private _currentPageNo: number = null; 
		private _numPages: number;
		private _numEntries: number;
		private allInfo: AllInfo = null;
		private contentChangedCallbacks: Array<(overviewContent: OverviewContent) => any> = [];
		
		constructor(jqElem: JQuery, private loadUrl: Jhtml.Url) {
			this.collection = Display.Collection.from(jqElem);
			this.selectorState = new SelectorState(this.collection);
		}	
		
		isInit(): boolean {
			return this._currentPageNo != null && this._numPages != null && this._numEntries != null;
		}
		
		initFromDom(currentPageNo: number, numPages: number, numEntries: number) {
			this.reset(false);
			this._currentPageNo = currentPageNo;
			this._numPages = numPages;
			this._numEntries = numEntries;
			let page = this.createPage(this.currentPageNo);
			page.jqContents = this.collection.jQuery.children();
			this.selectorState.observePage(page);
			
			if (this.allInfo) {
				this.allInfo = new AllInfo([page], 0);
			}
			
			this.buildFakePage();
			this.triggerContentChange();
		}
		
		init(currentPageNo: number) {
			this.reset(false);
			this.goTo(currentPageNo);
			
			if (this.allInfo) {
				this.allInfo = new AllInfo([this.pages[currentPageNo]], 0);
			}
			
			this.buildFakePage();
			this.triggerContentChange();
		}
		
		initFromResponse(snippet: Jhtml.Snippet, info: any) {
			this.reset(false);
			
			var page: Page = this.createPage(parseInt(info.pageNo));
			this._currentPageNo = page.pageNo;
			this.initPageFromResponse(page, snippet, info);
			
			if (this.allInfo) {
				this.allInfo = new AllInfo([page], 0);
			}
			
			this.buildFakePage();
			this.triggerContentChange();
		}
		
		clear(showLoader: boolean) {
			this.reset(showLoader);
			
			this.triggerContentChange();
		}
		
		private reset(showLoader: boolean) { 
			let page: Page = null;
			while (undefined !== (page = this.pages.pop())) {
				page.dispose();
				this.unmarkPageAsLoading(page.pageNo);
			}
			
			this._currentPageNo = null;
			
			if (this.fakePage) {
				this.fakePage.dispose();
				this.unmarkPageAsLoading(this.fakePage.pageNo);
				this.fakePage = null;
			}
			
			if (this.allInfo) {
				this.allInfo = new AllInfo([], 0);
			}
			
			if (showLoader) {
				this.addLoader();
			} else {
				this.removeLoader();
			}
		}
		
		initSelector(selectorObserver: Display.SelectorObserver) {
			this.selectorState.activate(selectorObserver);
			this.triggerContentChange();
			
			this.buildFakePage();
		}
		
		private buildFakePage() {
			if (!this.collection.selectable) return;
			
			if (this.fakePage) {
				throw new Error("Fake page already existing.");
			}
			
			this.fakePage = new Page(0);
			this.fakePage.hide();
			
			var idReps = this.collection.selectedIds;
			var unloadedIds = idReps.slice();
			var that = this;
		
			this.collection.entries.forEach(function (entry: Display.Entry) {
				let id = entry.id;
				
				let i;
				if (-1 < (i = unloadedIds.indexOf(id))) {
					unloadedIds.splice(i, 1);
				}
			});
			
			this.loadFakePage(unloadedIds);
			return this.fakePage;
		}
		
		private loadFakePage(unloadedIdReps: Array<string>) {
			if (unloadedIdReps.length == 0) {
				this.fakePage.jqContents = $();
				this.selectorState.observeFakePage(this.fakePage);
				return;
			}
			
			this.markPageAsLoading(0);
			
			let fakePage = this.fakePage;
			
			Jhtml.Monitor.of(this.collection.jQuery.get(0))
					.lookupModel(this.loadUrl.extR(null, { "idReps": unloadedIdReps }))
					.then((model: Jhtml.Model) => {
				if (fakePage !== this.fakePage) return; 
				
				this.unmarkPageAsLoading(0);
				
				var jqContents = $(model.snippet.elements).find(".rocket-collection:first").children();
				fakePage.jqContents = jqContents;
				this.collection.jQuery.append(jqContents);
				model.snippet.markAttached();
				
				this.selectorState.observeFakePage(fakePage);
				this.triggerContentChange();
			});
		}
		
		get selectedOnly(): boolean {
			return this.allInfo != null;
		}
		
		public showSelected() {
			var scrollTop =  $("html, body").scrollTop();
			var visiblePages = new Array<Page>();
			this.pages.forEach(function (page: Page) {
				if (page.visible) {
					visiblePages.push(page);
				}
				page.hide();
			});
			
			this.selectorState.showSelectedEntriesOnly();
			this.selectorState.autoShowSelected = true;	
			
			if (this.allInfo === null) {
				this.allInfo = new AllInfo(visiblePages, scrollTop);
			}
			
			this.updateLoader();
			this.triggerContentChange();
		}
		
//		get selectorState(): SelectorState {
//			return this._selectorState;
//		}
		
		public showAll() {
			if (this.allInfo === null) return;
			
			this.selectorState.hideEntries();
			this.selectorState.autoShowSelected = false;
			
			this.allInfo.pages.forEach(function (page: Page) {
				page.show();
			});
			
			$("html, body").scrollTop(this.allInfo.scrollTop);
			this.allInfo = null;
			
			this.updateLoader();
			this.triggerContentChange();
		}
		
//		containsIdRep(idRep: string): boolean {
//			for (let i in this.pages) {
//				if (this.pages[i].containsIdRep(idRep)) return true;
//			}
//			
//			return false;
//		}
		
		get currentPageNo(): number {
			return this._currentPageNo;
		}
		
		get numPages(): number {
			return this._numPages;
		}
		
		get numEntries(): number {
			return this._numEntries;
		}
		
		get numSelectedEntries(): number {
			if (!this.collection.selectable) return null;
			
			if (this.fakePage !== null && this.fakePage.isContentLoaded()) {
				return this.collection.selectedEntries.length;
			}
			
			return this.collection.selectedIds.length;
		}
		
		get selectable(): boolean {
			return this.collection.selectable;
		}
		
		private setCurrentPageNo(currentPageNo: number) {
			if (this._currentPageNo == currentPageNo) {
				return;
			}
			
			this._currentPageNo = currentPageNo;
			
			this.triggerContentChange();	
		}
		
		private triggerContentChange() {
			var that = this;
			this.contentChangedCallbacks.forEach(function (callback) {
				callback(that);
			});
		}
		
		private changeBoundaries(numPages: number, numEntries: number) {
			if (this._numPages == numPages && this._numEntries == numEntries) {
				return;
			}
			
			this._numPages = numPages;
			this._numEntries = numEntries;
			
			if (this.currentPageNo > this.numPages) {
				this.goTo(this.numPages);
				return;
			}
			
			this.triggerContentChange();
		}
		
		whenContentChanged(callback: (overviewContent: OverviewContent) => any) {
			this.contentChangedCallbacks.push(callback);
		}
		
		whenSelectionChanged(callback: () => any) {
			this.selectorState.whenChanged(callback);
		}
		
		isPageNoValid(pageNo: number): boolean {
			return (pageNo > 0 && pageNo <= this.numPages);
		}
		
		containsPageNo(pageNo: number): boolean {
			return this.pages[pageNo] !== undefined;
		}
		
		private applyContents(page: Page, jqContents: JQuery) {
			if (page.jqContents !== null) {
				throw new Error("Contents already applied.");
			}
			
			page.jqContents = jqContents;
			
			for (var pni = page.pageNo - 1; pni > 0; pni--) {
				if (this.pages[pni] === undefined || !this.pages[pni].isContentLoaded()) continue;
				
				jqContents.insertAfter(this.pages[pni].jqContents.last());
				this.selectorState.observePage(page);
				return;
			}
			
			this.collection.jQuery.prepend(jqContents);
			this.selectorState.observePage(page);
		}
		
		goTo(pageNo: number) {
			if (!this.isPageNoValid(pageNo)) {
				throw new Error("Invalid pageNo: " + pageNo);
			}
			
			if (this.selectedOnly) {
				throw new Error("No paging support for selected entries.");
			}
			
			if (pageNo === this.currentPageNo) {
				return;
			}
			
			if (this.pages[pageNo] === undefined) {
				this.showSingle(pageNo);
				this.load(pageNo);
				this.setCurrentPageNo(pageNo);
				return;
			}
			
			if (this.scrollToPage(this.currentPageNo, pageNo)) {
				this.setCurrentPageNo(pageNo);
				return;
			}
			
			this.showSingle(pageNo);
			this.setCurrentPageNo(pageNo);
		}
		
		private showSingle(pageNo: number) {
			for (var i in this.pages) {
				if (this.pages[i].pageNo == pageNo) {
					this.pages[i].show();
				} else {
					this.pages[i].hide();
				}
			}
		}
		
		private scrollToPage(pageNo: number, targetPageNo: number): boolean {
			var page: Page = null;
			if (pageNo < targetPageNo) {
				for (var i = pageNo; i <= targetPageNo; i++) {
					if (!this.containsPageNo(i) || !this.pages[i].isContentLoaded()) {
						return false;
					}
					
					page = this.pages[i];
					page.show();
				}
			} else {
				for (var i = pageNo; i >= targetPageNo; i--) {
					if (!this.containsPageNo(i) || !this.pages[i].isContentLoaded() || !this.pages[i].visible) {
						return false;
					}
					
					page = this.pages[i];
				}
			}
			
			$("html, body").stop().animate({
				scrollTop: page.jqContents.first().offset().top 
			}, 500);
			
			return true;
		}	
		
		private loadingPageNos: Array<number> = new Array<number>();
		private jqLoader: JQuery = null;
		
		private markPageAsLoading(pageNo: number) {
			if (-1 < this.loadingPageNos.indexOf(pageNo)) {
				throw new Error("page already loading");
			}

			this.loadingPageNos.push(pageNo);
			this.updateLoader();
		}
		
		private unmarkPageAsLoading(pageNo: number) {
			var i = this.loadingPageNos.indexOf(pageNo);
			
			if (-1 == i) return;
			
			this.loadingPageNos.splice(i, 1);
			this.updateLoader();
		}
		
		private updateLoader() {
			for (var i in this.loadingPageNos) {
				if (this.loadingPageNos[i] == 0 && this.selectedOnly) {
					this.addLoader();
					return;
				}
				
				if (this.loadingPageNos[i] > 0 && !this.selectedOnly) {
					this.addLoader();
					return;
				}
			}
			
			this.removeLoader();
		}
		
		private addLoader() {
			if (this.jqLoader) return;
			
			this.jqLoader = $("<div />", { "class": "rocket-impl-overview-loading" })
						.insertAfter(this.collection.jQuery.parent("table"));
		}
		
		private removeLoader() {
			if (!this.jqLoader) return;
			
			this.jqLoader.remove();
			this.jqLoader = null;
		}
		
		
		private createPage(pageNo: number): Page {
			if (this.containsPageNo(pageNo)) {
				throw new Error("Page already exists: " + pageNo);
			}
			
			var page = this.pages[pageNo] = new Page(pageNo);
			if (this.selectedOnly) {
				page.hide();
			}
			return page;
		}
		
		private load(pageNo: number) {
			var page: Page = this.createPage(pageNo);
			
			this.markPageAsLoading(pageNo);
			
			Jhtml.Monitor.of(this.collection.jQuery.get(0))
					.lookupModel(this.loadUrl.extR(null, { "pageNo": pageNo }))
					.then((model: Jhtml.Model) => {
						if (page !== this.pages[pageNo]) return;
						
						this.unmarkPageAsLoading(pageNo);
						
						this.initPageFromResponse(page, model.snippet, model.additionalData);
						this.triggerContentChange();
					})
					.catch(e => {
						if (page !== this.pages[pageNo]) return;
						
						this.unmarkPageAsLoading(pageNo);
						throw e;
					});
		}
		
		private initPageFromResponse(page: Page, snippet: Jhtml.Snippet, data: any) {
			this.changeBoundaries(data.numPages, data.numEntries);
			
			var jqContents = $(snippet.elements).find(".rocket-collection:first").children();
			
			snippet.elements = jqContents.toArray();
			this.applyContents(page, jqContents);
			snippet.markAttached();
		}
	}	
	
	
	class SelectorState {
		private fakeEntryMap: { [id: string]: Display.Entry } = {};
		private _autoShowSelected: boolean = false;
		
		constructor(private collection: Display.Collection) {
		}
		
		activate(selectorObserver: Display.SelectorObserver) {
			if (this.collection.selectable) {
				throw new Error("Selector state already activated");
			}
			
			if (!selectorObserver) return;

			this.collection.setupSelector(selectorObserver);
		}
		
		observeFakePage(fakePage: Page) {
			fakePage.entries.forEach((entry: Display.Entry) => {
				if (this.collection.containsEntryId(entry.id)) {
					entry.dispose();
				} else {
					this.registerEntry(entry);
				}
			});
		}
		
		observePage(page: Page) {
			var that = this;
			page.entries.forEach(function (entry: Display.Entry) {
				if (that.fakeEntryMap[entry.id]) {
					that.fakeEntryMap[entry.id].dispose();
				}
				
				that.registerEntry(entry);
			});
		}
		
		private registerEntry(entry: Display.Entry, fake: boolean = false) {
			this.collection.registerEntry(entry);
			if (fake) {
				this.fakeEntryMap[entry.id] = entry;
			}
			
			if (entry.selector === null) return;
			
			if (this.autoShowSelected && entry.selector.selected) {
				entry.show();
			}
			
			entry.selector.whenChanged(() => {
				if (this.autoShowSelected && entry.selector.selected) {
					entry.show();
				}
			});
			var onFunc = () => {
				delete this.fakeEntryMap[entry.id];
			};
			entry.on(Display.Entry.EventType.DISPOSED, onFunc);
			entry.on(Display.Entry.EventType.REMOVED, onFunc);
		}
		
		get autoShowSelected(): boolean {
			return this._autoShowSelected;
		}
		
		set autoShowSelected(showSelected: boolean) {
			this._autoShowSelected = showSelected; 
		}
		
		showSelectedEntriesOnly() {
			this.collection.entries.forEach(function (entry: Display.Entry) {
				if (entry.selector.selected) {
					entry.show();
				} else {
					entry.hide();
				}
			});
		}
		
		hideEntries() {
			this.collection.entries.forEach(function (entry: Display.Entry) {
				entry.hide();
			});
		}
		
		whenChanged(callback: () => any) {
			this.collection.onSelectionChanged(callback);
		}
	}
	
	class AllInfo {
		constructor(public pages: Array<Page>, public scrollTop: number) {
		}
	}
	
	class Page {
		private _visible: boolean = true;
		private _entries: Array<Display.Entry>;
		
		constructor(public pageNo: number, private _jqContents: JQuery = null) {
		}
		
		get visible(): boolean {
			return this._visible;
		}
		
		show() {
			this._visible = true;
			this.disp();
		}
		
		hide() {
			this._visible = false;
			this.disp();
		}
		
		dispose() {
			if (!this.isContentLoaded()) return;
			
			this._jqContents.remove();
			this._jqContents = null;
			this._entries = null;
		}
		
		isContentLoaded(): boolean {
			return this.jqContents !== null;
		}
		
		get entries(): Array<Display.Entry> {
			return this._entries;
		}
		
		get jqContents(): JQuery {
			return this._jqContents;
		}
		
		set jqContents(jqContents: JQuery) {
			this._jqContents = jqContents;
			
			this._entries = Display.Entry.filter(this.jqContents);
			
			this.disp();
			
			var that = this;
			for (var i in this._entries) {
				let entry = this._entries[i];
				entry.on(Display.Entry.EventType.DISPOSED, function () {
					let j = that._entries.indexOf(entry);
					if (-1 == j) return;
					
					that._entries.splice(j, 1);
				});
			}
		}
		
		private disp() {
			if (this._jqContents === null) return;
			
			var that = this;
			this._entries.forEach(function (entry: Display.Entry) {
				if (that._visible) {
					entry.show();
				} else {
					entry.hide();
				}
			});
		}
		
		removeEntryById(id: string) {
			for (var i in this._entries) {
				if (this._entries[i].id != id) continue;
				
				this._entries[i].jQuery.remove();
				this._entries.splice(parseInt(i), 1);
				return; 
			}
		}
	}
}