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
namespace rocket\impl\ei\component\prop\translation;

use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\ArgUtils;
use n2n\impl\persistence\orm\property\ToManyEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use rocket\impl\ei\component\prop\translation\model\TranslationGuiFieldFork;
use rocket\ei\util\model\EiuFrame;
use rocket\ei\manage\gui\GuiPropFork;
use rocket\impl\ei\component\prop\translation\conf\TranslationEiConfigurator;
use rocket\ei\manage\mapping\impl\Readable;
use rocket\ei\manage\mapping\impl\Writable;
use rocket\ei\manage\mapping\EiField;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\component\prop\FieldEiProp;
use rocket\ei\manage\gui\GuiFieldFork;
use rocket\ei\manage\EiObject;
use rocket\impl\ei\component\prop\relation\model\relation\EiPropRelation;
use n2n\core\container\N2nContext;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\critmod\filter\EiEntryFilterField;
use rocket\ei\EiPropPath;
use n2n\l10n\N2nLocale;
use rocket\impl\ei\component\prop\relation\model\RelationEntry;
use rocket\impl\ei\component\prop\relation\EmbeddedOneToManyEiProp;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use n2n\util\col\ArrayUtils;
use rocket\ei\component\prop\SortableEiPropFork;
use rocket\ei\manage\EiFrame;
use rocket\ei\manage\critmod\sort\SortFieldFork;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\critmod\sort\SortDefinition;
use n2n\persistence\orm\criteria\JoinType;
use rocket\ei\manage\critmod\sort\SortConstraint;
use rocket\ei\manage\critmod\sort\CriteriaAssemblyState;
use rocket\impl\ei\component\prop\translation\conf\N2nLocaleDef;
use rocket\ei\util\model\Eiu;
use rocket\ei\manage\mapping\EiEntry;
use rocket\ei\manage\gui\GuiIdPath;
use rocket\impl\ei\component\prop\translation\model\TranslationEiField;
use rocket\ei\component\prop\QuickSearchableEiProp;
use rocket\impl\ei\component\prop\translation\model\TranslationQuickSearchField;
use rocket\ei\manage\mapping\impl\EiFieldWrapperWrapper;
use rocket\ei\manage\gui\ViewMode;
use rocket\impl\ei\component\prop\translation\command\TranslationCopyCommand;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\gui\GuiDefinition;

