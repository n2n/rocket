namespace Rocket.Impl.Overview {
	import cmd = Rocket.Cmd;
	
	var $ = jQuery;
	
	export class OverviewContent {
		private pages: Array<Page> = new Array<Page>();
		private fakePage: Page = null;
		private selectorState: SelectorState = new SelectorState();
		private changedCallbacks: Array<(oc: OverviewContent) => any> = new Array<(oc: OverviewContent) => any>();
		private _currentPageNo: number = null; 
		private _numPages: number;
		private _numEntries: number;
		private allInfo: AllInfo = null;
		
		constructor(private jqElem: JQuery, private loadUrl: Jhtml.Url) {
		}	
		
		isInit(): boolean {
			return this._currentPageNo != null && this._numPages != null && this._numEntries != null;
		}
		
		initFromDom(currentPageNo: number, numPages: number, numEntries: number) {
			this.reset(false);
			this._currentPageNo = currentPageNo;
			this._numPages = numPages;
			this._numEntries = numEntries;
			var page = this.createPage(this.currentPageNo);
			page.jqContents = this.jqElem.children();
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
		
		initSelector(selectorObserver: SelectorObserver) {
			this.selectorState.activate(selectorObserver);
			this.triggerContentChange();
			
			this.buildFakePage();
		}
		
		private buildFakePage() {
			if (!this.selectorState.selectorObserver) return;
			
			if (this.fakePage) {
				throw new Error("Fake page already existing.");
			}
			
			this.fakePage = new Page(0);
			this.fakePage.hide();
			
			var idReps = this.selectorState.selectorObserver.getSelectedIds();
			var unloadedIds = idReps.slice();
			var that = this;
		
			this.selectorState.entries.forEach(function (entry: Display.Entry) {
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
			
			var fakePage = this.fakePage;
			
			Jhtml.Monitor.of(this.jqElem.get(0)).lookupModel(this.loadUrl.extR(null, { "idReps": unloadedIdReps }))
					.then((model: Jhtml.Model) => {
				if (fakePage !== this.fakePage) return; 
				
				this.unmarkPageAsLoading(0);
				
				var jqContents = $(model.snippet.elements).find(".rocket-overview-content:first").children();
				fakePage.jqContents = jqContents;
				this.jqElem.append(jqContents);
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
			if (!this.selectorState.isActive()) return null;
			
			if (this.fakePage !== null && this.fakePage.isContentLoaded()) {
				return this.selectorState.selectedEntries.length;
			}
			
			return this.selectorState.selectorObserver.getSelectedIds().length;
		}
		
		get selectable(): boolean {
			return this.selectorState.selectorObserver != null;
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
			this.changedCallbacks.forEach(function (callback) {
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
			this.changedCallbacks.push(callback);
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
			
			this.jqElem.prepend(jqContents);
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
						.insertAfter(this.jqElem.parent("table"));
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
			
			var that = this;
			$.ajax({
				"url": that.loadUrl.toString(),
				"data": { "pageNo": pageNo },
				"dataType": "json"
			}).fail(function (jqXHR, textStatus, data) {
				if (page !== that.pages[pageNo]) return;
				
				that.unmarkPageAsLoading(pageNo);
				
				if (jqXHR.status != 200) {
                    Rocket.getContainer().handleError(that.loadUrl, jqXHR.responseText);
					return;
				}
				
				throw new Error("invalid response");
			}).done(function (data, textStatus, jqXHR) {
				if (page !== that.pages[pageNo]) return;
				
				that.unmarkPageAsLoading(pageNo);
				
				that.initPageFromResponse(page, data);
				that.triggerContentChange();
			});
		}
		
		private initPageFromResponse(page: Page, snippet: Jhtml.Snippet, data: any) {
			this.changeBoundaries(data.numPages, data.numEntries);
			
			var jqContents = $(snippet.elements).find(".rocket-overview-content:first").children().toArray();
			
			snippet.elements = jqContents.toArray();
			this.applyContents(page, jqContents);
			snippet.markAttached();
		}
	}	
	
	
	class SelectorState {
		private _selectorObserver: SelectorObserver = null;
		private entryMap: { [id: string]: Display.Entry } = {};
		private fakeEntryMap: { [id: string]: Display.Entry } = {};
		private changedCallbacks: Array<() => any> = new Array<() => any>();
		private _autoShowSelected: boolean = false;
		
		activate(selectorObserver: SelectorObserver) {
			if (this._selectorObserver) {
				throw new Error("Selector state already activated");
			}
			
			this._selectorObserver = selectorObserver;
			
			if (!selectorObserver) return;
			
			for (let id in this.entryMap) {
				if (this.entryMap[id].selector === null) continue;
				
				selectorObserver.observeEntrySelector(this.entryMap[id].selector);
			}
		}
		
		observeFakePage(fakePage: Page) {
			var that = this;
			fakePage.entries.forEach(function (entry: Display.Entry) {
				if (that.containsEntryId(entry.id)) {
					entry.dispose();
				} else {
					that.registerEntry(entry);
				}
			});
		}
		
		get selectorObserver(): SelectorObserver {
			return this._selectorObserver;
		}
		
		isActive(): boolean {
			return this._selectorObserver != null;
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
			this.entryMap[entry.id] = entry;
			if (fake) {
				this.fakeEntryMap[entry.id] = entry;
			}
			
			if (entry.selector === null) return;
			
			if (this.selectorObserver !== null) {
				this.selectorObserver.observeEntrySelector(entry.selector);
			}
			
			if (this.autoShowSelected && entry.selector.selected) {
				entry.show();
			}
			
			var that = this;
			entry.selector.whenChanged(function () {
				if (that.autoShowSelected && entry.selector.selected) {
					entry.show();
				}
				
				that.triggerChanged();
			});

			var onFunc = function () {
				if (that.entryMap[entry.id] !== entry) return;
			
				delete that.entryMap[entry.id];
				delete that.fakeEntryMap[entry.id];
			};
			entry.on(Display.Entry.EventType.DISPOSED, onFunc);
			entry.on(Display.Entry.EventType.REMOVED, onFunc);
		}
		
		private containsEntryId(id: string) {
			return this.entryMap[id] !== undefined;
		}
		
		get entries(): Array<Display.Entry> {
			var k: any = Object;
			return k.values(this.entryMap);
		}
		
		get selectedEntries(): Array<Display.Entry> {
			var entries = new Array<Display.Entry>();
			
			for (let entry of this.entries) {
				if (!entry.selector || !entry.selector.selected) continue;
				
				entries.push(entry);
			}
			
			return entries;
		}
		
		get autoShowSelected(): boolean {
			return this._autoShowSelected;
		}
		
		set autoShowSelected(showSelected: boolean) {
			this._autoShowSelected = showSelected; 
		}
		
		showSelectedEntriesOnly() {
			this.entries.forEach(function (entry: Display.Entry) {
				if (entry.selector.selected) {
					entry.show();
				} else {
					entry.hide();
				}
			});
		}
		
		hideEntries() {
			this.entries.forEach(function (entry: Display.Entry) {
				entry.hide();
			});
		}
		
		private triggerChanged() {
			this.changedCallbacks.forEach(function (callback) {
				callback();
			});
		}
				
		whenChanged(callback: () => any) {
			this.changedCallbacks.push(callback);
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
			
			this._entries = Display.Entry.findAll(this.jqContents, true);
			
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
				
				this._entries[i].jqQuery.remove();
				this._entries.splice(parseInt(i), 1);
				return; 
			}
		}
	}
}