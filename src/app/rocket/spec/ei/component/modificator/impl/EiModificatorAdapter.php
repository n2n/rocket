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
namespace rocket\spec\ei\component\modificator\impl;

use rocket\spec\ei\component\modificator\EiModificator;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\component\impl\EiComponentAdapter;
use rocket\spec\ei\manage\gui\GuiDefinition;
use rocket\spec\ei\manage\mapping\MappingDefinition;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\gui\EiSelectionGui;
use rocket\spec\ei\manage\draft\DraftDefinition;

abstract class EiModificatorAdapter extends EiComponentAdapter implements EiModificator {
	
	public function setupEiState(EiState $eiState) {}
		
	public function setupEiMapping(EiState $eiState, EiMapping $eiMapping) {}
	
	public function setupGuiDefinition(GuiDefinition $guiDefinition) {}
	
	public function setupEiSelectionGui(EiSelectionGui $eiSelectionGui) {}
	
	public function setupDraftDefinition(DraftDefinition $draftDefinition) {}
}
