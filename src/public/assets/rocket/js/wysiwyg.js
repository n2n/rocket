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
var linkConfigurations = new Object();

jQuery(document).ready(function($) {
	
	(function() {
		var initDetail = function(jqElems) {
			jqElems.each(function() {
				var jqElem = $(this),
					jqElemContent = jqElem.prev(".rocket-wysiwyg-content:first"),
					jqElemIFrameBody = jqElem.contents().find("body:first"),
					bodyId = jqElem.data("body-id") || null,
					bodyClass = jqElem.data("body-class") || null,
					contentsCss = jqElem.data("contents-css") || null;
					
				if (null !== contentsCss) {
					contentsCss = $.parseJSON(contentsCss.replace(/'/g, '"'))
					var jqElemIFrameHead = jqElem.contents().find("head:first");
					for (var i in contentsCss) {
						$("<link />", {
							"href": contentsCss[i],
							"media": "screen",
							"rel": "stylesheet"
						}).appendTo(jqElemIFrameHead);
					}
				}
				
				if (bodyId != null) {
					jqElemIFrameBody.attr("id", bodyId);
				}
				
				if (bodyClass != null) {
					jqElemIFrameBody.addClass(bodyClass);
				}
				var html = jqElemContent.html();
				jqElem.load(function() {
					jqElem.contents().find("body:first").html(jqElemContent.html());
					var containerHeight = jqElem.contents().find("html:first").outerHeight(true, true);
					containerHeight = (containerHeight > 400) ? 400 : containerHeight;
					jqElem.height(containerHeight);
				});
			});
		};
		
		var initFunction = function() {
			$(".rocket-wysiwyg-detail").each(function() {
				var jqElem = $(this);
				if (jqElem.data("initialized.rocket-wysiwyg-detail")) return;
				
				jqElem.data("initialized.rocket-wysiwyg-detail", true);
				initDetail(jqElem);
			});
		}
		
		if (n2n != null) {
			n2n.dispatch.registerCallback(initFunction);
		}
		
		initFunction();
	})();
	
	var w = (function(){
		function initializeInpage(jqElemsInpageWysiwyg) {
			if (jqElemsInpageWysiwyg.length == 0) {
				return;
			}
			
			jqElemsInpageWysiwyg.each(function(){
				new Wysiwyg($(this), Wysiwyg.TYPE_INLINE);
			});
		};
		
		function initializeWysiwyg(jqElemsWysiwyg) {
			if (jqElemsWysiwyg.length == 0) {
				return;
			}
			
			jqElemsWysiwyg.each(function(){
				var jqElem = $(this);
				if (jqElem.data("initialized-wysiwyg")) return;
				jqElem.data("initialized-wysiwyg", true);
				if (jqElem.is(":visible")) {
					new Wysiwyg(jqElem, Wysiwyg.TYPE_NORMAL);
				} else {
					//hack to not get an error if elem is not visible on ckeditor.loaded event
					var maxTimes = 50,
						times = 0,
						checkFunction = function() {
							times++;
							if (times >= maxTimes) {
								jqElem.data("initialized-wysiwyg", false);
								return;
							};
							if (jqElem.is(":visible")) {
								new Wysiwyg(jqElem, Wysiwyg.TYPE_NORMAL);
								return;
							}
							setTimeout(checkFunction, 0);
					}
					checkFunction();
				}
			});
		}
		
		var Wysiwyg = function(jqElem, type) {
			this.jqElem = jqElem;
			this.type = type || Wysiwyg.TYPE_NORMAL;
			this.simpleToolbar = null;
			this.normalToolbar = null;
			this.advancedToolbar = null;
			this.bbCodePossibleToolbarItems = null;
			
			this.linkConfigurations = jqElem.data("link-configurations") || null;
			this.initialize();
			this.initializeUI();
		}
		
		Wysiwyg.ckHack = function(jqElem, callBack) {
			jqElem.find(".rocket-wysiwyg").each(function() {
				var jqWysiwyg = $(this);
				var jqElemCke = jqWysiwyg.next(".cke");
				if (jqElemCke.length > 0 && CKEDITOR.instances[jqElemCke.attr("id").replace("cke_", "")] != null) {
					var editorName = jqElemCke.attr("id").replace("cke_", "");
					try {
						//sometimes the destroy method fires an exception
						CKEDITOR.instances[editorName].destroy();
					} catch(e) {
						jqWysiwyg.next(".cke").remove();
						CKEDITOR.remove(CKEDITOR.instances[editorName]);
					}
					new Wysiwyg(jqWysiwyg);
					jqWysiwyg.data("initialized-wysiwyg", true);
				}
			});
			callBack.call();
		};
		
		Wysiwyg.TYPE_INLINE = 'inline';
		Wysiwyg.TYPE_NORMAL = 'normal';

		Wysiwyg.prototype.initialize = function() {
			//see http://ckeditor.com/forums/CKEditor/Complete-list-of-toolbar-items
			this.simpleToolbar =  [
			        { name: "basicstyles", items : [ "Bold", "Italic", "Underline", "Strike", "RemoveFormat" ]}
			];
			
			this.normalToolbar = [
			        { name: 'document', items: [ ]},
			        { name: "basicstyles", items : [ "Bold", "Italic", "Underline", "Strike", "RemoveFormat" ]},
		            { name: "clipboard", items : [ "Cut", "Copy", "Paste", "PateText", "PasteFromWord", "Undo", "Redo" ] },
		            { name: "editing", items: [ ]},
		            { name: "basicstyles", items : [ "Subscript", "Superscript" ]},
		            { name: "paragraph", items : [ "NumberedList", "BulletedList", "Outdent", "Indent", "Blockquote", "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock" ] },
		            { name: "links", items: [ "Link", "Unlink", "Anchor"]},
		            { name: "insert", items: [ "HorizontalRule", "SpecialChar"]},
		            { name: "styles", items: [ "Styles", "Format"]},
		            { name: "tools", items: [ "Maximize" ]},
		            { name: "about", items: [ "About" ]}
		    ];
			
			//unset formats
			if (!this.jqElem.data("format-tags")) {
				delete this.normalToolbar[8].items[1];
			}

		    var tmpAdvancedToolbar = $.extend(true, [], this.normalToolbar);
			this.advancedToolbar = new Array();
			for (var i in tmpAdvancedToolbar) {
				var toolbarItem = tmpAdvancedToolbar[i];
				this.advancedToolbar.push(toolbarItem)
				switch (toolbarItem.name) {
					case "document":
						toolbarItem.items.push("Source");
						break;
					case "paragraph":
						toolbarItem.items.push("CreateDiv");
						break;
					case "insert":
						toolbarItem.items.push("PageBreak");
						toolbarItem.items.push("Iframe");
						break;
					case "editing":
						toolbarItem.items.push("Find");
						toolbarItem.items.push("Replace");
						toolbarItem.items.push("SelectAll");
						break;
					case "styles":
						toolbarItem.items.push("FontSize");
						this.advancedToolbar.push({ name: "colors", items: [ "TextColor", "BGColor"]});
						break;
					case "tools":
						toolbarItem.items.push("ShowBlocks");
						break;
				}
			}
			
			this.bbCodePossibleToolbarItems =  {
			       document: ['Source'],
			       clipboard: [ "Cut", "Copy", "Paste", "PateText", "PasteFromWord", "Undo", "Redo" ],
			       editing: [ "Find", "Replace", "SelectAll" ],
			       basicstyles: [ "Bold", "Italic", "Underline", "RemoveFormat" ],
			       paragraph: ["NumberedList", "BulletedList", "Blockquote"],
			       links: ["Link", "Unlink"],
			       insert: [ "Image", "SpecialChar"],
			       styles: ["FontSize"],
			       colors: [ "TextColor" ],
			       tools: [ "Maximize", "ShowBlocks"]
			};
			this.defaultStylesSet = [{ name: 'Lead', element: 'p', attributes: { 'class' : 'lead'}}];
		};
		
		Wysiwyg.prototype.initializeUI = function() {
			var _obj = this;
			var ckEditor = null;
			switch (this.type) {
				case Wysiwyg.TYPE_INLINE:
					var jqElemDiv = $("<div/>").append(this.jqElem.text()).attr("contenteditable", "true")
						.addClass(this.jqElem.attr('class'));
					jqElemDiv.blur(function() {
						_obj.jqElem.html($(this).html());
					});
					this.jqElem.after(jqElemDiv);
					ckEditor = CKEDITOR.inline(jqElemDiv.get(0), this.getOptions());
					_obj.jqElem.hide();
					break;
				case Wysiwyg.TYPE_NORMAL:
					ckEditor = CKEDITOR.replace(this.jqElem.get(0), this.getOptions());
					break;
			}
			if (null !== ckEditor) {
				if (this.hasLinkConfigurations()) {
					linkConfigurations[ckEditor.id] = this.linkConfigurations;
				}
			}
		};
		
		Wysiwyg.prototype.hasLinkConfigurations = function() {
			return (null !== this.linkConfigurations);
		};
		
		Wysiwyg.prototype.getToolbar = function(mode, tableEditing, bbcode) {
			//if (mode == null) return normalToolbar;
			var toolbar = null;
			
			switch (mode) {
				case "simple":
					toolbar = this.simpleToolbar;
					break;
				case "normal":
					toolbar = this.normalToolbar;
					break;
				case "advanced":
					toolbar = this.advancedToolbar;
					break;
				default:
					toolbar = this.normalToolbar;
			}
			//bbcodify options
			if (bbcode) {
				var newToolbar = new Array();
				for (var i in toolbar) {
					var toolbarItem = toolbar[i];
					newToolbarItems = new Array();
					for (var j in toolbarItem.items) {
						var found = false;
						for (var k in this.bbCodePossibleToolbarItems[toolbarItem.name]) {
							if (this.bbCodePossibleToolbarItems[toolbarItem.name][k] == toolbarItem.items[j]) {
								found = true;
								break;
							}
						}
						if (found) {
							newToolbarItems.push(toolbarItem.items[j]);
						}
					}
					if (newToolbarItems.length > 0) {
						newToolbar.push({
							name: toolbarItem.name,
							items: newToolbarItems
						})
					}
				}
				return newToolbar;
			} else if (tableEditing) {
				//table Toolbar extends normalToolbar with Insert Table
				for (var i in toolbar) {
					var toolbarItem = toolbar[i];
					if (toolbarItem.name == "insert") {
						toolbarItem.items.push("Table");
						return toolbar;
					}
				}
				toolbar.push({
					name: "insert",
					items: ["Table"]
				});
			}
			return toolbar;
		}

		Wysiwyg.prototype.getOptions = function() {
			var toolbar = this.jqElem.data("toolbar") || 'normal',
					bbcode = this.jqElem.data("bbcode") || false,
					tableEditing = this.jqElem.data("table-editing") || false
					additionalStyles = this.jqElem.data("additional-styles") || null,
					formatTags = this.jqElem.data("format-tags") || null,
					contentsCss = null,
					contentsCssUnFormatted = this.jqElem.data("contents-css") || null,
					bodyId = this.jqElem.data("body-id") || null,
					bodyClass = this.jqElem.data("body-class") || null,
					options = new Object();
			if (contentsCssUnFormatted) {
				contentsCss = $.parseJSON(contentsCssUnFormatted.replace(/'/g, '"'));
			}
			options.toolbar = this.getToolbar(toolbar, tableEditing, bbcode);
			options.extraPlugins = '';
			if (bbcode) {
				options.extraPlugins = 'bbcode';
			}
			
			if (contentsCss) {
				options.contentsCss = contentsCss;
			}
			
			if (bodyClass) {
				options.bodyClass = bodyClass;
			}
			
			if (bodyId) {
				options.bodyId = bodyId;
			}
			
			if (formatTags) {
				options.format_tags = formatTags;
			}
			
			if (this.type = Wysiwyg.TYPE_NORMAL) {
				if (options.extraPlugins.length > 0) {
					options.extraPlugins += ',';
				}
				options.extraPlugins += 'autogrow';
				options.removePlugins = 'resize';
				options.autoGrow_maxHeight = $(window).outerHeight() - 250;
				if (options.autoGrow_maxHeight > 700) {
					options.autoGrow_maxHeight = 700;
				}
			}
			
			var stylesSet = this.defaultStylesSet;
			if (null != additionalStyles) {
				stylesSet = $.extend([], stylesSet, additionalStyles);
			}
			options.stylesSet = stylesSet;
			return options;
		}
		
		window.Wysiwyg = Wysiwyg;
		//register Initializer
		var initFunction = function() {
			$(".rocket-preview-inpage-wysiwyg").each(function() {
				var jqElem = $(this);
				if (jqElem.data("initialized.rocket-preview-inpage-wysiwyg")) return;
				
				jqElem.data("initialized.rocket-preview-inpage-wysiwyg", true);
				initializeInpage(jqElem);
			});

			$(".rocket-wysiwyg").each(function() {
				var jqElem = $(this);
				if (jqElem.data("initialized.rocket-wysiwyg")) return;
				
				jqElem.data("initialized.rocket-wysiwyg", true);
				Wysiwyg.ckHack(jqElem, function() {
					initializeWysiwyg(jqElem);
				});
			});
		}

		if (n2n != null) {
			n2n.dispatch.registerCallback(initFunction);
		}
		
		initFunction();
	});
	
	var check = function() {
		if (typeof CKEDITOR === 'undefined') {
			setTimeout(function() {
				check();
			}, 5);
		} else {
			w();
		}
	};
	
	check();
	
});
