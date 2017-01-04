<?php
namespace rocket\spec\ei\component\field\impl\string\cke\model;


use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\UiComponent;
use rocket\spec\ei\component\field\impl\string\cke\ui\CkeHtmlBuilder;
use rocket\spec\ei\component\field\impl\string\cke\ui\Cke;
use n2n\impl\web\dispatch\mag\model\StringMag;

class CkeMag extends StringMag {
	
	private $inputAttrs;
	
	private $mode;
	private $bbcode;
	private $tableEditing;
	/**
	 * @var array
	 */
	private $linkProviders;
	
	/**
	* @var \rocket\spec\ei\component\field\impl\string\wysiwyg\WysiwygCssConfig
	*/
	private $cssConfiguration;
	
	public function __construct($propertyName, $label, $default = null, $mandatory = false, $maxlength = null, array $inputAttrs = null, 
			$mode = self::MODE_NORMAL, $bbcode = false, $tableEditing = false, array $linkProviders, CkeCssConfig $cssConfiguration = null) {
		parent::__construct($propertyName, $label, $default, $mandatory, $maxlength, true, $inputAttrs);
		
		$this->inputAttrs = $inputAttrs;
		$this->mode = $mode;
		$this->bbcode = $bbcode;
		$this->tableEditing = $tableEditing;
		$this->linkProviders = $linkProviders;
		$this->cssConfiguration = $cssConfiguration;
	}
	
	public function isMultiline() {
		return true;
	}
	
	public function createUiField(PropertyPath $propertyPath, HtmlView $htmlView): UiComponent {
		$ckeHtml = new CkeHtmlBuilder($htmlView);
		return $ckeHtml->getEditor($propertyPath, $this->mode, $this->bbcode, false, $this->tableEditing, $this->linkProviders, $this->cssConfiguration, $this->inputAttrs);
	}
}
