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
namespace rocket\spec\ei\component\field\impl\translation;

use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\ArgUtils;
use n2n\impl\persistence\orm\property\ToManyEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\spec\ei\component\field\impl\relation\RelationEiField;
use rocket\spec\ei\component\field\impl\translation\model\TranslationGuiElement;
use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\manage\gui\GuiElementAssembler;
use rocket\spec\ei\manage\gui\GuiFieldFork;
use rocket\spec\ei\component\field\impl\translation\conf\TranslationEiConfigurator;
use rocket\spec\ei\manage\mapping\impl\Readable;
use rocket\spec\ei\manage\mapping\impl\Writable;
use rocket\spec\ei\manage\mapping\Mappable;
use rocket\spec\ei\component\field\GuiEiField;
use rocket\spec\ei\component\field\MappableEiField;
use rocket\spec\ei\manage\gui\GuiElementFork;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\component\field\impl\relation\model\relation\EiFieldRelation;
use n2n\core\container\N2nContext;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\critmod\filter\EiMappingFilterField;
use rocket\spec\ei\component\field\impl\relation\model\ToManyMappable;
use rocket\spec\ei\EiFieldPath;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\component\field\impl\relation\model\RelationEntry;
use rocket\spec\ei\component\field\impl\relation\EmbeddedOneToManyEiField;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use n2n\util\col\ArrayUtils;
use rocket\spec\ei\component\field\SortableEiFieldFork;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\critmod\sort\SortFieldFork;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\spec\ei\manage\critmod\sort\SortDefinition;
use n2n\persistence\orm\criteria\JoinType;
use rocket\spec\ei\manage\critmod\sort\SortConstraint;
use rocket\spec\ei\manage\critmod\sort\CriteriaAssemblyState;
use rocket\spec\ei\component\field\impl\translation\conf\N2nLocaleDef;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\util\model\EiuGui;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\component\field\impl\translation\model\TranslationMappable;

