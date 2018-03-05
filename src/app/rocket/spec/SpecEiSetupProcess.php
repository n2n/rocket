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
namespace rocket\spec;

use rocket\ei\component\IndependentEiComponent;
use n2n\core\container\N2nContext;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use rocket\ei\component\EiSetupProcess;
use rocket\ei\manage\util\model\Eiu;

class SpecEiSetupProcess implements EiSetupProcess {
	private $specManager;
	private $n2nContext;
	private $eiComponent;
	private $eiu;
	
	public function __construct(SpecManager $specManager, N2nContext $n2nContext, IndependentEiComponent $eiComponent) {
		$this->specManager = $specManager;
		$this->n2nContext = $n2nContext;
		$this->eiComponent = $eiComponent;
	}
	
// 	/**
// 	 * @return \rocket\spec\SpecManager
// 	 */
// 	public function getSpecManager() {
// 		return $this->specManager;
// 	}
	
	public function getN2nContext(): N2nContext {
		return $this->n2nContext;
	}
	
// 	/**
// 	 * @return EiDef
// 	 */
// 	public function getEiDef() {
// 		if (null !== ($eiMask = $this->eiComponent->getEiMask()->getEiEngine()->getEiMask())) {
// 			return $eiMask->getEiDef();
// 		}
// 		return $this->eiComponent->getEiMask()->getEiEngine()->getEiMask()->getEiType()->getDefaultEiDef();
// 	}

// 	public function getSupremeEiDef() {
// 		$supremeEiType = $this->eiComponent->getEiMask()->getEiEngine()->getEiMask()->getEiType()->getSupremeEiType();
// 		if (null !== ($eiMask = $this->eiComponent->getEiMask()->getEiEngine()->getEiMask())) {
// 			return $eiMask->determineEiMask($supremeEiType)->getEiDef();
// 		}
// 		return $supremeEiType->getDefaultEiDef();
// 	}

	public function createException(string $reason = null, \Exception $previous = null): InvalidEiComponentConfigurationException {
		$message = $this->eiComponent . ' invalid configured.';
							
		return new InvalidEiComponentConfigurationException($message 
				. ($reason !== null ? ' Reason: ' . $reason : ''), 0, $previous);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiSetupProcess::eiu()
	 */
	public function eiu(): Eiu {
		return $this->eiu 
				?? $this->eiu = new Eiu($this->specManager, $this->eiComponent->getEiMask()->getEiEngine(), $this->n2nContext);
	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\component\EiSetupProcess::containsClass($class)
// 	 */
// 	public function containsClass(\ReflectionClass $class): bool {
// 		return $this->specManager->containsEiTypeClass($class);
// 	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\component\EiSetupProcess::getEiTypeByClass($class)
// 	 */
// 	public function getEiTypeByClass(\ReflectionClass $class): EiType {
// 		return $this->specManager->getEiTypeByClass($class);
// 	}

// 	public function getEiPropCollection(): EiPropCollection {
// 		return $this->eiComponent->getEiMask()->getEiEngine()->getEiMask()->getEiPropCollection();
// 	}


// 	public function getEiCommandCollection(): EiCommandCollection {
// 		return $this->eiComponent->getEiMask()->getEiEngine()->getEiCommandCollection();
// 	}


// 	public function getEiModificatorCollection(): EiModificatorCollection {
// 		return $this->eiComponent->getEiMask()->getEiEngine()->getEiModificatorCollection();
// 	}

// 	public function getGenericEiPropertyByEiPropPath($eiPropPath): GenericEiProperty {
// 		return $this->eiComponent->getEiMask()->getEiEngine()->getGenericEiDefinition()
// 				->getGenericEiPropertyByEiPropPath($eiPropPath);
// 	}

// 	public function getScalarEiPropertyByFieldPath($eiPropPath): ScalarEiProperty {
// 		return $this->eiComponent->getEiMask()->getEiEngine()->getScalarEiDefinition()
// 				->getScalarEiPropertyByFieldPath($eiPropPath);
// 	}

}
