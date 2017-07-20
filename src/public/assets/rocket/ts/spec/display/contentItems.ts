/// <reference path="..\..\rocket.ts" />
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
module spec.display {
	$ = jQuery;

	class ContentItem {
		private elem: JQuery;
		private elemType: JQuery;
		private typeLabel: string;
		
		public constructor(elem) {
			this.elem = elem;
			this.elemType = elem.find(".rocket-gui-field-type").hide();
			this.typeLabel = this.elemType.children(".rocket-controls").text();
			elem.find(".rocket-field-orderIndex").hide();
			new spec.EntryHeader(this.typeLabel, elem);
		}
	}
	
	class ContentItemPanel {
		private elemHeader: JQuery;
		private elemContent: JQuery;
		
		public constructor(elemHeader: JQuery, elemContent: JQuery) {
			this.elemHeader = elemHeader;
			this.elemContent = elemContent;
			
			(function() {
				elemContent.children(".rocket-content-item").each(function() {
					new ContentItem($(this));	
				});
			}).call(this, this);
		}
	}
	
	class ContentItemComposer {
		private elem: JQuery;
		
		public constructor(elem: JQuery) {
			this.elem = elem;
			
			(function(that: ContentItemComposer) {
				elem.children("h4").each(function() {
					new ContentItemPanel($(this), $(this).next());
				});
			}).call(this, this);
		}
	}
	
	rocketTs.ready(function() {
		rocketTs.registerUiInitFunction(".rocket-content-item-composer", function(elemContentItemComposer: JQuery) {
			new ContentItemComposer(elemContentItemComposer);
		});
	});
}
