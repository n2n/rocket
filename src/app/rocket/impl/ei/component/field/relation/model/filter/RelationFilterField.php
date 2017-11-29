<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\impl\ei\component\field\relation\model\filter;

use n2n\util\config\Attributes;
use n2n\web\dispatch\mag\MagCollection;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use rocket\spec\ei\manage\util\model\EiUtils;
use n2n\l10n\N2nLocale;
use n2n\l10n\Lstr;
use n2n\persistence\orm\property\EntityProperty;
use rocket\spec\ei\manage\critmod\filter\ComparatorConstraint;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\util\model\UnknownEntryException;
use rocket\spec\ei\manage\critmod\filter\data\FilterGroupData;
use n2n\reflection\property\TypeConstraint;
use n2n\util\config\AttributesException;
use rocket\spec\ei\manage\critmod\filter\FilterField;
use rocket\spec\ei\manage\critmod\filter\FilterDefinition;
use rocket\spec\ei\manage\critmod\filter\impl\controller\FilterAjahHook;
use rocket\impl\ei\component\field\relation\TargetFilterDef;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\spec\ei\manage\critmod\filter\ComparatorConstraintGroup;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;
use rocket\spec\ei\manage\critmod\filter\impl\model\SimpleComparatorConstraint;
use rocket\spec\ei\manage\mapping\EiFieldConstraint;

class RelationFilterField implements FilterField {
	protected $labelLstr;
	protected $entityProperty;
	protected $targetEiUtils;
	protected $targetFilterDef;
	protected $targetSelectUrlCallback;
	
	public function __construct($labelLstr, EntityProperty $entityProperty, EiUtils $targetEiUtils, 
			TargetFilterDef $targetFilterDef) {
		$this->labelLstr = Lstr::create($labelLstr);
		$this->entityProperty = $entityProperty;
		$this->targetEiUtils = $targetEiUtils;
		$this->targetFilterDef = $targetFilterDef;
	}
	
	public function setTargetSelectUrlCallback(\Closure $targetSelectUrlCallback) {
		$this->targetSelectUrlCallback = $targetSelectUrlCallback;
	}
	
	public function getLabel(N2nLocale $n2nLocale): string {
		return $this->labelLstr->t($n2nLocale);
	}
	
	public function createComparatorConstraint(Attributes $attributes): ComparatorConstraint {
		$relationFilterConf = new RelationFilterConf($attributes);
		
		$operator = $relationFilterConf->getOperator();
		switch ($operator) {
			case CriteriaComparator::OPERATOR_IN:
			case CriteriaComparator::OPERATOR_NOT_IN:
				if ($this->entityProperty->isToMany()) break;
				
				return new SimpleComparatorConstraint(CrIt::p($this->entityProperty), $operator, 
						CrIt::c($this->lookupTargetEntityObjs($relationFilterConf->getTargetIdReps())));
			case CriteriaComparator::OPERATOR_CONTAINS:
			case CriteriaComparator::OPERATOR_CONTAINS_NOT:
				if (!$this->entityProperty->isToMany()) break;
				
				$group = new ComparatorConstraintGroup(true);
				foreach ($this->lookupTargetEntityObjs($relationFilterConf->getTargetIdReps()) as $targetEntityObj) {
					$group->addComparatorConstraint(new SimpleComparatorConstraint(CrIt::p($this->entityProperty), 
							$operator, CrIt::c($targetEntityObj)));
				}
				return $group;
			case CriteriaComparator::OPERATOR_EXISTS:
			case CriteriaComparator::OPERATOR_NOT_EXISTS:
				$targetComparatorContraint = $this->targetFilterDef->getFilterDefinition()->createComparatorConstraint(
						$relationFilterConf->getTargetFilterGroupData());
				
				return new TestComparatorConstraint($this->entityProperty, $targetComparatorContraint);
		}
	}
	
