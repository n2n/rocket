/// <reference path="..\rocket.ts" />
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
module spec {
	var $ = jQuery;
	
	class QuickSearchForm {
		private overviewTools: OverviewTools;
		private elemContainer: JQuery;
		private elemForm: JQuery;
		
		constructor(overviewTools: OverviewTools, elemContainer: JQuery) {
			this.overviewTools = overviewTools;
			this.elemContainer = elemContainer;
			this.elemForm = elemContainer.children("form");
			
			this.applyFormSubmissions();
		}
		
		public applyFormSubmissions() {
			var that = this;
				
			this.elemForm.find("[type=submit]").click(function(e) {
				e.preventDefault();
				that.post($(this).attr("name"));
			});
			
			this.elemForm.submit(function(e) {
				e.preventDefault();
				that.elemForm.find("[type=submit]:first").click();
			});
		}
				
		public post(methName: string) {
			this.overviewTools.postForm(methName, new FormData(<HTMLFormElement>this.elemForm.get(0)));
		}
	}
	
	class CritmodSortConstraint {
		private elemLi: JQuery;
		private elemRemove: JQuery;
		private elemSelectPropertyName: JQuery;
		private elemLabel: JQuery;
		private critmodSort: CritmodSort;
		
		public constructor(critmodSort: CritmodSort, elemLi: JQuery) {
			this.critmodSort = critmodSort;
			this.elemLi = elemLi;
			this.elemSelectPropertyName = elemLi.find("select:first").hide();
			this.elemLabel = $("<span />").insertAfter(this.elemSelectPropertyName);
			
			(function(that: CritmodSortConstraint) {
				that.elemRemove = rocketTs.creatControlElem("Text: remove", function() {
					that.remove();
				}, "fa fa-times").appendTo(elemLi);
				
				elemLi.on('clear.critmod', function() {
					that.remove();
				});
			}).call(this, this);
		}
		
		public setProperty(propertyName, label) {
			this.elemSelectPropertyName.val(propertyName);
			this.elemLabel.text(label);
		}
		
		public getPropertyName() {
			return this.elemSelectPropertyName.val();
		}
		
		public getLabel() {
			return this.elemLabel.text();
		}
		
		public remove() {
			this.critmodSort.addProperty(this.getPropertyName());
			this.elemLi.remove();
		}
		
		public getElemLi() {
			return this.elemLi;
		}
	}
	
	class CritmodSort {
		private elemUl: JQuery;
		private elemButtonAdd: JQuery;
		private elemContent: JQuery;
		private elemEmptyConstraint: JQuery;
		private sortFields: Object;
		private elemUlProperties: JQuery;
		
		public constructor(elemUl: JQuery, textAddSortLabel) {
			this.elemUl = elemUl;
			
			this.elemButtonAdd = $("<a />", {
				"text": textAddSortLabel,
				"class": "rocket-control"
			}).insertAfter(elemUl);
			
			this.elemContent = $("<div />").insertAfter(elemUl);
			this.sortFields = elemUl.data("sort-fields");
			
			(function(that: CritmodSort) {
				var availableProperties = [];
				that.elemEmptyConstraint = that.elemUl.children(".rocket-empty-sort-constraint").detach();
				that.elemUl.children().each(function() {
					var constraint = new CritmodSortConstraint(that, $(this));
					availableProperties.push(constraint.getPropertyName());
				});
				
				that.elemUlProperties = $("<ul />", {
					"class": "rocket-multi-add-entries"
				}).appendTo(that.elemContent);
				
				$.each(that.sortFields, function(propertyName, label) {
					if (availableProperties.indexOf(propertyName) >= 0) return;
					
					that.addProperty(propertyName, label);
				});
				
				new ui.MultiAdd(that.elemButtonAdd, that.elemContent, 
						ui.MultiAdd.ALIGNMENT_RIGHT);
			}).call(this, this);
		}
		
		public addProperty(propertyName, label: string = null) {
			if (null === label) {
				label = this.sortFields[propertyName];
			}
			var that = this;
			var elemLi = $("<li />").appendTo(that.elemUlProperties);
			 
			rocketTs.creatControlElem(label, function() {
				var constraint = that.requestConstraint(propertyName, label);
				constraint.getElemLi().appendTo(that.elemUl);
				elemLi.remove();
			}).removeClass("rocket-control").appendTo(elemLi);
		}
		
		public clear() {
			this.elemUl.children().trigger('clear.critmod');
		}
		
		private requestConstraint(propertyName, label) {
			var constraint = new CritmodSortConstraint(this, this.elemEmptyConstraint.clone());
			constraint.setProperty(propertyName, label);
			
			return constraint;
		}
	}
	
	class CritmodForm {
		private overviewTools: OverviewTools;
		private elemCritmod: JQuery;
		private elemForm: JQuery;
		private sort: CritmodSort
		private elemSelectFilter: JQuery;
		private methNameSelect: string;
		private formUrl: string;
		
		private elemToggleConfiguration: JQuery;
		private elemConfiguration: JQuery;
		private elemsFilterSubmitElems: JQuery;
		
		private critmodSort: CritmodSort;
		
		constructor(overviewTools: OverviewTools, elemCritmod: JQuery) {
			this.overviewTools = overviewTools;
			this.elemCritmod = elemCritmod;
			this.initialize(elemCritmod.children(".rocket-critmod-form:first"));
		}
		