class TranslationEiProp extends EmbeddedOneToManyEiProp implements GuiEiProp, FieldEiProp, RelationEiProp, 
		Readable, Writable, GuiPropFork, SortableEiPropFork, QuickSearchableEiProp {
	private $n2nLocaleDefs = array();
	private $minNumTranslations = 0;
	private $copyCommand;
	
	public function createEiPropConfigurator(): EiPropConfigurator {
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
		ArgUtils::valArray($n2nLocaleDefs, N2nLocaleDef::class);
		$this->n2nLocaleDefs = $n2nLocaleDefs;
	}
	
	public function getN2nLocaleDefs() {
		return $this->n2nLocaleDefs;
	}
	
	public function setCopyCommand(TranslationCopyCommand $translationCopyCommand = null) {
		$this->copyCommand = $translationCopyCommand;
	}
	
	/**
	 * @param int $minNumTranslations
	 */
	public function setMinNumTranslations(int $minNumTranslations) {
		$this->minNumTranslations = $minNumTranslations;
	}
	
	/**
	 * @return int
	 */
	public function getMinNumTranslations() {
		return $this->minNumTranslations;
	}
	
	public function getEiPropRelation(): EiPropRelation {
		return $this->eiPropRelation;
	}
	
	public function isEiField(): bool {
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\component\prop\EiProp::getEiField()
	 */
	public function buildEiField(Eiu $eiu) {
		$readOnly = $this->eiPropRelation->isReadOnly($eiu->entry()->getEiEntry(), $eiu->frame()->getEiFrame());
		
		return new TranslationEiField($eiu->entry()->getEiObject(), $this, $this,
				($readOnly ? null : $this));
	}
	
	public function buildEiFieldFork(EiObject $eiObject, EiField $eiField = null) {
		return null;
	}
	
	public function isEiEntryFilterable(): bool {
		return false;
	}
	
	public function createEiEntryFilterField(N2nContext $n2nContext): EiEntryFilterField {
		throw new IllegalStateException();
	}

	/* (non-PHPdoc)
	 * @see \rocket\ei\component\prop\EiProp::getGuiProp()
	 */
	public function getGuiProp(): ?GuiProp {
		return null;
	}

	/* (non-PHPdoc)
	 * @see \rocket\ei\component\prop\EiProp::getGuiPropFork()
	 */
	public function getGuiPropFork(): ?GuiPropFork {
		return $this;
	}

	public function getForkedGuiDefinition(): GuiDefinition {
		return $this->eiPropRelation->getTargetEiMask()->getEiEngine()->getGuiDefinition();
	}
	
	public function createGuiFieldFork(Eiu $eiu): GuiFieldFork {
		$eiFrame = $eiu->frame()->getEiFrame();
		$eiEntry = $eiu->entry()->getEiEntry();
		$eiObject = $eiEntry->getEiObject();
		$targetEiFrame = null;
		if ($eiu->entryGui()->isReadOnly()) {
			$targetEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame);
		} else {
			$targetEiFrame = $this->eiPropRelation->createTargetEditPseudoEiFrame($eiFrame, $eiEntry);
		}
		$targetEiuFrame = new EiuFrame($targetEiFrame);
		
		$toManyEiField = $eiEntry->getEiField(EiPropPath::from($this));
		
		$targetRelationEntries = array();
		foreach ($toManyEiField->getValue() as $targetRelationEntry) {
			$targetEntityObj = $targetRelationEntry->getEiObject()->getLiveObject();
			$n2nLocale = $targetEntityObj->getN2nLocale();
			ArgUtils::valTypeReturn($n2nLocale, N2nLocale::class, $targetEntityObj, 'getN2nLocale');
			if (!$targetRelationEntry->hasEiEntry()) {
				$targetRelationEntry = RelationEntry::fromM(
						$targetEiuFrame->entry($targetRelationEntry->getEiObject())->getEiEntry());
			}
			$targetRelationEntries[(string) $n2nLocale] = $targetRelationEntry;
		}
		
		$targetGuiDefinition = $targetEiuFrame->getEiFrame()->getContextEiEngine()->getGuiDefinition();
		$translationGuiField = new TranslationGuiFieldFork($toManyEiField, $targetGuiDefinition, 
				$this->labelLstr->t($eiFrame->getN2nContext()->getN2nLocale()), $this->minNumTranslations);
		if ($this->copyCommand !== null) {
			$translationGuiField->setCopyUrl($targetEiuFrame->getUrlToCommand($this->copyCommand)
					->extR(null, array('bulky' => $eiu->gui()->isBulky())));
		}

		foreach ($this->n2nLocaleDefs as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			
			$viewMode = null;
			$targetRelationEntry = null;
			if (isset($targetRelationEntries[$n2nLocaleId])) {
				$targetRelationEntry = $targetRelationEntries[$n2nLocaleId];
			} else {
				$eiObject = $targetEiuFrame->createNewEiObject();
				$eiObject->getLiveObject()->setN2nLocale($n2nLocaleDef->getN2nLocale());
				$targetRelationEntry = RelationEntry::fromM($targetEiuFrame->entry($eiObject)->getEiEntry());
			}
			
			$viewMode = ViewMode::determine($eiu->gui()->isBulky(), $eiu->gui()->isReadOnly(), 
					$targetRelationEntry->getEiEntry()->isNew());
			$targetEiuEntryGuiAssembler = $targetEiuFrame->entry($targetRelationEntry->getEiEntry())
					->newEntryGuiAssembler($eiu->gui()->getViewMode());
			
			$translationGuiField->registerN2nLocale($n2nLocaleDef, $targetRelationEntry, 
					$targetEiuEntryGuiAssembler, $n2nLocaleDef->isMandatory(), 
					isset($targetRelationEntries[$n2nLocaleId]));
		}
		
		return $translationGuiField;
	}
	
	public function determineForkedEiObject(EiObject $eiObject): ?EiObject {
		// @todo access locale and use EiObject with admin locale.
		return ArrayUtils::first($this->read($eiObject));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiPropFork::determineEiFieldWrapper()
	 */
	public function determineEiFieldWrapper(EiEntry $eiEntry, GuiIdPath $guiIdPath) {
		$eiFieldWrappers = array();
		foreach ($eiEntry->getValue(EiPropPath::from($this->eiPropRelation->getRelationEiProp())) as $targetRelationEntry) {
			if ($targetRelationEntry->hasEiEntry()) continue;
				
			if (null !== ($eiFieldWrapper = $this->guiDefinition
					->determineEiFieldWrapper($targetRelationEntry->getEiEntry(), $guiIdPath))) {
				$eiFieldWrappers[] = $eiFieldWrapper;
			}
		}
	
		if (empty($eiFieldWrappers)) return null;
	
		return new EiFieldWrapperWrapper($eiFieldWrappers);
	}
	
	
	public function buildManagedSortFieldFork(EiFrame $eiFrame) {
		return new TranslationSortFieldFork($this, 
				$this->getEiPropRelation()->getTargetEiMask()->getEiEngine()->createManagedSortDefinition($eiFrame),
				$this->getSortN2nLocale());
	}
	
	public function buildGlobalSortFieldFork(N2nContext $n2nContext) {
		return new TranslationSortFieldFork($this,
				$this->getEiPropRelation()->getTargetEiMask()->createSortDefinition($n2nContext),
				$this->getSortN2nLocale());
	}
	
	private function getSortN2nLocale() {
		$firstN2nLocaleDef = ArrayUtils::first($this->n2nLocaleDefs);
		if ($firstN2nLocaleDef !== null) {
			return $firstN2nLocaleDef->getN2nLocale();
		}
		
		return N2nLocale::getAdmin();
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\QuickSearchableEiProp::buildQuickSearchField()
	 */
	public function buildQuickSearchField(EiFrame $eiFrame) {
		$targetEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame);
		
		return new TranslationQuickSearchField(
				$this->eiPropRelation->getRelationEntityProperty(),
				$this->eiPropRelation->getTargetEiType()->getEntityModel()->getClass(), 
				$targetEiFrame->getContextEiEngine()->createQuickSearchDefinition($targetEiFrame));
	}

}

class TranslationSortFieldFork implements SortFieldFork {
	private $translationEiProp;
	private $targetSortDefinition;
	private $sortN2nLocale;
	
	public function __construct(TranslationEiProp $translationEiProp, SortDefinition $targetSortDefinition, 
			N2nLocale $sortN2nLocale) {
		$this->translationEiProp = $translationEiProp;
		$this->targetSortDefinition = $targetSortDefinition;
		$this->sortN2nLocale = $sortN2nLocale;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\translation\TranslationEiProp
	 */
	public function getTranslationEiProp() {
		return $this->translationEiProp;
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
		$eiPropPath = EiPropPath::from($this->fork->getTranslationEiProp());
		
		$cp = null;
		if ($cas->containsCpEiPropPath($eiPropPath)) {
			$cp = $cas->getCp($eiPropPath);
		} else {
			$cp = $this->provideCriteriaProperty($cas->getCriteria(), $alias);
			$cas->registerCp($eiPropPath, $cp);
		}
		
		$this->forkedSortConstraint->applyToCriteria($cas, $cp);
	}
	
	private function provideCriteriaProperty(Criteria $criteria, CriteriaProperty $alias): CriteriaProperty {
		$joinAlias = $criteria->uniqueAlias();
	
		$criteria->joinPropertyOn($alias->ext($this->fork->getTranslationEiProp()->getEntityProperty()), $joinAlias, 
						JoinType::LEFT)
				->match(CrIt::p($joinAlias, CrIt::p('n2nLocale')), '=', CrIt::c($this->fork->getSortN2nLocale()));
	
		return CrIt::p($joinAlias);
	}
}
