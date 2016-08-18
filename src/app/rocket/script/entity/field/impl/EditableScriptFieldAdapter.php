<?php
namespace rocket\script\entity\field\impl;

use rocket\script\entity\manage\ScriptSelection;
use n2n\util\Attributes;
use rocket\script\entity\manage\ScriptState;
use n2n\dispatch\option\impl\BooleanOption;
use n2n\core\DynamicTextCollection;
use n2n\core\N2nContext;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use n2n\dispatch\option\OptionCollection;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\EditableScriptField;
use rocket\script\entity\field\AccessControllableScriptField;
use n2n\persistence\orm\Entity;
use rocket\script\entity\field\WritableScriptField;
use rocket\script\entity\manage\mapping\Writable;
use rocket\script\entity\field\StatelessEditable;

abstract class EditableScriptFieldAdapter extends DisplayableScriptFieldAdapter implements EditableScriptField, 
		StatelessEditable, WritableScriptField, Writable, AccessControllableScriptField {
	const OPTION_CONSTANT_KEY = 'constant';
	const OPTION_READ_ONLY_KEY = 'readOnly';
	const OPTION_OPTIONAL_KEY = 'optional';
	const OPTION_REQUIRED_KEY = 'required';
	const ACCESS_WRITING_ALLOWED_KEY = 'writingAllowed';
	const ACCESS_WRITING_ALLOWED_DEFAULT = true;

	protected $optionConstantDefault = false;
	protected $optionReadOnlyDefault = false;
	protected $optionRequiredDefault = true;

	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		$this->applyEditOptions($optionCollection);
		return $optionCollection;
	}
	
	protected function applyEditOptions(OptionCollection $optionCollection, $addConstant = true, $addReadOnly = true, $addRequired = true) {
		$dtc = new DynamicTextCollection('rocket');
	
		if ($addReadOnly) {
			$optionCollection->addOption(self::OPTION_READ_ONLY_KEY,
					new BooleanOption($dtc->translate('script_impl_read_only_label'), $this->optionReadOnlyDefault));
		}
	
		if ($addRequired) {
			$default = $this->optionRequiredDefault;
				
			if (null !== ($optional = $this->attributes->get(self::OPTION_OPTIONAL_KEY, null))) {
				$default = !$optional;
			}
				
			$optionCollection->addOption(self::OPTION_REQUIRED_KEY,
					new BooleanOption($dtc->translate('script_impl_required_label'), $default));
		}
	}
		
	private function checkForWriteAccess(ScriptSelectionMapping $scriptSelectionMapping) {
		$ssPrivilegeConstraint = $scriptSelectionMapping->getSelectionPrivilegeConstraint();
		if ($ssPrivilegeConstraint === null) return true;
		
		foreach ($ssPrivilegeConstraint->getAccessGrants() as $accessGrant) {
			if (!$accessGrant->isRestricted() || $accessGrant->getAttributesById($this->getId())
					->get(self::ACCESS_WRITING_ALLOWED_KEY, self::ACCESS_WRITING_ALLOWED_DEFAULT)) return true;			
		}
		
		return false;
	}
	
	public function getWritable() {
		return $this;
	}

	public function write(Entity $entity, $value) {
		$this->getPropertyAccessProxy()->setValue($entity, $value);
	}
	
	public function createEditable(ScriptState $scriptState, Attributes $maskAttributes) {
		return new StatelessEditableDecorator($this, $scriptState, $maskAttributes);
	}
	/**
	 * @return bool
	 */
	public function isReadOnly(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		if (!$this->checkForWriteAccess($scriptSelectionMapping)) return true;
		
		if ($scriptSelectionMapping->getScriptSelection()->isNew() 
				&& $this->attributes->get(self::OPTION_CONSTANT_KEY, $this->optionConstantDefault)) {
			return true;
		}
		
		return $this->attributes->get(self::OPTION_READ_ONLY_KEY, $this->optionReadOnlyDefault);
	}
	
	public function isRequired(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		 return $this->isMandatory();
	}
	
	public function isMandatory() {
		if (null !== ($required = $this->attributes->get(self::OPTION_REQUIRED_KEY, null))) {
			return $required;
		}
		
		return !$this->attributes->get(self::OPTION_OPTIONAL_KEY, !$this->optionRequiredDefault);
	}
	
	public function optionAttributeValueToPropertyValue(Attributes $attributes, 
			ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$scriptSelectionMapping->setValue($this->id, $attributes->get($this->id));
	}
	
	public function propertyValueToOptionAttributeValue(ScriptSelectionMapping $scriptSelectionMapping, 
			Attributes $attributes, ManageInfo $manageInfo) {
		$attributes->set($this->id, $scriptSelectionMapping->getValue($this->id));
	}

	public function createAccessOptionCollection(N2nContext $n2nContext) {
		$dtc = new DynamicTextCollection('rocket', $n2nContext->getLocale());
		$optionCollection = new OptionCollectionImpl();
		$optionCollection->addOption(self::ACCESS_WRITING_ALLOWED_KEY, new BooleanOption(
				$dtc->translate('user_field_writable_label'), self::ACCESS_WRITING_ALLOWED_DEFAULT));
		return $optionCollection;
	}

	public function isWritingAllowed(Attributes $accessAttributes, ScriptState $scriptState, 
			ScriptSelection $scriptSelection = null) {
		return (boolean)$accessAttributes->get('writingAllowed');
	}
}