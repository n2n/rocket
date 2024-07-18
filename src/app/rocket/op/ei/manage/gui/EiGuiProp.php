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

use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\DefPropPath;
use rocket\ui\gui\field\GuiField;
use rocket\ui\gui\GuiProp;

interface EiGuiProp {

	function getGuiProp(): GuiProp;

	/**
	 * <p>Tests if this GuiProp is compatible with the passed EiGuiMaskDeclaration and returns an {@see DisplayDefinition}
	 * if it does. Use <code>$eiu->guiFrame()</code> to access the {@see \rocket\op\ei\util\gui\EiuGuiMaskDeclaration}
	 * object.<p>
	 *
	 * @return DisplayDefinition|null return null if this GuiProp is not compatible with passed EiGuiMaskDeclaration.
	 */
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField;

	function getForkEiGuiPropMap(): ?EiGuiPropMap;

	function getDisplayDefinition(): ?DisplayDefinition;

	/**
	 * @param DefPropPath $defPropPath
	 * @return DisplayDefinition|NULL
	 */
	function getForkedDisplayDefinition(DefPropPath $defPropPath): ?DisplayDefinition;
}