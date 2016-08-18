<?php
namespace rocket\script\entity\field\impl\string;

use n2n\util\Attributes;

use n2n\dispatch\option\impl\StringOption;
use n2n\ui\html\HtmlView;
use n2n\dispatch\PropertyPath;
use rocket\script\entity\preview\PreviewModel;
use rocket\script\entity\preview\PreviewableScriptField;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\ManageInfo;

class LinkScriptField extends AlphanumericScriptField implements PreviewableScriptField {
	public function getTypeName() {
		return "Link";
	}
	
	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
		return $view->getFormHtmlBuilder()->getInputField($propertyPath, array('class' => 'rocket-preview-inpage-component'));
	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$option = new StringOption($this->getLabel(), null,
						$this->isRequired($scriptSelectionMapping, $manageInfo), $this->getAttributes()->get('maxlength'), false,
				array('placeholder' => $this->getLabel()));
		$option->setContainerAttrs(array('class' => 'rocket-block'));
		return $option;
	}
	
	public function optionAttributeValueToPropertyValue(Attributes $attributes, 
			ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$optionValue = $attributes->get($this->getId());
		if (0 !== strlen($optionValue)) {
			if (0 == preg_match('/^(http:\/\/|https:\/\/|mailto:|tel:)/', $optionValue)) {
				$optionValue = 'http://' . $optionValue;	
			}
		}
		$scriptSelectionMapping->setValue($this->getId(), $optionValue);
	}
}