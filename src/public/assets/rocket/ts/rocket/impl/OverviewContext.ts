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
		
		public static scan(jqContainer: JQuery): OverviewContext {
			if (jqContainer.data("rocketImplOverviewContext")) return null;
			
			var overviewContext = new OverviewContext(jqContainer);
			jqContainer.data("rocketImplOverviewContext", overviewContext);
			
			jqContainer.data("content-url");
			
			var pagination = new Pagination(jqContainer.data("num-pages"), 
					jqContainer.data("num-entries"), jqContainer.data("current-page"));
			
			pagination.draw(jqContainer.children("form").children(".rocket-context-controls"));
			
			return overviewContext;
		}
	}
	
	class Pagination {
		private numPages: number;
		private numEntries: number;
		private currentPageNo: number
		
		private jqPagination: JQuery;
		private jqInput: JQuery;
		
		constructor(numPages: number, numEntries: number, currentPageNo: number) {
			this.numPages = numPages;
			this.numEntries = numEntries;
			this.currentPageNo = currentPageNo;
		}
		
		public getCurrentPageNo(): number {
			return this.currentPageNo;
		}
		
		public getNumPages(): number {
			return this.numPages;
		}
		
		public goTo(pageNo: number) {
			alert(pageNo);
		}
		
		
		public draw(jqContainer: JQuery) {
			var that = this;
			
			this.jqPagination = $("<div />", { "class": "rocket-impl-overview-pagination" });
			jqContainer.append(this.jqPagination);
			
			this.jqPagination.append(
					 $("<a />", {
						"href": "#",
						"class": "rocket-impl-pagination-first rocket-control",
						"click": function () { that.goTo(1) }
					}).append($("<span />", {
						"text": 1	
					})).append($("<i />", {
						"class": "fa fa-step-backward"	
					})));
			
			this.jqPagination.append(
					 $("<a />", {
						"href": "#",
						"class": "rocket-impl-pagination-prev rocket-control",
						"click": function () { that.goTo(that.getCurrentPageNo() - 1) }
					}).append($("<i />", {
						"class": "fa fa-chevron-left"	
					})));
			
			this.jqInput = $("<input />", {
				"class": "rocket-impl-pagination-no",
				"type": "text",
				"value": this.currentPageNo
			});
			this.jqPagination.append(this.jqInput);
			
			this.jqPagination.append(
					 $("<a />", {
						"href": "#",
						"class": "rocket-impl-pagination-next rocket-control",
						"click": function () { that.goTo(that.getCurrentPageNo() + 1); }
					}).append($("<i />", {
						"class": "fa fa-chevron-right"	
					})));
		
			this.jqPagination.append(
					 $("<a />", {
						"href": "#",
						"class": "rocket-impl-pagination-last rocket-control",
						"click": function () { that.goTo(that.getNumPages()); }
					}).append($("<span />", {
						"text": that.getNumPages()	
					})).append($("<i />", {
						"class": "fa fa-step-forward"	
					})));
		}
			
	}
	
}
