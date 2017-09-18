namespace rocket.impl.overview {
	import cmd = rocket.cmd;
	
	var $ = jQuery;
	
	export class OverviewContent {
		private pages: Array<Page> = new Array<Page>();
		private fakePage: Page = null;
		private _selectorState: SelectorState = new SelectorState();
		private changedCallbacks: Array<(OverviewContent) => any> = new Array<(OverviewContent) => any>();
		private _currentPageNo: number = null; 
		private _numPages: number;
		private _numEntries: number;
		private allInfo: AllInfo = null;
		
		constructor(private jqElem: JQuery, private loadUrl) {
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
			this._selectorState.observePage(page);
			
			if (this.allInfo) {
				this.allInfo = new AllInfo([page], 0);
			}
			
			this.buildFakePage();
			this.triggerContentChange();
		}
		
		initFromResponse(data: any) {
			this.reset(false);
			
			var page: Page = this.createPage(parseInt(data.additional.pageNo));
			this.initPageFromResponse(page, data);
			
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
			this._selectorState.activate(selectorObserver);
			this.triggerContentChange();
			
			this.buildFakePage();
		}
		
		private buildFakePage() {
			if (!this._selectorState.selectorObserver) return;
			
			if (this.fakePage) {
				throw new Error("Fake page already existing.");
			}
			
			this.fakePage = new Page(0);
			this.fakePage.hide();
			
			var idReps = this._selectorState.selectorObserver.getSelectedIds();
			var unloadedIds = idReps.slice();
			var that = this;
		
			this._selectorState.entries.forEach(function (entry: display.Entry) {
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
				this._selectorState.observeFakePage(this.fakePage);
				return;
			}
			
			this.markPageAsLoading(0);
			
			var fakePage = this.fakePage;
			var that = this;
			$.ajax({
				"url": that.loadUrl,
				"data": { "idReps": unloadedIdReps },
				"dataType": "json"
			}).fail(function (jqXHR, textStatus, data) {
				if (fakePage !== that.fakePage) return;
				
				that.unmarkPageAsLoading(0);
				
				if (jqXHR.status != 200) {
                    rocket.getContainer().handleError(that.loadUrl, jqXHR.responseText);
					return;
				}
				
				throw new Error("invalid response");
			}).done(function (data, textStatus, jqXHR) {
				if (fakePage !== that.fakePage) return; 
				
				that.unmarkPageAsLoading(0);
				
				var jqContents = $(n2n.ajah.analyze(data)).find(".rocket-overview-content:first").children();
				fakePage.jqContents = jqContents;
				that.jqElem.append(jqContents);
				n2n.ajah.update();
				
				that._selectorState.observeFakePage(fakePage);
				that.pageLoadded(fakePage);
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
			
			if (this.fakePage) {
				this.fakePage.hide();
			}
			
			this._selectorState.selectedEntries.forEach(function (entry: display.Entry) {
				entry.show();
			});	
			
			if (this.allInfo === null) {
				this.allInfo = new AllInfo(visiblePages, scrollTop);
			}
			
			this.triggerContentChange();
		}
		
//		get selectorState(): SelectorState {
//			return this._selectorState;
//		}
		
		public showAll() {
			if (this.allInfo === null) return;
			
			this.pages.forEach(function (page: Page) {
				page.hide();
			});
			
			if (this.fakePage) {
				this.fakePage.hide();
			}
			
			this.allInfo.pages.forEach(function (page: Page) {
				page.show();
			});
			
			$("html, body").scrollTop(this.allInfo.scrollTop);
			this.allInfo = null;
			
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
			return this._selectorState.selectorObserver.getSelectedIds().length;
		}
		
		get selectable(): boolean {
			return this._selectorState.selectorObserver != null;
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
		
		whenContentChanged(callback: (OverviewContent) => any) {
			this.changedCallbacks.push(callback);
		}
		
		whenSelectionChanged(callback: () => any) {
			this._selectorState.whenChanged(callback);
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
				if (this.pages[pni] === undefined && this.pages[pni].isContentLoaded()) continue;
				
				jqContents.insertAfter(this.pages[pni].jqContents.last());
				this._selectorState.observePage(page);
				this.pageLoadded(page);
				return;
			}
			
			this.jqElem.prepend(jqContents);
			this._selectorState.observePage(page);
			this.pageLoadded(page);
		}
		
		private pageLoadded(page: Page) {
			if (!this.selectedOnly) return;
			
			this._selectorState.selectedEntries.forEach(function (entry: display.Entry) {
				entry.show();
			});
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

			this.addLoader();
			
			this.loadingPageNos.push(pageNo);
		}
		
		private unmarkPageAsLoading(pageNo: number) {
			var i = this.loadingPageNos.indexOf(pageNo);
			
			if (-1 == i) return;
			
			this.loadingPageNos.splice(i, 1);
			
			if (this.loadingPageNos.length == 0) {
				this.removeLoader();
			}
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
				throw new Error();
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
				"url": that.loadUrl,
				"data": { "pageNo": pageNo },
				"dataType": "json"
			}).fail(function (jqXHR, textStatus, data) {
				if (page !== that.pages[pageNo]) return;
				
				that.unmarkPageAsLoading(pageNo);
				
				if (jqXHR.status != 200) {
                    rocket.getContainer().handleError(that.loadUrl, jqXHR.responseText);
					return;
				}
				
				throw new Error("invalid response");
			}).done(function (data, textStatus, jqXHR) {
				if (page !== that.pages[pageNo]) return;
				
				that.unmarkPageAsLoading(pageNo);
				
				that.initPageFromResponse(page, data);
			});
		}
		
		private initPageFromResponse(page: Page, jsonData: any) {
			this.changeBoundaries(jsonData.additional.numPages, jsonData.additional.numEntries);
			var jqContents = $(n2n.ajah.analyze(jsonData)).find(".rocket-overview-content:first").children();
			this.applyContents(page, jqContents);
			n2n.ajah.update();
		}
	}	
	
	
	class SelectorState {
		private _selectorObserver: SelectorObserver = null;
		private entryMap: { [id: string]: display.Entry } = {};
		private fakeEntryMap: { [id: string]: display.Entry } = {};
		private changedCallbacks: Array<() => any> = new Array<() => any>();
		
		activate(selectorObserver: SelectorObserver) {
			this._selectorObserver = selectorObserver;
			
			if (!selectorObserver) return;
			
			for (let id in this.entryMap) {
				if (this.entryMap[id].selector === null) continue;
				
				selectorObserver.observeEntrySelector(this.entryMap[id].selector);
			}
		}
		
		observeFakePage(fakePage: Page) {
			var that = this;
			fakePage.entries.forEach(function (entry: display.Entry) {
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
			page.entries.forEach(function (entry: display.Entry) {
				if (that.fakeEntryMap[entry.id]) {
					that.fakeEntryMap[entry.id].dispose();
				}
				
				that.registerEntry(entry);
			});
		}
		
		private registerEntry(entry: display.Entry, fake: boolean = false) {
			this.entryMap[entry.id] = entry;
			if (fake) {
				this.fakeEntryMap[entry.id] = entry;
			}
			
			if (entry.selector === null) return;
			
			if (this.selectorObserver !== null) {
				this.selectorObserver.observeEntrySelector(entry.selector);
			}
			
			var that = this;
			entry.selector.whenChanged(function () {
				that.triggerChanged();
			});
			console.log("register " + entry.id);
			var onFunc = function () {
				if (that.entryMap[entry.id] !== entry) return;
			
				console.log("unregister " + entry.id);
				delete that.entryMap[entry.id];
				delete that.fakeEntryMap[entry.id];
			};
			entry.on(display.Entry.EventType.DISPOSED, onFunc);
			entry.on(display.Entry.EventType.REMOVED, onFunc);
		}
		
		private containsEntryId(id: string) {
			return this.entryMap[id] !== undefined;
		}
		
		get entries(): Array<display.Entry> {
			var k: any = Object;
			return k.values(this.entryMap);
		}
		
		get selectedEntries(): Array<display.Entry> {
			var entries = new Array<display.Entry>();
			
			var that = this;
			this.selectorObserver.getSelectedIds().forEach(function (id: string) {
				if (that.entryMap[id] === undefined) return;
				
				entries.push(that.entryMap[id]);
			});	
			
			return entries;
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
		private _entries: Array<display.Entry>;
		
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
		
		get entries(): Array<display.Entry> {
			return this._entries;
		}
		
		get jqContents(): JQuery {
			return this._jqContents;
		}
		
		set jqContents(jqContents: JQuery) {
			this._jqContents = jqContents;
			
			this._entries = display.Entry.findAll(this.jqContents, true);
			
			this.disp();
			
			var that = this;
			for (var i in this._entries) {
				let entry = this._entries[i];
				entry.on(display.Entry.EventType.DISPOSED, function () {
					let j = that._entries.indexOf(entry);
					if (-1 == j) return;
					
					that._entries.splice(j, 1);
				});
			}
		}
		
		private disp() {
			if (this._jqContents === null) return;
			
			var that = this;
			this._entries.forEach(function (entry: display.Entry) {
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