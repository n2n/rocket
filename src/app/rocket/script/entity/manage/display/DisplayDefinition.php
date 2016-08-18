<?php
namespace rocket\script\entity\manage\display;

use rocket\script\entity\EntityScript;
use rocket\script\entity\mask\ScriptMask;
use n2n\dispatch\option\impl\OptionForm;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use n2n\util\Attributes;
use n2n\reflection\ArgumentUtils;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\model\EntryModel;

class DisplayDefinition {
	private $displayables = array();
	private $editables = array();
	
	public function __construct(EntityScript $entityScript, ScriptMask $scriptMask) {
		$this->entityScript = $entityScript;
		$this->scriptMask = $scriptMask;
	}

	public function getEntityScript() {
		return $this->entityScript;
	}
	
	public function getScriptMask() {
		return $this->scriptMask;
	}
	
	public function registerDisplayable($id, Displayable $displayable) {
		if (isset($this->displayables[$id])) {
			throw new DisplayException('Displayable with id \'' . $id . '\' is already registered');
		}
		
		$this->displayables[$id] = $displayable;
	}
	
	public function containsDisplayableId($id) {
		return isset($this->displayables[$id]);
	}
	
	public function getDisplayableById($id) {
		if (!isset($this->displayables[$id])) {
			throw new DisplayException('No Displayable with id \'' . $id . '\' registered');
		}
		
		return $this->displayables[$id];
	}
	
	public function getDisplayables() {
		return $this->displayables;
	}
	
	public function registerEditable($id, Editable $editable) {
		$this->registerDisplayable($id, $editable);
		$this->editables[$id] = $editable;
	}
	
	public function containsEditableId($id) {
		return isset($this->editables[$id]);
	}
	
	public function getEditableById($id) {
		if (!$this->containsEditableId($id, $scriptState, $scriptSelection)) {
			throw new DisplayException('No Editable with id \'' . $id . '\' registered');
		}
		
		return $this->editables[$id];
	}
	
	public function containsProperEditableId($id, EntryModel $entryModel) {
		return isset($this->editables[$id]) && !$this->editables[$id]->isReadOnly($entryModel);
	}

	public function findProperEditable($id, EntryModel $entryModel) {
		if ($this->containsProperEditableId($id, $entryModel)) {
			return $this->editables[$id];
		}
		
		return null;
	}
	
	public function findProperEditables(EntryModel $entryModel) {
		$editables = array();
		foreach ($this->editables as $id => $editable) {
			if ($editable->isReadOnly($entryModel)) continue;
			$editables[$id] = $editable;
		}
		return $editables;
	}
	
	public function createOptionForm(EntryModel $entryModel) {
		$optionCollection = new OptionCollectionImpl();
		$scriptSelectionMapping = $entryModel->getScriptSelectionMapping();
		$attributes = new Attributes();
		
		foreach ($this->findProperEditables($entryModel) as $id => $editable) {
			$option = $editable->createOption($entryModel);
			ArgumentUtils::validateReturnType($option, 'n2n\dispatch\option\Option', $editable, 'createOption');
			$optionCollection->addOption($id, $option);
			$editable->propertyValueToOptionAttributeValue($scriptSelectionMapping, $attributes, $entryModel);
		}
		
		return new OptionForm($optionCollection, $attributes);
	}
			
	public function writeAttributes(Attributes $attributes, ScriptSelectionMapping $scriptSelectionMapping, EntryModel $entryModel) {
		foreach ($this->findProperEditables($entryModel) as $editableId => $editable) {
			$editable->optionAttributeValueToPropertyValue($attributes, $scriptSelectionMapping, $entryModel);
			
			if ($entryModel->getScriptSelectionMapping() !== $scriptSelectionMapping) {				
				$scriptSelectionMapping->unregisterRelatedMappings($editableId);
				foreach ($entryModel->getScriptSelectionMapping()->getRelatedMappings($editableId) as $relatedMapping) {
					$scriptSelectionMapping->registerRelatedMapping($editableId, $relatedMapping);
				}
				
				$scriptSelectionMapping->unregisterFieldRelatedListeners($editableId);
				foreach ($entryModel->getScriptSelectionMapping()->getFieldRelatedListeners($editableId) as $relatedListener) {
					$scriptSelectionMapping->registerListener($relatedListener, $editableId);
				}
			}
		}
	}
}