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
 */
jQuery(document).ready(function($) {
	(function() {
		var Filter = function(jqElem) {
			this.jqElem = jqElem;
			this.iconClassNameAdd = jqElem.data("icon-class-name-add");
			this.iconClassNameRemove = jqElem.data("remove-icon-class-name");
			this.iconClassNameAnd = jqElem.data("and-icon-class-name");
			this.iconClassNameOr = jqElem.data("or-icon-class-name");
			this.textAddGroup = jqElem.data("text-add-group");
			this.textAddField = jqElem.data("text-add-field");
			this.textOr = jqElem.data("text-or");
			this.textAnd = jqElem.data("text-and");
			this.textRemove = jqElem.data("text-delete");
			this.filterFieldItemFormUrl = jqElem.data("filter-field-item-form-url");
			this.filterGroupFormUrl = jqElem.data("filter-group-form-url");
			this.fields = jqElem.data("filter-fields");
			console.log(this.fields);
			
			new FilterGroup(jqElem.children(":first"), this, null);
		};
		
		Filter.prototype.requestFilterFieldItem = function(filterGroup, fieldId, propertyPath, callback) {
			$.getJSON(this.filterFieldItemFormUrl, {
				filterFieldId: fieldId,
				propertyPath: propertyPath
			}, function(filterFieldData) {
				var jqElemFilterFieldItem = $($.parseHTML(n2n.dispatch.analyze(filterFieldData))),
					filterFieldItem = new FilterFieldItem(jqElemFilterFieldItem, filterGroup);
				callback(filterFieldItem);
			});
		};
		
		Filter.prototype.requestFilterGroup = function(parentFilterGroup, propertyPath, callback) {
			var that = this;
			$.getJSON(this.filterGroupFormUrl, {
				propertyPath: propertyPath
			}, function(filterGroupData) {
				var jqElemFilterGroup = $($.parseHTML(n2n.dispatch.analyze(filterGroupData))),
					filterGroup = new FilterGroup(jqElemFilterGroup, that, parentFilterGroup);
				callback(filterGroup);
			});
		};
		
		Filter.prototype.getLabelForFieldId = function(fieldId) {
			if (!this.fields.hasOwnProperty(fieldId)) return;
			
			return this.fields[fieldId];
		};
		
		var FilterGroup = function(jqElem, filter, parentFilterGroup) {
			this.jqElem = jqElem;
			this.filter = filter;
			this.removable = (null !== parentFilterGroup);
			
			this.jqElemFieldItems = jqElem.find(".rocket-filter-field-items:first");
			this.nextFieldItemIndex = jqElem.children("li").length;
			this.baseFieldItemPropertyPath = this.jqElemFieldItems.data("new-form-array-property-path"); 
			
			this.jqElemGroups = jqElem.find(".rocket-filter-groups:first");
			this.nextGroupIndex = jqElem.children("li").length;
			this.baseGroupPropertyPath = this.jqElemGroups.data("new-form-array-property-path");
			
			this.jqElemCbxAndIndicator = jqElem.find(".rocket-filter-and-indicator:first").hide();
			
			this.jqElemUlCommands = null;
			this.jqElemSpanAndOrSwitchText = null;
			this.jqElemIAndOrSwitch = null;
			this.jqElemAAndOrSwitch = null;

			this.jqElemAAddFieldItem = null;
			this.jqElemAAddGroup = null;
			this.jqElemARemove = null;
			this.jqElemDivFieldListContainer = null;
			this.jqElemArrowFieldList = null;
			this.jqElemUlFieldsList = null;
			this.mouseLeaveTimeout = null;
			
			this.initializeCommands();
			
			(function(that) {
				this.jqElemAAndOrSwitch.click(function(e) {
					e.preventDefault();
					that.jqElemCbxAndIndicator.prop("checked", 
							!that.jqElemCbxAndIndicator.prop("checked"));
					that.applyAndOrSwitchTexts();
					that.applyAndOrSwitchIcons();
				});
				
				if (null !== parentFilterGroup) {
					this.jqElemSpanTextAndOr = $("<span />", {
						"text": parentFilterGroup.jqElemSpanAndOrSwitchText.text(),
						"class": "rocket-filter-field-text-and-or"
					}).appendTo(this.jqElem);

					parentFilterGroup.jqElemAAndOrSwitch.on("filter.changedText", function(e, text) {
						that.jqElemSpanTextAndOr.text(text);
					});
				}
				
				this.jqElemAAddGroup.click(function(e) {
					e.preventDefault();
					var jqElemLoading = $("<li />", {
						"class": "rocket-filter-group"
					}).append($("<div />", {
						"class": "rocket-loading"
					})).appendTo(that.jqElemGroups);
					
					filter.requestFilterGroup(that, that.buildNextGroupPropertyPath(), function(group) {
						jqElemLoading.remove();
						that.jqElemGroups.append(group.jqElem);
						that.jqElem.trigger('heightChange');
					});
				});
				
				this.jqElemAAddFieldItem.click(function(e) {
					e.preventDefault();
					e.stopPropagation();
					if (that.jqElemUlFieldsList.is(":hidden")) {
						that.showFieldList();
					} else {
						that.hideFieldList();
					}
				});
				
				this.jqElemFieldItems.children("li").each(function() {
					new FilterFieldItem($(this), that);
				});
				
				this.jqElemGroups.children("li").each(function() {
					new FilterGroup($(this), filter, true);
				});
				
				this.applyAndOrSwitchTexts();
				this.applyAndOrSwitchIcons();
			}).call(this, this);
		};
		
		FilterGroup.prototype.showFieldList = function() {
			this.jqElemDivFieldListContainer.show();
			var jqElemOpener = this.jqElemAAddFieldItem,
				left = jqElemOpener.offset().left + jqElemOpener.outerWidth();
			this.jqElemArrow.show().css({
				"top": jqElemOpener.offset().top + (jqElemOpener.outerHeight() / 2) 
						- (this.jqElemArrow.outerHeight() / 2),
				"left": left + 2
			});
			  
			left += this.jqElemArrow.outerWidth() / 2;
			  
			this.jqElemDivFieldListContainer.css({
				"top": this.determineContentTopPos(),
				"left": left
			});
			this.applyFieldListMouseLeave();
		};
		
		FilterGroup.prototype.determineContentTopPos = function() {
			var jqElemOpener = this.jqElemAAddFieldItem;
			return jqElemOpener.offset().top +  jqElemOpener.outerHeight() / 2 -
						$(window).scrollTop() - (this.jqElemDivFieldListContainer.outerHeight() / 2);
		};
		
		FilterGroup.prototype.applyFieldListMouseLeave = function() {
			var that = this,
				jqElemContentContainer = this.jqElemDivFieldListContainer,
				jqElemOpener = this.jqElemAAddFieldItem;
			this.resetFieldListMouseLeave();

			jqElemContentContainer.on("mouseenter.multi-add", function() {
				that.applyFieldListMouseLeave();
			}).on("mouseleave.multi-add", function() {
				that.mouseLeaveTimeout = setTimeout(function() {
					that.hideFieldList();
				}, 1000);
			}).on("click.multi-add", function(e) {
				e.stopPropagation();
			});
			
			$(window).on("keyup.multi-add", function(e) {
				if (e.which === 27) {
					//escape	
					that.hideFieldList();	
				};
			}).on("click.multi-add", function() {
				that.hideFieldList();
			});
		};
		
		FilterGroup.prototype.hideFieldList = function() {
			this.jqElemDivFieldListContainer.hide();
			this.jqElemArrow.hide();
			this.resetFieldListMouseLeave();
		};
		
		FilterGroup.prototype.resetFieldListMouseLeave = function() {
			if (null !== this.mouseLeaveTimeout) {
				clearTimeout(this.mouseLeaveTimeout);
				this.mouseLeaveTimeout = null;
			}
			
			this.jqElemDivFieldListContainer.off("mouseenter.multi-add mouseleave.multi-add click.multi-add");
			$(window).off("keyup.multi-add click.multi-add");
		};
		
		FilterGroup.prototype.initializeCommands = function() {
			var that = this;
			this.jqElemUlCommands = $("<ul />", {
				"class": "rocket-filter-group-controls"
			}).insertAfter(this.jqElemGroups);
			
			this.jqElemSpanAndOrSwitchText = $("<span />");
			this.jqElemIAndOrSwitch = $("<i />");
			
			this.jqElemAAndOrSwitch = $("<a />", {
				"href": "#",
				"class": "rocket-control"
			}).append(this.jqElemIAndOrSwitch).append(this.jqElemSpanAndOrSwitchText)
			.appendTo($("<li />").appendTo(this.jqElemUlCommands));

			this.initializeMultiAdd();

			this.jqElemAAddGroup = $("<a />", {
				"href": "#",
				"class": "rocket-control"
			}).append($("<i />", {
				"class": this.filter.iconClassNameAdd
			})).append($("<span />", {
				"text": this.filter.textAddGroup
			})).appendTo($("<li />").appendTo(this.jqElemUlCommands));

			
			if (this.removable) {
				this.jqElemARemove = $("<a />", {
					"href": "#",
					"class": "rocket-control"
				}).append($("<i />", {
					"class": this.filter.iconClassNameRemove
				})).append($("<span />", {
					"text": this.filter.textRemove
				})).appendTo($("<li />").appendTo(this.jqElemUlCommands)).click(function(e) {
					e.preventDefault();
					that.jqElem.remove();
					that.jqElem.trigger('heightChange');
				});
			}
		};
		
		FilterGroup.prototype.initializeMultiAdd = function() {
			var that = this;
			this.jqElemAAddFieldItem = $("<a />", {
				"href": "#",
				"class": "rocket-control"
			}).append($("<i />", {
				"class": this.filter.iconClassNameAdd
			})).append($("<span />", {
				"text": this.filter.textAddField
			})).appendTo($("<li />").appendTo(this.jqElemUlCommands));
			
			this.jqElemDivFieldListContainer = $("<div />", {
				"class": "rocket-multi-add-content-container"
			}).css({
				"position": "fixed",
				"zIndex": 1000
			}).insertAfter(this.jqElemUlCommands).hide().click(function() {
				that.jqElemArrow.hide();
			});
			
			this.jqElemArrow = $("<span />").insertAfter(this.jqElemDivFieldListContainer).css({
				"position": "fixed",
				"background": "#818a91",
				"transform": "rotate(45deg)",
				"width": "15px",
				"height": "15px",
				"zIndex": 999
			}).addClass("rocke-multi-add-arrow-left").hide();
			
			this.jqElemUlFieldsList = $("<ul />", {
				"class": "rocket-multi-add-entries"
			}).appendTo(this.jqElemDivFieldListContainer);
			
			var that = this;
			
			for (var fieldId in this.filter.fields) {
				$("<a />", {
					href: "#",
					text: this.filter.fields[fieldId]
				}).data("field-id", fieldId).click(function(e) {
					e.preventDefault();
					var jqElemLoading = $("<li />", {
						"class": "rocket-filter-field-item"
					}).append($("<div />", {
						"class": "rocket-loading"
					})).appendTo(that.jqElemFieldItems);
					
					that.filter.requestFilterFieldItem(that, $(this).data("field-id"), that.buildNextFieldItemPropertyPath(), 
							function(fieldItem) {
						jqElemLoading.remove();
						that.jqElemFieldItems.append(fieldItem.jqElem);
						that.filter.jqElem.trigger('heightChange');
						n2n.dispatch.update();
					});
				}).appendTo($("<li />").appendTo(this.jqElemUlFieldsList));
			}
		};
		
		FilterGroup.prototype.applyAndOrSwitchTexts = function() {
			if (this.jqElemCbxAndIndicator.prop("checked")) {
				this.jqElemSpanAndOrSwitchText.text(this.filter.textAnd);
			} else {
				this.jqElemSpanAndOrSwitchText.text(this.filter.textOr);
			}
			
			this.jqElemAAndOrSwitch.trigger("filter.changedText", [this.jqElemSpanAndOrSwitchText.text()]);
		};
		
		FilterGroup.prototype.applyAndOrSwitchIcons = function() {
			if (this.jqElemCbxAndIndicator.prop("checked")) {
				this.jqElemIAndOrSwitch.removeClass().addClass(this.filter.iconClassNameAnd);
			} else {
				this.jqElemIAndOrSwitch.removeClass().addClass(this.filter.iconClassNameOr);
			}
		};
		
		FilterGroup.prototype.buildNextFieldItemPropertyPath = function() {
			return this.baseFieldItemPropertyPath + '[' + this.nextFieldItemIndex++ + ']';
		};
		
		FilterGroup.prototype.buildNextGroupPropertyPath = function() {
			return this.baseGroupPropertyPath + '[' + this.nextGroupIndex++ + ']';
		};
		
		var FilterFieldItem = function(jqElem, filterGroup) {
			this.jqElem = jqElem;
			this.filterGroup = filterGroup;
			this.jqElemSpanTextAndOr = $("<span />", {
				"text": filterGroup.jqElemSpanAndOrSwitchText.text(),
				"class": "rocket-filter-field-text-and-or"
			}).appendTo(this.jqElem);
			 
			this.jqElemARemove = $("<a />", {
				"href": "#",
				"class": "rocket-control rocket-filter-field-remove"
			}).append($("<i />", {
				"class": this.filterGroup.filter.iconClassNameRemove
			})).append($("<span />", {
				"text": this.filterGroup.filter.textRemove
			})).appendTo(this.jqElem);
			
			(function(that) {
				filterGroup.jqElemAAndOrSwitch.on("filter.changedText", function(e, text) {
					that.jqElemSpanTextAndOr.text(text);
				});
				
				this.jqElemARemove.click(function(e) {
					e.preventDefault();
					that.jqElem.remove();
					that.jqElem.trigger('heightChange');
				});
				
				var jqElemFieldId = jqElem.find(".rocket-filter-field-id:first");
				$("<span />", {
					"text": this.filterGroup.filter.getLabelForFieldId(jqElemFieldId.children("input").hide().val())
				}).appendTo(jqElemFieldId);
				
			}).call(this, this);
		};

		
		var initialize = function() {
			$(".rocket-filter").each(function() {
				var jqElem = $(this);
				if (jqElem.data("initialized-rocket-filter")) return;
				jqElem.data("initialized-rocket-filter", true);
				
				new Filter(jqElem);
			});
		};
		
		if (Jhtml) {
			Jhtml.ready(initialize);
		}
		
		initialize();
		n2n.dispatch.registerCallback(initialize);
	})();
});
