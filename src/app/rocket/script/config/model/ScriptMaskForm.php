<?php

namespace rocket\script\config\model;

use n2n\dispatch\Dispatchable;
use n2n\dispatch\DispatchAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use rocket\script\entity\mask\IndependentScriptMask;
use n2n\l10n\Locale;
use n2n\dispatch\map\BindingConstraints;
use rocket\script\entity\filter\FilterForm;
use rocket\script\entity\manage\display\Displayable;
use rocket\script\entity\filter\SortForm;
use n2n\dispatch\val\ValIsset;
use n2n\dispatch\map\BindingErrors;
use n2n\core\MessageCode;
use n2n\dispatch\val\ValArrayKeys;
use rocket\script\entity\command\IndependentScriptCommand;
use rocket\script\entity\field\DisplayableScriptField;

class ScriptMaskForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, array('names' => array('id', 'label', 'pluralLabel', 
				'knownStringPattern', 'draftDisabled', 'translationDisabled', 'defaultSortEnabled', 
				'commandsRestricted', 'commandIds', 'partialControlOrder', 'overallControlOrder', 'entryControlOrder')));
		$as->p('listFieldOrderForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
		$as->p('entryFieldOrderForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
		$as->p('detailFieldOrderForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
		$as->p('editFieldOrderForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
		$as->p('addFieldOrderForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
		$as->p('filterForm', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
		$as->p('defaultSort', DispatchAnnotations::MANAGED_DISPATCHABLE_PROPERTY);
		$as->m('save', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $mask;
	private $extraction;
	
	public $commandsRestricted;
	public $commandIds = array();
	public $listFieldOrderForm;
	public $entryFieldOrderForm;
	public $detailFieldOrderForm;
	public $editFieldOrderForm;
	public $addFieldOrderForm;
	public $filterForm;
	public $defaultSort;
	public $defaultSortEnabled = false;
	
	public function __construct(IndependentScriptMask $mask, FilterForm $filterForm, SortForm $sortForm) {
		$this->mask = $mask;
		$this->extraction = $mask->getExtraction();
		$this->listFieldOrderForm = FieldOrderForm::createFromOrder($this->extraction->getListFieldOrder());
		$this->entryFieldOrderForm = FieldOrderForm::createFromOrder($this->extraction->getEntryFieldOrder());
		$this->detailFieldOrderForm = FieldOrderForm::createFromOrder($this->extraction->getDetailFieldOrder());
		$this->editFieldOrderForm = FieldOrderForm::createFromOrder($this->extraction->getEditFieldOrder());
		$this->addFieldOrderForm = FieldOrderForm::createFromOrder($this->extraction->getAddFieldOrder());
		$commandIds = $this->extraction->getCommandIds();
		$this->commandsRestricted = $commandIds !== null;
		$this->commandIds = array_combine((array) $commandIds, (array) $commandIds);
		$this->filterForm = $filterForm;
		$this->defaultSort = $sortForm;

		if (null !== ($filterData = $this->extraction->getFilterData())) {
			$this->filterForm->writeFilterData($filterData);
		}
		
		if (null !== ($sortDirections = $this->extraction->getDefaultSortDirections())) {
			$this->defaultSortEnabled = true;
			$this->defaultSort->setSortDirections($sortDirections);
		}
	}
	
	public function setId($id) {
		$this->extraction->setId($id);
	}
	
	public function getId() {
		return $this->extraction->getId();
	}
	
	public function getLabel() {
		return $this->extraction->getLabel();
	}
	
	public function setLabel($label) {
		$this->extraction->setLabel($label);
	}
	
	public function getPluralLabel() {
		return $this->extraction->getPluralLabel();
	}
	
	public function setPluralLabel($pluralLabel) {
		$this->extraction->setPluralLabel($pluralLabel);
	}
	
	public function getKnownStringPattern() {
		return $this->extraction->getKnownStringPattern();
	}
	
	public function setKnownStringPattern($knownStringPattern) {
		$this->extraction->setKnownStringPattern($knownStringPattern);
	}
	
	public function isDraftDisabled() {
		return $this->extraction->isDraftDisabled();
	}
	
	public function setDraftDisabled($draftDisabled) {
		$this->extraction->setDraftDisabled((boolean) $draftDisabled);
	}
	
	public function isTranslationDisabled() {
		return $this->extraction->isTranslationDisabled();
	}
	
	public function setTranslationDisabled($translationDisabled) {
		$this->extraction->setTranslationDisabled((boolean) $translationDisabled);
	}
	
	public function isCommandsRestricted() {
		return $this->commandsRestricted;
	}
	
	public function setCommandsRestricted($commandsRestricted) {
		$this->commandsRestricted = (boolean) $commandsRestricted;
	}
	
	public function getCommandIds() {
		return $this->commandIds;
	}
	
	public function setCommandIds(array $commandIds) {
		$this->commandIds = $commandIds;
	}
	
	public function getCommandIdOptions() {
		$commandIdOptions = array();
		foreach ($this->mask->getEntityScript()->getCommandCollection() as $command) {
			if ($command instanceof IndependentScriptCommand) {
				$commandIdOptions[$command->getId()] = $command->getId();
			}
		}
		return $commandIdOptions;
	}
	
	public function getPartialControlOptions(Locale $locale) {
		return $this->mask->getPartialControlOptions($locale);
	}
	
	public function getOverallControlOptions(Locale $locale) {
		return $this->mask->getOverallControlOptions($locale);
	}
	
	public function getEntryControlOptions(Locale $locale) {
		return $this->mask->getEntryControlOptions($locale);
	}
		
	public function getPartialControlOrder() {
		return $this->extraction->getPartialControlOrder();
	}
	
	public function setPartialControlOrder(array $partialControlOrder) {
		$this->extraction->setPartialControlOrder($partialControlOrder);
	}
	
	public function getOverallControlOrder() {
		return $this->extraction->getOverallControlOrder();
	}
	
	public function setOverallControlOrder(array $overallControlOrder) {
		$this->extraction->setOverallControlOrder($overallControlOrder);
	}
	
	public function getEntryControlOrder() {
		return $this->extraction->getEntryControlOrder();
	}
	
	public function setEntryControlOrder(array $entryControlOrder) {
		$this->extraction->setEntryControlOrder($entryControlOrder);
	}
	
	public function getFieldDataAttrs() {
		$fieldIdOptions = array();
		foreach ($this->mask->getEntityScript()->getFieldCollection() as $id => $field) {
			if ($field instanceof DisplayableScriptField) {
				$fieldIdOptions[$id] = $field->getLabel();
			}
		}
		return $fieldIdOptions;
	}
	
	private function _validation(BindingConstraints $bc) {
		$bc->val('id', new ValIsset());
		$that = $this;
		$bc->addClosureValidator(function ($id, BindingErrors $be) use ($that) {
			if ($that->extraction->getId() == $id) return;
			
			if ($that->mask->getEntityScript()->getMaskSet()->offsetExists($id)) {
				$be->addError('id', new MessageCode('script_mask_id_not_unique_err'));
			}
		});
		$bc->val('commandIds', new ValArrayKeys(array_keys($this->getCommandIdOptions())));
// 		$bc->val('partialControlOrder', new ValEnum($this->getPartialControlOrder()));
// 		$bc->val('overallControlOrder', new ValEnum($this->getOverallControlOrder()));
// 		$bc->val('entryControlOrder', new ValEnum($this->getEntryControlOrder()));
	}
	
	public function save() {
		$this->extraction->setListFieldOrder($this->listFieldOrderForm->toOrder());
		$this->extraction->setEntryFieldOrder($this->entryFieldOrderForm->toOrder());
		$this->extraction->setDetailFieldOrder($this->detailFieldOrderForm->toOrder());
		$this->extraction->setEditFieldOrder($this->editFieldOrderForm->toOrder());
		$this->extraction->setAddFieldOrder($this->addFieldOrderForm->toOrder());
		
		if ($this->commandsRestricted) {
			$this->extraction->setCommandIds(array_keys($this->commandIds));
		} else {
			$this->extraction->setCommandIds(null);
		}
		
		$filterData = $this->filterForm->readFilterData();
		$this->extraction->setFilterData($filterData->isEmpty() ? null : $filterData);
		
		if ($this->defaultSortEnabled) {
			$this->extraction->setDefaultSortDirections($this->defaultSort->getSortDirections());
		} else {
			$this->extraction->setDefaultSortDirections(null);
		}
	}
}