		private initialize(elemForm: JQuery) {
			this.elemForm = elemForm;
			this.elemSelectFilter = elemForm.find(".rocket-critmod-save-select select:first");
			this.elemConfiguration = elemForm.find(".rocket-critmod-configuration:first").hide();
			this.formUrl = elemForm.attr("action");
			
			this.elemToggleConfiguration = $("<a />", {
				"href": "#",
				"class": "rocket-control rocket-critmod-configuration-opener"	
			}).insertAfter(this.elemSelectFilter);
			
			(function(that: CritmodForm) {
				var elemSubmitSelect = elemForm.find(".rocket-critmod-select[type=submit]:first");
				that.methNameSelect = elemSubmitSelect.attr("name");
				elemSubmitSelect.remove();
				
				that.elemSelectFilter.change(function() {
					that.post(that.methNameSelect);
				});
				
				that.elemConfiguration.hide();
				var elemIconToggle = $("<i />", {
					"class": "fa fa-cogs"	
				}).appendTo(that.elemToggleConfiguration);
				
				that.elemToggleConfiguration.click(function(e) {
					e.preventDefault();
					if (that.elemToggleConfiguration.hasClass("open")) {
						that.close();
					} else {
						that.open();
					}
					
					that.overviewTools.getElem().trigger('heightChange');
				});
				
				that.critmodSort = new CritmodSort(elemForm.find(".rocket-sort:first"), "Add Sort");
				that.applyFormSubmissions();
			}).call(this, this);
		}
		
		public open(immediately: boolean = false) {
			var that = this;
			this.elemToggleConfiguration.addClass("open");
			if (immediately) {
				this.elemConfiguration.show();
				this.overviewTools.getElem().trigger('heightChange');
			} else {
				this.elemConfiguration.stop(true, true).slideDown({
					duration: 200,
					step: function() {
						that.overviewTools.getElem().trigger('heightChange');
					}
				});
			}
		}
		
		public close() {
			var that = this;
			this.elemToggleConfiguration.removeClass("open");
			this.elemConfiguration.stop(true, true).slideUp({
				duration: 200,
				step: function() {
					that.overviewTools.getElem().trigger('heightChange');
				}
			});	
		}
		
		public isFilterSelected() {
			return !!this.elemSelectFilter.val();
		}
		
		
		private hideFilterSubmitElems() {
			this.elemsFilterSubmitElems.each(function() {
				$(this).parents("li:first").hide();
			});
		} 
		
		private showFilterSubmitElems() {
			this.elemsFilterSubmitElems.each(function() {
				$(this).parents("li:first").show();
			});
		} 
		
		public applyFormSubmissions() {
			var elemSubmitApply = this.elemForm.find(".rocket-critmod-submit-apply:first"),
				that = this;
			elemSubmitApply.click(function(e) {
				e.preventDefault();
				that.post(elemSubmitApply.attr("name"));
			});
			
			this.elemsFilterSubmitElems = this.elemForm.find(".rocket-critmod-submit-save, .rocket-critmod-submit-delete");
			this.elemsFilterSubmitElems.click(function(e) {
				e.preventDefault();
				that.post($(this).attr("name"));
			});

			if (!this.isFilterSelected()) {
				this.hideFilterSubmitElems();
			}	
			
			var elemSubmitClear = this.elemForm.find(".rocket-critmod-submit-clear");
			elemSubmitClear.click(function(e) {
				e.preventDefault();
				that.clear();
			});
			
			this.elemForm.submit(function(e) {
				e.preventDefault();
				elemSubmitApply.click();
			});
		}
		
		public clear() {
			this.critmodSort.clear();
		}
		
		public post(methName: string) {
			//this.overviewContent.clear();
			//this.setLoading(true);
			var formData = new FormData(<HTMLFormElement>this.elemForm.get(0));
			formData.append(methName, true);
			var that = this;
			
			$.ajax({
				"url": this.formUrl,
				"type": "POST",
				"data": formData,
				"processData": false,
				"contentType": false,
				"success":  function(data) {
					that.elemCritmod.empty();
					var elemCritmod = $($.parseHTML(n2n.dispatch.analyze(data))),
						additionalData = data['additional'];
					that.initialize(elemCritmod.children(".rocket-critmod-form:first").appendTo(that.elemCritmod));
					rocketTs.updateUi();
					that.open(true);
					
					if (additionalData['valid']) {
						that.overviewTools.reloadContent();
					}
				}
			});
		}
	}
	
	class Pagination {
		private overviewContent: OverviewContent;
		private overviewTools: OverviewTools;
		private elemContainer: JQuery;
		private elemFixedContainer: JQuery;
		
		private elemFirstPage: JQuery;
		private elemPrevPage: JQuery;
		private elemPageNo: JQuery;
		private elemNextPage: JQuery;
		private elemLastPage: JQuery;
		
		private numPages: number;
		private pageNo: number;
		private overviewPath: string;
		
		private elemsPreloadedPage: Object = {};
		
		private loadingJqXHR: JQueryXHR = null;
		private preloadingJqXHRs: Object = [];
		
		private contentHeight: number = null;
		private firstLoadedPageNo: number = null;
		private lastLoadedPageNo: number = null;
		private pageChangePosition: number = null;
		
