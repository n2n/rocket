jQuery(document).ready(function($) {
	(function() {
		var jqElemDivMailCenter = $("#rocket-tools-mail-center");
		if (jqElemDivMailCenter.length == 0) return;
		jqElemDivMailCenter.find("article").each(function() {
			var jqElem = $(this);
			var jqElemDlMessage = jqElem.children("dl:first");
			jqElem.children("header:first").click(function() {
				jqElemDlMessage.slideToggle();
			});
			jqElemDlMessage.hide();
		});
	})();
});