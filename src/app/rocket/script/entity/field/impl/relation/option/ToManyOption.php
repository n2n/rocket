<?php

namespace rocket\script\entity\field\impl\relation\option;

use n2n\dispatch\PropertyPath;
use n2n\ui\html\HtmlView;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\EntryManageUtils;
use n2n\core\IllegalStateException;
use n2n\dispatch\Dispatchable;
use n2n\dispatch\map\BindingConstraints;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\DispatchAnnotations;
use n2n\dispatch\map\BindingErrors;
use n2n\core\MessageCode;
use n2n\dispatch\val\ValEnum;
use n2n\dispatch\option\impl\OptionAdapter;
use n2n\dispatch\DispatchableTypeAnalyzer;
use n2n\dispatch\ManagedPropertyType;
use n2n\dispatch\PropertyPathPart;
use n2n\http\BadRequestException;
use n2n\core\Message;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\manage\mapping\FlushMappingListener;

class ToManyOption extends OptionAdapter {
	private $mappingId;
	private $scriptSelectionMapping;
	private $targetUtils;
	private $min;
	private $max;
	private $embeddedAddablesNum = 0;
	private $embeddedEditEnabled = false;
	private $embeddedUnsetAllowed = false;
	private $selectableEntities;
	private $removeUnsusedEnabled = false;
	private $targetScriptSelections = array();
	private $targetEntryManagers = array();
	
	public function __construct($mappingId, $label, ScriptSelectionMapping $scriptSelectionMapping,
			EntryManageUtils $targetUtils, $min = null, $max = null) {
		parent::__construct($label, array(), $max == 0);
		
		$this->mappingId = $mappingId;
		$this->scriptSelectionMapping = $scriptSelectionMapping;
		$this->targetUtils = $targetUtils;
		$this->min = $min; 
		$this->max = $max;
	}
	/* (non-PHPdoc)
	 * @see \n2n\dispatch\option\Option::applyValidation()
	*/
	public function applyValidation($propertyName, BindingConstraints $bindingConstraints) {}
	
	public function createManagedPropertyType($propertyName, DispatchableTypeAnalyzer $dispatchableTypeAnalyzer) {
		$propertyType = new ManagedPropertyType($dispatchableTypeAnalyzer, $propertyName);
		$propertyType->setType(ManagedPropertyType::TYPE_OBJECT);
		return $propertyType;
	}
	
	public function setTargetScriptSelections(array $targetScriptSelections) {
		$this->targetScriptSelections = $targetScriptSelections;
	}
	
	public function setEmbeddedAddablesNum($embeddedAddablesNum) {
		$this->embeddedAddablesNum = (int) $embeddedAddablesNum;
	}
	
	public function getEmbeddedAddablesNum() {
		return $this->embeddedAddablesNum;
	}

	public function setEmbeddedEditEnabled($embeddedEditEnabled) {
		$this->embeddedEditEnabled = (boolean) $embeddedEditEnabled;
	}
	
	public function isEmbeddedEditEnabled() {
		return $this->embeddedEditEnabled;
	}
	
