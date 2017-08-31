/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 * 
 */
namespace rocket.impl {
	import cmd = rocket.cmd;
	
	var $ = jQuery;
	
	export class OverviewContext {
		private jqContainer: JQuery;
		
		private contentUrl: string;
		private numPages: number;
		private numEntries: number;
		private currentPageNo: number;
		
		private jqContextControls: JQuery;
		
		constructor(jqContainer: JQuery) {
			this.jqContainer = jqContainer;
		}
		
		private initPageNav() {
		}
		
		public static from(jqElem: JQuery): OverviewContext {
			var overviewContext: OverviewContext = jqElem.data("rocketImplOverviewContext");
			if (overviewContext instanceof OverviewContext) {
				return overviewContext;
			}
			
			overviewContext = new OverviewContext(jqElem);
			jqElem.data("rocketImplOverviewContext", overviewContext);
			
			
			var jqForm = jqElem.children("form");
			
			var overviewContent = new OverviewContent(jqElem.find("tbody.rocket-overview-content:first"), 
					jqElem.children(".rocket-impl-overview-tools").data("content-url"));
			
			overviewContent.initFromDom(jqElem.data("current-page"), jqElem.data("num-pages"));
			
			new ContextUpdater(rocket.cmd.Context.findFrom(jqElem), jqElem.data("overview-path"))
					.init(overviewContent);
			
			var pagination = new Pagination(overviewContent);
			pagination.draw(jqForm.children(".rocket-context-commands"));
			
			var fixedHeader = new FixedHeader(jqElem.data("num-entries"));
			fixedHeader.draw(jqElem.children(".rocket-impl-overview-tools"), jqForm.find("table:first"));
			
			return overviewContext;
		}
	}
	
	class OverviewContent {
		private pages: Array<Page> = new Array<Page>();
		private callback: Array<(OverviewContent) => any> = new Array<(OverviewContent) => any>();
		private _currentPageNo: number = null; 
		private _numPages: number;
		
		constructor(private jqElem: JQuery, private loadUrl) {
			
		}	
		
		isInit(): boolean {
			return this._currentPageNo != null && this._numPages != null;
		}
		
		initFromDom(currentPageNo: number, numPages: number) {
			rocket.util.IllegalStateError.assertTrue(this.isInit());
			this._currentPageNo = currentPageNo;
			this._numPages = numPages;
			this.createPage(this.currentPageNo).jqContents = this.jqElem.children();
		}
		
		init() {
			rocket.util.IllegalStateError.assertTrue(this.isInit());
			this.goTo(1);
		}
		
		get currentPageNo(): number {
			return this._currentPageNo;
		}
		
		private setCurrentPageNo(pageNo: number) {
			this._currentPageNo = pageNo;
			
			var that = this;
			this.callback.forEach(function (callback) {
				callback(that);
			});
		}
		
		public whenCurrentPageNoChanged(callback: (OverviewContent) => any) {
			this.callback.push(callback);
		}
		
		get numPages(): number {
			return this._numPages;
		}
		
		private setNumPages(numPages: number) {
			for (var pageNo = 1; pageNo < this._numPages; pageNo++) {
//				this.context.
			}
		}
		
		isPageNoValid(pageNo: number) {
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
					if (!this.containsPageNo(i) || !this.pages[i].isContentLoaded()) return false;
					
					page = this.pages[i];
					page.visible = true;
				}
			} else {
				for (var i = pageNo; i >= targetPageNo; i--) {
					if (!this.containsPageNo(i) || !this.pages[i].isContentLoaded() || !this.pages[i].visible) return false;
					
					page = this.pages[i];
				}
			}
			
