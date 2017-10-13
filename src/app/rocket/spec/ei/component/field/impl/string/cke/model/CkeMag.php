<?php
namespace rocket\spec\ei\component\field\impl\string\cke\model;

use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\UiComponent;
use rocket\spec\ei\component\field\impl\string\cke\ui\CkeHtmlBuilder;
use rocket\spec\ei\component\field\impl\string\cke\ui\Cke;
use n2n\impl\web\dispatch\mag\model\StringMag;

class CkeMag extends StringMag {
	private $mode;
	private $bbcode;
	private $tableEditing;
	private $ckeLinkProviderLookupIds;
	private $ckeCssCssConfigLookupId;
	
	public function __construct(string $propertyName, $label, $value = null, bool $mandatory = false, 
			int $maxlength = null, array $inputAttrs = null, string $mode = self::MODE_NORMAL, bool $bbcode = false, 
			bool $tableEditing = false, array $ckeLinkProviderLookupIds, string $ckeCssConfigLookupId = null) {
		parent::__construct($propertyName, $label, $value, $mandatory, $maxlength, true, $inputAttrs);
		
		$this->mode = $mode;
		$this->bbcode = $bbcode;
		$this->tableEditing = $tableEditing;
		$this->ckeLinkProviderLookupIds = $ckeLinkProviderLookupIds;
		$this->ckeCssCssConfigLookupId = $ckeCssConfigLookupId;
	}
	
	public function isMultiline() {
		return true;
	}
	
	public function createUiField(PropertyPath $propertyPath, HtmlView $htmlView): UiComponent {
		/* , $this->bbcode, false, $this->tableEditing, 
				$this->ckeLinkProviderLookupIds, $this->ckeCssCssConfigLookupId, $this->getInputAttrs()*/
		
		$ckeHtml = new CkeHtmlBuilder($htmlView);

		return $ckeHtml->getEditor($propertyPath,
				Cke::classic()->mode($this->mode)->table($this->tableEditing)->bbcode($this->bbcode));
	}
}