		public constructor (overviewContent: OverviewContent, pageNo, numPages, overviewPath: string = null) {
			this.overviewContent = overviewContent;
			this.overviewTools = overviewContent.getOverviewTools();
			this.overviewPath = overviewPath;
			
			this.elemContainer = $("<div />", {
				"class": "rocket-overview-pagination"
			});
			
			this.elemFirstPage = $("<a />", {
				"href": "#",
				"class": "rocket-pagination-first rocket-control"
			}).append($("<span />", {
				"text": 1	
			})).append($("<i />", {
				"class": "fa fa-step-backward"	
			})).appendTo(this.elemContainer);
			
			this.elemPrevPage = $("<a />", {
				"href": "#",
				"class": "rocket-pagination-prev rocket-control"
			}).append($("<i />", {
				"class": "fa fa-chevron-left"	
			})).appendTo(this.elemContainer);
			
			this.elemPageNo = $("<input />", {
				"class": "rocket-pagination-current",
				"type": "text"
			}).appendTo(this.elemContainer);
			
			this.elemNextPage = $("<a />", {
				"href": "#",
				"class": "rocket-pagination-next rocket-control"
			}).append($("<i />", {
				"class": "fa fa-chevron-right"	
			})).appendTo(this.elemContainer);
			
			this.elemLastPage = $("<a />", {
				"href": "#",
				"class": "rocket-pagination-last rocket-control"
			}).append($("<i />", {
				"class": "fa fa-step-forward"	
			})).append($("<span />", {
				"text": numPages 
			})).appendTo(this.elemContainer);
			
			(function(that: Pagination) {
				that.elemFirstPage.click(function(e) {
					e.preventDefault();
					that.loadPage(1);
				});
				
				that.elemPrevPage.click(function(e) {
					e.preventDefault();
					if (that.pageNo <= 1) return;
					that.loadPage(that.pageNo - 1);
				});
				
				that.elemNextPage.click(function(e) {
					e.preventDefault();
					if (that.pageNo >= that.numPages) return;
					that.loadPage(that.pageNo + 1);
				});
				
				that.elemLastPage.click(function(e) {
					e.preventDefault();
					that.loadPage(that.numPages);
				});
				
				that.elemPageNo.keydown(function (e) {
					if (e.which == 13) {
						e.preventDefault();
						that.loadPage(that.purifyPageNo(that.elemPageNo.val()));
					}
				}).focus(function() {
					$(this).select();	
				});
				
				that.firstLoadedPageNo = pageNo;
				that.lastLoadedPageNo = pageNo
				that.setCurrentPageNo(pageNo);
				that.setNumPages(numPages);
				that.preloadPages(pageNo);
				
			}).call(this, this);
		}
		
		public getFirstLoadedPageNo() {
			return this.firstLoadedPageNo;	
		}

		public applyElemFixedContainer(elemFixedContainer: JQuery) {
			var that = this;
			this.elemFixedContainer = elemFixedContainer;
			elemFixedContainer.scroll(function(e) {
				if (that.overviewContent.isScrolling()) return;
				if (that.overviewContent.isInSelectionMode()) return;
				
				that.checkNextPageLoad();
				that.determineCurrentPage();
			}).scroll();
			
			this.pageChangePosition = null;
		}
		
		private purifyPageNo(dirtyPageNo) {
			var pageNo = parseInt(dirtyPageNo);
			if (isNaN(pageNo) || pageNo < 1) {
				pageNo = 1;	
			} else if (pageNo > this.numPages) {
				pageNo = this.numPages;
			}
			
			return pageNo;
		}
		
		private checkNextPageLoad() {
			if (this.isLoading()) return;
			if (this.pageNo >= this.numPages) return;

			var elemFixedContainer = this.elemFixedContainer,
				containerHeight = elemFixedContainer.outerHeight(),
				offset = containerHeight / 3,
				contentHeight = elemFixedContainer.get(0).scrollHeight;
			
			if (contentHeight === 0) return;
			
			if ((contentHeight - offset) < elemFixedContainer.scrollTop() + containerHeight) {
				this.loadPage(this.pageNo + 1, true);
			}
		}
		
		public hasPage(pageNo) {
			return this.firstLoadedPageNo <= pageNo && this.lastLoadedPageNo >= pageNo;
		}
				
		private determineCurrentPage() {
			var lastPageNo = this.firstLoadedPageNo,
				that = this;
			
			if (null === this.pageChangePosition) {
				this.pageChangePosition = this.elemFixedContainer.offset().top + (this.elemFixedContainer.outerHeight() / 3 * 2);
			}
			
			$.each(this.overviewContent.getPageOffsets(), function(pageNo, offsetTop) {
				if (offsetTop > that.pageChangePosition) return false;
				
				lastPageNo = pageNo;
			});
			
			if (lastPageNo === this.pageNo) return;
			this.setCurrentPageNo(lastPageNo);
		}
		
		private isLoading() {
			return null !== this.loadingJqXHR;	
		}
		
		private resetPreloading() {
			$.each(this.preloadingJqXHRs, function(pageNo, jqXhr: JQueryXHR) {
				if (jqXhr) {
					jqXhr.abort();
				}
			});
			
			this.elemsPreloadedPage = {};	
		}
		
