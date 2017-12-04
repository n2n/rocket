(function() {
	Jhtml.ready(function (elements) {
		console.log(elements.length);
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
			
			let editor;
			if (visible) {
				editor = CKEDITOR.replace(elem);
				if (tos[editor.name]) {
					clearInterval(tos[editor.name]);
				}
			}
			
			let hackCheck = function () {
				if (!document.contains(elem)) return;
				
				if (visible == parentJq.is(":visible")) {
					setTimeout(() => {
						requestAnimationFrame(hackCheck);
					}, 500);
					return;
				}
				
				visible = parentJq.is(":visible");
				if (editor) {
					editor.updateElement();
					editor.destroy();
					editor = null;
				}
				
				if (visible) {
					editor = CKEDITOR.replace(elem);
				}
				
				requestAnimationFrame(hackCheck);
			};
			requestAnimationFrame(hackCheck);
			
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
			try {
				var contentsCss = JSON.parse(contentsCss.replace(/'/g, '"'))
				var jqElemIFrameHead = jqElem.contents().find("head:first");
				for (var i in contentsCss) {
					jqElemIFrameHead.append($("<link />", { href: contentsCss[i], rel: "stylesheet", media: "screen"}));
				}
			} catch (e) { }
			
		}

		if (bodyId != null) {
			jqElemIFrameBody.attr("id", bodyId);
		}

		if (bodyClass != null) {
			jqElemIFrameBody.addClass(bodyClass);
		}
	};
})();