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
namespace rocket\spec\config;

use rocket\spec\ei\component\IndependentEiComponent;
use n2n\core\container\N2nContext;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use rocket\spec\ei\EiType;
use rocket\spec\ei\component\EiSetupProcess;
use rocket\spec\ei\component\prop\EiPropCollection;
use rocket\spec\ei\component\command\EiCommandCollection;
use rocket\spec\ei\component\modificator\EiModificatorCollection;
use rocket\spec\ei\manage\generic\GenericEiProperty;
use rocket\spec\ei\manage\generic\ScalarEiProperty;
use rocket\spec\ei\EiDef;

class SpecEiSetupProcess implements EiSetupProcess {
	private $specManager;
	private $n2nContext;
	private $eiComponent;
	
	public function __construct(SpecManager $specManager, N2nContext $n2nContext, IndependentEiComponent $eiComponent) {
		$this->specManager = $specManager;
		$this->n2nContext = $n2nContext;
		$this->eiComponent = $eiComponent;
	}
	
// 	/**
// 	 * @return \rocket\spec\config\SpecManager
// 	 */
// 	public function getSpecManager() {
// 		return $this->specManager;
// 	}
	
	public function getN2nContext(): N2nContext {
		return $this->n2nContext;
	}
	
	/**
	 * @return EiDef
	 */
	public function getEiDef() {
		if (null !== ($eiMask = $this->eiComponent->getEiEngine()->getEiMask())) {
			return $eiMask->getEiDef();
		}
		return $this->eiComponent->getEiEngine()->getEiType()->getDefaultEiDef();
	}
	
	public function getSupremeEiDef() {
		$supremeEiType = $this->eiComponent->getEiEngine()->getEiType()->getSupremeEiType();
		
		if (null !== ($eiMask = $this->eiComponent->getEiEngine()->getEiMask())) {
			return $eiMask->determineEiMask($supremeEiType)->getEiDef();
		}
		return $supremeEiType->getDefaultEiDef();
	}
	
	public function createException($reason = null, \Exception $previous = null): InvalidEiComponentConfigurationException {
		$message = $this->eiComponent . ' invalid configured.';
							
		return new InvalidEiComponentConfigurationException($message 
				. ($reason !== null ? ' Reason: ' . $reason : ''), 0, $previous);
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\EiSetupProcess::containsClass($class)
	 */
	public function containsClass(\ReflectionClass $class): bool {
		return $this->specManager->containsEiTypeClass($class);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\EiSetupProcess::getEiTypeByClass($class)
	 */
	public function getEiTypeByClass(\ReflectionClass $class): EiType {
		return $this->specManager->getEiTypeByClass($class);
	}

	public function getEiPropCollection(): EiPropCollection {
		return $this->eiComponent->getEiEngine()->getEiPropCollection();
	}
	
	public function getEiCommandCollection(): EiCommandCollection {
		return $this->eiComponent->getEiEngine()->getEiCommandCollection();
	}
	
	public function getEiModificatorCollection(): EiModificatorCollection {
		return $this->eiComponent->getEiEngine()->getEiModificatorCollection();
	}
	
	public function getGenericEiPropertyByEiPropPath($eiPropPath): GenericEiProperty {
		return $this->eiComponent->getEiEngine()->getGenericEiDefinition()
				->getGenericEiPropertyByEiPropPath($eiPropPath);
	}
	
	public function getScalarEiPropertyByFieldPath($eiPropPath): ScalarEiProperty {
		return $this->eiComponent->getEiEngine()->getScalarEiDefinition()
				->getScalarEiPropertyByFieldPath($eiPropPath);
	}
}