		private setLoading() {
			this.elemFirstPage.prop("disabled", true);	
			this.elemPrevPage.prop("disabled", true);	
			this.elemNextPage.prop("disabled", true);	
			this.elemLastPage.prop("disabled", true);
			
			this.resetPreloading();
			this.overviewTools.setLoading();
		}
		
		private resetLoading() {
			this.loadingJqXHR = null;
							
			this.elemFirstPage.prop("disabled", false);	
			this.elemPrevPage.prop("disabled", false);	
			this.elemNextPage.prop("disabled", false);	
			this.elemLastPage.prop("disabled", false);	
			this.overviewTools.setLoading(false);
			$(window).scroll();
		}
		
		public loadPage(pageNo, append = false) {
			if (this.isLoading()) return;
			
			this.overviewContent.preparePageLoad();
			
			pageNo = parseInt(pageNo);
	
			if (this.numPages < pageNo) {
				throw new Error("Invalid page num");	
			}
			
			
			if (this.hasPage(pageNo)) {
				if (!append) {
					if (this.overviewContent.scrollToPage(pageNo)) {
						this.setCurrentPageNo(pageNo);
					};
				}
					
				return;	
			}
			
			if (append) {
				this.lastLoadedPageNo = pageNo;
			} else {
				this.firstLoadedPageNo = pageNo;
				this.lastLoadedPageNo = pageNo;
			}

			if (this.elemsPreloadedPage.hasOwnProperty(pageNo.toString())) {
				if (append) {
					this.overviewContent.appendPage(this.elemsPreloadedPage[pageNo].clone(), pageNo);
				} else {
					this.overviewContent.replaceContent(this.elemsPreloadedPage[pageNo].clone(), pageNo);	
				}
				this.setCurrentPageNo(pageNo);
				this.elemFixedContainer.scroll();
			} else {
				var that = this;
				this.setCurrentPageNo(pageNo);
				this.setLoading();
				if (!append) {
					this.overviewContent.clear();	
				}
				
				this.loadingJqXHR = this.overviewTools.getContent(pageNo, function(elem: JQuery, data: Object) {
					that.elemsPreloadedPage[pageNo] = elem.clone();
					if (append) {
						that.overviewContent.appendPage(elem, pageNo);
					} else {
						that.overviewContent.replaceContent(elem, pageNo);
					}
					that.overviewContent.setNumEntries(data['numEntries']);
					that.resetLoading();
					that.setNumPages(data['numPages']);
					that.elemFixedContainer.scroll();
				});
			}
			
			this.preloadPages(pageNo);
		}
		
		private updateUrl() {
			if (null !== this.overviewPath && typeof history !== 'undefined') {
				var path = this.overviewPath;
				if (this.pageNo > 1) {
					path += "/" + this.pageNo;	
				}
				history.pushState(null, null, path);
			}	
		}
		
		private preloadPages(pageNo) {
			var numPages = 2;
			for (var i = (pageNo - numPages); i <= (pageNo + numPages); i++) {
				this.preloadPage(i);
			}
		}
		
		private preloadPage(pageNo) {
			if (pageNo < 1 || pageNo === this.pageNo || pageNo > this.numPages 
					|| this.preloadingJqXHRs.hasOwnProperty(pageNo.toString())
					|| this.elemsPreloadedPage.hasOwnProperty(pageNo.toString()) 
					|| this.hasPage(pageNo)) return;
			
			var that = this;
			var preloadingJqXHR = this.overviewTools.getContent(pageNo, function(elem: JQuery, data: Object) {
				that.elemsPreloadedPage[pageNo.toString()] = elem;
				if (that.preloadingJqXHRs.hasOwnProperty(pageNo.toString())) {
					delete that.preloadingJqXHRs[pageNo];
				}
			});
			this.preloadingJqXHRs[pageNo] = preloadingJqXHR;
		}
		
		public getElemContainer() {
			return this.elemContainer;
		}
		
		public setNumPages(numPages) {
			this.numPages = numPages;
			this.elemPageNo.attr("max", numPages);
		}
		
		public setCurrentPageNo(pageNo) {
			this.pageNo = parseInt(pageNo);
			
			this.elemPageNo.val(pageNo);
			if (this.pageNo == 1) {
				this.elemFirstPage.prop("disabled", true);	
				this.elemPrevPage.prop("disabled", true);
			} 
			
			if (this.pageNo == this.numPages){
				this.elemNextPage.prop("disabled", true);	
				this.elemLastPage.prop("disabled", true);	
			}
			
			this.updateUrl();
		}
		
	}
	
	class EntryRow {
		private elem: JQuery;
		private elemIdRep: JQuery;
		private elemCbx: JQuery;
		private idRep: string;
		private pageNo: number;
		
