/// <reference path="../rocket.ts" />
/// <reference path="../ui/panels.ts" />
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
module spec {
	$ = jQuery;
	class AsideContainer {
		private elemContainer: JQuery;
		private elemMainContainer: JQuery;
		private elemAsideContainer: JQuery;
		
		public constructor(elemContainer: JQuery) {
			this.elemContainer = elemContainer;
			this.elemMainContainer = $("<div />", {
				"class": "rocket-main-bundle"	
			});
			this.elemAsideContainer = $("<div />", {
				"class": "rocket-aside-bundle"	
			});
			
			this.elemContainer.children(":not(.rocket-control-group-aside)").appendTo(this.elemMainContainer);
			this.elemContainer.children(".rocket-control-group-aside").appendTo(this.elemAsideContainer);
			
			this.elemMainContainer.appendTo(this.elemContainer);
			this.elemAsideContainer.appendTo(this.elemContainer);
		}
	}
	
	rocketTs.ready(function() {
		rocketTs.registerUiInitFunction(".rocket-aside-container", function(elem: JQuery) {
			new AsideContainer(elem);	
		});
		
		rocketTs.registerUiInitFunction(".rocket-control-group-main", function(elem: JQuery) {
			var elemNextMainContainer = elem.next(".rocket-control-group-main");
			if (elemNextMainContainer.length === 0) return;
			
			var elemPanelGroup = $("<div />", {
				"class": "rocket-grouped-panels"	
			});
			
			elemPanelGroup.insertBefore(elem).append(elem);
			var tmpElemNextMainContainer;
			do {
				tmpElemNextMainContainer = elemNextMainContainer.next(".rocket-control-group-main");
				elemPanelGroup.append(rocketTs.markAsInitialized(".rocket-control-group-main", elemNextMainContainer));
				elemNextMainContainer = tmpElemNextMainContainer;
			} while(elemNextMainContainer.length > 0);
			
			rocketTs.markAsInitialized(".rocket-grouped-panels", elemPanelGroup);
			new ui.PanelGroup(elemPanelGroup);
			
		});
	});
}
