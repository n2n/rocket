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
namespace rocket\spec\ei;

use n2n\l10n\Lstr;

interface EiEngineModel {
	
	/**
	 * @return \n2n\l10n\Lstr
	 */
	public function getLabelLstr(): Lstr;
	
	/**
	 * @return \n2n\l10n\Lstr
	 */
	public function getPluralLabelLstr(): Lstr;
	
	/**
	 * @return string
	 */
	public function getIconType(): string;
	
	/**
	 * @return \rocket\spec\ei\EiEngine
	 */
	public function getEiEngine(): EiEngine;
	
	/**
	 * @return string
	 */
	public function getModuleNamespace(): string;
	
	/**
	 * @return string 
	 */
	public function __toString(): string;
}