	private function lookupTargetEntityObjs(array $targetIdReps) {
		$targetEntityObjs = array();
		foreach ($targetIdReps as $targetIdRep) {
			try {
				$targetEntityObjs[] = $this->targetEiUtils->lookupEiEntityObj($targetIdRep, 
						CriteriaConstraint::ALL_TYPES);
			} catch (UnknownEntryException $e) { }
		}
		return $targetEntityObjs;
	}

	public function createMagDispatchable(Attributes $attributes): MagDispatchable {
		$form = new RelationFilterMagForm($this->entityProperty->isToMany(), $this->targetEiUtils, 
				$this->targetFilterDef->getFilterDefinition(), $this->targetFilterDef->getFilterAjahHook(), 
				$this->targetSelectUrlCallback);
		$relationFilterConf = new RelationFilterConf($attributes);
		
		$form->getOperatorMag()->setValue($relationFilterConf->getOperator());
		
		if ($this->targetSelectUrlCallback !== null) {
			$targetLiveEntries = array();
			foreach ($relationFilterConf->getTargetIdReps() as $targetIdRep) {
				try {
					$targetLiveEntries[$targetIdRep] = $this->targetEiUtils->lookupEiEntityObj(
							$this->targetEiUtils->idRepToId($targetIdRep), CriteriaConstraint::ALL_TYPES);
				} catch (UnknownEntryException $e) {}
			}
			$form->getSelectorMag()->setTargetLiveEntries($targetLiveEntries);
		}
		
		$form->getFilterGroupMag()->setValue($relationFilterConf->getTargetFilterGroupData());
		
		return $form;
	}
	
	public function buildAttributes(MagDispatchable $form): Attributes {
		ArgUtils::assertTrue($form instanceof RelationFilterMagForm);
		
		$relationFilterConf = new RelationFilterConf(new Attributes());
		
		$relationFilterConf->setOperator($form->getOperatorMag()->getValue());
		
		$targetIdReps = array();
		foreach ($form->getTargetLiveEntries() as $targetEiEntityObj) {
			$targetIdReps[] = $this->targetEiUtils->idToIdRep($targetEiEntityObj->getId());
		}
		$relationFilterConf->setTargetIdReps($targetIdReps);	
		
		return $relationFilterConf->getAttributes();
	}
	
	/**
	 * 
	 * @param Attributes $attributes
	 * @return EiFieldConstraint
	 */
	public function createEiFieldConstraint(Attributes $attributes) {
		$relationFilterConf = new RelationFilterConf(new Attributes());
		
		$operator = $relationFilterConf->getOperator();
		switch ($operator) {
			case CriteriaComparator::OPERATOR_IN:
			case CriteriaComparator::OPERATOR_NOT_IN:
				if ($this->entityProperty->isToMany()) break;
		
				return new SimpleComparatorConstraint(CrIt::p($this->entityProperty), $operator,
						CrIt::c($this->lookupTargetEntityObjs($relationFilterConf->getTargetIdReps())));
			case CriteriaComparator::OPERATOR_CONTAINS:
			case CriteriaComparator::OPERATOR_CONTAINS_NOT:
				if (!$this->entityProperty->isToMany()) break;
		
				$group = new ComparatorConstraintGroup(true);
				foreach ($this->lookupTargetEntityObjs($relationFilterConf->getTargetIdReps()) as $targetEntityObj) {
					$group->addComparatorConstraint(new SimpleComparatorConstraint(CrIt::p($this->entityProperty),
							$operator, CrIt::c($targetEntityObj)));
				}
				return $group;
			case CriteriaComparator::OPERATOR_EXISTS:
			case CriteriaComparator::OPERATOR_NOT_EXISTS:
				$targetComparatorContraint = $this->targetFilterDef->getFilterDefinition()->createComparatorConstraint(
				$relationFilterConf->getTargetFilterGroupData());
		
				return new TestComparatorConstraint($this->entityProperty, $targetComparatorContraint);
		}
	}
}

