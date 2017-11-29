(function() {
	Jhtml.ready(function (elements) {
		$(elements).find(".rocket-impl-cke-classic").each(function (i, elem) {
			
			
			
//			var observer = new MutationObserver(function(mutations) {
//				  mutations.forEach(function(mutation) {
//				    console.log(mutation.type);
//				  });    
//			});
//			
//			var config = { attributes: true, childList: true, characterData: true };
//			observer.observe(elem.parentElement, config);
			
			
			
//			editor.checkDirty();
//			editor.resetDirty();
//			editor.resize();
			
//			createFakeElement
//			cke.js:10:5
//			createFakeParserElement
//			cke.js:10:5
//			restoreRealElement
			
//			CKEDITOR.remove(elem);
//			console.log("ee2");
//			for (let i in editor) {
//				console.log(i);
//			}
//			
			
			
			let parentJq = $(elem.parentElement);
			let visible = parentJq.is(":visible");
			
			let editorName;
			if (visible) {
				editorName = CKEDITOR.instances[CKEDITOR.replace(elem).name].name;
			}
			
			setInterval(function () {
				if (visible == parentJq.is(":visible")) return;
				
				visible = $(parentJq).is(":visible");
				if (editorName && CKEDITOR.instances[editorName]) {
					let editor = CKEDITOR.instances[editorName];
					editor.updateElement();
					editor.destroy();
					CKEDITOR.remove(editor);
					editorName = null;
				}
				
				if (visible) {
					editorName = CKEDITOR.instances[CKEDITOR.replace(elem).name].name;
				}
			}, 1000);
			
			
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