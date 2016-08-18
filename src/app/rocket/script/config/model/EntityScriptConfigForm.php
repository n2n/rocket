<?php

namespace rocket\script\config\model;

use n2n\dispatch\Dispatchable;
use rocket\script\entity\EntityScript;
use n2n\l10n\Locale;
use n2n\dispatch\map\BindingConstraints;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\DispatchAnnotations;
use rocket\script\entity\field\HighlightableScriptField;
use rocket\script\entity\field\SortableScriptField;
use n2n\persistence\orm\criteria\Criteria;
use rocket\script\entity\FilterModelFactory;
use rocket\script\entity\filter\SortForm;
use n2n\core\N2nContext;

class EntityScriptConfigForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->annotateClass(DispatchAnnotations::MANAGED_PROPERTIES, 
				array('names' => array('knownStringPattern', 'defaultMaskId')));
		$as->annotateProperty('sortForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
		$as->annotateProperty('commandConfigModels', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY);
		$as->annotateProperty('fieldConfigModels', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY);
		$as->annotateProperty('constraintConfigModels', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY);
		$as->annotateProperty('listenerConfigModels', DispatchAnnotations::MANAGED_DISPATCHABLE_ARRAY_PROPERTY);
		$as->annotateMethod('save', DispatchAnnotations::MANAGED_METHOD);
		$as->annotateMethod('saveAndGoToOverview', DispatchAnnotations::MANAGED_METHOD);
		$as->annotateMethod('saveAndBack', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $entityScript;
	
	public $sortForm;
	public $commandConfigModels = array();
	public $fieldConfigModels = array();
	public $constraintConfigModels = array();
	public $listenerConfigModels = array();
	
	private $partialControlOptions;
	private $overallControlOptions;
	private $entryControlOptions;
	
	public function __construct(EntityScript $entityScript, Locale $locale, N2nContext $n2nContext) {
		$this->entityScript = $entityScript;
		
		$this->sortForm = SortForm::createFromSortModel(
				FilterModelFactory::createSortModel($entityScript, $n2nContext));
		$this->sortForm->setSortDirections($entityScript->getDefaultSortDirections());
		
		foreach ($this->entityScript->getCommandCollection()->filterLevel(true) as $id => $command) {
			$this->commandConfigModels[$id] = new ScriptElementConfigModel($command);
		}
		
		foreach ($this->entityScript->getFieldCollection()->filterLevel(true) as $id => $field) {
			$this->fieldConfigModels[$id] = new ScriptElementConfigModel($field);
		}
		
		foreach ($this->entityScript->getModificatorCollection()->filterLevel(true) as $id => $constraint) {
			$this->constraintConfigModels[$id] = new ScriptElementConfigModel($constraint);
		}
		
		foreach ($this->entityScript->getListenerCollection()->filterLevel(true) as $id => $listener) {
			$this->listenerConfigModels[$id] = new ScriptElementConfigModel($listener);
		}
		
		$this->sortFieldIdOptions = array(null => null);
		foreach ($this->entityScript->getFieldCollection() as $scriptField) {
			if ($scriptField instanceof SortableScriptField) {
				$this->sortFieldIdOptions[$scriptField->getId()] = $scriptField->getLabel();
			}
		}
		
		$this->sortDirectionOptions = array_combine(Criteria::getOrderDirections(), Criteria::getOrderDirections());
	}
	
	public function getEntityScript() {
		return $this->entityScript;
	}
		
	public function getHighlightableScriptFieldFieldNames() {
		$highlightableFieldNames = array();
		foreach ($this->entityScript->getFieldCollection()->toArray() as $scriptField) {
			if (!($scriptField instanceof HighlightableScriptField)) continue;

			$placeHolder = EntityScript::KNOWN_STRING_FIELD_OPEN_DELIMITER 
					. $scriptField->getId() . EntityScript::KNOWN_STRING_FIELD_CLOSE_DELIMITER;
			$highlightableFieldNames[$placeHolder] = $scriptField->getLabel(); 
		}
		return $highlightableFieldNames;
	}
	
	public function setKnownStringPattern($knownStringPattern) {
		$this->entityScript->setKnownStringPattern($knownStringPattern);
	} 
	
	public function getKnownStringPattern() {
		return $this->entityScript->getKnownStringPattern();
	}
	
	public function getDefaultMaskId() {
		if (null !== ($mask = $this->entityScript->getDefaultMask())) {
			return $mask->getId();
		}
		return null;
	}
	
	public function setDefaultMaskId($defaultMaskId) {
		$defaultMask = null;
		$maskSet = $this->entityScript->getMaskSet();
		if (isset($maskSet[$defaultMaskId])) {
			$defaultMask = $maskSet[$defaultMaskId];
		}

		$this->entityScript->setDefaultMask($defaultMask);
	}
	
	public function getDefaultMaskIdOptions() {
		$options = array(null => null);
		foreach ($this->entityScript->getMaskSet() as $id => $maskSet) {
			$options[$id] = $maskSet->getLabel() . '(' . $id . ')';
		}
		return $options;
	}
	
	private function _validation(BindingConstraints $bc) {}
	
	public function save() {
		$this->entityScript->setDefaultSortDirections($this->sortForm->getSortDirections());
		
// 		$sortCollection = $this->entityScript->getDefaultSortModificatorCollection();
// 		$sortCollection->removeSortableScriptFields();
// 		foreach ($this->sortFieldIds as $key => $fieldId) {
// 			$direction = null;
// 			if (isset($this->sortDirections[$key])) {
// 				$direction = $this->sortDirections[$key];
// 			} else {
// 				$direction = Criteria::ORDER_DIRECTION_ASC;
// 			}
			
// 			$sortCollection->addSortableScriptField($this->entityScript->getFieldCollection()->getById($fieldId), $direction);
// 		}
	}
	
	public function saveAndGoToOverview() {
		$this->save();
	}
	
	public function saveAndBack() {
		$this->save();
	}
}