		public constructor(overviewContent: OverviewContent, elem: JQuery, pageNo: number, selectable = true) {
			this.elem = elem;
			this.elemIdRep = elem.find(".rocket-entry-selector");
			this.elemCbx = this.elemIdRep.children("input[type=checkbox]:first");
			this.idRep = this.elemIdRep.data("entry-id-rep");
			this.elem.addClass('rocket-id-rep-' + this.idRep);
			this.pageNo = pageNo;
			
			(function(that: EntryRow) {
				if (selectable) {
					that.elemCbx.change(function() {
						if (that.elemCbx.is(":checked")) {
							that.elem.addClass("selected");
							that.elemCbx.trigger('changed.select');
						} else {
							that.elem.removeClass("selected");
							that.elemCbx.trigger('changed.select');
							if (overviewContent.isInSelectionMode()) {
								if (overviewContent.getPagination().hasPage(pageNo)) {
									that.elem.hide();	
								} else {
									that.elem.remove();	
								}
							}
						}
					}).change();
				} else {
					that.setSelectable(false);	
				}
				that.elem.data("entry-row", this);
				that.elem.click(function(e) {
					var elemTarget = $(e.target);
					if (elemTarget.is("a") || elemTarget.is("button") || elemTarget.is(that.elemCbx)) return;
					
					var isClickable = false;
					elemTarget.parentsUntil(that.elem).each(function() {
						if (!$(this).is("a") && !$(this).is("button")) return;
						
						isClickable = true;
						return false;
					});
					
					if (isClickable) return;
					
					that.select(!that.elemCbx.prop("checked"));
				});
			}).call(this, this);
		}
		
		public getPageNo() {
			return this.pageNo;	
		}
		
		public getElem() {
			return this.elem;	
		}
		
		public getIdRep() {
			return this.idRep;	
		}
		
		public equals(obj) {
			return obj instanceof EntryRow && obj.getIdRep() === this.idRep;
		}
		
		public select(select: boolean = true) {
			this.elemCbx.prop("checked", select).change();
		}
		
		public isSelected() {
			return this.elemCbx.prop("checked");	
		}
		
		public setSelectable(selectable) {	
			if (selectable) {
				this.elemCbx.appendTo(this.elem);
			} else {
				this.elemCbx.detach();	
			}
		}
	}
	
	class OverviewContent {
		private overviewTools: OverviewTools;
		private elemMainContent: JQuery;
		private elemContent: JQuery;
		private elemEntryControls: JQuery;
		private elemEntryInfos: JQuery;
		private elemNumEntries: JQuery;
		private elemNumSelectedEntries: JQuery;
		
		private pagination: Pagination;
		private scrolling: boolean = false;
		private inSelectionMode = false;
		private selectable = true;
		private overviewPath: string;
		
		public static CLASS_NAME_NEW_ROW = 'rocket-overview-page-first-row';
		
		public constructor(overviewTools: OverviewTools, 
				elemMainContent: JQuery, numPages: number, numEntries: number, pageNo: number) {
			this.overviewTools = overviewTools;
			this.elemMainContent = elemMainContent;
			this.elemContent = elemMainContent.find(".rocket-overview-content");
			this.overviewPath = elemMainContent.data("overview-path") || null;
			
			this.elemEntryControls = $("<div />", {
				"class": "rocket-overview-entry-controls"
			}).appendTo(overviewTools.getElem());
			
			this.elemEntryInfos = $("<div />", {
				"class": "rocket-overview-entry-infos"
			}).appendTo(this.elemEntryControls);
			
			this.elemNumEntries = $("<a />", {
				"href": "#",
				"class": "rocket-control"
			}).appendTo(this.elemEntryInfos);

			(function(that: OverviewContent) {
				that.pagination = new Pagination(that, pageNo, numPages, that.overviewPath);
				if (numPages > 1) {
					that.elemEntryControls.append(that.pagination.getElemContainer());
				}
				
				that.elemNumEntries.click(function(e) {
					e.preventDefault();
					that.inSelectionMode = false;
					that.showAll();
				});
				
				that.setNumEntries(numEntries);

				that.elemNumSelectedEntries = $("<a />", {
					"href": "#",
					"class": "rocket-control"
				}).appendTo(that.elemEntryInfos).click(function(e) {
					e.preventDefault();
					that.inSelectionMode = true;
					that.showSelected();
				});
				
				var numSelectedEntries = 0,
					first = true;
				that.elemContent.children("tr").each(function() {
					var entryRow = new EntryRow(that, $(this), 1);
					if (first) {
						entryRow.getElem().addClass(OverviewContent.CLASS_NAME_NEW_ROW)
								.attr("data-page-no", pageNo);
						first = false;
					}	
					if (!entryRow.isSelected()) return;

					numSelectedEntries++;
				});
				
				that.setNumSelectedEntries(numSelectedEntries);
				
				that.elemContent.on('changed.select', function() {
					that.setNumSelectedEntries(that.elemContent.children("tr.selected").length);
				});
			}).call(this, this);
		}
		
		public preparePageLoad() {
			this.elemNumEntries.click();
		}
				
		public isInSelectionMode() {
			return this.inSelectionMode;	
		}
		
		public setSelectable(selectable) {
			if (selectable) {
				this.elemNumSelectedEntries.appendTo(this.elemEntryInfos)
			} else {
				this.elemNumSelectedEntries.detach();
			}
			
			this.elemContent.children("tr").each(function() {
				$(this).data("entry-row").setSelectable(selectable);
			});
			
			this.selectable = selectable;
		}
		
		public getElemMainContent() {
			return this.elemMainContent;	
		}
		
		public getPagination() {
			return this.pagination;
		}
		
		public getOverviewTools() {
			return this.overviewTools;	
		}
		
