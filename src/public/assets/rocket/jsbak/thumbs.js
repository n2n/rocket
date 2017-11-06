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
	var jqElemResizer = $("#rocket-image-resizer");
	if (jqElemResizer.length == 0) return;
	
	var jqElemPageControls = $("#rocket-page-controls");
	var jqElemRocketHeader = $("#rocket-header");
	var jqElemWindow = $(window);
	
	new HnmImageResizer(jqElemResizer, $("#rocket-thumb-dimension-select"), null, function() {
		return jqElemWindow.height() - jqElemRocketHeader.outerHeight() - jqElemPageControls.outerHeight() - 50;
	});
	
	var jqElemInpPositionX = $("#rocket-thumb-pos-x").hide();
	var jqElemInpPositionY = $("#rocket-thumb-pos-y").hide();
	var jqElemInpWidth = $("#rocket-thumb-width").hide();
	var jqElemInpHeight = $("#rocket-thumb-height").hide();
	
	
	$(".rocket-thumbnail > img").each(function() {
		$(this).attr('src', $(this).attr("src") + "?timestamp=" + new Date().getTime());
	});
		
	jqElemResizer.on('dimensionChanged', function( event, dimension ) {
		jqElemInpPositionX.val(Math.floor(dimension.left));
		jqElemInpPositionY.val(Math.floor(dimension.top));
		jqElemInpWidth.val(Math.floor(dimension.width));
		jqElemInpHeight.val(Math.floor(dimension.height));
	});
});
