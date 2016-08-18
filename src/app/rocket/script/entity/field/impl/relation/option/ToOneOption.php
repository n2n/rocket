<?php

namespace rocket\script\entity\field\impl\relation\option;

use n2n\dispatch\option\impl\OptionAdapter;
use n2n\dispatch\PropertyPath;
use n2n\ui\html\HtmlView;
use n2n\dispatch\DispatchableTypeAnalyzer;
use n2n\dispatch\ManagedPropertyType;
use n2n\dispatch\map\BindingConstraints;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\EntryManageUtils;
use n2n\dispatch\Dispatchable;
use n2n\dispatch\map\BindingErrors;
use n2n\core\MessageCode;
use n2n\core\IllegalStateException;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\DispatchAnnotations;
use rocket\script\entity\manage\model\EntryForm;
use n2n\dispatch\val\ValEnum;
use rocket\script\entity\manage\ScriptSelection;
use n2n\dispatch\val\ValIsset;
use rocket\script\entity\manage\mapping\FlushMappingListener;

class ToOneOption extends OptionAdapter {	
	private $mappingId;
	private $scriptSelectionMapping;
	private $targetScriptSelection;
	private $targetUtils;
	private $embeddedAddEnabled = false;
	private $embeddedEditEnabled = false;
	private $embeddedUnsetAllowed = false;
	private $removeUnsusedEnabled = false;
	private $selectableEntities;
	private $selectableEntityLabels = array();
	private $targetEntryManager;
	
	public function __construct($mappingId, $label, ScriptSelectionMapping $scriptSelectionMapping, EntryManageUtils $targetUtils, $required = false) {
		parent::__construct($label, null, $required);
		
		$this->mappingId = $mappingId;
		$this->scriptSelectionMapping = $scriptSelectionMapping;
		$this->targetUtils = $targetUtils;
		
		$this->setContainerAttrs(array('class' => 'rocket-properties-option'));
	}
	/* (non-PHPdoc)
	 * @see \n2n\dispatch\option\Option::applyValidation()
	 */
	public function applyValidation($propertyName, BindingConstraints $bindingConstraints) {}
	
	public function setTargetScriptSelection(ScriptSelection $targetScriptSelection = null) {
		$this->targetScriptSelection = $targetScriptSelection;
	}
	
	public function getTargetScriptSelection() {
		return $this->targetScriptSelection;
	}
	
	public function setEmbeddedAddEnabled($embeddedAddEnabled) {
		$this->embeddedAddEnabled = (boolean) $embeddedAddEnabled;
	}
	
	public function isEmbeddedAddActivated() {
		return $this->embeddedAddEnabled;
	}

	public function setEmbeddedEditEnabled($embeddedEditEnabled) {
		$this->embeddedEditEnabled = (boolean) $embeddedEditEnabled;
	}
	
	public function isEmbeddedEditEnabled() {
		return $this->embeddedEditEnabled;
	}
	
	public function setEmbeddedUnsetAllowed($embeddedUnsetEnabled) {
		$this->embeddedUnsetAllowed = (boolean) $embeddedUnsetEnabled;
	}
	
	public function isEmbeddedUnsetAllowed() {
		return $this->embeddedUnsetAllowed;
	}
	
	public function setRemoveUnusedEnabled($removeUnusedEnabled) {
		$this->removeUnsusedEnabled = $removeUnusedEnabled;
	}
	
	public function isRemoveUnusedEnabled() {
		return $this->removeUnsusedEnabled;
	}
	
	public function setSelectableEntities(array $selectableEntities = null) {
		$this->selectableEntities = $selectableEntities;
	}
	
	public function getSelectableEntities() {
		return $this->selectableEntities;
	}
	
	public function setSelectableEntityLabels(array $selectableEntityLabels = null) {
		$this->selectableEntityLabels = $selectableEntityLabels;
	}
	
	public function getSelectableEntityLabels() {
		return $this->selectableEntityLabels;
	} 
	