		public showSelected() {
			this.elemContent.children("tr:not(.selected)").hide();
			this.elemContent.children("tr.selected").show();
		}
		
		public getSelectedIdentityStrings() {
			var identityStrings = {};
			this.elemContent.find("tr.selected > .rocket-entry-selector").each(function() {
				var elemEntrySelector = $(this);
				identityStrings[elemEntrySelector.data("entry-id-rep")] = elemEntrySelector.data("identity-string");
			});
			
			return identityStrings;
		}
		
		public removeSelection() {
			this.elemContent.children("tr.selected").each(function() {
				$(this).data('entry-row').select(false);
			});
		}
		
		public showAll() {
			var that = this;
			this.elemContent.children("tr:not(.selected)").show();
			this.elemContent.children("tr.selected").each(function() {
				var elemEntryRow = $(this),
					entryRow = <EntryRow> elemEntryRow.data('entry-row');
				if (entryRow.getPageNo() < that.pagination.getFirstLoadedPageNo()) {
					elemEntryRow.hide();
				} else {
					elemEntryRow.show();	
				}
			});
		}
		
		public clear() {
			this.elemContent.children("tr:not(.selected)").remove();
			this.elemContent.children("tr.selected").hide();
		}
		
		public isScrolling() {
			return this.scrolling;	
		}
					
		public getPageOffsets() {
			var pageOffsets = {};
			this.elemContent.children("tr." + OverviewContent.CLASS_NAME_NEW_ROW).each(function() {
				var elemRow = $(this)
				pageOffsets[elemRow.data("page-no")] = elemRow.offset().top;	
			});
			
			return pageOffsets;
		}
		
		public replaceContent(elem: JQuery, startPageNo = 1) {
			var that = this;
			
			this.elemContent.children("tr:not(.selected)").remove();
			this.elemContent.children("tr.selected").hide()
			this.appendPage(elem, startPageNo);
		}
		
		private appendRow(entryRow: EntryRow) {
			if (this.isSelected(entryRow)) {
				this.removeSelectedEquivalent(entryRow);
				entryRow.select();
			}
			
			this.elemContent.append(entryRow.getElem());
		}
		
		public scrollToPage(pageNo) {
			if (this.scrolling) return false;
			
			var elemPage = this.elemContent.find("." + OverviewContent.CLASS_NAME_NEW_ROW + "[data-page-no=" + pageNo + "]"),
				scrollTop = "0",
				that = this;
			this.scrolling = true;
			
			if (elemPage.length > 0) {
				scrollTop = "+=" + (elemPage.offset().top - this.determineHeaderOffsetPosition());
			}
			
			this.getOverviewTools().getElemFixedContainer().animate({
				"scrollTop": scrollTop
			}, function() {
				that.scrolling = false;
			});
			
			return true;
		}
		
		private determineHeaderOffsetPosition() {
			if (this.overviewTools.getFixedHeader().isFixed()) {
				return this.overviewTools.getElem().offset().top + this.overviewTools.getElem().outerHeight();  
			}
			
			return this.overviewTools.getElemFixedContainer().offset().top 
					+ this.overviewTools.getFixedHeader().getElemH3().outerHeight() 
					+ this.overviewTools.getElem().outerHeight()
					+ this.elemMainContent.find("thead tr:first").outerHeight();

		}
		
		public appendPage(elem: JQuery, pageNo) {
			var first = true,
				that = this;
			this.extractRows(elem).each(function() {
				var entryRow = new EntryRow(that.overviewTools.getOverviewContent(), 
						$(this), pageNo, that.selectable);
				if (first) {
					entryRow.getElem().addClass(OverviewContent.CLASS_NAME_NEW_ROW)
							.attr("data-page-no", pageNo);
					first = false;
				}
				that.appendRow(entryRow);
			});
			this.elemMainContent.trigger('overview.rowappended');
			
			rocketTs.updateUi();
		}
		
		public removeSelectedEquivalent(entryRow: EntryRow) {
			this.elemContent.children(".selected.rocket-id-rep-" 
					+ entryRow.getIdRep()).remove();
		}
		
		public isSelected(entryRow: EntryRow): boolean {
			return this.elemContent.children(".selected.rocket-id-rep-" 
					+ entryRow.getIdRep()).length > 0;
		}
		
		private extractRows(elem: JQuery): JQuery {
			return elem.find(".rocket-overview-content").children("tr");
		}
		
		public setNumSelectedEntries(numSelectedEntries: number) {
			this.elemNumSelectedEntries.text((numSelectedEntries === 1) 
					? numSelectedEntries + " " + this.overviewTools.getTextSelectedLabel()
					: numSelectedEntries + " " + this.overviewTools.getTextSelectedPluralLabel());
		}
		
		public setNumEntries(numEntries: number) {
			this.elemNumEntries.text((numEntries === 1) 
					? numEntries + " " + this.overviewTools.getTextEntriesLabel()
					: numEntries + " " + this.overviewTools.getTextEntriesPluralLabel());
		}
	}
	
	class FixedHeader {
		private overviewTools: OverviewTools;
		private elemFixedContainer: JQuery = null;
		private elemH3: JQuery;
		private elemH3Clone: JQuery = null;
		private elemTable: JQuery;
		private elemTableHeader: JQuery;
		private elemTableClone: JQuery = null;
		private elemTableCloneHeader: JQuery = null;
		private elemRocketPanel: JQuery;
		private fixed: boolean = false;
		private processing: boolean = false;
		private applyDefaultFixedContainer: boolean = true;
		
