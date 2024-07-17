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

namespace testmdl\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiModCallback;
use rocket\attribute\impl\EiSetup;
use rocket\op\ei\util\Eiu;
use rocket\attribute\impl\EiPropBool;
use rocket\attribute\impl\EiPropEnum;
use rocket\attribute\impl\EiPropDecimal;
use rocket\attribute\impl\EiPropString;

#[EiType]
#[EiPreset(EiPresetMode::READ, excludeProps: ['exclProp'])]
class AnnotatedReadPresetTestObj {

	public int $id;

	public string $exclProp;

	#[EiPropBool(onGuiProps: ['on1', 'on2'], offGuiProps: ['off1', 'off2'])]
	public bool $pubBoolTest = false;

	#[EiPropEnum(['big' => 'Big', 'super-big' => 'Super Big'], emptyLabel: 'No selection',
			guiPropsMap: ['big' => ['big1', 'big2'], 'super-big' => ['superBig1', 'superBig2']])]
	public ?string $pubEnumTest = null;

	#[EiPropDecimal(decimalPlaces: 3)]
	public float $pubDecimalTest = 0;

	#[EiPropString(multiline: true)]
	public string $pubStringTest = 'string';
}

