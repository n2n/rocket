<?php
namespace rocket\script\entity\field\impl\string;

use rocket\script\entity\preview\PreviewModel;
use n2n\dispatch\PropertyPath;
use n2n\dispatch\option\impl\BooleanOption;
use n2n\l10n\Locale;
use n2n\dispatch\option\impl\StringOption;
use n2n\persistence\orm\Entity;
use n2n\ui\html\HtmlView;
use rocket\script\entity\preview\PreviewableScriptField;
use rocket\script\entity\field\HighlightableScriptField;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\ManageInfo;

class StringScriptField extends AlphanumericScriptField implements HighlightableScriptField, PreviewableScriptField {
	const OPTION_MULTILINE_KEY = 'multiline';
	
	public function getTypeName() {
		return 'String';
	}
	
	public function isMultiline() {
		return $this->getAttributes()->get(self::OPTION_MULTILINE_KEY);
	}
	
	public function setMultiline($multline) {
		$this->getAttributes()->set(self::OPTION_MULTILINE_KEY, $multline);
	}
	
	public function createOptionCollection() {
		$optionForm = parent::createOptionCollection();
		$optionForm->addOption(self::OPTION_MULTILINE_KEY, new BooleanOption('Multiline', null, false));
		return $optionForm;
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, 
			HtmlView $view, ManageInfo $manageInfo)  {
		$html = $view->getHtmlBuilder();
		$value = $scriptSelectionMapping->getValue($this->id);
		if ($this->isMultiline()) {
			return $html->getEscBr($value);
		}
		
		return $html->getEsc($value);
	}
	
	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath,
			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
		if ($this->isMultiline()) {
			return $view->getFormHtmlBuilder()->getTextarea($propertyPath, array('class' => 'rocket-preview-inpage-component'));
		}
		return $view->getFormHtmlBuilder()->getInputField($propertyPath, array('class' => 'rocket-preview-inpage-component'));
	}
	

	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$option = new StringOption($this->getLabel(), null,
				$this->isRequired($scriptSelectionMapping, $manageInfo), 
				$this->getMaxlength(), $this->isMultiline(),
				array('placeholder' => $this->getLabel(), 'maxlength' => $this->getMaxlength()));
		$option->setContainerAttrs(array('class' => 'rocket-block'));
		return $option;
	}

	public function createKnownString(Entity $entity, Locale $locale) {
		return $this->read($entity);
	}
}