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
namespace rocket.impl.overview {
	import cmd = rocket.cmd;
	
	var $ = jQuery;
	
	export class OverviewContext {
		private jqContextControls: JQuery;
		
		constructor(private jqContainer: JQuery, private overviewContent: OverviewContent) {
		}
		
		public initSelector(selectorObserver: SelectorObserver) {
			this.overviewContent.initSelector(selectorObserver);
		}
		
		public static findAll(jqElem: JQuery): Array<OverviewContext> {
			var oc: Array<OverviewContext> = new Array();
			
			jqElem.find(".rocket-impl-overview").each(function () {
				oc.push(OverviewContext.from($(this)));
			});
			
			return oc;
		}
		
		public static from(jqElem: JQuery): OverviewContext {
			var overviewContext: OverviewContext = jqElem.data("rocketImplOverviewContext");
			if (overviewContext instanceof OverviewContext) {
				return overviewContext;
			}
			
			
			var jqForm = jqElem.children("form");
			
			var overviewContent = new OverviewContent(jqElem.find("tbody.rocket-overview-content:first"), 
					jqElem.children(".rocket-impl-overview-tools").data("content-url"));
			
			new ContextUpdater(rocket.cmd.Context.findFrom(jqElem), new cmd.Url(jqElem.data("overview-path")))
					.init(overviewContent);
			
			overviewContent.initFromDom(jqElem.data("current-page"), jqElem.data("num-pages"), jqElem.data("num-entries"));
			
			
			var pagination = new Pagination(overviewContent);
			pagination.draw(jqForm.children(".rocket-context-commands"));
			
			
			var header = new Header(overviewContent);
			header.draw(jqElem.children(".rocket-impl-overview-tools"));
			
			overviewContext = new OverviewContext(jqElem, overviewContent);
			jqElem.data("rocketImplOverviewContext", overviewContext);
			
			overviewContent.initSelector(new MultiEntrySelectorObserver(["51","53"]));
			
			return overviewContext;
		}
	}
	
	
	
	
//	
	
//	class Entry {
//		
//		constructor (private _idRep: string, public identityString: string) {
//		}
//		
//		get idRep(): string {
//			return this._idRep;
//		}
//	}
	
	class ContextUpdater {
		private overviewContent: OverviewContent;
		private lastCurrentPageNo: number = null;
		private pageUrls: Array<cmd.Url> = new Array<cmd.Url>();
		
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
						"click": function () { 
							if (that.getCurrentPageNo() > 1) {
								that.goTo(that.getCurrentPageNo() - 1);
							} 
						}
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
						"click": function () { 
							if (that.getCurrentPageNo() < that.getNumPages()) {
								that.goTo(that.getCurrentPageNo() + 1);
							} 
						}
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
			
			this.overviewContent.whenChanged(function () {
				if (that.overviewContent.selectedOnly || that.overviewContent.numPages == 1) {
					that.jqPagination.hide();
				} else {
					that.jqPagination.show();
				}
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
			
//			this.cloneTableHeader();
			
//			var that = this;
//			$(window).scroll(function () {
//				that.scrolled();
//			});
			
//			var headerOffset = this.jqHeader.offset().top;
//			var headerHeight = this.jqHeader.height();
//			var headerWidth = this.jqHeader.width();
//			this.jqHeader.css({"position": "fixed", "top": headerOffset});
//			this.jqHeader.parent().css("padding-top", headerHeight);
			
//			this.calcDimensions();
//			$(window).resize(function () {
//				that.calcDimensions();
//			});
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
