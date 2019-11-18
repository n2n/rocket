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

use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\GuiFieldAssembler;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ei\manage\gui\GuiFieldMap;
use rocket\ei\util\entry\EiuEntry;
use n2n\util\type\CastUtils;
use rocket\ei\util\gui\EiuEntryGui;
use n2n\l10n\N2nLocale;
use rocket\ei\EiPropPath;
use rocket\impl\ei\component\prop\translation\TranslationGuiField;
use rocket\impl\ei\component\prop\translation\conf\TranslationConfig;
use rocket\impl\ei\component\prop\translation\SplitGuiField;
use rocket\ei\manage\gui\GuiPropSetup;
use rocket\ei\util\gui\EiuGuiFrame;

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
	 * @var TranslationConfig
	 */
	private $translationConfig;
	
	/**
	 * @param GuiDefinition $guiDefinition
	 */
	function __construct(RelationModel $relationModel, TranslationConfig $translationConfig) {
		$this->forkGuiDefinition = $relationModel->getTargetEiuEngine()->getGuiDefinition();
		$this->relationModel = $relationModel;
		$this->translationConfig = $translationConfig;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiPropSetup()
	 */
	function buildGuiPropSetup(Eiu $eiu, ?array $forkedGuiPropPaths): ?GuiPropSetup {
		$forkEiuFrame = $eiu->frame()->forkDiscover($eiu->prop()->getPath());
		if ($eiu->guiFrame()->isReadOnly()) {
			$forkEiuFrame->exec($this->relationModel->getTargetReadEiCommandPath());
		} else {
			$forkEiuFrame->exec($this->relationModel->getTargetEditEiCommandPath());
		}
		
		$targetEiuGuiFrame = $forkEiuFrame->newGuiFrame($eiu->guiFrame()->getViewMode(), $forkedGuiPropPaths);
		
		return new TranslationGuiPropSetup($targetEiuGuiFrame, $this->translationConfig);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::getForkGuiDefinition()
	 */
	function getForkGuiDefinition(): ?GuiDefinition {
		return $this->forkGuiDefinition;
	}
}
