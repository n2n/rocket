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

use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;

/**
 * Don't use this class directly. Use factory methods of {@see GuiFields}.  
 */
class GuiPropProxy implements GuiProp {
	private $eiu;
	private $displayConfig;
	private $guiFieldFactory;
	private $guiFieldClosure;
	
	/**
	 * @param Eiu $eiu
	 * @param DisplayConfig $displayConfig
	 * @param GuiFieldFactory $guiFieldFactory
	 */
	function __construct(Eiu $eiu, DisplayConfig $displayConfig, ?GuiFieldFactory $guiFieldFactory, ?\Closure $guiFieldClosure) {
		$this->eiu = $eiu;
		$this->displayConfig = $displayConfig;
		$this->guiFieldFactory = $guiFieldFactory;
		$this->guiFieldClosure = $guiFieldClosure;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildDisplayDefinition()
	 */
	function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
		return $this->displayConfig->buildDisplayDefinitionFromEiu($eiu);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiField()
	 */
	function buildGuiField(Eiu $eiu): ?GuiField {
		if ($this->guiFieldFactory !== null) {
			return $this->guiFieldFactory->buildGuiField($eiu);
		}
		
		if ($this->guiFieldClosure !== null) {
			$mmi = new MagicMethodInvoker($this->eiu->getN2nContext());
			$mmi->setClassParamObject(Eiu::class, $this->eiu);
			$mmi->setReturnTypeConstraint(TypeConstraints::type(GuiField::class, true));
			return $mmi->invoke(null, $this->guiFieldClosure);
		}
		
		return null;
	}
}