class RelationFilterConf {
	const OPERATOR_KEY = 'operator';
	const TARGET_ID_REPS = 'targetIdReps';
	const TARGET_FILTER_GROUP_ATTRS = 'targetFilterGroupAttrs';
	
	private $attributes;
	
	public function __construct(Attributes $attributes) {
		$this->attributes = $attributes;
	}
	
	public function getOperator() {
		return $this->attributes->getString(self::OPERATOR_KEY, false);
	}
	
	public function setOperator(string $operator) {
		$this->attributes->set(self::OPERATOR_KEY, $operator);
	}
	
	public function getTargetIdReps(): array {
		return $this->attributes->getArray(self::TARGET_ID_REPS, false, array(), TypeConstraint::createSimple('string'));
	}
	
	public function setTragetIdReps(array $targetIdReps) {
		$this->attributes->set(self::TARGET_ID_REPS, $targetIdReps);
	}
	
	public function getTargetFilterGroupData(): FilterGroupData {
		try {
			return FilterGroupData::create(new Attributes($this->attributes
					->getArray(self::TARGET_FILTER_GROUP_ATTRS, false)));
		} catch (AttributesException $e) {
			return new FilterGroupData();
		}
	}
	
	public function setTargetFilterGroupData(FilterGroupData $targetFilterGroupData) {
		$this->attributes->set(self::TARGET_FILTER_GROUP_ATTRS, $targetFilterGroupData->toAttrs());
	}
}


class RelationFilterMagForm extends MagForm {
	private $toMany;
	private $operatorMag;
	private $selectorMag;
	private $filterGroupMag;
	
	public function __construct(bool $toMany, EiUtils $targetEiUtils, FilterDefinition $targetFilterDefinition, 
			FilterAjahHook $filterAjahHook, \Closure $targetSelectUrlCallback = null) {
		$this->toMany = $toMany;
				
		if ($targetSelectUrlCallback !== null) {
			$this->selectorMag = new RelationSelectorMag('selector', $targetEiUtils, 
					$targetSelectUrlCallback);
		}
		$this->filterGroupMag = new RelationFilterGroupMag('filterGroup', $targetFilterDefinition, $filterAjahHook);
		$this->operatorMag = new EnumMag('operator', 'Operator',
				$this->buildOperatorOptions(), null, true);
		
		$magCollection = new MagCollection();
		$magCollection->addMag($this->operatorMag);
		
		if (null !== $this->selectorMag) {
			$magCollection->addMag($this->selectorMag);
		}
		
		$magCollection->addMag($this->filterGroupMag);
		
		parent::__construct($magCollection);
	}
	
	public function getOperatorMag(): EnumMag {
		return $this->operatorMag;
	}
	
	public function getSelectorMag(): RelationSelectorMag{
		return $this->selectorMag;
	}
	
	public function getFilterGroupMag(): RelationFilterGroupMag {
		return $this->filterGroupMag;
	}
		
	public function buildOperatorOptions(): array {
		$operatorOptions = array();

		if ($this->selectorMag !== null) {
			if ($this->toMany) {
				$operatorOptions[CriteriaComparator::OPERATOR_CONTAINS] = new Lstr('common_operator_contains_label', 'rocket');
				$operatorOptions[CriteriaComparator::OPERATOR_CONTAINS_NOT] = new Lstr('common_operator_contains_not_label', 'rocket');
			} else {
				$operatorOptions[CriteriaComparator::OPERATOR_IN] = new Lstr('common_operator_in_label', 'rocket');
				$operatorOptions[CriteriaComparator::OPERATOR_NOT_IN] = new Lstr('common_operator_not_in_label', 'rocket');
			}
		}
		
		$operatorOptions[CriteriaComparator::OPERATOR_EXISTS] = new Lstr('common_operator_exists_label', 'rocket');
		$operatorOptions[CriteriaComparator::OPERATOR_NOT_EXISTS] = new Lstr('common_operator_not_exists_label', 'rocket');
		
		return $operatorOptions;
	}
}
