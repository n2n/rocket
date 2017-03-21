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
 * 
 */
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