	public function setEmbeddedUnsetAllowed($embeddedUnsetAllowed) {
		$this->embeddedUnsetAllowed = (boolean) $embeddedUnsetAllowed;
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
	
	private function createEntryIdOptions() {
		$targetScriptState = $this->targetUtils->getScriptState();
		$options = array();
		foreach ($this->selectableEntities as $id => $entity) {
			$options[$id] = $targetScriptState->createKnownString($entity);
		}
		
		return $options;
	}

	public function attributeValueToOptionValue($attributeValue) {
		if ($this->embeddedEditEnabled && $this->selectableEntities !== null) {
			throw new IllegalStateException('Embedded Edit and Entity Selection cannot both be enabled.');
		}
	
		$this->toManyForm = new ToManyForm($this->min, $this->max, $this->embeddedUnsetAllowed, $this->getLabel());
	
		$newEntryForms = array();
		for ($i = 0; $i < $this->embeddedAddablesNum; $i++) {
			$newEntryForms[] = $this->targetUtils->createEntryForm();
		}
		$this->toManyForm->setAvailableNewEntryForms($newEntryForms);

		foreach ($this->targetScriptSelections as $targetScriptSelection) {
			$targetEntryManager = $this->targetUtils->createScriptSelectionMapping($targetScriptSelection);
			$this->targetEntryManagers[$targetScriptSelection->getId()] = $this->targetUtils->createEntryManager($targetEntryManager, false);
		}
	
		if ($this->embeddedEditEnabled) {
			$currentEntryForms = array();
			foreach ($this->targetEntryManagers as $id => $targetEntryManager) {
				$currentEntryForms[$id] = $this->targetUtils->createEntryForm($targetEntryManager->getScriptSelectionMapping());
			}
			
			$this->toManyForm->setCurrentEntryForms($currentEntryForms);
		} else if ($this->selectableEntities !== null) {
			$this->toManyForm->setEntryIdOptions($this->createEntryIdOptions());
			$entryIds = array_keys($this->targetEntryManagers);
			
			$this->toManyForm->setEntryIds(array_combine($entryIds, $entryIds));
		}
	
		return $this->toManyForm;
	}

	public function optionValueToAttributeValue($optionValue) {
		$attributeValue = new \ArrayObject();
		
		foreach ($this->toManyForm->getNewEntryForms() as $newEntryForm) {
			$newTargetScriptSelectionMapping = $newEntryForm->buildScriptSelectionMapping();
			$entryManager = $this->targetUtils->createEntryManager();
			$entryManager->create($newTargetScriptSelectionMapping);
					
			$this->scriptSelectionMapping->registerRelatedMapping($this->mappingId, $newTargetScriptSelectionMapping);
			$attributeValue[] = $newTargetScriptSelectionMapping->getScriptSelection()->getEntity();
		}
		
		if ($this->embeddedEditEnabled) {
			$currentEntryForms = $this->toManyForm->getCurrentEntryForms();
			foreach ($this->targetEntryManagers as $id => $targetEntryManager) {
				if (!isset($currentEntryForms[$id])) {
					$this->remove($targetEntryManager->getScriptSelectionMapping()->getScriptSelection());
					continue;
				}
				
				$targetScriptSelectionMapping = $currentEntryForms[$id]->buildScriptSelectionMapping();
				$targetEntryManager->save($targetScriptSelectionMapping);
				$this->scriptSelectionMapping->registerRelatedMapping($this->mappingId, $targetScriptSelectionMapping);
				$attributeValue[] = $targetScriptSelectionMapping->getScriptSelection()->getEntity();	
			}
		} else if ($this->selectableEntities !== null) {
			foreach ($this->toManyForm->getEntryIds() as $id) {
				$attributeValue[] = $this->selectableEntities[$id];
			}
		}
		 
		return $attributeValue;
	}
	
	private function remove(ScriptSelection $scriptSelection) {
		if (!$this->removeUnsusedEnabled) return;
		// @todo this doesn't work yet
		$this->scriptSelectionMapping->registerListener(new FlushMappingListener(
				function() use ($scriptSelection) {
					$this->targetUtils->removeScriptSelection($scriptSelection);
				}), $this->mappingId);
	}
	/* (non-PHPdoc)
	 * @see \n2n\dispatch\option\Option::createUiField()
	 */
	public function createUiField(PropertyPath $propertyPath, HtmlView $view) {
		return $view->getImport('\rocket\script\entity\field\impl\relation\view\toManyOption.html',
				array('propertyPath' => $propertyPath, 'targetScriptMask' => $this->targetUtils->getScriptState()->getScriptMask()));
	}
}

class ToManyForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, array('names' => array('entryIds')));
		$as->p('currentEntryForms', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY, 
				array('creator' => function () { throw new BadRequestException(); }));
		$as->p('newEntryForms', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY,
				array('creator' => function (ToManyForm $toManyForm, PropertyPathPart $pathPart) {
					// @todo remove hack after n2n.dispatch update
					$key = $pathPart->getResolvedArrayKey();
					if (isset($toManyForm->availableNewEntryForms[$key])) {
						return $toManyForm->availableNewEntryForms[$key];
					}
					throw new BadRequestException();
				}));
	}
	
	private $entryIds = array();
	private $currentEntryForms = array();
	private $newEntryForms = array();
	
	private $min;
	private $max;
	private $currentUnsetAllowed;
	private $entryIdOptions = null;
	private $availableNewEntryForms = 0;
	
	public function __construct($min, $max, $currentUnsetAllowed, $label) {
		$this->min = $min;
		$this->max = $max;
		$this->currentUnsetAllowed = $currentUnsetAllowed;
		$this->label = $label;
	}
	
	public function getMin() {
		return $this->min;
	}
	
	public function getMax() {
		return $this->max;
	}
	
	public function areEntryIdOptionsAvailable() {
		return $this->entryIdOptions !== null;
	}
	
	public function setEntryIdOptions(array $entryIdOptions = null) {
		$this->entryIdOptions = $entryIdOptions;
	}
	
	public function getEntryIdOptions() {
		return $this->entryIdOptions;
	}
	
	public function getEntryIds() {
		return $this->entryIds;
	}
	
	public function setEntryIds(array $entryIds) {
		$this->entryIds = $entryIds;
	}
	
	public function areEntryFormsAvailable() {
		return sizeof($this->currentEntryForms) || sizeof($this->availableNewEntryForms);
	}
	
	public function isCurrentUnsetAllowed() {
		return $this->currentUnsetAllowed;
	}

	public function hasCurrentEntryForms() {
		return (boolean) sizeof($this->newEntryForms);
	}
	
	public function setCurrentEntryForms(array $currentEntryForms) { 
		$this->currentEntryForms = $currentEntryForms;
	}
	
	public function getCurrentEntryForms() {
		return $this->currentEntryForms;
	}
	
	public function setAvailableNewEntryForms(array $availableNewEntryForms) {
		$this->availableNewEntryForms = $availableNewEntryForms;
	}
	
	public function getAvailableNewEntryFormNum() {
		return sizeof($this->availableNewEntryForms);
	}
	
	public function hasNewEntryForms() {
		return (boolean) sizeof($this->newEntryForms);
	}
	
	public function setNewEntryForms(array $newEntryForms) {
		$this->newEntryForms = $newEntryForms;
	}
	
	public function getNewEntryForms() {
		return $this->newEntryForms;
	}
	
	private function _validation(BindingConstraints $bc) {
		if (sizeof($this->entryIdOptions)) {
			$bc->val('entryIds', new ValEnum(array_keys($this->entryIdOptions)));
		}
		
		$that = $this;
		
		if (!$this->currentUnsetAllowed) {
			$bc->addClosureValidator(function (array $currentEntryForms, BindingErrors $be) use ($that) {
				if (sizeof($that->currentEntryForms) != sizeof($currentEntryForms)) {
					$be->addError('currentEntryForms', new Message('Not allowed to unset current.'));
				}
			});
		}
		
		$bc->addClosureValidator(function (array $entryIds, array $currentEntryForms, array $newEntryForms, BindingErrors $be) use ($that) {
			$num = sizeof($entryIds) + sizeof($currentEntryForms) + sizeof($newEntryForms);
			
			if ($that->min !== null && $that->min > $num) {
				$be->addError('newEntryForms', new MessageCode('script_impl_field_array_size_min_err', 
						array('field' => $that->label, 'min' => $that->min)));
			}
			
			if ($that->max !== null && $that->max < $num) {
				$be->addError('newEntryForms', new MessageCode('script_impl_field_array_size_max_err', 
						array('field' => $that->label, 'max' => $that->max)));
			}
		});
	}
}