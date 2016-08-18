<?php
namespace rocket\script\entity\field\impl\file;

use n2n\dispatch\option\impl\EnumOption;
use rocket\script\entity\field\impl\string\StringScriptField;
use rocket\script\core\SetupProcess;
use rocket\script\entity\field\impl\file\command\MultiUploadScriptCommand;
use n2n\util\Attributes;

class MultiUploadFileScriptField extends FileScriptField {
	
	const PROP_NAME_REFERENCED_NAME_PROPERTY_ID = 'referencedNamePropertyId';
	
	public function getTypeName() {
		return 'File (MultiUpload)';
	}
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		$command = new MultiUploadScriptCommand(new Attributes());
		$command->setScriptField($this);
		$this->getEntityScript()->getCommandCollection()->add($command);
	}
	
	public function getReferencedNamePropertyId() {
		return $this->getAttributes()->get(self::PROP_NAME_REFERENCED_NAME_PROPERTY_ID);
	}
	
	public function createOptionCollection() {
		$optionCollection = parent::createOptionCollection();
		$optionCollection->addOption(self::PROP_NAME_REFERENCED_NAME_PROPERTY_ID, 
				new EnumOption('Referenced Name Property', $this->determineNamePropertyOptions()));
		return $optionCollection;
	}
	
	private function determineNamePropertyOptions() {
		$options = array();
		foreach ($this->getEntityScript()->getFieldCollection() as $field) {
			if (!($field instanceof StringScriptField)) continue;
			$options[$field->getId()] = $field->getPropertyName();  
		}
		return $options;
	}
}