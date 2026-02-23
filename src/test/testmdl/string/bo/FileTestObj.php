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

namespace testmdl\string\bo;

use rocket\attribute\EiType;
use rocket\attribute\EiPreset;
use rocket\op\spec\setup\EiPresetMode;
use rocket\attribute\impl\EiPropFile;
use rocket\ui\gui\field\impl\file\ImageDimensionsImportMode;
use rocket\attribute\impl\EiPropImageFile;
use n2n\io\managed\File;
use n2n\persistence\orm\attribute\ManagedFile;
use n2n\io\managed\img\ImageDimension;

#[EiType]
#[EiPreset(EiPresetMode::EDIT_CMDS, editProps: ['file', 'annoatedFile', 'annoatedImageFile'])]
class FileTestObj {

	public int $id;

	#[ManagedFile]
	public ?File $file = null;

	#[ManagedFile]
	#[EiPropFile(1024, ['pdf', 'ods'], ['holeradio/huii'], ImageDimensionsImportMode::NONE,
			[new ImageDimension(230, 23, false, false)], false)]
	public ?File $annoatedFile = null;

	#[ManagedFile]
	#[EiPropImageFile(2048, ImageDimensionsImportMode::USED_ONLY, [new ImageDimension(240, 24, false, false)])]
	public ?File $annoatedImageFile = null;
}