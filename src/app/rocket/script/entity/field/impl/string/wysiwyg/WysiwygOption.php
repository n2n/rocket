<?php
namespace rocket\script\entity\field\impl\string\wysiwyg;

use n2n\ui\html\HtmlView;
use n2n\dispatch\PropertyPath;
use n2n\dispatch\option\impl\StringOption;

class WysiwygOption extends StringOption {
	
	private $inputAttrs;
	
	private $mode;
	private $bbcode;
	private $tableEditing;
	/**
	 * @var array
	 */
	private $linkConfigurations;
	
	/**
	* @var \rocket\script\entity\field\impl\string\wysiwyg\WysiwygCssConfiguration
	*/
	private $cssConfiguration;
	
	public function __construct($label, $default = null, $required = false, $maxlength = null, array $inputAttrs = null, 
			$mode = self::MODE_NORMAL, $bbcode = false, $tableEditing = false, array $linkConfigurations = null, WysiwygCssConfiguration $cssConfiguration = null) {
		parent::__construct($label, $default, $required, $maxlength, true, $inputAttrs);
		
		$this->inputAttrs = $inputAttrs;
		$this->mode = $mode;
		$this->bbcode = $bbcode;
		$this->tableEditing = $tableEditing;
		$this->linkConfigurations = $linkConfigurations;
		$this->cssConfiguration = $cssConfiguration;
	}
	
	public function isMultiline() {
		return true;
	}
	
	public function createUiField(PropertyPath $propertyPath, HtmlView $htmlView) {
		$wysiwygHtml = new WysiwygHtmlBuilder($htmlView);
		return $wysiwygHtml->getWysiwygEditor($propertyPath, $this->mode, $this->bbcode, false, $this->tableEditing, $this->linkConfigurations, $this->cssConfiguration, $this->inputAttrs);
	}
}