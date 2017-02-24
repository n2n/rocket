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

use rocket\spec\ei\manage\mapping\impl\Readable;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\manage\mapping\Mappable;
use rocket\spec\ei\manage\gui\GuiField;
use rocket\spec\ei\manage\mapping\impl\SimpleMappable;
use n2n\l10n\N2nLocale;
use n2n\util\ex\UnsupportedOperationException;
use rocket\spec\ei\component\field\GuiEiField;
use rocket\spec\ei\component\field\MappableEiField;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;

abstract class DisplayableEiFieldAdapter extends ConfObjectPropertyEiFieldAdapter implements StatelessDisplayable, 
		MappableEiField, GuiEiField, GuiField, Readable {
	protected $displayDefinition;
	
	public function __construct() {
		parent::__construct();
		
		$this->displayDefinition = new DisplayDefinition();
	}
	
	public function getDisplayDefinition(): DisplayDefinition {
		return $this->displayDefinition;
	}

	public function createEiFieldConfigurator(): EiFieldConfigurator {
		$eiFieldConfigurator = parent::createEiFieldConfigurator();
		IllegalStateException::assertTrue($eiFieldConfigurator instanceof AdaptableEiFieldConfigurator);
		$eiFieldConfigurator->registerDisplayDefinition($this->displayDefinition);
		return $eiFieldConfigurator;
	}
	
	public function isMappable(): bool {
		return true;
	}
	
	public function buildMappable(Eiu $eiu) {
		return new SimpleMappable($eiu->entry()->getEiSelection(), 
				$this->getObjectPropertyAccessProxy()->getConstraint()->getLenientCopy(), 
				$this);
	}
	
	public function buildMappableFork(EiObject $eiObject, Mappable $mappable = null) {
		return null;
	}
	
// 	public function isEiMappingFilterable(): bool {
// 		return false;
// 	}
	
// 	public function createEiMappingFilterField(N2nContext $n2nContext): EiMappingFilterField {
// 		throw new IllegalStateException('EiField cannot provide an EiMappingFilterField: ' . $this);
// 	}
	
// 	public function getTypeConstraint() {
// 		$typeConstraint = $this->getPropertyAccessProxy()->getConstraint();
// 		if ($typeConstraint === null) return null;
// 		return $typeConstraint->getLenientCopy();
// 	}
	
	public function read(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			return $eiObject->getDraftValueMap()->getValue($this);
		}
		
		return $this->getObjectPropertyAccessProxy()->getValue($eiObject->getLiveObject());
	}
	
	public function getGuiField() {
		return $this;
	}
	
	public function getGuiFieldFork() {
		return null;
	}

	public function getDisplayLabel(): string {
		return $this->getLabelLstr();
	}
	
	public function buildGuiElement(Eiu $eiu) {
		return new StatelessDisplayElement($this, $eiu);
	}
	
	public function getUiOutputLabel(Eiu $eiu) {
		return $this->getLabelLstr();
	}
	
	public function getOutputHtmlContainerAttrs(Eiu $eiu) {
		$eiMask = $this->eiEngine->getEiMask();
		return array('class' => 'rocket-ei-spec-' . $this->eiEngine->getEiSpec()->getId()
						. ($eiMask !== null ? ' rocket-ei-mask-' . $eiMask->getId() : '') 
						. ' rocket-ei-field-' . $this->getId(), 
				'title' => $this->displayDefinition->getHelpText());
	}
	
	public function isStringRepresentable(): bool {
		return false;
	}
	
	public function buildIdentityString(EiObject $eiObject, N2nLocale $n2nLocale) {
		throw new UnsupportedOperationException('EiField ' . $this->id . ' not summarizable.');
	}
}
