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

namespace rocket\op\ei\util\entry;

use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\EiObject;
use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\component\prop\EiPropNature;
use rocket\ui\si\content\SiEntryQualifier;
use rocket\op\ei\component\prop\EiProp;
use n2n\reflection\property\PropertyAccessException;
use n2n\reflection\property\AccessProxy;
use rocket\op\ei\util\EiuPerimeterException;

class EiuObject {
	private $eiObject;
	private $eiuAnalyst;
	
	/**
	 * @param EiObject $eiObject
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiObject $eiObject, EiuAnalyst $eiuAnalyst) {
		$this->eiObject = $eiObject;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return boolean
	 */
	public function isNew() {
		return $this->eiObject->isNew();
	}
	
	/**
	 * @param bool $required
	 * @return NULL|string
	 */
	public function getPid(bool $required = true) {
		$eiEntityObj = $this->eiObject->getEiEntityObj();
		
		if (!$required && !$eiEntityObj->hasId()) {
			return null;
		}
		
		return $eiEntityObj->getPid();
	}
	
	/**
	 * @return EiObject
	 */
	public function getEiObject() {
		return $this->eiObject;
	}

	public function getEntityObj(): mixed {
		return $this->eiObject->getEiEntityObj()->getEntityObj();
	}
	
	/**
	 * @return \rocket\op\ei\EiType
	 */
	public function getEiType() {
		return $this->eiObject->getEiEntityObj()->getEiType();
	}
	
//	/**
//	 * @param EiProp $eiProp
//	 * @return boolean
//	 */
//	public function isDraftProp(EiProp $eiProp) {
//		return $this->eiObject->isDraft()
//				&& $eiProp->getWrapper()->getEiPropCollection()->getEiMask()->getEiEngine()->getDraftDefinition()
//						->containsEiPropPath(EiPropPath::from($eiProp));
//	}
	
	/**
	 * @param EiPropNature $eiProp
	 * @return object
	 */
	public function getForkObject(EiProp $eiProp) {
		$eiPropPath = $eiProp->getEiPropPath();
		return $eiProp->getEiPropCollection()->getEiMask()->getForkObject($eiPropPath->poped(), $this->eiObject);
	}

	private function getEiProp(EiProp|EiPropPath|string|null $arg = null) {
		if ($arg === null) {
			$arg = $this->eiuAnalyst->getEiPropPath(true);
		}

		if (!($arg instanceof EiProp)) {
			$arg = $this->getEiType()->getEiMask()->getEiPropCollection()->getByPath(EiPropPath::create($arg));
		}

		return $arg;
	}

	/**
	 * @param EiProp|EiPropPath|string|null $eiProp
	 * @return mixed
	 * @throws PropertyAccessException
	 */
	public function readNativeValue(EiProp|EiPropPath|string|null $eiProp = null): mixed {
		$eiProp = $this->getEiProp($eiProp);

		try {
			return $this->getNativeAccessProxy($eiProp, true)->getValue($this->getForkObject($eiProp));
		} catch (PropertyAccessException $e) {
			throw new EiuPerimeterException('Could not read from native AccessProxy of ' . $eiProp,
					previous: $e);
		}
	}
	
	/**
	 * @param EiProp $eiProp
	 * @return bool
	 */
	public function isNativeWritable(EiProp $eiProp): bool {
		return (bool) $this->getNativeAccessProxy($eiProp, false)?->isWritable();
	}

	/**
	 * @param EiProp $eiProp
	 * @param bool $required
	 * @return AccessProxy|null
	 */
	private function getNativeAccessProxy(EiProp $eiProp, bool $required): ?AccessProxy {
		$nativeAccessProxy = $eiProp->getNature()->getNativeAccessProxy();
		if (!$required || $nativeAccessProxy !== null) {
			return $nativeAccessProxy;
		}
		
		throw new EiuPerimeterException('There is no native AccessProxy configured for ' . $eiProp);
	}

	/**
	 * @param mixed $value
	 * @param EiProp|EiPropPath|string|null $eiProp
	 * @return EiuObject
	 * @throws PropertyAccessException
	 */
	public function writeNativeValue(mixed $value, EiProp|EiPropPath|string|null $eiProp = null): static {
		$eiProp = $this->getEiProp($eiProp);

		$this->getNativeAccessProxy($eiProp, true)->setValue($this->getForkObject($eiProp), $value);
		return $this;
	}
	
	/**
	 * @return EiuEntry
	 */
	public function newEntry(): EiuEntry {
		$this->eiuAnalyst->getEiFrame(true);
		return new EiuEntry(null, $this, null, $this->eiuAnalyst);
	}
	
	/**
	 * @return string
	 */
	function createIdentityString(): string {
		return $this->eiuAnalyst->getEiuFrame(true)->engine()->createIdentityString($this->eiObject);
	}
	
	/**
	 * @param string $name
	 * @return SiEntryQualifier
	 */
	function createSiEntryQualifier(?string $name = null): SiEntryQualifier {
		$name = $name ?? $this->createIdentityString();
		
		$siMaskQualifier = $this->eiuAnalyst->getEiuFrame(true)->mask($this->eiObject)->createSiMaskQualifier();
		
		return $this->eiObject->createSiEntryIdentifier()->toQualifier($siMaskQualifier, $name);
	}
}