class TranslationEiField extends EmbeddedOneToManyEiField implements GuiEiField, MappableEiField, RelationEiField, 
		Readable, Writable, GuiFieldFork, SortableEiFieldFork {
	private $n2nLocaleDefs = array();

	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new TranslationEiConfigurator($this);
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ToManyEntityProperty
				&& $entityProperty->getType() == RelationEntityProperty::TYPE_ONE_TO_MANY);
	
		if (!$entityProperty->getRelation()->getTargetEntityModel()->getClass()
				->implementsInterface(Translatable::class)) {
			throw new \InvalidArgumentException('Target entity ('
					. $entityProperty->getTargetEntityModel()->getClass()->getName() . ') must implement '
					. Translatable::class);
		}

		$this->entityProperty = $entityProperty;
	}
	
	public function setN2nLocaleDefs(array $n2nLocaleDefs) {
		ArgUtils::valArrayLike($n2nLocaleDefs, N2nLocaleDef::class);
		$this->n2nLocaleDefs = $n2nLocaleDefs;
	}
	
	public function getN2nLocaleDefs() {
		return $this->n2nLocaleDefs;
	}
	
	public function getEiFieldRelation(): EiFieldRelation {
		return $this->eiFieldRelation;
	}
	
	public function isMappable(): bool {
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\EiField::getMappable()
	 */
	public function buildMappable(Eiu $eiu) {
		return new TranslationMappable($eiu->entry()->getEiSelection(), $this->eiFieldRelation, $this, $this);
	}
	
	public function buildMappableFork(EiObject $eiObject, Mappable $mappable = null) {
		return null;
	}
	
	public function isEiMappingFilterable(): bool {
		return false;
	}
	
	public function createEiMappingFilterField(N2nContext $n2nContext): EiMappingFilterField {
		throw new IllegalStateException();
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\EiField::getGuiField()
	 */
	public function getGuiField() {
		return null;
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\EiField::getGuiFieldFork()
	 */
	public function getGuiFieldFork() {
		return $this;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\gui\GuiFieldFork::getForkedGuiDefinition()
	 */
	public function getForkedGuiDefinition() {
		return $this->eiFieldRelation->getTargetEiMask()->getEiEngine()->getGuiDefinition();
	}
	
	public function createGuiElementFork(Eiu $eiu, bool $makeEditable): GuiElementFork {
		$eiFrame = $eiu->frame()->getEiFrame();
		$eiMapping = $eiu->entry()->getEiMapping();
		$eiSelection = $eiMapping->getEiSelection();
		$targetEiFrame = null;
		if ($makeEditable) {
			$targetEiFrame = $this->eiFieldRelation->createTargetEditPseudoEiFrame($eiFrame, $eiMapping);
		} else {
			$targetEiFrame = $this->eiFieldRelation->createTargetReadPseudoEiFrame($eiFrame);
		}
		$targetUtils = new EiuFrame($targetEiFrame);
		
		$toManyMappable = $eiMapping->getMappable(EiFieldPath::from($this));
		
		$targetRelationEntries = array();
		foreach ($toManyMappable->getValue() as $targetRelationEntry) {
			$targetEntityObj = $targetRelationEntry->getEiSelection()->getLiveObject();
			$n2nLocale = $targetEntityObj->getN2nLocale();
			ArgUtils::valTypeReturn($n2nLocale, N2nLocale::class, $targetEntityObj, 'getN2nLocale');
			if (!$targetRelationEntry->hasEiMapping()) {
				$targetRelationEntry = RelationEntry::fromM(
						$targetUtils->createEiMapping($targetRelationEntry->getEiSelection()));
			}
			$targetRelationEntries[(string) $n2nLocale] = $targetRelationEntry;
		}
		
		$targetGuiDefinition = $targetUtils->getEiFrame()->getContextEiMask()->getEiEngine()->getGuiDefinition();
		$translationGuiElement = new TranslationGuiElement($toManyMappable, $targetGuiDefinition, 
				$this->labelLstr->t($eiFrame->getN2nLocale()));

		foreach ($this->n2nLocaleDefs as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			
			$targetRelationEntry = null;
			if (isset($targetRelationEntries[$n2nLocaleId])) {
				$targetRelationEntry = $targetRelationEntries[$n2nLocaleId];
			} else {
				$eiSelection = $targetUtils->createNewEiSelection();
				$eiSelection->getLiveObject()->setN2nLocale($n2nLocaleDef->getN2nLocale());
				$targetRelationEntry = RelationEntry::fromM($targetUtils->createEiMapping($eiSelection));
			}
			
			$translationGuiElement->registerN2nLocale($n2nLocaleDef, $targetRelationEntry, 
					new GuiElementAssembler($targetGuiDefinition, new EiuGui(
							$targetRelationEntry->getEiMapping(), $targetUtils->getEiFrame(), 
							$eiu->gui()->getEiSelectionGui())), 
					$n2nLocaleDef->isMandatory());
		}
		
		return $translationGuiElement;
	}
	
	public function determineForkedEiObject(EiObject $eiObject) {
		// @todo access locale and use EiObject with admin locale.
		return ArrayUtils::first($this->read($eiObject));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiFieldFork::determineMappableWrapper()
	 */
	public function determineMappableWrapper(EiMapping $eiMapping, GuiIdPath $guiIdPath) {
		$mappableWrappers = array();
		foreach ($eiMapping->getValue(EiFieldPath::from($this->eiFieldRelation->getRelationEiField())) as $targetRelationEntry) {
			if ($targetRelationEntry->hasEiMapping()) continue;
				
			if (null !== ($mappableWrapper = $this->guiDefinition
					->determineMappableWrapper($targetRelationEntry->getEiMapping(), $guiIdPath))) {
				$mappableWrappers[] = $mappableWrapper;
			}
		}
	
		if (empty($mappableWrappers)) return null;
	
		return new MappableWrapperWrapper($mappableWrappers);
	}
	
	
	public function buildManagedSortFieldFork(EiFrame $eiFrame) {
		return new TranslationSortFieldFork($this, 
				$this->getEiFieldRelation()->getTargetEiMask()->getEiEngine()->createManagedSortDefinition($eiFrame),
				$this->getSortN2nLocale());
	}
	
	public function buildGlobalSortFieldFork(N2nContext $n2nContext) {
		return new TranslationSortFieldFork($this,
				$this->getEiFieldRelation()->getTargetEiMask()->createSortDefinition($n2nContext),
				$this->getSortN2nLocale());
	}
	
	private function getSortN2nLocale() {
		$firstN2nLocaleDef = ArrayUtils::first($this->n2nLocaleDefs);
		if ($firstN2nLocaleDef !== null) {
			return $firstN2nLocaleDef->getN2nLocale();
		}
		
		return N2nLocale::getAdmin();
	}
}

class TranslationSortFieldFork implements SortFieldFork {
	private $translationEiField;
	private $targetSortDefinition;
	private $sortN2nLocale;
	
	public function __construct(TranslationEiField $translationEiField, SortDefinition $targetSortDefinition, 
			N2nLocale $sortN2nLocale) {
		$this->translationEiField = $translationEiField;
		$this->targetSortDefinition = $targetSortDefinition;
		$this->sortN2nLocale = $sortN2nLocale;
	}
	
	/**
	 * @return \rocket\spec\ei\component\field\impl\translation\TranslationEiField
	 */
	public function getTranslationEiField() {
		return $this->translationEiField;
	}
	
	public function getForkedSortDefinition(): SortDefinition {
		return $this->targetSortDefinition;
	}
	
	/**
	 * @return \n2n\l10n\N2nLocale
	 */
	public function getSortN2nLocale() {
		return $this->sortN2nLocale;
	}
	
	public function createSortConstraint(SortConstraint $forkedSortConstraint): SortConstraint {
		return new TranslationSortConstraint($forkedSortConstraint, $this);
	}
}

class TranslationSortConstraint implements SortConstraint {
	private $forkedSortConstraint;
	private $fork;
	
	public function __construct(SortConstraint $forkedSortConstraint, TranslationSortFieldFork $fork) {
		$this->forkedSortConstraint = $forkedSortConstraint;
		$this->fork = $fork;
	}
	
	public function applyToCriteria(CriteriaAssemblyState $cas, CriteriaProperty $alias) {
		$eiFieldPath = EiFieldPath::from($this->fork->getTranslationEiField());
		
		$cp = null;
		if ($cas->containsCpEiFieldPath($eiFieldPath)) {
			$cp = $cas->getCp($eiFieldPath);
		} else {
			$cp = $this->provideCriteriaProperty($cas->getCriteria(), $alias);
			$cas->registerCp($eiFieldPath, $cp);
		}
		
		$this->forkedSortConstraint->applyToCriteria($cas, $cp);
	}
	
	private function provideCriteriaProperty(Criteria $criteria, CriteriaProperty $alias): CriteriaProperty {
		$joinAlias = $criteria->uniqueAlias();
	
		$criteria->joinPropertyOn($alias->ext($this->fork->getTranslationEiField()->getEntityProperty()), $joinAlias, 
						JoinType::LEFT)
				->match(CrIt::p($joinAlias, CrIt::p('n2nLocale')), '=', CrIt::c($this->fork->getSortN2nLocale()));
	
		return CrIt::p($joinAlias);
	}
}
