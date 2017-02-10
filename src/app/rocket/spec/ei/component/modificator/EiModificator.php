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
namespace rocket\spec\ei\component\modificator;

use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\component\EiComponent;
use rocket\spec\ei\manage\gui\GuiDefinition;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\gui\EiSelectionGui;
use rocket\spec\ei\manage\draft\DraftDefinition;
use rocket\spec\ei\manage\util\model\Eiu;

interface EiModificator extends EiComponent {
	
	/**
	 * @param EiState $eiState
	 */
	public function setupEiState(EiState $eiState);
	
	/**
	 * @param EiMapping $eiMapping
	 * @param Eiu $eiu
	 */
	public function setupEiMapping(Eiu $eiu);
	
	/**
	 * @param GuiDefinition $guiDefinition
	 */
	public function setupGuiDefinition(GuiDefinition $guiDefinition);
	
	/**
	 * @param EiSelectionGui $eiSelectionGui
	 */
	public function setupEiSelectionGui(EiSelectionGui $eiSelectionGui);
	
	/**
	 * @param DraftDefinition $draftDefinition
	 */
	public function setupDraftDefinition(DraftDefinition $draftDefinition); 
}
