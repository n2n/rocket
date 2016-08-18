/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.extraAllowedContent = 'dl dt dd';
	CKEDITOR.config.format_small = {element:"small"};
	CKEDITOR.config.format_cite = {element:"cite"};
};
