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
namespace rocket\impl\ei\component;

use n2n\reflection\ReflectionUtils;
use rocket\ei\component\EiComponent;
use n2n\util\ex\IllegalStateException;
use rocket\ei\mask\EiMask;

abstract class EiComponentAdapter implements EiComponent {
	protected $eiMask;
	protected $id;
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponent::getEiMask()
	 */
	public function getEiMask(): EiMask {
		if ($this->eiMask !== null) {
			return $this->eiMask;
		}
		
		throw new IllegalStateException(get_class($this) . ' is not assigned to an EiMask.');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponent::setEiMask()
	 */
	public function setEiMask(EiMask $eiMask) {
		$this->eiMask = $eiMask;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponent::getId()
	 */
	public function getId(): ?string {
		return $this->id;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponent::setId()
	 */
	public function setId(string $id) {
		$this->id = $id;
	}
	
	public function getIdBase() {
		return ReflectionUtils::buildTypeAcronym(get_class($this));
	}
	
	public function equals($obj) {
		return $obj instanceof EiComponent && $this->id == $obj->getId();
	}
	
	public function __toString(): string {
		return (new \ReflectionClass($this))->getShortName() . ' (id: ' . $this->id . ')';
	}
}