		public constructor(overviewTools: OverviewTools) {
			this.overviewTools = overviewTools;
		}
		
		public isInitialized() {
			return null !== this.elemFixedContainer;
		}
		
		public isFixed() {
			return this.fixed;	
		}
		
		public assignElemFixedContainer(elemFixedContainer: JQuery) {
			this.elemFixedContainer = elemFixedContainer;
			this.initElements();
			
			$(window).off("resize.overview overview.rowappended");
			elemFixedContainer.off("scroll.overview");
		}
		
		public getElemFixedContainer() {
			return this.elemFixedContainer;	
		}
		
		public getElemH3() {
			return this.elemH3;	
		}
		
		public isApplyDefaultFixedContainer() {
			return this.applyDefaultFixedContainer;	
		}
		
		public setApplyDefaultFixedContainer(applyDefaultFixedContainer) {
			this.applyDefaultFixedContainer = applyDefaultFixedContainer;
		}
		
		private initTableHeaders() {
			var clonedChildren = this.elemTableCloneHeader.children();
			this.elemTableHeader.children().each(function(index) {
				clonedChildren.eq(index).innerWidth($(this).innerWidth());
				clonedChildren.css({
					"boxSizing": "border-box"	
				});
			});
		}
		
		public startObserving() {
			var that = this;
			$(window).off("resize.overview overview.rowappended").on("resize.overview overview.rowappended", function() {
				that.initTableHeaders();
				that.elemFixedContainer.trigger("scroll.overview");
			}).trigger('resize.overview');
			
			this.elemFixedContainer.off("scroll.overview").on("scroll.overview", function() {
				if (that.elemFixedContainer.offset().top > that.elemH3.offset().top) {
					that.initFixed();
					return;
				}

				that.reset();
			});
			
			this.overviewTools.getElem().off("heightChange").on("heightChange", function() {
				if (!that.fixed) return;
				that.elemH3.css("marginBottom", $(this).outerHeight(true) - that.elemTableCloneHeader.outerHeight(true));
			});
		}
		
		public stopObserving() {
			$(window).off("resize.overview overview.rowappended");
			this.elemFixedContainer.off("scroll.overview");
			this.overviewTools.getElem().off("heightChange");
		}
		
		private initFixed() {
			if (this.fixed) return;

			this.initTableHeaders();
			
			var elemOverviewTools = this.overviewTools.getElem(),
				that = this;
			
			this.elemH3Clone.css({
				"position": "fixed"
			}).appendTo(this.elemRocketPanel);
			
			this.elemH3.css("marginBottom", "+=" + elemOverviewTools.outerHeight(true));
			elemOverviewTools.css({
				"position": "fixed"
			}).addClass("rocket-fixed");
			
			this.elemTableClone.appendTo(elemOverviewTools);
			this.fixed = true;
		}
		
		public reset() {
			if (!this.fixed) return;
		
			this.elemH3Clone.detach();
			this.elemTableClone.detach();
			this.elemH3.removeAttr("style");
			this.overviewTools.getElem().insertAfter(this.elemH3).removeAttr("style").removeClass("rocket-fixed");
			this.fixed = false;
		}
		
		private initElements() {
			if (null !== this.elemH3Clone) {
				this.elemH3Clone.remove();	
			}
			
			if (null !== this.elemTableClone) {
				this.elemTableClone.remove();	
			}
			
			this.elemH3 = this.elemFixedContainer.find("h3:first");
			this.elemH3Clone = this.elemH3.clone().addClass("rocket-cloned-header");
			this.elemTable = this.elemFixedContainer.find("table.rocket-list:first");
			this.elemTableHeader = this.elemTable.find("thead:first > tr");
			this.elemTableClone = this.elemTable.clone();
			this.elemTableCloneHeader = this.elemTableClone.find("thead:first > tr");
			this.elemRocketPanel = this.elemFixedContainer.find(".rocket-panel:first");
			
			this.elemTableClone.find("tbody").detach();
			
			this.reset();
		}
	}
	
	export class OverviewTools {
		private elem: JQuery;
		private critmodFormUrl: string;
		private contentUrl: string;
		private overviewContent: OverviewContent = null;
		private critmodForm: CritmodForm;
		private quickserachForm: QuickSearchForm;
		private fixedHeader: FixedHeader;
		private elemLoading: JQuery;
		
