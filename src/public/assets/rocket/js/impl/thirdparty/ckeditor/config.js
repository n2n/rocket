/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

$( document ).ready(function() {
	var configOptions = $(".rocket-impl-cke-classic").data("rocket-impl-toolbar")

	CKEDITOR.editorConfig = function( config ) {
		config.plugins = 'dialogui,dialog,about,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,notification,button,toolbar,clipboard,panelbutton,panel,floatpanel,colorbutton,colordialog,templates,menu,contextmenu,copyformatting,div,resize,elementspath,enterkey,entities,popup,filebrowser,find,fakeobjects,flash,floatingspace,listblock,richcombo,font,forms,format,horizontalrule,htmlwriter,iframe,wysiwygarea,image,indent,indentblock,indentlist,smiley,justify,menubutton,language,link,list,liststyle,magicline,maximize,newpage,pagebreak,pastetext,pastefromword,preview,print,removeformat,save,selectall,showblocks,showborders,sourcearea,specialchar,scayt,stylescombo,tab,table,tabletools,tableselection,undo,wsc';
		config.skin = 'moono-lisa';

		config.bodyId = configOptions["bodyId"];
		config.bodyClass = configOptions["bodyClass"];
		config.tablesEnabled = configOptions["tablesEnabled"];

		if (configOptions["contentsCss"] !== null) {
			configOptions["contentsCss"].push(config.contentsCss);
			config.contentsCss = configOptions["contentsCss"];
			config.extraPlugins = "stylesheetparser";
		}

		if (configOptions["mode"] === "simple") {
			config.toolbar = [{ name: "basicstyles", items : ["Bold", "Italic", "Underline", "Strike", "RemoveFormat" ]}]
		}

		if (configOptions["mode"] === "normal") {
			config.toolbar = [{ name: "basicstyles", items : [ "Bold", "Italic", "Underline", "Strike", "RemoveFormat" ]},
				{ name: "clipboard", items : [ "Cut", "Copy", "Paste", "PateText", "PasteFromWord", "Undo", "Redo" ] },
				{ name: "editing", items: [ ]},
				{ name: "basicstyles", items : [ "Subscript", "Superscript" ]},
				{ name: "paragraph", items : [ "NumberedList", "BulletedList", "Outdent", "Indent", "Blockquote", "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock" ] },
				{ name: "links", items: [ "Link", "Unlink", "Anchor"]},
				{ name: "insert", items: [ "HorizontalRule", "SpecialChar"]},
				{ name: "styles", items: [ "Styles", "Format"]},
				{ name: "tools", items: [ "Maximize" ]},
				{ name: "about", items: [ "About" ]}]
		}

		if (configOptions["mode"] !== "advanced" && configOptions["tableEditing"]) {
			config.toolbar.push({ name: "table", items: ["Table"]});
		}

		if (configOptions["bbcode"]) {
			config.extraPlugins = "bbcode";
		}
	};
});