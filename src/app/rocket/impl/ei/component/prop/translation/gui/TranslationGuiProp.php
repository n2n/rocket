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
use rocket\ei\manage\gui\GuiFieldMap;

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

		$tef = new TranslationEssentialsFactory($eiu, $forkEiuFrame, $this->translationConfig->getN2nLocaleDefs());

		$forkGuiPropPaths = $eiu->gui()->getForkGuiFieldPaths($eiu->prop()->getPath());
		$tef->init($forkGuiPropPaths);
		
		$guiFieldMap = new GuiFieldMap();
		foreach ($tef->getEiuGui()->getEiPropPaths() as $eiPropPath) {
			$guiFieldMap->putGuiField($eiPropPath, $tef->createSplitGuiField($eiPropPath));
		}
		
		return new TranslationGuiField($this->translationConfig->getMinNumTranslations(),
				$this->translationConfig->getN2nLocaleDefs(), $guiFieldMap);
		
		// 		if ($this->copyCommand !== null) {
		// 			$translationGuiField->setCopyUrl($targetEiuFrame->getUrlToCommand($this->copyCommand)
		// 					->extR(null, array('bulky' => $eiu->gui()->isBulky())));
		// 		}
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
	function createSplitGuiField(EiPropPath $eiPropPath) {
		$splitGuiField = new SplitGuiField();
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			
			$splitGuiField->putGuiField($n2nLocaleId, 
					$this->targetEiuEntryGuis[$n2nLocaleId]->getGuiFieldByEiFieldPath($eiPropPath));
		}
		return $splitGuiField;
	}
}
	
