(function() {
	Jhtml.ready(function (elements) {
		$(elements).find(".rocket-impl-cke-classic").each((i, elem) => {
			var editor = CKEDITOR.replace(elem);
			

//			let formJq = $(elem).closest("form");
//			formJq.submit(() => {
//				for (let i in CKEDITOR.instances) {
//					CKEDITOR.instances[i].updateElement();
//				}
//			});
//			formJq.find("input[type=submit], button[type=submit]").click(() => {
//				alert();
//				for (let i in CKEDITOR.instances) {
//					CKEDITOR.instances[i].updateElement();
//				}
//			});
		});

		$(elements).find(".rocket-cke-detail").each(function () {
			var elemJq = $(this);
			
			this.contentWindow.document.open();
			this.contentWindow.document.write(elemJq.data("content-html-json"));
			this.contentWindow.document.close();
			configureIframe(this.contentWindow.document, elemJq.data("contentsCss"), elemJq.data("bodyId"), elemJq.data("bodyClass"));
		});
	});

	function configureIframe(document, contentsCss, bodyId, bodyClass) {
		var jqElem = $(document)
		var jqElemIFrameBody = $(document).find("body:first");

		if (null !== contentsCss) {
			contentsCss = JSON.parse(contentsCss.replace(/'/g, '"'))
			var jqElemIFrameHead = jqElem.contents().find("head:first");
			for (var i in contentsCss) {
				jqElemIFrameHead.append($("<link />", { href: contentsCss[i], rel: "stylesheet", media: "screen"}));
			}
		}

		if (bodyId != null) {
			jqElemIFrameBody.attr("id", bodyId);
		}

		if (bodyClass != null) {
			jqElemIFrameBody.addClass(bodyClass);
		}
	};
})();