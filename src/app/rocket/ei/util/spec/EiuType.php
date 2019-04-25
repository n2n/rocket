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
namespace rocket\ei\util\spec;

use rocket\ei\util\EiuAnalyst;
use rocket\ei\EiType;
use rocket\ei\util\entry\EiuObject;
use rocket\user\model\LoginContext;
use n2n\util\type\CastUtils;

class EiuType  {
	private $eiType;
	private $eiuMask;
	private $eiuAnalyst;
	private $supremeEiuType = null;
	private $allSubEiuTypes = null;
	
	/**
	 * @param EiType $eiType
	 * @param EiuMask $eiuMask
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(EiType $eiType, EiuAnalyst $eiuAnalyst) {
		$this->eiType = $eiType;
		$this->eiuAnalyst = $eiuAnalyst;
	}

	/**
	 * @return string
	 */
	function getId() {
		return $this->eiType->getId();
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuMask
	 */
	function mask() {
		if ($this->eiuMask !== null) {
			return $this->eiuMask;
		}
		
		return $this->eiuMask = new EiuMask($this->eiType->getEiMask(), $this->eiuAnalyst);
	}
	
	/**
	 * @return boolean
	 */
	function isAbstract() {
		return $this->eiType->isAbstract();
	}
	
	/**
	 * @param bool $draft
	 * @return \rocket\ei\util\entry\EiuObject
	 */
	function newObject(bool $draft = false) {
		$eiObject = $this->eiType->createNewEiObject($draft);
		
		if ($draft) {
			$loginContext = $this->eiuAnalyst->getN2nContext(true)->lookup(LoginContext::class);
			CastUtils::assertTrue($loginContext instanceof LoginContext);
			
			$eiObject->getDraft()->setUserId($loginContext->getCurrentUser()->getId());
		}
		
		return new EiuObject($eiObject, $this->eiuAnalyst);
	}
	
	
	/**
	 * @return EiuType 
	 */
	function supremeType() {
		if ($this->supremeEiuType !== null) {
			return $this->supremeEiuType;
		}
		
		if (!$this->eiType->hasSuperEiType()) {
			return $this->supremeEiuType = $this;
		}
		
		return $this->supremeEiuType = new EiuType($this->eiType->getSupremeEiType(), $this->eiuAnalyst);
	}
	
	/**
	 * @return EiuType[]
	 */
	function allSubTypes() {
		if ($this->allSubEiuTypes !== null) {
			return $this->allSubEiuTypes;
		}
		
		$this->allSubEiuTypes = [];
		foreach ($this->eiType->getAllSubEiTypes() as $eiType) {
			$this->allSubEiuTypes[] = new EiuType($eiType, $this->eiuAnalyst);
		}
		return $this->allSubEiuTypes;
	}
}