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
namespace rocket\spec\ei\component\field\impl\relation;

use rocket\spec\ei\manage\mapping\MappableSource;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\gui\GuiField;
use rocket\spec\ei\component\field\DraftableEiField;
use rocket\spec\ei\manage\draft\DraftProperty;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\component\field\impl\relation\model\ToManyMappable;
use rocket\spec\ei\manage\gui\DisplayDefinition;

abstract class ToManyEiFieldAdapter extends SimpleRelationEiFieldAdapter implements GuiField, DraftableEiField, 
		DraftProperty {
	protected $min;
	protected $max;
	
	public function getMin() {
		return $this->min;
	}
	
	public function setMin(int $min = null) {
		$this->min = $min;
		if ($min !== null && $min > 0) {
			$this->standardEditDefinition->setMandatory(true);
		}
	}
	
	public function getMax() {
		return $this->max;
	}
	
	public function setMax(int $max = null) {
		$this->max = $max;
	}
	
	public function getRealMin(): int {
		if ($this->min !== null && $this->min > 0) return $this->min;
		
		if ($this->standardEditDefinition->isMandatory()) return 1;
		
		return 0;
	}

	public function buildMappable(EiObject $eiObject) {
		return new ToManyMappable($eiObject, $this, $this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::getDisplayLabel()
	 */
	public function getDisplayLabel(): string {
		return $this->getLabelLstr();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::getDisplayDefinition()
	 */
	public function getDisplayDefinition(): DisplayDefinition {
		return $this->displayDefinition;
	}
	
	
	public function getGuiField() {
		return $this;
	}
	
	public function getGuiFieldFork() {
		return null;
	}
	
	/**
	 * @return boolean
	 */
	public function isStringRepresentable(): bool {
		return true;
	}
	
	/**
	 * @param MappableSource $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function buildIdentityString(EiObject $eiObject, N2nLocale $n2nLocale): string {
		$targetEiSelections = $this->read($eiObject);
		
		$numTargetEiSelections = count($targetEiSelections);
		if ($numTargetEiSelections == 1) {
			return $numTargetEiSelections . ' ' . $this->eiFieldRelation->getTargetEiMask()->getLabel();
		}
		
		return $numTargetEiSelections . ' ' . $this->eiFieldRelation->getTargetEiMask()->getPluralLabel();
		
	}
}
