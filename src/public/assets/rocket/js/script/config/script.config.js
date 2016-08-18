jQuery(document).ready(function($) {
	(function() {
		$(".rocket-impl-take-locale-from-config").each(function() {
			var jqElem = $(this),
			jqElemCustomOptions = jqElem.next(), 
			jqElemCbxTakeFromConfig = jqElem.find("input:first"); 
			jqElemCbxTakeFromConfig.change(function() {
				if ($(this).is(":checked")) {
					jqElemCustomOptions.hide();
				} else {
					jqElemCustomOptions.show();
				}
			}).change();
		});
	})();
	(function() {
		//////////////////////
		//General
		//////////////////////
		//caret function from http://www.examplet.buss.hk/jquery/caret.php
		(function($,len,createRange,duplicate){
			$.fn.caret=function(options,opt2){
				var start,end,t=this[0],browser=false;
				if(typeof options==="object" && typeof options.start==="number" && typeof options.end==="number") {
					start=options.start;
					end=options.end;
				} else if(typeof options==="number" && typeof opt2==="number"){
					start=options;
					end=opt2;
				} else if(typeof options==="string"){
					if((start=t.value.indexOf(options))>-1) end=start+options[len];
					else start=null;
				} else if(Object.prototype.toString.call(options)==="[object RegExp]"){
					var re=options.exec(t.value);
					if(re != null) {
						start=re.index;
						end=start+re[0][len];
					}
				}
				if(typeof start!="undefined"){
					if(this[0].selectionStart == null){
						var selRange = this[0].createTextRange();
						selRange.collapse(true);
						selRange.moveStart('character', start);
						selRange.moveEnd('character', end-start);
						selRange.select();
					} else {
						this[0].selectionStart=start;
						this[0].selectionEnd=end;
					}
					this[0].focus();
					return this;
				} else {
					// Modification as suggested by Андрей Юткин
		           if(t.selectionStart == null){
		                if (this[0].tagName.toLowerCase() != "textarea") {
		                    var val = this.val(),
							selection=document.selection,
		                    range = selection[createRange]()[duplicate]();
		                    range.moveEnd("character", val[len]);
		                    var s = (range.text == "" ? val[len]:val.lastIndexOf(range.text));
		                    range = selection[createRange]()[duplicate]();
		                    range.moveStart("character", -val[len]);
		                    var e = range.text[len];
		                } else {
		                    var range = selection[createRange](),
		                    stored_range = range[duplicate]();
		                    stored_range.moveToElementText(this[0]);
		                    stored_range.setEndPsoint('EndToEnd', range);
		                    var s = stored_range.text[len] - range.text[len],
		                    e = s + range.text[len];
		                }
					// End of Modification
		            } else {
						var s=t.selectionStart,
							e=t.selectionEnd;
					}
					var te=t.value.substring(s,e);
					return {start:s,end:e,text:te,replace:function(st){
						return t.value.substring(0,s)+st+t.value.substring(e,t.value[len]);
					}};
				};
			};
		})(jQuery,"length","createRange","duplicate");
		
		var KnownStringPatternEditor = function(jqElemInput, placeholders) {
			this.jqElemInput = jqElemInput;
			this.placeholders = placeholders;
			this.activeInputField = null;
			this.jqElemUl = $('<ul class="rocket-fake-input">');
			this.jqElemInput.after(this.jqElemUl);
			this.firstInputField = null;
			this.lastInputField = null;
			this.elements = new Array();
			this.initialize();
			this.removedPlaceholder = false;
		};
		
		KnownStringPatternEditor.prototype.initialize = function () {
			var valuePart = this.jqElemInput.val();
			var nextPlaceholderName = this.getNextMatchingPlaceholderName(valuePart);
			var currentPlaceholder = null;
			while (nextPlaceholderName) {
				valueIndex = valuePart.indexOf(nextPlaceholderName);
				var stringInput = new KnownStringInput(valuePart.substr(0, valueIndex), this);
				if (currentPlaceholder == null) {
					this.firstInputField = stringInput;
				} else {
					stringInput.setPreviousPlaceholder(currentPlaceholder);
				}
				valuePart = valuePart.substr(valueIndex);
				this.pushElement(stringInput);
				if (currentPlaceholder) {
					currentPlaceholder.setNextStringInput(stringInput);
				}
				currentPlaceholder = new KnownStringPlaceholder(nextPlaceholderName, this.placeholders[nextPlaceholderName], this);
				currentPlaceholder.setPreviousStringInput(stringInput);
				stringInput.setNextPlaceholder(currentPlaceholder);
				this.pushElement(currentPlaceholder);
				valuePart = valuePart.substr(nextPlaceholderName.length);
				nextPlaceholderName = this.getNextMatchingPlaceholderName(valuePart);
			}
			
			//last Element
			this.lastInputField = new KnownStringInput(valuePart, this);
			
			this.pushElement(this.lastInputField);
			if (currentPlaceholder) {
				currentPlaceholder.setNextStringInput(this.lastInputField);
				this.lastInputField.setPreviousPlaceholder(currentPlaceholder)
			} else {
				this.firstInputField = this.lastInputField;
			}
			
			var _obj = this
			this.jqElemUl.click(function(event) {
				if ($(event.target).is(_obj.jqElemUl)) {
					//if the current position is up to 5px right of the left position, focus the first input
					if (event.pageX < (_obj.jqElemUl.offset().left + 5)) {
						_obj.firstInputField.focus();
						_obj.firstInputField.movePointerTo(0);
					} else {
						_obj.lastInputField.focus();
						_obj.lastInputField.movePointerToEnd();
					}
				}
			}).hover(
				function () {
					$(this).css("cursor", "text");
				},function () {
					$(this).css("cursor", "default");
				}
			);
			
			//label click 
			var jqElemLabel = $("[for='" + this.jqElemInput.attr("id")  + "']");
			if (jqElemLabel.length > 0) {
				jqElemLabel.click(function() {
					//defaultbehaviour label click
					_obj.firstInputField.focus();
					_obj.firstInputField.movePointerTo(0);
					return false;
				});
			}
		};
		
		KnownStringPatternEditor.prototype.pushElement = function(element) {
			this.jqElemUl.append(element.getJqListItem());
			if (element.setInputWidth) {
				element.setInputWidth();
			}
		};
		
		KnownStringPatternEditor.prototype.getNextMatchingPlaceholderName = function (value) {
			var placeholderName = null;
			var valueIndex = -1;
			for (var i in this.placeholders) {
				var tempValueIndex = value.indexOf(i);
				if ((tempValueIndex >= 0)) {
					if (valueIndex < 0 || (tempValueIndex < valueIndex)) {
						valueIndex = tempValueIndex;
						placeholderName = i;
					}
				} 
			}
			return placeholderName;
		};
		
		KnownStringPatternEditor.prototype.updateInputField = function() {
			var inputFieldValue = '';
			
			this.jqElemUl.find("li[data-type]").each(function() {
				var dataType = $(this).attr("data-type");
				if (dataType == "placeholder") {
					inputFieldValue += $(this).attr("data-value");
				} else if (dataType == "input") {
					inputFieldValue += $(this).find("input[type=text]").val();
				}
			});
			this.jqElemInput.val(inputFieldValue);
		}
		
		KnownStringPatternEditor.prototype.setActiveInputField = function(activeInputField) {
			this.activeInputField = activeInputField;
		}
		
		KnownStringPatternEditor.prototype.addPlaceHolderWithNameAtCurrentPosition = function(placeholderName) {
			var start, end, inputField;
			if (this.activeInputField != null) {
				start = this.activeInputField.getStart();
				end = this.activeInputField.getEnd();
				input = this.activeInputField;
			} else {
				input = this.lastInputField;
				start = input.getValue().length;
				end = start;
			}
			var value = input.getValue();
			input.setValue(value.substr(0, start));
			
			var jqElemLi = input.getJqListItem();
			var newPlaceholder = new KnownStringPlaceholder(placeholderName, this.placeholders[placeholderName], this);
			var newInput = new KnownStringInput(value.substr(end), this);
			
			if (input == this.lastInputField) {
				this.lastInputField = newInput;
			}
			
			newPlaceholder.setPreviousStringInput(input);
			newPlaceholder.setNextStringInput(newInput);
			newInput.setNextPlaceholder(input.getNextPlaceholder());
			newInput.setPreviousPlaceholder(newPlaceholder);
			if (input.getNextPlaceholder()) {
				input.getNextPlaceholder().setPreviousStringInput(newInput);
			}
			input.setNextPlaceholder(newPlaceholder);
			
			input.getJqListItem().after(newPlaceholder.getJqListItem());
			newPlaceholder.getJqListItem().after(newInput.getJqListItem());
			newInput.setInputWidth();
			//focus new Field and set active
			newInput.focus();
			this.updateInputField();
		}
		
		KnownStringPatternEditor.prototype.setLastInputField = function(lastInputField) {
			this.lastInputField = lastInputField;
		}
		
		var KnownStringPlaceholder = function(name, title, editor) {
			this.editor = editor;
			this.displayClass = "rocket-knownStringPattern-placeholder";
			this.previousStringInput = null;
			this.nextStringInput = null;
			this.jqElemInput = $("<button>").append($("<i/>", {"text": '', "class": "fa fa-times"}));
			this.jqElemLi = $('<li data-type="placeholder" data-value="' + name + '" class="' + this.displayClass + '">'
					+ '<span>'+ title + '</span></li>' );
			this.jqElemLi.append(this.jqElemInput);
			this.initialize();
		};
		
		KnownStringPlaceholder.prototype.initialize = function() {
			var _obj = this;
			this.jqElemInput.click(function() {
				_obj.remove();
			});
		}
		
		KnownStringPlaceholder.prototype.getJqListItem = function() {
			return this.jqElemLi;
		};
		
		KnownStringPlaceholder.prototype.getPreviousStringInput = function() {
			return this.previousStringInput;
		}
		
		KnownStringPlaceholder.prototype.setPreviousStringInput = function(knownStringInput) {
			this.previousStringInput = knownStringInput;
		}
		
		KnownStringPlaceholder.prototype.getNextStringInput = function() {
			return this.nextStringInput;
		}
		
		KnownStringPlaceholder.prototype.setNextStringInput = function(knownStringInput) {
			this.nextStringInput = knownStringInput;
		}
		
		KnownStringPlaceholder.prototype.toString = function() {
			return this.jqElemLi.attr("data-value");
		}
		
		KnownStringPlaceholder.prototype.focus = function() {
			this.jqElemInput.focus();
		}
		
		KnownStringPlaceholder.prototype.remove = function() {
			var previousInputLength = this.getPreviousStringInput().getValue().length;
			this.getPreviousStringInput().setValue(
					this.getPreviousStringInput().getValue() +
					this.getNextStringInput().getValue());
			
			if (this.getNextStringInput().getNextPlaceholder()) {
				this.getPreviousStringInput().setNextPlaceholder(this.getNextStringInput().getNextPlaceholder());
				this.getNextStringInput().getNextPlaceholder().setPreviousStringInput(this.getPreviousStringInput());
			} else {
				this.getPreviousStringInput().setNextPlaceholder(null);
				this.editor.setLastInputField(this.getPreviousStringInput());
			}
			this.getNextStringInput().getJqListItem().remove();
			this.jqElemLi.remove();
			this.editor.setActiveInputField(this.getPreviousStringInput());
			this.getPreviousStringInput().focus();
			this.getPreviousStringInput().movePointerTo(previousInputLength);
			this.editor.updateInputField();
			delete this.getNextStringInput();
			delete this;
		}
		
		var KnownStringInput = function(value, editor) {
			this.editor = editor
			this.displayClass = "rocket-knownStringPattern-input";
			this.jqElemInput = $('<input type="text" value="' + value + '"/>').css("padding", "0");
			this.jqElemLi = $('<li data-type="input" class="' + this.displayClass + '">')
			this.jqElemLi.append(this.jqElemInput);
			this.start = 0;
			this.end = 0;
			this.initialize();
			this.nextPlaceholder = null;
		};
		
		KnownStringInput.prototype.initialize = function() {
			var _obj = this;
			this.jqElemInput.mousemove(function(event){
				_obj.start = $(this).caret().start;
				_obj.end = $(this).caret().end;
			}).keyup(function(event){
				_obj.start = $(this).caret().start;
				_obj.end = $(this).caret().end;
				_obj.editor.updateInputField();
			}).keypress(function(event) {
				var keyCode = (window.event) ? event.keyCode : event.which;
				if (keyCode) {
					_obj.setInputWidth(keyCode);
				}
				
			}).keydown(function(event) {
				var keyCode = (window.event) ? event.keyCode : event.which;
				switch (keyCode) {
					case (36):
						//Home
						var input = _obj.editor.firstInputField;
						input.focus();
						input.movePointerTo(0);
						return false;
					case 35:
						//End
						var input = _obj.editor.lastInputField;
						input.focus();
						input.movePointerToEnd();
						return false;
				}
				if ($(this).caret().start == 0) {
					switch (keyCode) {
						//Arrow left
						case (37):
							if (_obj.getPreviousStringInput()) {
								var previousStringInput = _obj.getPreviousStringInput();
								previousStringInput.focus();
								previousStringInput.movePointerToEnd();
								return false;
							}
							break;
						//Backspace
						case (8):
							if (_obj.getPreviousPlaceholder()) {
								_obj.getPreviousPlaceholder().remove();
								_obj.editor.removedPlaceholder = true;
							}
							return false;
							break;
					}
					
				} 
				if ($(this).caret().end == _obj.getValue().length) {
					switch (keyCode) {
						//arrow right
						case (39):
							if (_obj.getNextStringInput()) {
								var nextStringInput = _obj.getNextStringInput();
								nextStringInput.focus();
								nextStringInput.movePointerTo(0);
								return false;
							}
							break;
						//delete
						case (46):
							if (_obj.getNextPlaceholder()) {
								_obj.getNextPlaceholder().remove();
								this.editor.removedPlaceholder = true;
							}
							_obj.setInputWidth(keyCode);
							return false;
							break;
					}
				}
				
				//delete doesn't set the keyCode event in keypress
				if ((window.event) && (event.keyCode == 8) || keyCode == 46) {
					_obj.setInputWidth(keyCode);
				}
				
			}).focus(function(){
				_obj.editor.setActiveInputField(_obj);
				_obj.start = $(this).caret().start;
				_obj.end = $(this).caret().end;
			});
		};
		
		KnownStringInput.prototype.getPreviousStringInput = function () {
			if (this.getPreviousPlaceholder()) {
				//A Placholder always has a previous and next StringInput
				return this.getPreviousPlaceholder().getPreviousStringInput();
			}
			return null;
		};
		
		KnownStringInput.prototype.getNextStringInput = function () {
			if (this.getNextPlaceholder()) {
				//A Placholder always has a previous and next StringInput
				return this.getNextPlaceholder().getNextStringInput();
			}
			return null;
		};
		
		KnownStringInput.prototype.setInputWidth = function(keycode) {
			var value = this.jqElemInput.val();
			if (keycode == null) {
				nextChar = "";
			} else {
				switch(keycode) {
					case(8):
						//Backspace doesn't fire keypress event in ie
						if (!(this.editor.removedPlaceholder)) {
							value = value.substring(0, this.start-1) + value.substring(this.end);
							nextChar = "";
						} else {
							this.editor.removedPlaceholder = false;
						}
						break;
					//delete
					case(46):
						//delete doesn't fire keypress event in ie
						if (!(this.editor.removedPlaceholder)) {
							value = value.substring(0, this.start) + value.substring(this.end + 1);
							nextChar = "";
						} else {
							this.editor.removedPlaceholder = false;
						}
						break;
					case(32):
						//Space
						nextChar = " ";
						break;
					default:
						nextChar = String.fromCharCode(keycode);
						break;
				}
				
			}
			var jqElemSpan = $("<span/>").text(value + nextChar)
			.css("font-family", this.jqElemInput.css("font-family"))
			.css("display", "inline")
			.css("position", "absolute")
			.css("font-size", this.jqElemInput.css("font-size"))
			.css("white-space", "pre");
			$("body").append(jqElemSpan);
			var textWidth = jqElemSpan.outerWidth() + 2;
			jqElemSpan.remove();
			this.jqElemInput.width(textWidth);
		};
		
		KnownStringInput.prototype.getJqListItem = function() {
			return this.jqElemLi;
		};
		
		KnownStringInput.prototype.getValue = function() {
			return this.jqElemInput.val();
		};
		
		KnownStringInput.prototype.setValue = function(value) {
			this.jqElemInput.val(value);
			this.setInputWidth();
		};
		
		KnownStringInput.prototype.getStart = function() {
			return this.start;
		};
		
		KnownStringInput.prototype.getEnd = function() {
			return this.end;
		};
		
		KnownStringInput.prototype.focus = function() {
			this.jqElemInput.focus();
		};
		
		KnownStringInput.prototype.movePointerToEnd = function() {
			this.movePointerTo(this.getValue().length);
		}
		
		KnownStringInput.prototype.movePointerTo = function(position) {
			this.jqElemInput.caret({start: position, end: position});
			this.start = position;
			this.end = position;
		}
		
		KnownStringInput.prototype.getNextPlaceholder = function() {
			return this.nextPlaceholder;
		}
		
		KnownStringInput.prototype.setNextPlaceholder = function(knownPlaceHolder) {
			this.nextPlaceholder = knownPlaceHolder;
		}
		
		KnownStringInput.prototype.getPreviousPlaceholder = function() {
			return this.previousPlaceholder;
		}
		
		KnownStringInput.prototype.setPreviousPlaceholder = function(previousPlaceholder) {
			this.previousPlaceholder = previousPlaceholder;
		}
		
		KnownStringInput.prototype.toString = function() {
			return this.getValue();
		}
		
		//first the placeholders
		var jqElemDlPlaceholders = $("#rocket-edit-entity-script-available-placeholders");
		var jqElemDivPlaceholders = $("<ul class='rocket-placeholder-tag-list'>");
		var availablePlaceholders = new Object();
		jqElemDlPlaceholders.find('dt').each(function(){
			var name = $(this).text();
			var title = $(this).next("dd:first").text();
			availablePlaceholders[name] = title;
			var jqElemBtnPlaceholder = $("<li><input type='button' class='rocket-command-tag-btn' value='" + title +"' data-value='" + name +"'></li>")
			jqElemDivPlaceholders.append(jqElemBtnPlaceholder);
		});
		jqElemDlPlaceholders.after(jqElemDivPlaceholders);
		jqElemDlPlaceholders.hide();
		
		//Create the Editor
		var kspEditor = new KnownStringPatternEditor($("#rocket-form-knownStringPattern"), availablePlaceholders);
		$('#rocket-form-knownStringPattern').hide();
		jqElemDivPlaceholders.find("input[type=button]").click(function(){
			kspEditor.addPlaceHolderWithNameAtCurrentPosition($(this).attr("data-value"));
		});
	})();
});