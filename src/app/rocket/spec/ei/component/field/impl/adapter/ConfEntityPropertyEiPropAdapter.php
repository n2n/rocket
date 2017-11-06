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
namespace rocket\spec\ei\component\field\impl\adapter;

use n2n\persistence\orm\property\EntityProperty;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\field\indepenent\EiPropConfigurator;

abstract class ConfEntityPropertyEiPropAdapter extends IndependentEiPropAdapter implements ConfEntityPropertyEiProp {
	protected $entityProperty;
	protected $entityPropertyRequired = true;
	
	public function getIdBase() {
		return $this->entityProperty->getName();
	}
	
	/**
	 * @param EntityProperty $entityProperty
	 * @throws \InvalidArgumentException
	 */
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		if ($entityProperty === null && $this->entityPropertyRequired) {
			throw new \InvalidArgumentException($this . ' requires an EntityProperty.');
		}
		
		$this->entityProperty = $entityProperty;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\impl\adapter\ConfEntityPropertyEiProp::getEntityProperty()
	 */
	public function getEntityProperty(bool $required = false) {
		if ($this->entityProperty === null && $required) {
			throw new IllegalStateException('No EntityProperty assigned to ' . $this);
		}
		
		return $this->entityProperty;
	}
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		$eiPropConfigurator = parent::createEiPropConfigurator();
		IllegalStateException::assertTrue($eiPropConfigurator instanceof AdaptableEiPropConfigurator);
		$eiPropConfigurator->registerConfEntityPropertyEiProp($this);
		return $eiPropConfigurator;
	}
	
}