			$(window).stop().animate({
				scrollTop: page.jqContents.first().offset().top 
			}, 500);
		}	
		
		private loadingPageNos: Array<number> = new Array<number>();
		private jqLoader: JQuery = null;
		
		private markPageAsLoading(pageNo: number) {
			if (-1 < this.loadingPageNos.indexOf(pageNo)) {
				throw new Error("page already loading");
			}
			
			if (this.jqLoader === null) {
				this.jqLoader = $("<div />", { "class": "rocket-loading" })
						.insertAfter(this.jqElem.parent("table"));
			}
			
			this.loadingPageNos.push(pageNo);
		}
		
		private unmarkPageAsLoading(pageNo: number) {
			if (-1 < this.loadingPageNos.indexOf(pageNo)) return;
			
			this.loadingPageNos.slice(pageNo, 1);
			
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
				
				var jqContents = $(n2n.ajah.analyze(data)).find(".rocket-overview-content:first").children();
				that.applyContents(page, jqContents);
				n2n.ajah.update();
			});
		}
		
		public onNewPage() {
		}
	}
	
	class Page {
		private _visible: boolean = true;
		
		constructor(public pageNo: number, private _jqContents: JQuery = null) {
		}
		
		get visible(): boolean {
			return this._visible;
		}
		
		set visible(visible: boolean) {
			this._visible = visible;
			
			this.disp();
		}
		
		public isContentLoaded(): boolean {
			return this.jqContents !== null;
		}
		
		get jqContents(): JQuery {
			return this._jqContents;
		}
		
		set jqContents(jqContents: JQuery) {
			this._jqContents = jqContents;
			
			this.disp();
		}
		
		private disp() {
			if (this._jqContents === null) return
			
			if (this._visible) {
				this._jqContents.show();
			} else {
				this._jqContents.hide();
			}
		}
	}
	
	class ContextUpdater {
		private overviewContent: OverviewContent;
		private lastCurrentPageNo: number = null;
		private pageUrls: Array<Url> = new Array<Url>();
		
		constructor(private context: cmd.Context, private overviewBaseUrl: cmd.Url) {
			var that = this;
			this.context.on(cmd.Context.EventType.ACTIVE_URL_CHANGED, function () {
				that.contextUpdated();
			});
		}
		
		public init(overviewContent: OverviewContent) {
			this.overviewContent = overviewContent;
			var that = this;
			overviewContent.whenChanged(function () {
				that.contentUpdated();
			});
		}
		
		private contextUpdated() {
			var newActiveUrl = this.context.activeUrl;
			for (var i in this.pageUrls) {
				if (!this.pageUrls[i].equals(newActiveUrl)) continue;
				
				this.overviewContent.currentPageNo = (parseInt(i) + 1);
				return;
			}
		}
		
		private contentUpdated() {
			var newCurPageNo = this.overviewContent.currentPageNo;
			var newNumPages = this.overviewContent.numPages;
			
			if (this.pageUrls.length < newNumPages) {
				for (let pageNo = this.pageUrls.length + 1; pageNo <= newNumPages; pageNo++) {
					var pageUrl = this.overviewBaseUrl.extR(pageNo > 1 ? pageNo.toString() : null);
					this.pageUrls[pageNo - 1] = pageUrl;
					this.context.registerUrl(pageUrl);
				}
			} else if (this.pageUrls.length > newNumPages){
				for (let pageNo = this.pageUrls.length; pageNo > newNumPages; pageNo--) {
					this.context.unregisterUrl(this.pageUrls.pop());
				}
			}
			
			var newActiveUrl = this.pageUrls[newCurPageNo - 1];
			if (!this.context.activeUrl.equals(newActiveUrl)) {
				this.context.getLayer().pushHistoryEntry(newActiveUrl);
			}
		}
		
	}
	
	class Pagination {
		private jqPagination: JQuery;
		private jqInput: JQuery;
		
		constructor(private overviewContent: OverviewContent) {
		}
		
		public getCurrentPageNo(): number {
			return this.overviewContent.currentPageNo;
		}
		
		public getNumPages(): number {
			return this.overviewContent.numPages;
		}
		
		public goTo(pageNo: number) {
			this.overviewContent.goTo(pageNo);
			return;
		}
		
		public draw(jqContainer: JQuery) {
			var that = this;
			
			this.jqPagination = $("<div />", { "class": "rocket-impl-overview-pagination" });
			jqContainer.append(this.jqPagination);
			
			this.jqPagination.append(
					 $("<button />", {
						"type": "button",
						"class": "rocket-impl-pagination-first rocket-control",
						"click": function () { that.goTo(1) }
					}).append($("<i />", {
						"class": "fa fa-step-backward"	
					})));
			
			this.jqPagination.append(
					 $("<button />", {
						"type": "button",
						"class": "rocket-impl-pagination-prev rocket-control",
						"click": function () { that.goTo(that.getCurrentPageNo() - 1) }
					}).append($("<i />", {
						"class": "fa fa-chevron-left"	
					})));
			
			this.jqInput = $("<input />", {
				"class": "rocket-impl-pagination-no",
				"type": "text",
				"value": this.getCurrentPageNo()
			}).on("change", function () {
				var pageNo: number = parseInt(that.jqInput.val());
				if (pageNo === NaN || !that.overviewContent.isPageNoValid(pageNo)) {
					that.jqInput.val(that.overviewContent.currentPageNo);
					return;
				}
				
				that.jqInput.val(pageNo);
				that.overviewContent.goTo(pageNo);
			});
			this.jqPagination.append(this.jqInput);
			
			this.jqPagination.append(
					$("<button />", {
						"type": "button",
						"class": "rocket-impl-pagination-next rocket-control",
						"click": function () { that.goTo(that.getCurrentPageNo() + 1); }
					}).append($("<i />", {
						"class": "fa fa-chevron-right"	
					})));
		
			this.jqPagination.append(
					 $("<button />", {
						"type": "button",
						"class": "rocket-impl-pagination-last rocket-control",
						"click": function () { that.goTo(that.getNumPages()); }
					}).append($("<i />", {
						"class": "fa fa-step-forward"
					})));
			
			this.overviewContent.whenCurrentPageNoChanged(function () {
				that.jqInput.val(that.overviewContent.currentPageNo);
			});		
		}
	}
	
	class FixedHeader {
		private numEntries: number;
		
		private jqHeader: JQuery;
		private jqTable: JQuery;
		private jqTableClone: JQuery;
		
		public constructor(numEntries: number) {
			this.numEntries = numEntries;	
		}
		
		public getNumEntries(): number {
			return this.numEntries;	
		}
		
		public draw(jqHeader: JQuery, jqTable: JQuery) {
			this.jqHeader = jqHeader;
			this.jqTable = jqTable;
			
			this.cloneTableHeader();
			
			var that = this;
			$(window).scroll(function () {
				that.scrolled();
			});
			
//			var headerOffset = this.jqHeader.offset().top;
//			var headerHeight = this.jqHeader.height();
//			var headerWidth = this.jqHeader.width();
//			this.jqHeader.css({"position": "fixed", "top": headerOffset});
//			this.jqHeader.parent().css("padding-top", headerHeight);
			
			this.calcDimensions();
			$(window).resize(function () {
				that.calcDimensions();
			});
		}
		
		private fixedCssAttrs;
		
		private calcDimensions() {
			this.jqHeader.parent().css("padding-top", null);
			this.jqHeader.css("position", "relative");
			
			var headerOffset = this.jqHeader.offset();
			this.fixedCssAttrs = {
				"position": "fixed",
				"top": $("#rocket-content-container").offset().top, 
				"left": headerOffset.left, 
				"right": $(window).width() - (headerOffset.left + this.jqHeader.outerWidth()) 
			};
			
			this.scrolled();
		}
		
		private fixed: boolean = false;
		
		private scrolled() {
			var headerHeight = this.jqHeader.children().outerHeight();
			if (this.jqTable.offset().top - $(window).scrollTop() <= this.fixedCssAttrs.top + headerHeight) {
				if (this.fixed) return;
				this.fixed = true;
				this.jqHeader.css(this.fixedCssAttrs);
				this.jqHeader.parent().css("padding-top", headerHeight);
				this.jqTableClone.show();
			} else {
				if (!this.fixed) return;
				this.fixed = false;
				this.jqHeader.css({
					"position": "relative",
					"top": "", 
					"left": "", 
					"right": "" 
				});
				this.jqHeader.parent().css("padding-top", "");
				this.jqTableClone.hide();
			}
		}
		
		private cloneTableHeader() {
			this.jqTableClone = this.jqTable.clone();
			this.jqTableClone.css("margin-bottom", 0);
			this.jqTableClone.children("tbody").remove();
			this.jqHeader.append(this.jqTableClone);
			this.jqTableClone.hide();
						
			var jqClonedChildren = this.jqTableClone.children("thead").children("tr").children();
			this.jqTable.children("thead").children("tr").children().each(function(index) {
				jqClonedChildren.eq(index).innerWidth($(this).innerWidth());
//				jqClonedChildren.css({
//					"boxSizing": "border-box"	
//				});
			});
			
//			this.jqTable.children("thead").hide();
		}
	}
}
