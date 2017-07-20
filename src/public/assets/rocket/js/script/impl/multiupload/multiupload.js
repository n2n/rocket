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
jQuery(document).ready(function($){
	(function() {
		var jqElemMultiUploadForm = $("#rocket-multi-upload-form");
		var jqElemMultiUploadSubmit = $("#rocket-multi-upload-submit");
		if (jqElemMultiUploadForm.length === 0 || jqElemMultiUploadSubmit.length === 0) return;
		
		var MultiUpload = function(jqElemForm, jqElemASubmit) {
			this.jqElemFileList = null;
			this.jqElemDropZone =  null;
			this.jqElemAUpload = null;
			this.fileData = new Object();
			this.i = 0;
			
			(function(_obj) {
				this.jqElemFileList = jqElemForm.children("ul:first");
				this.jqElemDropZone =  jqElemForm.children("#rocket-multi-upload-drop");
				this.jqElemAUpload = this.jqElemDropZone.children("a").click(function() {
					// Simulate a click on the file input button
					//to show the file browser dialog
					_obj.jqElemDropZone.find('input').click();
				});
				jqElemForm.fileupload({
			        // This element will accept file drag/drop uploading
			        dropZone: _obj.jqElemDropZone,
			        drop: function(e, data) {
			        	data.files.sort(function(a, b) {
			        		return (a.name < b.name) ? -1 : 1;
			        	});
			        },
			        // This function is called when a file is added to the queue;
			        // either via the browse button, or via drag/drop:
			        add: function (e, data) {
			        	var jqElemText = $("<p/>"),
			        			jqElemAction = $("<span/>"),
			        			jqElemInput = $("<input/>", {
			        				"type": "text", 
			        				"value": 0, 
			        				"data-width": 30, 
			        				"data-height": 48,
			        				"data-fgColor": "#ED8207",
			        				"data-readOnly": "1",
			        				"data-bgColor": "#3e4043"
			        			}),
			        			jqElemLi = $("<li/>", {"class": "working"})
				        			.append(jqElemInput).append(jqElemText).append(jqElemAction);
			                	file = data.files[0];
			            data.context = jqElemLi.appendTo(_obj.jqElemFileList);
			            jqElemText.text(file.name);
			            if (file.type.split("/").shift() !== "image") {
			            	data.context.addClass("error");
			            	jqElemText.append($("<i/>", {"text": "Es sind nur Bilder für den Upload erlaubt"}))
			            	setTimeout(function() {
			            		jqElemAction.click();
			            	}, 1000);
			            } else {
			            	jqElemText.append($("<i/>", {"text": _obj.formatFileSize(file.size)}))
			            	  _obj.fileData[++_obj.i] = data;
			            	jqElemLi.data('key', _obj.i)
			            }
			            // Initialize the knob plugin
			            jqElemInput.knob();
			            // Listen for clicks on the cancel icon
			            jqElemAction.click(function(){
//			                if(tpl.hasClass('working')){
//			                    jqXHR.abort();
//			                }
			            	_obj.removeElem(jqElemLi);
			            });
			         
			        },
			        progress: function(e, data){
			            // Calculate the completion percentage of the upload
			            var progress = parseInt(data.loaded / data.total * 100, 10);

			            // Update the hidden input field and trigger a change
			            // so that the jQuery knob plugin knows to update the dial
			            data.context.find('input').val(progress).change();

			            if (progress == 100){
			                data.context.removeClass('working');
			                delete _obj.fileData[data.context.data('key')];
			                setTimeout(function() {
			                	_obj.removeElem(data.context);
			                }, 2000);
			            }
			        },
			        fail: function(e, data){
			            // Something has gone wrong!
			            data.context.addClass('error');
			            delete _obj.fileData[data.context.data('key')];
			            setTimeout(function() {
			        		_obj.removeElem(data.context);
			        	}, 2000);
			        }
			    });
				jqElemASubmit.click(function(e) {
					e.preventDefault();
					for (var i in _obj.fileData) {
						_obj.fileData[i].submit();
					}
				});
			}).call(this, this);
			
		};
		MultiUpload.prototype.formatFileSize = function(bytes) {
	        if (typeof bytes !== 'number') {
	            return '';
	        }

	        if (bytes >= 1000000000) {
	            return (bytes / 1000000000).toFixed(2) + ' GB';
	        }

	        if (bytes >= 1000000) {
	            return (bytes / 1000000).toFixed(2) + ' MB';
	        }

	        return (bytes / 1000).toFixed(2) + ' KB';
	    }
		
		MultiUpload.prototype.removeElem = function(jqElem) {
			jqElem.fadeOut(function(){
				jqElem.remove();
            });
		};

		new MultiUpload(jqElemMultiUploadForm, jqElemMultiUploadSubmit);
		
		
		// Prevent the default action when a file is dropped on the window
	    $(document).on('drop dragover', function (e) {
	        e.preventDefault();
	    });
	})();
});
