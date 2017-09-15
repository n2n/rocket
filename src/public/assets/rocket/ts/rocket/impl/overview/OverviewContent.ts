namespace rocket.impl.overview {
	import cmd = rocket.cmd;
	
	var $ = jQuery;
	
	export class OverviewContent {
		private pages: Array<Page> = new Array<Page>();
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
			rocket.util.IllegalStateError.assertTrue(!this.isInit());
			this._currentPageNo = currentPageNo;
			this._numPages = numPages;
			this._numEntries = numEntries;
			var page = this.createPage(this.currentPageNo);
			page.jqContents = this.jqElem.children();
			this.selectorState.observePage(page);
			
			this.triggerChange();
		}
		
		initSelector(selectorObserver: SelectorObserver) {
			this.selectorState.activate(selectorObserver);
			this.triggerChange();
			
			var fakePage = new Page(0);
			fakePage.hide();
			
			var idReps = selectorObserver.getSelectedIds();
			var unloadedIds = idReps.slice();
			var that = this;
		
			this.pages.forEach(function (page: Page) {
				if (!page.isContentLoaded()) return;
				
				page.entries.forEach(function (entry: display.Entry) {
					let id = entry.id;
					
					let i;
					if (-1 < (i = unloadedIds.indexOf(id))) {
						unloadedIds.splice(i, 1);
					}
				});
			});
			
			this.loadFakePage(fakePage, unloadedIds);
		}
		
		private loadFakePage(fakePage: Page, unloadedIdReps: Array<string>) {
			if (unloadedIdReps.length == 0) {
				fakePage.jqContents = $();
				this.initFakePage(fakePage);	
				return;
			}
			
			this.markPageAsLoading(0);
			
			var that = this;
			$.ajax({
				"url": that.loadUrl,
				"data": { "idReps": unloadedIdReps },
				"dataType": "json"
			}).fail(function (jqXHR, textStatus, data) {
				that.unmarkPageAsLoading(0);
				
				if (jqXHR.status != 200) {
                    rocket.getContainer().handleError(that.loadUrl, jqXHR.responseText);
					return;
				}
				
				throw new Error("invalid response");
			}).done(function (data, textStatus, jqXHR) {
				that.unmarkPageAsLoading(0);
				
				var jqContents = $(n2n.ajah.analyze(data)).find(".rocket-overview-content:first").children();
				fakePage.jqContents = jqContents;
				that.jqElem.append(jqContents);
				n2n.ajah.update();
				
				that.initFakePage(fakePage);
				that.pageLoadded(fakePage);
			});
		}
		
		private initFakePage(fakePage: Page) {
			this._selectorState.init(fakePage);
			this.triggerChange();
		}
		
		get selectedOnly(): boolean {
			return this.allInfo !== null;
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
			
			this._selectorState.selectedEntries.forEach(function (entry: display.Entry) {
				entry.show();
			});	
			
			if (this.allInfo === null) {
				this.allInfo = new AllInfo(visiblePages, scrollTop);
			}
			
			this.triggerChange();
		}
		
		get selectorState(): SelectorState {
			return this._selectorState;
		}
		
		public showAll() {
			if (this.allInfo === null) return;
			
			this.pages.forEach(function (page: Page) {
				page.hide();
			});
			
			this.allInfo.pages.forEach(function (page: Page) {
				page.show();
			});
			
			$("html, body").scrollTop(this.allInfo.scrollTop);
			this.allInfo = null;
			
			this.triggerChange();
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
		
		private setCurrentPageNo(currentPageNo: number) {
			if (this._currentPageNo == currentPageNo) {
				return;
			}
			
			this._currentPageNo = currentPageNo;
			
			this.triggerChange();	
		}
		
		private triggerChange() {
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
			
			this.triggerChange();
		}
		
		public whenChanged(callback: (OverviewContent) => any) {
			this.changedCallbacks.push(callback);
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
			
			if (this.jqLoader === null) {
				this.jqLoader = $("<div />", { "class": "rocket-impl-overview-loading" })
						.insertAfter(this.jqElem.parent("table"));
			}
			
			this.loadingPageNos.push(pageNo);
		}
		
		private unmarkPageAsLoading(pageNo: number) {
			var i = this.loadingPageNos.indexOf(pageNo);
			
			if (-1 == i) return;
			
			this.loadingPageNos.splice(i, 1);
			
			if (this.loadingPageNos.length == 0) {
				this.jqLoader.remove();
				this.jqLoader = null;
			}
		}
		
		private createPage(pageNo: number): Page {
			if (this.containsPageNo(pageNo)) {
				throw new Error();
			}
			
			return this.pages[pageNo] = new Page(pageNo);
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
				that.unmarkPageAsLoading(pageNo);
				
				if (jqXHR.status != 200) {
                    rocket.getContainer().handleError(that.loadUrl, jqXHR.responseText);
					return;
				}
				
				throw new Error("invalid response");
			}).done(function (data, textStatus, jqXHR) {
				that.unmarkPageAsLoading(pageNo);
				that.changeBoundaries(data.additional.numPages, data.additional.numEntries);
				var jqContents = $(n2n.ajah.analyze(data)).find(".rocket-overview-content:first").children();
				that.applyContents(page, jqContents);
				n2n.ajah.update();
			});
		}
	}	
	
	
	class SelectorState {
		private _selectorObserver: SelectorObserver = null;
		private fakePage: Page = null;
		private entries: { [id: string]: display.Entry } = {};
		private changedCallbacks: Array<() => any> = new Array<() => any>();
		
		activate(selectorObserver: SelectorObserver) {
			this._selectorObserver = selectorObserver;
			
			for (let id in this.entries) {
				if (this.entries[id].selector === null) continue;
				
				selectorObserver.observeEntrySelector(this.entries[id].selector);
			}
		}
		
		init(fakePage: Page) {
			if (!this.isActive()) {
				throw new Error("No SelectorObserver provided.");
			}
			
			this.fakePage = fakePage;
			
			var that = this;
			fakePage.entries.forEach(function (entry: display.Entry) {
				if (!that.containsEntryId(entry.id)) {	
					that.registerEntry(entry);
				} else {
					entry.dispose();
				}
			});
		}
		
		get selectorObserver(): SelectorObserver {
			return this._selectorObserver;
		}
		
		isActive(): boolean {
			return this._selectorObserver !== null;
		}
		
		isInit(): boolean {
			return this.fakePage !== null;
		}
		
		observePage(page: Page) {
			var that = this;
			page.entries.forEach(function (entry: display.Entry) {
				if (that.fakePage !== null) {
					that.fakePage.removeEntryById(entry.id);
				}
				
				that.registerEntry(entry);
			});
		}
		
		private registerEntry(entry: display.Entry) {
			this.entries[entry.id] = entry;
			
			if (entry.selector === null) return;
			
			if (this.selectorObserver !== null) {
				this.selectorObserver.observeEntrySelector(entry.selector);
			}
			
			var that = this;
			entry.selector.whenChanged(function () {
				that.triggerChanged();
			});
			entry.on(display.Entry.EventType.DISPOSED, function () {
				delete that.entries[entry.id];
			});
			entry.on(display.Entry.EventType.REMOVED, function () {
				delete that.entries[entry.id];
			});
		}
		
		private containsEntryId(id: string) {
			return this.entries[id] !== undefined;
		}
		
		get selectedEntries(): Array<display.Entry> {
			var entries = new Array<display.Entry>();
			
			var that = this;
			this.selectorObserver.getSelectedIds().forEach(function (id: string) {
				if (that.entries[id] === undefined) return;
				
				entries.push(that.entries[id]);
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