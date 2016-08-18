<?php
namespace rocket\script\config\model;

use n2n\dispatch\val\ValIsset;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\DispatchAnnotations;
use n2n\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnotationSet;
use rocket\script\entity\field\IndependentScriptField;
use n2n\dispatch\option\impl\OptionForm;

class ScriptFieldConfigModel implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES,
				array('names' => array('label', 'optionForm')));
	}

	private $scriptField;
	private $className;
	private $optionForm;

	public function __construct(IndependentScriptField $scriptField) {
		$this->scriptField = $scriptField;
		$this->className = get_class($scriptField);
		$this->optionForm = new OptionForm($scriptField->createOptionCollection(), $scriptField->getAttributes());
	}

	public function getName() {
		return $this->scriptField->getId();
	}

	public function getTypeName() {
		return $this->scriptField->getTypeName();
	}

	public function getScriptField() {
		return $this->scriptField;
	}

	public function getClassName() {
		return $this->className;
	}

	public function getLabel() {
		return $this->scriptField->getLabel();
	}

	public function setLabel($label) {
		$this->scriptField->setLabel($label);
	}
	
// 	public function isEditable() {
// 		return $this->scriptField instanceof Editable;
// 	}
	
// 	public function isReadOnly() {
// 		if ($this->isEditable()) {
// 			return $this->scriptField->isReadOnly();
// 		}
// 		return null;
// 	}
	
// 	public function setReadOnly($readOnly) {
// 		if ($this->isEditable()) {
// 			$this->scriptField->setReadOnly($readOnly);
// 		}
// 	}
	
// 	public function isOptional() {
// 		if ($this->isEditable()) {
// 			return $this->scriptField->isOptional();
// 		}
		
// 		return null;
// 	}
	
// 	public function setOptional($optional) {
// 		if ($this->isEditable()) {
// 			$this->scriptField->setOptional($optional);
// 		}
// 	}
	
// 	public function isDisplayable() {
// 		return $this->scriptField instanceof Displayable;
// 	}
	
// 	public function isDraftable() {
// 		return $this->scriptField instanceof DraftableScriptField;
// 	}
	
// 	public function setDraftEnabled($draftEnabled) {
// 		if ($this->isDraftable()) {
// 			$this->scriptField->setDraftEnabled($draftEnabled);
// 		}
// 	}
	
// 	public function isDraftEnabled() {
// 		if ($this->isDraftable()) {
// 			return $this->scriptField->isDraftEnabled();
// 		}
	
// 		return null;
// 	}
	
// 	public function isTranslatable() {
// 		return $this->scriptField instanceof TranslatableScriptField;
// 	}
	
// 	public function setTranslationEnabled($translationEnabled) {
// 		if ($this->isTranslatable()) {
// 			$this->scriptField->setTranslationEnabled($translationEnabled);
// 		}
// 	}
	
// 	public function isTranslationEnabled() {
// 		if ($this->isTranslatable()) {
// 			return $this->scriptField->isTranslationEnabled();
// 		}
	
// 		return null;
// 	}
	
// 	public function isDisplayInListViewEnabled() {
// 		if (!$this->isDisplayable()) return null;
// 		return $this->scriptField->isDisplayInListViewEnabled();
// 	}
	
// 	public function setDisplayInListViewEnabled($displayInListViewEnabled) {
// 		if (!$this->isDisplayable()) return;
// 		$this->scriptField->setDisplayInListViewEnabled($displayInListViewEnabled);
// 	}
	
// 	public function isDisplayInDetailViewEnabled() {
// 		if (!$this->isDisplayable()) return null;
// 		return $this->scriptField->isDisplayInDetailViewEnabled();
// 	}
	
// 	public function setDisplayInDetailViewEnabled($displayInDetailViewEnabled) {
// 		if (!$this->isDisplayable()) return;
// 		$this->scriptField->setDisplayInDetailViewEnabled($displayInDetailViewEnabled);
// 	}
	
// 	public function isDisplayInEditViewEnabled() {
// 		if (!$this->isDisplayable()) return null;
// 		return $this->scriptField->isDisplayInEditViewEnabled();
// 	}
	
// 	public function setDisplayInEditViewEnabled($displayInEditViewEnabled) {
// 		if (!$this->isDisplayable()) return;
// 		$this->scriptField->setDisplayInEditViewEnabled($displayInEditViewEnabled);
// 	}

	public function getOrderIndex() {
		return $this->orderIndex;
	}

	public function setOrderIndex($orderIndex) {
		$this->orderIndex = $orderIndex;
	}

	public function getOptionForm() {
		return $this->optionForm;
	}

	public function setOptionForm(OptionForm $optionForm) {
		$this->optionForm = $optionForm;
	}

	private function _validation(BindingConstraints $bc) {
		$bc->val('label', new ValIsset());
	}
}