CKEDITOR.config.removeButtons = 'Maximize'; // maximize causes cke height = 0. Needs to be fixed

CKEDITOR.on('instanceReady', function(e) {
	var cke = CKEDITOR.instances['content'];
	cke.on('change', function() {
		cke.updateElement();
		var element = cke.element.$;
		if ('createEvent' in document) {
			var event = document.createEvent('HTMLEvents');
			event.initEvent('change', false, true);
			element.dispatchEvent(event);
		} else {
			element.fireEvent('onchange');
		}
	});

	// CKEDITOR.on('dialogDefinition', function (e) {
	//     var dialog = e.data.definition.dialog;
	//     dialog.on('show', function () {
	//         iframeJq.css('height', this.getSize().height + 50);
	//     });
	//     dialog.on('hide', function () {
	//         iframeJq.css('height', initialHeight);
	//     });
	// });

	var focusHeightIncreasePx = 300;

	var initialEditorHeight;
	var initialEditorIframeHeight;
	e.editor.on('focus', function(event) {
		var editorJq = $(this.element.$.parentElement.parentElement);
		var editorIframeJq = $(this.element.$.parentElement).find('iframe');

		initialEditorHeight = editorJq.height();
		initialEditorIframeHeight = editorIframeJq.height();

		editorJq.css('height', (initialEditorHeight + focusHeightIncreasePx));
		editorIframeJq.height((initialEditorIframeHeight + focusHeightIncreasePx));
	});

	e.editor.on('blur', function(event) {
		$(this.element.$.parentElement.parentElement).css('height', initialEditorHeight);
		$(this.element.$.parentElement).find('iframe').height(initialEditorIframeHeight);
	});

});