	public function createManagedPropertyType($propertyName, DispatchableTypeAnalyzer $dispatchableTypeAnalyzer) {
		$propertyType = new ManagedPropertyType($dispatchableTypeAnalyzer, $propertyName);
		$propertyType->setType(ManagedPropertyType::TYPE_OBJECT);
		return $propertyType;
	}
	/* (non-PHPdoc)
	 * @see \n2n\dispatch\option\Option::createUiField()
	 */
	public function createUiField(PropertyPath $propertyPath, HtmlView $view) {
		return $view->getImport('script\entity\field\impl\relation\view\toOneOption.html',
				array('propertyPath' => $propertyPath, 'itemLabel' => $this->targetUtils->getScriptState()->getScriptMask()->getLabel()));
	}
	
	private function createEntryIdOptions($forceNull) {
		$options = array();
		if ($forceNull || !$this->isRequired()) {
			$options[null] = null;
		}
		
		$targetScriptState = $this->targetUtils->getScriptState();
		foreach ($this->selectableEntities as $id => $entity) {
			if (isset($this->selectableEntityLabels[$id])) {
				$options[$id] = $this->selectableEntityLabels[$id];
				continue;
			}
			$options[$id] = $targetScriptState->createKnownString($entity);
		}
		
		return $options;
	}
	
	private $toOneForm;
	
	public function attributeValueToOptionValue($attributeValue) {
		if ($this->embeddedEditEnabled && $this->selectableEntities !== null) {
			throw new IllegalStateException('Embedded Edit and Entity Selection cannot both be enabled.');
		}
		
		$this->toOneForm = new ToOneForm($this->getLabel(), $this->isRequired(), $this->isEmbeddedUnsetAllowed());

		if ($this->embeddedAddEnabled) {
			$this->toOneForm->setAvailableNewEntryForm($this->targetUtils->createEntryForm());	
		}

		$targetScriptSelectionMapping = null;
		if ($this->targetScriptSelection !== null) {
			$targetScriptSelectionMapping = $this->targetUtils->createScriptSelectionMapping(
					$this->targetScriptSelection);
			$this->targetEntryManager = $this->targetUtils->createEntryManager($targetScriptSelectionMapping, false);
		}
		
		if ($this->embeddedEditEnabled) {
			if ($targetScriptSelectionMapping !== null) {
				$this->toOneForm->setCurrentEntryForm($this->targetUtils->createEntryForm($targetScriptSelectionMapping));
			}	
		} else if ($this->selectableEntities !== null) {
			$this->toOneForm->setEntryIdOptions($this->createEntryIdOptions($targetScriptSelectionMapping === null));
			
			if ($targetScriptSelectionMapping !== null) {
				$this->toOneForm->setEntryId($targetScriptSelectionMapping->getScriptSelection()->getId());
			}
		} else if ($targetScriptSelectionMapping !== null) {
			$this->toOneForm->setKeepFrozen(true);
			$this->toOneForm->setFrozenLabel((string) $this->targetUtils->getScriptState()->createKnownString(
					$targetScriptSelectionMapping->getScriptSelection()->getEntity()));
		}
		
		return $this->toOneForm;
	}
	
	public function optionValueToAttributeValue($optionValue) {
		if (null !== ($newEntryForm = $this->toOneForm->getNewEntryForm())) {
			$newTargetScriptSelectionMapping = $newEntryForm->buildScriptSelectionMapping();
			$entryManager = $this->targetUtils->createEntryManager();
			$entryManager->create($newTargetScriptSelectionMapping);

			if ($this->targetEntryManager !== null) {
				$this->remove();
			}
			
			$this->scriptSelectionMapping->registerRelatedMapping($this->mappingId, $newTargetScriptSelectionMapping);
			return $newTargetScriptSelectionMapping->getScriptSelection()->getEntity();
		}
	 		
		if ($this->embeddedEditEnabled) {
		 	if ($this->targetEntryManager !== null) {
		 		if (null !== ($currentEntryForm = $this->toOneForm->getCurrentEntryForm())) {
		 			$targetScriptSelectionMapping = $currentEntryForm->buildScriptSelectionMapping();
		 			$this->targetEntryManager->save($targetScriptSelectionMapping);
		 			$this->scriptSelectionMapping->registerRelatedMapping($this->mappingId, $targetScriptSelectionMapping);
		 			return $targetScriptSelectionMapping->getScriptSelection()->getEntity();
		 		}
		 		
		 		if ($this->toOneForm->getKeepFrozen()) {
		 			return $targetScriptSelectionMapping->getScriptSelection()->getEntity();
		 		}
		 		
		 		$this->remove();
		 	}
		} else if ($this->selectableEntities !== null) {
	 		if (null !== ($entryId = $this->toOneForm->getEntryId())) {
	 			return $this->selectableEntities[$entryId];
	 		}
		} 
	 	
	 	return null;
	}
	
