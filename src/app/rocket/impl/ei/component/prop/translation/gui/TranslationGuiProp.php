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

use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\relation\conf\RelationConfig;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
<<<<<<< HEAD
use n2n\util\type\CastUtils;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\util\frame\EiuFrame;
use rocket\impl\ei\component\prop\translation\conf\TranslationConfig;
use rocket\impl\ei\component\prop\translation\conf\N2nLocaleDef;
use rocket\ei\util\gui\EiuEntryGui;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\EiPropPath;
=======
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\manage\gui\GuiFieldMap;
>>>>>>> branch '3.0.x' of git@github.com:n2n/rocket.git

class TranslationGuiProp implements GuiProp {
	/**
	 * @var GuiDefinition
	 */
	private $forkGuiDefinition;

	/**
	 * @var RelationModel
	 */
	private $relationModel;
	
	/**
	 * @param GuiDefinition $guiDefinition
	 */
	function __construct(GuiDefinition $forkGuiDefinition, RelationConfig $relationModel) {
		$this->forkGuiDefinition = $forkGuiDefinition;
		$this->relationModel = $relationModel;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildDisplayDefinition()
	 */
	function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::getForkGuiDefinition()
	 */
	function getForkGuiDefinition(): ?GuiDefinition {
		return $this->forkGuiDefinition;
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		$forkEiuFrame = $eiu->frame()->forkDiscover($eiu->prop()->getPath());
		if ($eiu->gui()->isReadOnly()) {
			$forkEiuFrame->exec($this->relationModel->getTargetReadEiCommandPath());
		} else {
			$forkEiuFrame->exec($this->relationModel->getTargetEditEiCommandPath());
		}
			
<<<<<<< HEAD
		$tef = new TranslationEssentialsFactory($eiu, $forkEiuFrame, $this->translationConfig);
=======
		
>>>>>>> branch '3.0.x' of git@github.com:n2n/rocket.git
		
		$translationGuiField = new TranslationGuiField($toManyEiField, $targetGuiDefinition,
				$this->labelLstr->t($eiFrame->getN2nContext()->getN2nLocale()), $this->minNumTranslations);
		if ($this->copyCommand !== null) {
			$translationGuiField->setCopyUrl($targetEiuFrame->getUrlToCommand($this->copyCommand)
					->extR(null, array('bulky' => $eiu->gui()->isBulky())));
		}
		
<<<<<<< HEAD
		
		$targetEiuEntries = $tef->deterTargetEiuEntries();
		
		$tef->initTargetGui();
		
		
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			
		}
=======
		$forkGuiPropPaths = $eiu->gui()->getForkGuiFieldPaths($eiu->prop()->getPath());
		$eiuGui = $forkEiuFrame->newGui($eiu->gui()->getViewMode(), $forkGuiPropPaths);
>>>>>>> branch '3.0.x' of git@github.com:n2n/rocket.git
		
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
<<<<<<< HEAD
		
			$
		
=======
			
			$eiuGui->$tragetEiuEntries[$n2nLocaleId] 
			$viewMode = null;
			$targetRelationEntry = null;
			if (isset($targetRelationEntries[$n2nLocaleId])) {
				$targetRelationEntry = $targetRelationEntries[$n2nLocaleId];
			} else {
				$eiObject = $targetEiuFrame->createNewEiObject();
				$eiObject->getLiveObject()->setN2nLocale($n2nLocaleDef->getN2nLocale());
				$targetRelationEntry = RelationEntry::fromM($targetEiuFrame->entry($eiObject)->getEiEntry());
			}
>>>>>>> branch '3.0.x' of git@github.com:n2n/rocket.git
			
			$viewMode = ViewMode::determine($eiu->gui()->isBulky(), $eiu->gui()->isReadOnly(),
					$targetRelationEntry->getEiEntry()->isNew());
			$targetEiuEntryGuiAssembler = $targetEiuFrame->entry($targetRelationEntry->getEiEntry())
					->newEntryGuiAssembler($eiu->gui()->getViewMode());
			
			$translationGuiField->registerN2nLocale($n2nLocaleDef, $targetRelationEntry,
					$targetEiuEntryGuiAssembler, $n2nLocaleDef->isMandatory(),
					isset($targetRelationEntries[$n2nLocaleId]));
		}
		
