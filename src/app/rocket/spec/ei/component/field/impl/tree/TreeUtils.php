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
namespace rocket\spec\ei\component\field\impl\tree;

use rocket\spec\ei\mask\EiMask;
use n2n\persistence\orm\property\EntityProperty;

class TreeUtils {
	
	public static function findNestedSetDef(EiMask $eiMask) {
		return null;
	}
}

class NestedSetDef {
	private $lftEntityProperty;
	private $rgtEntityProperty;
	
	public function __construct(EntityProperty $lftEntityProperty, EntityProperty $rgtEntityProperty) {
		$this->lftEntityProperty = $lftEntityProperty;
		$this->rgtEntityProperty = $rgtEntityProperty;
	}
	
	/**
	 * @return EntityProperty 
	 */
	public function getLftEntityProperty(): EntityProperty {
		return $this->lftEntityProperty;
	}
	
	/**
	 * @return EntityProperty
	 */
	public function getRgtEntityProperty(): EntityProperty {
		return $this->rgtEntityProperty;
	}
}
