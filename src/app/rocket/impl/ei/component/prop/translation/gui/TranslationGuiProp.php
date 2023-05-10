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

use rocket\op\ei\manage\gui\GuiDefinition;
use rocket\op\ei\manage\gui\GuiProp;
use rocket\op\ei\util\Eiu;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\translation\conf\TranslationConfig;
use rocket\op\ei\manage\gui\GuiPropSetup;
use rocket\impl\ei\component\prop\translation\TranslationEiPropNature;

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
	function __construct(RelationModel $relationModel, TranslationEiPropNature $translationConfig) {
		$this->forkGuiDefinition = $relationModel->getTargetEiuEngine()->getGuiDefinition();
		$this->relationModel = $relationModel;
		$this->translationConfig = $translationConfig;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\gui\GuiProp::buildGuiPropSetup()
	 */
	function buildGuiPropSetup(Eiu $eiu, ?array $forkedDefPropPaths): ?GuiPropSetup {
		$targetEiuGuiFrame = $this->relationModel->getTargetEiuEngine()->newGuiFrame($eiu->guiFrame()->getViewMode(), $forkedDefPropPaths);
		$eiCmdPath = null;
		if ($eiu->guiFrame()->isReadOnly()) {
			$eiCmdPath = $this->relationModel->getTargetReadEiCmdPath();
		} else {
			$eiCmdPath = $this->relationModel->getTargetEditEiCmdPath();
		}
		
		return new TranslationGuiPropSetup($targetEiuGuiFrame, $eiCmdPath, $this->translationConfig);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\manage\gui\GuiProp::getForkGuiDefinition()
	 */
	function getForkGuiDefinition(): ?GuiDefinition {
		return $this->forkGuiDefinition;
	}
}
