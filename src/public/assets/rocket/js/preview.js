jQuery(document).ready(function($) {
	var iframe = document.getElementById('rocket-preview-content');
	
	var currentIframeDocument = null;
	var submitting = false;
	
	onIframeReady(iframe, function(iframeDocument) {
		currentIframeDocument = iframeDocument;
		
		function prepareSubmit(jqCommandButton) {
			$("#" + jqCommandButton.attr("id"), currentIframeDocument).removeAttr("disabled");
			$("#rocket-preview-draft-name-input", currentIframeDocument).val($("#rocket-preview-draft-name-input").val());
			currentIframeDocument.rocketPreviewDisposed = true;
		}
		
		function handleSubmit(jqCommandButton) {
			onIframeReady(iframe, function(iframeDocument) {
				currentIframeDocument = iframeDocument;
				
				var messageListHtml = currentIframeDocument.getElementById("rocket-preview-inpage-form")
						.getAttribute("data-rocket-preview-message-list");
				
				if (messageListHtml) {
					document.getElementById("rocket-preview-messages").innerHTML = messageListHtml;
					submitting = false;
					return;
				}
				
				window.location = currentIframeDocument.getElementById("rocket-preview-inpage-form")
						.getAttribute("data-rocket-preview-redirect-url");
			});
		}
		
		var clickListener = function() {
			if (submitting) return;
			submitting = true;
	
			var jqCommandButton = $(this);
			prepareSubmit(jqCommandButton);
			
			$("#rocket-preview-inpage-form", currentIframeDocument).submit();
			
			handleSubmit(jqCommandButton);
		};

		$("#rocket-preview-save-command").click(clickListener);
		$("#rocket-preview-draft-name-input").val($("#rocket-preview-draft-name-input", currentIframeDocument).val());
		$("#rocket-preview-publish-command").click(function() {
			var jqButton = $(this);
			var dialog = new rocket.Dialog(jqButton.attr("data-rocket-confirm-msg"));
			dialog.addButton(jqButton.attr("data-rocket-confirm-ok-label"), $.proxy(function() {
				clickListener.apply(this);
			}, this));
			dialog.addButton(jqButton.attr("data-rocket-confirm-cancel-label"));
			
			rocket.core.stressWindow.open(dialog);
		});
		
		$("#rocket-preview-inpage-form", currentIframeDocument).submit(function() {
			if (submitting) return;
			submitting = true;
			
			var jqCommandButton = $("#rocket-preview-save-command");
			prepareSubmit(jqCommandButton);
			handleSubmit(jqCommandButton);
		});
	});
		
	function onIframeReady(iframe, callback) {
		var ready = false;
		
		var handle;
		var readyFunc = function() {
			var iframeDocument = iframe.contentWindow ? iframe.contentWindow.document : iframe.contentDocument;

			if (!iframeDocument.body || !iframeDocument.body.innerHTML || iframeDocument.rocketPreviewDisposed) return;
			clearInterval(handle);
			callback(iframeDocument); 
		};
		handle = setInterval(readyFunc, 13);
	}

});