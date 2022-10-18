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
namespace rocket\ei\component\prop;

use rocket\ei\component\EiComponentCollection;
use rocket\ei\component\UnknownEiComponentException;
use rocket\ei\mask\EiMask;
use rocket\ei\EiPropPath;

class EiPropCollection extends EiComponentCollection {
	private array $eiPropPaths = array();
	
	/**
	 * @param EiMask $eiMask
	 */
	public function __construct(EiMask $eiMask) {
		parent::__construct('EiProp', EiPropNature::class);
		$this->setEiMask($eiMask);
	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @return EiPropNature
	 * @throws UnknownEiComponentException
	 */
	public function getByPath(EiPropPath $eiPropPath) {
		return $this->getElementByIdPath($eiPropPath);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return EiPropNature[]
	 */
	public function getForkedByPath(EiPropPath $eiPropPath) {
		return $this->getElementsByForkIdPath($eiPropPath);
	}
	
	/**
	 * @param EiPropNature $eiPropNature
	 * @param string $id
	 * @param EiPropPath $forkEiPropPath
	 * @return EiProp
	 */
	public function add(?string $id, EiPropNature $eiPropNature, EiPropPath $forkEiPropPath = null) {
		$id = $this->makeId($id, $eiPropNature);
		
		$eiPropPath = null;
		if ($forkEiPropPath === null) {
			$eiPropPath = new EiPropPath([$id]);
		} else {
			$eiPropPath = $forkEiPropPath->ext($id);
		}
		
		$eiPropNature = new EiProp($eiPropPath, $eiPropNature, $this);
		
		$this->addEiComponent($eiPropPath, $eiPropNature);
		
		return $eiPropNature;
	}

	/**
	 * @param bool $includeInherited
	 * @return EiPropNature[]
	 */
	function toArray(bool $includeInherited = true): array {
		return parent::toArray($includeInherited);
	}
}
