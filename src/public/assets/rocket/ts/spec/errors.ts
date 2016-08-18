/// <reference path="../rocket.ts" />
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
	
	class Error {
		private elemError: JQuery;
		private elemLi: JQuery;
		private elemMessage: JQuery;
		
		public constructor(errorList: ErrorList, elemError: JQuery, elemMessage: JQuery) {
			this.elemError = elemError;
			this.elemLi = $("<li />");
			this.elemMessage = elemMessage;
			
			(function(that: Error) {
				var elemA = $("<a />", {
					"href": "#"
				}).appendTo(that.elemLi);
				
				$("<div />", {
					"class": "error-list-label",
					"text": elemMessage.text() || "Fehler"	
				}).appendTo(elemA);
				
				$("<div />", {
					"class": "error-list-path",
					"text": errorList.determinePathLabel(that)	
				}).appendTo(elemA);
				
				elemA.click(function(e) {
					e.preventDefault();
					errorList.scrollTo(that);
					that.elemLi.find("input[type=text], textarea").first().focus();
				});
				
				elemA.mouseenter(function() {
					errorList.highlight(that);
				}).mouseleave(function() {
					errorList.normalize(that);
				});
			}).call(this, this);
		}
		
		public getElem() {
			return this.elemError;	
		}
		
		public getElemLi() {
			return this.elemLi;	
		}
	}
	
	class ErrorList {
		private elemFixedContainer: JQuery;
		private elemList: JQuery;
		
		public constructor() {
			this.elemFixedContainer = rocketTs.getElemContentContainer();
			
			this.elemList = $("<ul />", {
				"class": "rocket-error-list"	
			});
		}
		
		public hasErrors() {
			return this.elemList.children().length > 0;	
		}
		
		public getElemList() {
			return this.elemList;	
		}
		
		private determinePathElements(elem): JQuery {
			return elem.parents(".rocket-has-error");
		}
		
		public determinePathLabel(error: Error) {
			var elem = error.getElem(), 
				labelParts = [elem.children("label:first").text()];
			this.determinePathElements(error.getElem()).each(function() {
				labelParts.unshift($(this).children("label:first").text());
			});
			
			return labelParts.join(" / ");
		}
		
		public highlight(error: Error) {
			var elem = error.getElem().addClass("rocket-highlighted");
//			this.determinePathElements(error.getElem()).each(function() {
//				$(this).addClass("rocket-highlighted");
//			});
		}
		
		public normalize(error: Error) {
			var elem = error.getElem().removeClass("rocket-highlighted");
//			this.determinePathElements(error.getElem()).each(function() {
//				$(this).removeClass("rocket-highlighted");
//			});
		}
		
		public scrollTo(error: Error) {
			this.elemFixedContainer.animate({
				scrollTop: "+=" + (error.getElem().offset().top - this.elemFixedContainer.offset().top)
			});
		}
		
		public addError(elemError: JQuery, elemMessage: JQuery) {
			var error = new Error(this, elemError, elemMessage);
			this.elemList.append(error.getElemLi());
			return error;
		}
	}
	
	rocketTs.ready(function() {
		var errorList = new ErrorList();
		
		$(".rocket-message-error").each(function() {
			errorList.addError($(this).parents(".rocket-has-error:first"), $(this));
		});

		if (errorList.hasErrors()) {
			var additionalContent = rocketTs.getOrCreateAdditionalContent(); 
			additionalContent.createAndPrependEntry(additionalContent.getElemContent().data("error-list-label"), 
					errorList.getElemList());
		}
	});
}
