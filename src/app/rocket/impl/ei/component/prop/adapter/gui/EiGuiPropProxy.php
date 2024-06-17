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
namespace rocket\impl\ei\component\prop\adapter\gui;

use rocket\op\ei\util\Eiu;
use rocket\ui\gui\field\GuiField;
use rocket\op\ei\manage\gui\EiGuiDefinition;
use rocket\op\ei\manage\gui\EiGuiPropSetup;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;
use rocket\op\ei\manage\gui\EiGuiProp;

/**
 * Don't use this class directly. Use factory methods of {@see GuiFields}.  
 */
class EiGuiPropProxy implements EiGuiProp {
	private $guiPropSetupCallback;
	
	/**
	 * @param \Closure $closure
	 */
	function __construct(\Closure $guiPropSetupCallback) {
		$this->guiPropSetupCallback = new \ReflectionFunction($guiPropSetupCallback);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\gui\GuiProp::buildGuiPropSetup()
	 */
	function buildGuiPropSetup(Eiu $eiu, ?array $defPropPaths): ?EiGuiPropSetup {
		$mmi = new MagicMethodInvoker($eiu->getN2nContext());
		$mmi->setClassParamObject(Eiu::class, $eiu);
		$mmi->setParamValue('defPropPaths', $defPropPaths);
		$mmi->setReturnTypeConstraint(TypeConstraints::type(EiGuiPropSetup::class, true));
		
		return $mmi->invoke(null, $this->guiPropSetupCallback);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\gui\GuiProp::buildGuiField()
	 */
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		if ($this->eiGuiField !== null) {
			return $this->eiGuiField->buildGuiField($eiu, $readOnly);
		}
		
		if ($this->guiFieldClosure !== null) {
			
		}
		
		return null;
	}
	
	function getForkEiGuiDefinition(): ?EiGuiDefinition {
		return null;
	}
}
