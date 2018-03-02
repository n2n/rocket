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

use rocket\spec\ei\component\IndependentEiComponent;
use rocket\spec\ei\component\EiConfigurator;
use n2n\reflection\ReflectionUtils;
use n2n\util\config\Attributes;
use n2n\web\dispatch\mag\MagCollection;
use n2n\core\container\N2nContext;
use rocket\spec\ei\component\EiSetupProcess;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\impl\web\dispatch\mag\model\MagForm;
use rocket\spec\ei\component\EiComponent;
use rocket\spec\ei\manage\util\model\Eiu;

abstract class EiConfiguratorAdapter implements EiConfigurator {
	protected $eiComponent;
	protected $attributes;
	protected $reseted = false;
	
	public function __construct(IndependentEiComponent $eiComponent) {
		$this->eiComponent = $eiComponent;
		$this->attributes = new Attributes();
	}
	
// 	/* (non-PHPdoc)
// 	 * @see \rocket\spec\ei\component\EiConfigurator::getComponentClass()
// 	 */
// 	public function getComponentClass() {
// 		return new \ReflectionClass($this->eiComponent);
// 	}
	
	public function getEiComponent(): EiComponent {
		return $this->eiComponent;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\EiConfigurator::getAttributes()
	 */
	public function getAttributes(): Attributes {
		return $this->attributes;
	}
	
	public function setAttributes(Attributes $attributes) {
		$this->attributes = $attributes;
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\EiConfigurator::getTypeName()
	 */
	public function getTypeName(): string {
        return ReflectionUtils::prettyName((new \ReflectionClass($this->getEiComponent()))->getShortName());
	}
	
	public static function shortenTypeName($typeName, array $suffixes) {
		$nameParts = explode(' ', $typeName);
		while (null !== ($suffix = array_pop($suffixes))) {
			if (end($nameParts) != $suffix) break;
				
			array_pop($nameParts);
		}
	
		return implode(' ', $nameParts);
	}
	
	public function setup(EiSetupProcess $eiSetupProcess) {
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @return \rocket\spec\ei\manage\util\model\Eiu
	 */
	protected function eiu(N2nContext $n2nContext) {
		return new Eiu($this->eiComponent->getEiMask()->getEiEngine(), $n2nContext);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\EiConfigurator::createMagDispatchable($n2nContext)
	 */
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		return new MagForm(new MagCollection());
	}
	
	/**
	 * {@inheritDoc}
	 * 
	 * <p>Overwrite this method if you have custom attributes to save. If you call this method it will overwrite 
	 * the current attributes Properties with a new empty {@see Attributes} object</p
	 * 
	 * @see \rocket\spec\ei\component\EiConfigurator::saveMagDispatchable()
	 */
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		$this->attributes = new Attributes();
	}
}
