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
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\GuiFieldAssembler;
use rocket\ei\manage\gui\GuiPropSetups;
use rocket\ei\manage\gui\GuiPropSetup;

/**
 * Don't use this class directly. Use factory methods of {@see GuiFields}.  
 */
class GuiPropProxy implements GuiProp {
	private $displayConfig;
	private $guiFieldAssembler;
	private $guiFieldClosure;
	
	/**
	 * @param Eiu $eiu
	 * @param DisplayConfig $displayConfig
	 * @param GuiFieldFactory $guiFieldFactory
	 */
	function __construct(DisplayConfig $displayConfig, ?GuiFieldAssembler $guiFieldAssembler, ?\Closure $guiFieldClosure) {
		$this->displayConfig = $displayConfig;
		$this->guiFieldAssembler = $guiFieldAssembler;
		
		if ($guiFieldClosure !== null) {
			$this->guiFieldAssembler = $this->createAssemblerFromClosure($guiFieldClosure);
		}
	}
	
	/**
	 * @param \Closure $guiFieldClosure
	 * @return GuiFieldAssembler
	 */
	private function createAssemblerFromClosure($guiFieldClosure) {
		return new class($guiFieldClosure) implements GuiFieldAssembler {
			private $guiFieldClosure;
			
			function __construct($guiFieldClosure) {
				$this->guiFieldClosure = $guiFieldClosure;
			}
			
			function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
				$mmi = new MagicMethodInvoker($eiu->getN2nContext());
				$mmi->setClassParamObject(Eiu::class, $eiu);
				$mmi->setParamValue('readOnly', $readOnly);
				$mmi->setReturnTypeConstraint(TypeConstraints::type(GuiField::class, true));
				return $mmi->invoke(null, $this->guiFieldClosure);
			}
		};
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiPropSetup()
	 */
	function buildGuiPropSetup(Eiu $eiu, ?array $defPropPaths): ?GuiPropSetup {
		$displayDefinition = $this->displayConfig->buildDisplayDefinitionFromEiu($eiu);
		
		return GuiPropSetups::simple($this->guiFieldAssembler, $displayDefinition);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiField()
	 */
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		if ($this->guiFieldAssembler !== null) {
			return $this->guiFieldAssembler->buildGuiField($eiu, $readOnly);
		}
		
		if ($this->guiFieldClosure !== null) {
			
		}
		
		return null;
	}
	
	function getForkGuiDefinition(): ?GuiDefinition {
		return null;
	}
}
