<?php
namespace rocket\script\entity\field\impl\string\wysiwyg;

use n2n\ui\html\HtmlView;

interface WysiwygCssConfiguration {
	public function getContentCssPaths(HtmlView $view);
	public function getBodyId();
	public function getBodyClass();
	/**
	 * @return \rocket\script\entity\field\impl\string\wysiwyg\WysiwygStyle[]
	 */
	public function getAdditionalStyles();
	/**
	 * returns an array of the format tags possible tags are 
	 * ("p", "h1", "h2", "h3", "h4", "h5", "h6", "pre", "address")
	 * 
	 * @return array
	 */
	public function getFormatTags();
}