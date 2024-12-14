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
namespace rocket\op\ei\manage\gui;

use rocket\op\ei\EiPropPath;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\DefPropPath;
use n2n\core\container\N2nContext;
use rocket\op\ei\component\prop\EiProp;
use rocket\ui\gui\UnresolvableDefPropPathException;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\ui\gui\field\GuiField;
use rocket\ui\gui\control\GuiControl;
use rocket\op\ei\component\command\EiCmd;
use rocket\op\ei\EiCmdPath;
use n2n\util\type\ArgUtils;
use rocket\op\ei\component\InvalidEiConfigurationException;
use n2n\util\type\TypeUtils;

class EiGuiCmdWrapper {

	function __construct(private EiCmdPath $eiCmdPath, private EiGuiCmd $eiGuiCmd) {
	}

	function getEiCmdPath(): EiCmdPath {
		return $this->eiCmdPath;
	}

	/**
	 * @param EiGuiDefinition $eiGuiDefinition
	 * @param EiFrame $eiFrame
	 * @return GuiControl[]
	 */
	function createGeneralGuiControls(EiGuiDefinition $eiGuiDefinition, EiFrame $eiFrame): array {
		$guiControls = $this->eiGuiCmd->createGeneralGuiControls(new Eiu($this->eiCmdPath, $eiGuiDefinition, $eiFrame));
		$this->valReturn($guiControls, 'createGeneralGuiControls');
		return $guiControls;
	}

	/**
	 * @param EiGuiDefinition $eiGuiDefinition
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @return GuiControl[]
	 */
	function createEntryGuiControls(EiGuiDefinition $eiGuiDefinition, EiFrame $eiFrame, EiEntry $eiEntry): array {
		$guiControls = $this->eiGuiCmd->createEntryGuiControls(new Eiu($this->eiCmdPath, $eiGuiDefinition, $eiFrame, $eiEntry));
		$this->valReturn($guiControls, 'createEntryGuiControls');
		return $guiControls;
	}

	private function valReturn(array $guiControls, string $methodName): void {
		ArgUtils::valArrayReturn($guiControls, $this->eiGuiCmd, $methodName);
		foreach ($guiControls as $key => $guiControl) {
			if (!EiPropPath::constainsSpecialIdChars($key)) {
				continue;
			}

			throw new EiGuiException(TypeUtils::prettyMethName(get_class($guiControl), $methodName)
					. ' return invalid GuiControl key: ' . $key);
		}
	}


	
}