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
// namespace rocket\impl\ei\component\prop\adapter\entry;

// use rocket\ei\manage\entry\EiFieldOperationFailedException;
// use n2n\reflection\property\AccessProxy;
// use rocket\ei\manage\entry\EiFieldMap;

// class PropertyEiField extends EiFieldAdapter {
// 	protected $objectPropertyAccessProxy;
// 	protected $eiFieldMap;
// 	protected $readOnly;
// 	private $readOnly = false;

// 	public function __construct(AccessProxy $objectPropertyAccessProxy, EiFieldMap $eiFieldMap, bool $readOnly) {
// 		parent::__construct($eiFieldMap, $this, ($readOnly ? null : $this), $this);
		
		
// 	}

// 	public function isReadable(): bool {
// 		return true;
// 	}

// 	protected function readValue() {
// 		$eiEntry = $this->eiFieldMap->getEiEntry();
		
// 		if (!$eiEntry->getEiObject()->isDraft()) {
// 			return $this->objectPropertyAccessProxy->getValue($this->eiFieldMap->getObject());
// 		}
		
// 		if (null !== $this->readable) {
// 			$value = $this->readable->read($this->eiFieldMap);
// 			// @todo convert exception to better exception
// 			return $value;
// 		}

// 		throw new EiFieldOperationFailedException('EiField is not readable.');
// 	}

// 	public function isWritable(): bool {
// 		return !$this->readOnly;
// 	}
	
// 	protected function writeValue($value) {
// 		if (null !== $this->writable) {
// 			$this->writable->write($this->eiFieldMap, $value);
// 			return;
// 		}

// 		throw new EiFieldOperationFailedException('EiField is not writable.');
// 	}
// }