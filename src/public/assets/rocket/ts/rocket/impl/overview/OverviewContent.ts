namespace rocket.impl.overview {
	import cmd = rocket.cmd;
	
	var $ = jQuery;
	
	export class OverviewContent {
		private pages: Array<Page> = new Array<Page>();
		private selectorState: SelectorState = null;
		private changedCallbacks: Array<(OverviewContent) => any> = new Array<(OverviewContent) => any>();
		private _currentPageNo: number = null; 
		private _numPages: number;
		private _numEntries: number;
		
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
			this.createPage(this.currentPageNo).jqContents = this.jqElem.children();
			
			var that = this;
			this.changedCallbacks.forEach(function (callback) {
				callback(that);
			});
		}
		
		initSelector(selectorObserver: SelectorObserver) {
			var fakePage = new Page(0);
			this.selectorState = new SelectorState(selectorObserver);
			fakePage.visible = false;
			
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
			
			var that = this;
			$.ajax({
				"url": that.loadUrl,
				"data": { "idReps": unloadedIdReps },
				"dataType": "json"
			}).fail(function (jqXHR, textStatus, data) {
				if (jqXHR.status != 200) {
                    rocket.getContainer().handleError(that.loadUrl, jqXHR.responseText);
					return;
				}
				
				throw new Error("invalid response");
			}).done(function (data, textStatus, jqXHR) {
				var jqContents = $(n2n.ajah.analyze(data)).find(".rocket-overview-content:first").children();
				fakePage.jqContents = jqContents;
				that.jqElem.append(jqContents);
				n2n.ajah.update();
				
				that.initFakePage(fakePage);	
			});
		}
		
		private initFakePage(fakePage: Page) {
			this.selectorState.init(fakePage);
			var that = this;
			this.pages.forEach(function (page: Page) {
				if (!page.isContentLoaded()) return;
				
				that.selectorState.observePage(page);
			});
		}
		
		private showSelected() {
			if (this.selectorState.allInfo !== null) {
				return;
			}
			
			var visiblePages = new Array<Page>();
			this.pages.forEach(function (page: Page) {
				if (page.visible) {
					visiblePages.push(page);
				}
			});
			
			this.selectorState.allInfo = new AllInfo(visiblePages, $("html, body").scrollTop());
			
			this.selectorState.selectedEntries.forEach(function (entry: display.Entry) {
				entry.show();
			});
			
			this.selectorState.allInfo = null;
		}
		
		private showAll() {
			if (this.selectorState.allInfo === null) return;
			
			this.selectorState.allInfo.pages.forEach(function (page: Page) {
				page.visible = true;
			});
			
			$("html, body").scrollTop(this.selectorState.allInfo.scrollTop);
			this.selectorState.allInfo = null;
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
			
			var that = this;
			this.changedCallbacks.forEach(function (callback) {
				callback(that);
			});
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
				return;
			}
			
			this.jqElem.prepend(jqContents);
			
			if (this.selectorState !== null) {
				this.selectorState.observePage(page);
			}
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
				this.pages[i].visible = (this.pages[i].pageNo == pageNo);
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
					page.visible = true;
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
		
		public onNewPage() {
		}
	}	
	
	
	class SelectorState {
		public allInfo: AllInfo = null;
		private fakePage: Page = null;
		private entries: { [id:string]: display.Entry } = {};
		private changedCallbacks: Array<() => any> = new Array<() => any>();
		
		constructor(public selectorObserver: SelectorObserver) {
		}
		
		init(fakePage: Page) {
			this.fakePage = fakePage;
			
			var that = this;
			fakePage.entries.forEach(function (entry: display.Entry) {
				that.registerEntry(entry);
			});
		}
		
		isInit(): boolean {
			return this.fakePage !== null;
		}
		
		observePage(page: Page) {
			if (!this.isInit()) {
				throw new Error("Fake page not yet loaded.");
			}
			
			var that = this;
			page.entries.forEach(function (entry: display.Entry) {
				that.fakePage.removeEntryById(entry.id);
				
				that.registerEntry(entry);
			});
		}
		
		private registerEntry(entry: display.Entry) {
			this.entries[entry.id] = entry;
			
			if (entry.selector === null) return;
				
			this.selectorObserver.observeEntrySelector(entry.selector);
			
			var that = this;
			entry.selector.whenChanged(function () {
				that.triggerChanged();
			});
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
		
		set visible(visible: boolean) {
			this._visible = visible;
			
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