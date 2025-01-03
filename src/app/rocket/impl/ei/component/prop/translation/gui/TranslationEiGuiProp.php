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
namespace rocket\impl\ei\component\prop\translation\gui;

use rocket\op\ei\manage\gui\EiGuiPropSetup;
use rocket\op\ei\manage\gui\EiGuiField;
use rocket\op\ei\manage\gui\DisplayDefinition;
use rocket\op\ei\util\Eiu;
use rocket\ui\gui\field\GuiField;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\EiCmdPath;
use rocket\impl\ei\component\prop\translation\TranslationEiPropNature;
use rocket\op\ei\util\gui\EiuGuiDeclaration;
use rocket\op\ei\manage\gui\EiGuiProp;
use rocket\op\ei\util\gui\EiuGuiDefinition;
use rocket\ui\gui\GuiProp;
use rocket\op\ei\manage\gui\EiGuiPropMap;

class TranslationEiGuiProp implements EiGuiProp {

	
	function __construct(private EiuGuiDefinition $targetEiuGuiDefinition, private EiCmdPath $eiCmdPath,
			private TranslationEiPropNature $translationConfig) {
//		$this->targetEiuGuiMaskDeclaration = $targetEiuGuiDefinition->singleMaskDeclaration();
		$this->eiCmdPath = $eiCmdPath;
		$this->translationConfig = $translationConfig;
	}
	
	function getDisplayDefinition(): ?DisplayDefinition {
		return null;
	}

//	function getGuiProp(): GuiProp {
//		$guiProp = new GuiProp('Not required label');
//		$guiProp->setDescendantGuiPropNames($this->targetEiuGuiDefinition->getDefPropPaths())
//	}

	function getForkEiGuiPropMap(): ?EiGuiPropMap {
		return $this->targetEiuGuiDefinition->getEiGuiDefinition()->getEiGuiPropMap();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\gui\EiGuiPropSetup::getForkedDisplayDefinition()
	 */
	function getForkedDisplayDefinition(DefPropPath $defPropPath): ?DisplayDefinition {
		return $this->targetEiuGuiDefinition->getDisplayDefinition($defPropPath);
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		$targetEiu = $eiu->frame()->forkDiscover($eiu->prop()->getPath(), $eiu->object(), $this->targetEiuGuiDefinition);
		$targetEiu->frame()->exec($this->eiCmdPath);
		
		$lted = new LazyTranslationEssentialsDeterminer($eiu, $targetEiu, $this->translationConfig);
		$tgff = new SplitGuiFieldFactory($lted, $readOnly);
		
		return $tgff->createGuiField();
		
// 		$guiFieldMap = new GuiFieldMap();
// 		foreach ($this->targetEiuGuiMaskDeclaration->getEiPropPaths() as $eiPropPath) {
// 			$guiFieldMap->putGuiField($eiPropPath, $tgff->createGuiField($eiPropPath));
// 		}
		
// 		return new TranslationGuiField($lted, $guiFieldMap);
		
		// 		if ($this->copyCommand !== null) {
		// 			$translationGuiField->setCopyUrl($targetEiuFrame->getUrlToCommand($this->copyCommand)
		// 					->extR(null, array('bulky' => $eiu->guiFrame()->isBulky())));
		// 		}
	}
	
}