		private textEntriesLabel: string;
		private textEntriesPluralLabel: string;
		private textSelectedLabel: string;
		private textSelectedPluralLabel: string;
		
		
		public constructor(elem: JQuery, elemOverview: JQuery = null) {
			this.elem = elem;
			this.contentUrl = elem.data("content-url");
			this.critmodFormUrl = elem.data("critmod-form-url");
			this.fixedHeader = new FixedHeader(this);
			
			this.textEntriesLabel = this.elem.data("entries-label");
			this.textEntriesPluralLabel = this.elem.data("entries-plural-label");
			this.textSelectedLabel = this.elem.data("selected-label");
			this.textSelectedPluralLabel =  this.elem.data("selected-plural-label");
			this.elemLoading = rocketTs.createLoadingElem();
			
			(function(that: OverviewTools) {
				that.critmodForm = new CritmodForm(that, elem.find(".rocket-critmod:first"));
				that.quickserachForm = new QuickSearchForm(that, elem.find(".rocket-quicksearch:first"));
				if (null !== elemOverview && elemOverview.length > 0) {
					that.initOverview(elemOverview, elemOverview.data("num-pages"), 
							elemOverview.data("num-entries"), elemOverview.data("current-page"));
				} else {
					that.elem.before($("<h3 />", {
						"text": that.elem.data("entries-plural-label")	
					}));
					that.setLoading(true);
					that.getContent(1, function(elemContent, data) {
						that.setLoading(false);
						that.initOverview(elemContent, data['numPages'], data['numEntries'], 1);
						rocketTs.updateUi();
					});
				};
			}).call(this, this);
		}
		
		public setSelectable(selectable) {
			this.overviewContent.setSelectable(selectable);
		}
		
		public getTextEntriesLabel() {
			return this.textEntriesLabel;	
		}
		
		public getTextEntriesPluralLabel() {
			return this.textEntriesPluralLabel;	
		}
		
		public getTextSelectedLabel() {
			return this.textSelectedLabel;	
		}
		
		public getTextSelectedPluralLabel() {
			return this.textSelectedPluralLabel;	
		}
		
		public getElemFixedContainer() {
			return this.fixedHeader.getElemFixedContainer();	
		}
		
		public setLoading(loading: boolean = true) {
			if (loading) {
				if (null !== this.overviewContent) {
					this.elemLoading.insertAfter(this.overviewContent.getElemMainContent());
				} else {
					this.elemLoading.insertAfter(this.elem);
				}
			} else {
				this.elemLoading.detach();
			}
		}
		
		public setElemFixedContainer(elemFixedContainer: JQuery, startObserving: boolean = true) {
			var that = this;
			this.fixedHeader.assignElemFixedContainer(elemFixedContainer);
			if (startObserving) {
				this.fixedHeader.startObserving();
			}
			
			this.overviewContent.getPagination().applyElemFixedContainer(elemFixedContainer);
		}
		
		public getOverviewContent() {
			return this.overviewContent;	
		}
		
		public getElem() {
			return this.elem;	
		}
		
		public getFixedHeader() {
			return this.fixedHeader;
		}
		
		private initOverview(elemOverview: JQuery, numPages, numEntries, pageNo) {
			elemOverview.insertAfter(this.elem);
			this.overviewContent = new OverviewContent(this, elemOverview, numPages, numEntries, pageNo);
			//Hook to change the fixed container
			this.elem.trigger('overview.contentLoaded', [this]);
			
			if (this.fixedHeader.isApplyDefaultFixedContainer()) {
				this.setElemFixedContainer(rocketTs.getElemContentContainer());
			}
		}
		
		public getContent(pageNo: number, callback: (elem: JQuery, data: Object) => void): JQueryXHR {
			var that = this;
			return $.getJSON(this.contentUrl, {
				pageNo: pageNo
			}, function(data) {
				callback(rocketTs.analyzeAjahData(data), data['additional']);
			});
		}
		
		public reloadContent() {
			var that = this;
			this.overviewContent.clear();
			this.setLoading(true);
			
			this.getContent(1, function(elemContent: JQuery, additionalData: Object) {
				that.setLoading(false);
				that.overviewContent.replaceContent(elemContent);
				that.overviewContent.setNumEntries(additionalData['numEntries']);
				var pagination = that.overviewContent.getPagination();
				pagination.setCurrentPageNo(1);
				pagination.setNumPages(additionalData['numPages']);
			});
		}
		
		public postForm(methName: string, formData: FormData, callback: (data: Object) => void = null,
				formUrl: string = null): JQueryXHR {
			this.overviewContent.clear();
			this.setLoading(true);
			formData.append(methName, true);
			var that = this;
			
			return  $.ajax({
				"url": this.contentUrl + "?pageNo=1",
				"type": "POST",
				"data": formData,
				"processData": false,
				"contentType": false,
				"success":  function(data) {
					var elemContent = $($.parseHTML(n2n.dispatch.analyze(data))),
						additionalData = data['additional'];
					that.setLoading(false);
					that.overviewContent.replaceContent(elemContent);
					that.overviewContent.setNumEntries(additionalData['numEntries']);
					var pagination = that.overviewContent.getPagination();
					pagination.setCurrentPageNo(1);
					pagination.setNumPages(additionalData['numPages']);
					
					if (null !== callback) {
						callback(additionalData);	
					}
				}
			});
		}
	}
		
	rocketTs.ready(function() {

		var elemOverviewTools = $(".rocket-overview-tools:first"),
			elemMainContent = $(".rocket-overview-main-content:first");
		if (elemOverviewTools.length > 0) {
			elemOverviewTools.data("initialized-overview-tools", true);
			new OverviewTools(elemOverviewTools, elemMainContent);
		}
				
		n2n.dispatch.registerCallback(function() {
			$(".rocket-overview-tools").each(function() {
				var elemOverview = $(this);
				if (elemOverview.data("initialized-overview-tools")) return;
				
				elemOverview.data("initialized-overview-tools", true);
				new OverviewTools(elemOverview);
			});
		});
	});
}
