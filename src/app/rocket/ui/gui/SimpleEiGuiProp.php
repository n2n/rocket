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
namespace rocket\ui\gui;

use rocket\op\ei\manage\DefPropPath;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\gui\DisplayDefinition;
use rocket\op\ei\manage\gui\EiGuiField;
use rocket\op\ei\manage\gui\EiGuiPropSetup;
use rocket\op\ei\manage\gui\EiGuiProp;
use rocket\op\ei\util\Eiu;
use rocket\ui\gui\field\GuiField;
use n2n\util\ex\NotYetImplementedException;


class SimpleEiGuiProp implements EiGuiProp {
	private EiGui $eiGuiField;
	private $displayDefinition;
	private $forkedDisplayDefinitions;
	
	function __construct(EiGuiField $eiGuiField, ?DisplayDefinition $displayDefinition,
			array $forkedDisplayDefinitions) {
		$this->eiGuiField = $eiGuiField;
		$this->displayDefinition = $displayDefinition;
		ArgUtils::valArray($forkedDisplayDefinitions, DisplayDefinition::class, 'forkedDisplayDefinitions');
		$this->forkedDisplayDefinitions = $forkedDisplayDefinitions;
	}


	function buildGuiField(Eiu $eiu, ?array $defPropPaths): ?GuiField {
		throw new NotYetImplementedException();
	}

	public function getDisplayDefinition(): DisplayDefinition {
		return $this->displayDefinition;
	}
	
	public function getForkedDisplayDefinition(DefPropPath $defPropPath): ?DisplayDefinition {
		return $this->forkedDisplayDefinitions[(string) $defPropPath] ?? null;
	}

	
}