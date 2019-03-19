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
Jhtml.ready(function(elements) {
	(function() {
		if (typeof $.fn.magnificPopup != 'function') return;
		$(elements).find(".rocket-image-previewable").magnificPopup({
			type: 'image', 
			gallery: {
				enabled:true
			}
		});
	})();
	
	(function() {
		if (typeof $.fn.magnificPopup != 'function') return;
		$(elements).find(".rocket-video-previewable").magnificPopup({
            type: 'iframe'
		});
	})();
	
	(function(){
		var jqImagePreviewItem = $(elements).find('.rocket-image-previewable');
		
		jqImagePreviewItem.each(function(){
			$(this).parent('td').addClass('rocket-image-preview-item');
		})
	})();
});
