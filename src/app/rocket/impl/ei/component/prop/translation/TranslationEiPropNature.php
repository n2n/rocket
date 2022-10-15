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

use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\impl\persistence\orm\property\ToManyEntityProperty;
use n2n\l10n\N2nLocale;
use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\criteria\JoinType;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\property\EntityProperty;
use n2n\util\col\ArrayUtils;
use n2n\util\type\ArgUtils;
use rocket\ei\EiPropPath;


use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\ei\manage\critmod\sort\CriteriaAssemblyState;
use rocket\ei\manage\critmod\sort\SortConstraint;
use rocket\ei\manage\critmod\sort\SortDefinition;
use rocket\ei\manage\critmod\sort\SortPropFork;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\impl\ei\component\prop\relation\RelationEiPropNatureAdapter;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\relation\model\ToManyEiField;
use rocket\impl\ei\component\prop\translation\conf\TranslationConfig;
use rocket\impl\ei\component\prop\translation\gui\TranslationGuiProp;
use rocket\impl\ei\component\prop\translation\model\TranslationQuickSearchProp;
use rocket\ei\manage\idname\IdNamePropFork;
use rocket\impl\ei\component\prop\translation\model\TranslationIdNamePropFork;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\impl\ei\component\prop\translation\conf\N2nLocaleDef;
use n2n\core\config\WebConfig;

class TranslationEiPropNature extends RelationEiPropNatureAdapter {
	/**
	 * @var TranslationConfig
	 */
	private $translationConfig;

	public function __construct(RelationEntityProperty $entityProperty, PropertyAccessProxy $accessProxy) {
		ArgUtils::assertTrue($entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_MANY
				&& $entityProperty->getTargetEntityModel()->getClass()->implementsInterface(Translatable::class));

		parent::__construct($entityProperty, $accessProxy,
				new RelationModel($this, false, true, RelationModel::MODE_INTEGRATED, null));
	}

	/**
	 * @var array
	 */
	private array $n2nLocaleDefs = array();
	/**
	 * @var int
	 */
	private int $translationsMinNum = 1;


// 	public function __construct(TranslationEiProp $translationEiProp) {
// 		$this->translationEiProp = $translationEiProp;
// 	}


	function setup(Eiu $eiu): void {
		parent::setup($eiu);

		if (empty($this->n2nLocaleDefs)) {
			$n2nLocaleDefs = array_map(fn ($l) => new N2nLocaleDef($l, false),
					$eiu->lookup(WebConfig::class)->getAllN2nLocales());

			foreach ($n2nLocaleDefs as $n2nLocaleDef) {
				$this->n2nLocaleDefs[$n2nLocaleDef->getN2nLocale()->getId()] = $n2nLocaleDef;
			}
		}
	}

	public function setN2nLocaleDefs(array $n2nLocaleDefs) {
		ArgUtils::valArray($n2nLocaleDefs, N2nLocaleDef::class);
		$this->n2nLocaleDefs = $n2nLocaleDefs;
	}

	/**
	 * @return N2nLocaleDef[]
	 */
	public function getN2nLocaleDefs() {
		return $this->n2nLocaleDefs;
	}

// 	public function setCopyCommand(TranslationCopyCommand $translationCopyCommand = null) {
// 		$this->copyCommand = $translationCopyCommand;
// 	}

	/**
	 * @param int $minNumTranslations
	 */
	public function setTranslationsMinNum(int $minNumTranslations) {
		$this->translationsMinNum = $minNumTranslations;
	}

	/**
	 * @return int
	 */
	public function getTranslationsMinNum() {
		return $this->translationsMinNum;
	}
	
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		return new TranslationGuiProp($this->getRelationModel(), $this);
	}

	public function buildIdNamePropFork(Eiu $eiu): ?IdNamePropFork {
		return new TranslationIdNamePropFork($this->getRelationModel(), $this);
		
	}
// 	public function determineForkedEiObject(Eiu $eiu): ?EiObject {
// 		// @todo access locale and use EiObject with admin locale.
		
// 		$targetObjects = $eiu->object()->readNativValue($eiu->prop()->getEiProp());
		
// 		if (empty($targetObjects)) {
// 			return null;
// 		}
		
// 		if ($targetObjects instanceof \ArrayObject) {
// 			$targetObjects = $targetObjects->getArrayCopy();
// 		}
		
// 		return LiveEiObject::create($this->eiPropRelation->getTargetEiType(), ArrayUtils::first($targetObjects));
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\manage\gui\GuiPropFork::determineEiFieldWrapper()
// 	 */
// 	public function determineEiFieldAbstraction(Eiu $eiu, DefPropPath $defPropPath): EiFieldAbstraction {
// 		$eiEntry = $eiu->entry()->getEiEntry();
		
// 		$eiFieldWrappers = array();
// 		foreach ($eiEntry->getValue(EiPropPath::from($this->eiPropRelation->getRelationEiProp())) as $targetRelationEntry) {
// 			if (!$targetRelationEntry->hasEiEntry()) continue;
				
// 			if (null !== ($eiFieldWrapper = $eiu->engine()->getGuiDefinition()
// 					->determineEiFieldAbstraction($eiu->getN2nContext(), $targetRelationEntry->getEiEntry(), $defPropPath))) {
// 				$eiFieldWrappers[] = $eiFieldWrapper;
// 			}
// 		}
	
// 		return new EiFieldWrapperCollection($eiFieldWrappers);
// 	}
	
	public function buildSortPropFork(Eiu $eiu): ?SortPropFork {
		$targetSortDefinition = null;
		if (null !== ($eiuFrame = $eiu->frame(false))) {
			$targetSortDefinition = $this->getEiPropRelation()->getTargetEiMask()->getEiEngine()
					->createFramedSortDefinition($eiuFrame->getEiFrame());
		} else {
			$targetSortDefinition = $this->getEiPropRelation()->getTargetEiMask()->getEiEngine()
					->createSortDefinition($eiu->getN2nContext());
		}
		
		return new TranslationSortPropFork($this, $targetSortDefinition, $this->getSortN2nLocale());
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
	 * @see \rocket\ei\component\prop\QuickSearchableEiProp::buildQuickSearchProp()
	 */
	public function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		$targetEiuFrame = $eiu->frame()->forkDiscover($this)
				->frame()->exec($this->getRelationModel()->getTargetReadEiCmdPath());
		
		return new TranslationQuickSearchProp(
				$this->getRelationModel()->getRelationEntityProperty(),
				$this->getRelationModel()->getTargetEiuEngine()->mask()->type()->getClass(), 
				$targetEiuFrame->getQuickSearchDefinition());
	}
	
	public function buildEiField(Eiu $eiu): ?EiField {
		$targetEiuFrame = $eiu->frame()->forkDiscover($eiu->prop(), $eiu->object())
				->frame()->exec($this->getRelationModel()->getTargetReadEiCmdPath());
		
		return new ToManyEiField($eiu, $targetEiuFrame, $this, $this->getRelationModel());
	}
}

class TranslationSortPropFork implements SortPropFork {
	private $translationEiProp;
	private $targetSortDefinition;
	private $sortN2nLocale;
	
	public function __construct(TranslationEiPropNature $translationEiProp, SortDefinition $targetSortDefinition,
			N2nLocale $sortN2nLocale) {
		$this->translationEiProp = $translationEiProp;
		$this->targetSortDefinition = $targetSortDefinition;
		$this->sortN2nLocale = $sortN2nLocale;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\translation\TranslationEiPropNature
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
	
	public function __construct(SortConstraint $forkedSortConstraint, TranslationSortPropFork $fork) {
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
