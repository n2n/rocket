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
			
		$targetEiuEntries = $eiu->field()->getValue();
		
		$translationGuiField = new TranslationGuiField($toManyEiField, $targetGuiDefinition,
				$this->labelLstr->t($eiFrame->getN2nContext()->getN2nLocale()), $this->minNumTranslations);
		if ($this->copyCommand !== null) {
			$translationGuiField->setCopyUrl($targetEiuFrame->getUrlToCommand($this->copyCommand)
					->extR(null, array('bulky' => $eiu->gui()->isBulky())));
		}
		
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
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
	
}