	private function remove() {
		if (!$this->removeUnsusedEnabled) return;
		// @todo this doesn't work yet
		$this->scriptSelectionMapping->registerListener(new FlushMappingListener(
				function() {
					$this->targetUtils->removeScriptSelection($this->targetScriptSelection);
				}), $this->mappingId);
	}
}

class ToOneForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, array('names' => array('entryId', 'keepFrozen')));
		$as->p('currentEntryForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY, 
				array('creator' => function () { throw new IllegalStateException(); }));
		$as->p('newEntryForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY, array('creator' => 
				function (ToOneForm $toOneForm) { 
					return $toOneForm->availableNewEntryForm; 
				}));
	}
	
	private $required;
	private $label;
	private $entryIdOptions;
	protected $availableNewEntryForm;
	private $unsetCurrentAllowed;
	private $frozenLabel;
	
	private $entryId;
	private $currentEntryForm;
	private $newEntryForm;
	private $keepFrozen;
		
	public function __construct($label, $required, $usetCurrentAllowed) {
		$this->label = $label;
		$this->required = $required;
		$this->unsetCurrentAllowed = $usetCurrentAllowed;
	}
	
	public function isRequired() {
		return $this->required;
	}
	
	public function getEntryIdOptions() {
		return $this->entryIdOptions;
	}
	
	public function setEntryIdOptions(array $currentEntryIdOptions = null) {
		$this->entryIdOptions = $currentEntryIdOptions;
	}
	
	public function setEntryId($entryId) {
		$this->entryId = $entryId;
	}
	
	public function getEntryId() {
		return $this->entryId;
	}

	public function isUnsetCurrentAllowed() {
		return $this->unsetCurrentAllowed;
	}
	
	public function getCurrentEntryForm() {
		return $this->currentEntryForm;
	}
	
	public function setCurrentEntryForm(EntryForm $currentEntryForm = null) {
		$this->currentEntryForm = $currentEntryForm;
	}
	
	public function getCurrentEntity() {
		return $this->currentEntity;
	}
	
	public function getKeepFrozen() {
		return $this->keepFrozen;
	}
	
	public function hasFrozen() {
		return null !== $this->frozenLabel;
	}
	
	public function setKeepFrozen($keepFrozen) {
		$this->keepFrozen = $keepFrozen;
	}
	
	public function setFrozenLabel($frozenLabel) {
		$this->frozenLabel = $frozenLabel;
	}
	
	public function getFrozenLabel() {
		return $this->frozenLabel;
	}
	
	public function isNewEntryFormAvailable() {
		return $this->availableNewEntryForm !== null;
	}
	
	public function setAvailableNewEntryForm(EntryForm $availableNewEntryForm) {
		$this->availableNewEntryForm = $availableNewEntryForm;
	}
	
	public function getNewEntryForm() {
		return $this->newEntryForm;
	}
	
	public function setNewEntryForm(EntryForm $newEntryForm = null) {
		$this->newEntryForm = $newEntryForm;
	}

	private function _validation(BindingConstraints $bc) {
		if (null !== $this->entryIdOptions) {
			$bc->val('entryId', new ValEnum(array_keys($this->entryIdOptions), $this->required));
		}
		
		if (!$this->unsetCurrentAllowed && $this->currentEntryForm !== null) {
			$bc->val('currentEntryForm', new ValIsset());
		}
		
		if ($this->required) {
			$that = $this;
			$bc->addClosureValidator(function ($entryId, $currentEntryForm, $newEntryForm, $keepFrozen, 
					BindingErrors $be) use ($that) {
				if ($entryId !== null || $currentEntryForm !== null || $newEntryForm !== null 
						|| ($that->hasFrozen() && $keepFrozen)) return;
				
				$be->addError('entryId', new MessageCode('common_field_required_err', array('field' => $that->label)));
			});
		}
	}
}

