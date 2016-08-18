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