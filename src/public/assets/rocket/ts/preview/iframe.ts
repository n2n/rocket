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
 */
module preview {
	$ = jQuery;
	
	class Iframe {
		private elemIframe: JQuery;
		private elemHeader: JQuery;
		private elemPanelTitle: JQuery;
		private elemMainCommands: JQuery;
		
		public constructor(elemIframe: JQuery) {
			this.elemIframe = elemIframe;
			this.elemHeader = $("#rocket-header");
			this.elemPanelTitle = $(".rocket-panel:first h3:first");
			this.elemMainCommands = $(".rocket-main-commands:first");
			
			(function(that: Iframe) {
				$(window).resize(function() {
					rocketTs.waitForFinalEvent(function() {
						that.adjustIframeHeight();
					}, 30, 'preview.resize');	
				});
				
				that.adjustIframeHeight();
			}).call(this, this);
		}
		
		public adjustIframeHeight() {
			var iFrameMinHeight = $(window).height() - this.elemHeader.height() 
					- this.elemMainCommands.outerHeight() - this.elemPanelTitle.outerHeight();
			this.elemIframe.css({
				"min-height": iFrameMinHeight	
			});
		}
	}
	
	rocketTs.ready(function() {
		var elemIframe = $("#rocket-preview-content");
		if (elemIframe.length === 0) return;
		
		new Iframe(elemIframe);
	});
}
