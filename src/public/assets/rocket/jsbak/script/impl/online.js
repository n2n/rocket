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
	rocketTs.registerUiInitFunction("a.rocket-online-cmd", function(jqA) {
		
		
		var onlineUrl = jqA.data("online-url");
		var offlineUrl = jqA.data("offline-url");

		jqA.click(function () {
			if (jqA.hasClass("rocket-control-success")) {
				jqA.addClass("rocket-control");
				jqA.removeClass("rocket-control-success");
				jqA.children("i").attr("class", "fa fa-circle");
				
				$.ajax(offlineUrl).done(function () {
					jqA.addClass("rocket-control-danger");
					jqA.children("i").attr("class", "fa fa-minus-circle");
				});
			} else if (jqA.hasClass("rocket-control-danger")) {
				jqA.addClass("rocket-control");
				jqA.removeClass("rocket-control-danger");
				jqA.children("i").attr("class", "fa fa-circle");
				
				$.ajax(onlineUrl).done(function () {
					jqA.addClass("rocket-control-success");
					jqA.children("i").attr("class", "fa fa-check-circle");
				});
			}
		});
	});
	
	
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
