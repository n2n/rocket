jQuery(document).ready(function($) {
	(function() {
		if (typeof $.fn.colorbox != 'function') return
		$(".rocket-image-previewable").colorbox({rel:"rocket-image-previewable", transition:"fade"});
	})();
	
	(function(){
		var jqImagePreviewItem = $('.rocket-image-previewable');
		
		jqImagePreviewItem.each(function(){
			$(this).parent('td').addClass('rocket-image-preview-item');
		})
	})();
});