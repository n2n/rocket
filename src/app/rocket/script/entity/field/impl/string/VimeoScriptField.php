<?php
namespace rocket\script\entity\field\impl\string;

use n2n\ui\html\HtmlView;
use n2n\ui\Raw;
use n2n\dispatch\option\impl\StringOption;
use n2n\dispatch\PropertyPath;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\ManageInfo;

class VimeoScriptField extends AlphanumericScriptField {
	
	public function getTypeName() {
		return 'Vimeo Video';
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\impl\string\AlphanumericScriptField::createUiOutputField()
	 */
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, 
			HtmlView $view, ManageInfo $manageInfo)  {
		$html = $view->getHtmlBuilder();
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$value = $this->getPropertyAccessProxy()->getValue($scriptSelection->getCurrentEntity());
		if ($value === null) return null;
		
		$raw = '<iframe src="//player.vimeo.com/video/' . $html->getEsc($value) 
				. '" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
		return new Raw($raw);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\field\StatelessEditable::createOption()
	 */
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return new VimeoOption($this->getLabel(), null,
				$this->isRequired($scriptSelectionMapping, $manageInfo), $this->getMaxlength(), null,
				array('placeholder' => $this->getLabel()));
	}	
}

class VimeoOption extends StringOption {
	
	public function createUiField(PropertyPath $propertyPath, HtmlView $view) {
		return new Raw('<span style="display: inline-block; line-height: 16px">http://vimeo.com/' . parent::createUiField($propertyPath, $view) . '</span>');
	}
	
}
