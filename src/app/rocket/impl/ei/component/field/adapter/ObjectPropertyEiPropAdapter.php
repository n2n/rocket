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
namespace rocket\impl\ei\component\field\adapter;

use n2n\reflection\property\AccessProxy;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\component\field\indepenent\EiPropConfigurator;

abstract class ObjectPropertyEiPropAdapter extends EntityPropertyEiPropAdapter implements ObjectPropertyConfigurable {
	protected $objectPropertyAccessProxy;
	protected $objectPropertyRequired = true;
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\ObjectPropertyEiProp::getPropertyAccessProxy()
	 */
	public function getObjectPropertyAccessProxy(bool $required = false) {
		if ($this->entityProperty === null && $required) {
			throw new IllegalStateException('No EntityProperty assigned to ' . $this);
		}
		
		return $this->objectPropertyAccessProxy;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $objectPropertyAccessProxy = null) {
		if ($objectPropertyAccessProxy === null && $this->objectPropertyRequired) {
			throw new \InvalidArgumentException($this . ' requires an object property AccessProxy.');
		}
		
		$this->objectPropertyAccessProxy = $objectPropertyAccessProxy;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\field\adapter\EntityPropertyEiPropAdapter::createEiConfigurator()
	 */
	public function createEiPropConfigurator(): EiPropConfigurator {
		$eiPropConfigurator = parent::createEiPropConfigurator();
		IllegalStateException::assertTrue($eiPropConfigurator instanceof AdaptableEiPropConfigurator);
		$eiPropConfigurator->registerObjectPropertyConfigurable($this);
		return $eiPropConfigurator;
	}
	
	public function getPropertyName(): string {
		return $this->objectPropertyAccessProxy->getPropertyName();
	}
	
// 	public function checkCompatibility(CompatibilityTest $compatibilityTest) {
// 		parent::checkCompatibility($compatibilityTest);
		
// 		if ($compatibilityTest->hasFailed()) return;
// 	}
}
