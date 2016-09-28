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
jQuery(document).ready(function ($) {
	rocketTs.registerUiInitFunction(".rocket-entry-selector", function(jqSelector) {
		var jqSelectorInput = jqSelector.find("input[type=checkbox], input[type=radio]");
		if (jqSelectorInput.size() == 0 && jqSelector.children().size() == 0) {
			jqSelectorInput = $("<input />", { "type": "checkbox" });
			jqSelector.append(jqSelectorInput);
		}
		
		jqSelectorInput.change(function () {
			if ($(this).is(':checked') || 0 < $(".rocket-entry-selector input:checked").size()) {
				$("a.rocket-order-before-cmd").show();
				$("a.rocket-order-after-cmd").show();
				$("a.rocket-order-child-cmd").show();
			} else {
				$("a.rocket-order-before-cmd").hide();
				$("a.rocket-order-after-cmd").hide();
				$("a.rocket-order-child-cmd").hide();
			}
		});
		
		var jqBeforeA = null;
		var jqAfterA = null;
		var jqChildA = null;
		do {
			var jqContainer = jqSelector.parent();
			if (!jqContainer || 0 == jqContainer.size()) return;
			
			jqBeforeA = jqContainer.find("a.rocket-order-before-cmd");
			jqAfterA = jqContainer.find("a.rocket-order-after-cmd");
			jqChildA = jqContainer.find("a.rocket-order-child-cmd");
		} while (jqBeforeA.size() == 0 && jqAfterA.size() > 0);
		
		if (!jqSelectorInput.is(':checked') && 0 == $(".rocket-entry-selector input:checked").size()) {
			jqBeforeA.hide();
			jqAfterA.hide();
			jqChildA.hide();
		}
		
		jqBeforeA.click(function (e) {
			e.stopPropagation();
			move(jqBeforeA);
			return false;
		});
		jqAfterA.click(function (e) {
			e.stopPropagation();
			move(jqAfterA);
			return false;
		});
		jqChildA.click(function (e) {
			e.stopPropagation();
			move(jqChildA);
			return false;
		});
	});
	
	function move(jqA) {
		var idRepParams = new Array();
		$(".rocket-entry-selector input:checked").each(function () {
			idRepParams.push("idReps[]=" + $(this).parent(".rocket-entry-selector").data("entry-id-rep"));
		});
		
		var href = jqA.prop("href");
		if (0 > href.indexOf("?")) {
			href += "?";
		} else {
			href += "&";
		}
		
		window.location = href + idRepParams.join("&");
	}
	
//	var initFunc = function () {
//		console.log("huiii");
//		$("a.rocket-online-cmd").each(function () {	
//			if (this.oci === true) return;
//			this.oci = true;
//			
//			var jqA = $(this);
//			
//			var onlineUrl = jqA.data("online-url");
//			var offlineUrl = jqA.data("offline-url");
//	
//			jqA.click(function () {
//				if (jqA.hasClass("rocket-control-success")) {
//					jqA.addClass("rocket-control");
//					jqA.removeClass("rocket-control-success");
//					jqA.children("i").attr("class", "fa fa-circle");
//					
//					$.ajax(offlineUrl).done(function () {
//						jqA.addClass("rocket-control-danger");
//						jqA.children("i").attr("class", "fa fa-minus-circle");
//					});
//				} else if (jqA.hasClass("rocket-control-danger")) {
//					jqA.addClass("rocket-control");
//					jqA.removeClass("rocket-control-danger");
//					jqA.children("i").attr("class", "fa fa-circle");
//					
//					$.ajax(onlineUrl).done(function () {
//						jqA.addClass("rocket-control-success");
//						jqA.children("i").attr("class", "fa fa-check-circle");
//					});
//				}
//			});
//		});
//	};
//	
//	initFunc();
//	n2n.dispatch.analyze(initFunc);	
});