		$translationGuiField = new TranslationGuiField();
		
		$translationGuiFieldMap = new GuiFieldMap($forkGuiFieldPath);
		foreach ($forkEiPropPaths as $eiPropPath) {
			$splitGuiField = new SplitGuiField();
			foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
				$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
				$splitGuiField->putGuiField($n2nLocaleDef->getN2nLocale(), 
						$targetEiuEntryGuis[$n2nLocaleId]->getGuiFieldByEiPropPath($eiPropPath));
			}
			
			$translationGuiFieldMap->putGuiField($translationGuiField, $splitGuiField);
		}
		
		return $translationGuiField;
	}
	private function buildEiEntryGuis() {
		$translationGuiField = new TranslationGuiField()
		
		$targetEiuEntries = $translationGuiField->deterTargetEiuEntries();
		
		foreach ($targetEiuEntries as $targetEiuEntry) {
			$targetEiuEntry->
		}
		
		$this->
		
		
	}
	
	
	
	/**
	 * @return EiuEntry[] 
	 */
	private function determineEiuEntries() {
		foreach ($eiu->field()->getValue() as $targetEiuEntry) {
			$target
		}
	}
	
}

class TranslationEssentialsFactory {
	private $eiu;
	private $forkEiuFrame;
	private $translationConfig;
	
	private $targetEiuGui;
	private $targetEiuEntryGuis;
	
	function __construct(Eiu $eiu, EiuFrame $forkEiuFrame, TranslationConfig $translationConfig) {
		$this->eiu = $eiu;
		$this->forkEiuFrame = $forkEiuFrame;
		$this->translationConfig = $translationConfig;
	}
	
	/**
	 * @return EiuEntry[] 
	 */
	private function deterTargetEiuEntries() {
		$mappedValues = [];
		foreach ($this->eiu->field()->getValue() as $targetEiuEntry) {
			CastUtils::assertTrue($targetEiuEntry instanceof EiuEntry);
			
			$mappedValues[(string) $targetEiuEntry->getEntityObj()->getN2nLocale()] = $targetEiuEntry;
		}
		
		$targetEiuEntries = [];
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			
			$targetEiuEntries[$n2nLocaleId] = $mappedValues[$n2nLocaleId] 
					?? $this->createTargetEiuEntry($n2nLocaleDef);
		}
		return $targetEiuEntries;
	}
	
	/**
	 * @return EiuEntry[]
	 */
	function init(array $forkGuiFieldPaths) {
		$this->targetEiuGui = $this->forkEiuFrame->newGui($this->eiu->gui()->getViewMode(), $forkGuiFieldPaths);
		$targetEiuEntries = $this->deterTargetEiuEntries();
		
		$this->targetEiEntryGuis = [];
		foreach ($targetEiuEntries as $n2nLocaleId => $targetEiuEntry) {
			$this->targetEiEntryGuis[$n2nLocaleId] = $this->targetEiuGui->appendNewEntryGui($targetEiuEntry);
		}
		return $targetEiuEntries;
	}
	
	/**
	 * @param N2nLocaleDef $n2nLocaleDef
	 * @return EiuEntry
	 */
	private function createTargetEiuEntry($n2nLocaleDef) {
		$targetEiuEntry = $this->forkEiuFrame->newEntry();
		$targetEiuEntry->getEntityObj()->setN2nLocale($n2nLocaleDef->getN2nLocale());
		return $targetEiuEntry;
	}
	
	/**
	 * @param EiPropPath $eiFieldPath
	 * @return EiuEntryGui[] 
	 */
	function createSplitGuiField(EiPropPath $eiFieldPath) {
		$splitGuiField = new SplitGuiField();
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			
			$splitGuiField->putGuiField($n2nLocaleId, 
					$this->targetEiuEntryGuis[$n2nLocaleId]->getGuiFieldByEiFieldPath($eiFieldPath));
		}
		return $splitGuiField;
	}
}
	
