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
use n2n\core\container\N2nContext;
use rocket\spec\ei\component\field\impl\relation\model\filter\ToOneEiMappingFilterField;
use rocket\spec\ei\manage\util\model\GlobalEiUtils;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\field\impl\relation\model\ToOneMappable;

abstract class ToOneEiFieldAdapter extends SimpleRelationEiFieldAdapter implements GuiField, DraftableEiField, 
		DraftProperty {

	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ToOneEntityProperty);
		parent::setEntityProperty($entityProperty);
	}

	public function buildMappable(Eiu $eiu) {
		$readOnly = $this->eiFieldRelation->isReadOnly($eiu->entry()->getEiMapping(), $eiu->frame()->getEiFrame());
	
		return new ToOneMappable($eiu->entry()->getEiSelection(), $this, $this,
				($readOnly ? null : $this));
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
		$targetEiSelection = $this->read($eiObject);
		if ($targetEiSelection === null) return '';
		
		return $this->eiFieldRelation->getTargetEiMask()->createIdentityString($targetEiSelection, $n2nLocale);
	}
			
	public function isEiMappingFilterable(): bool {
		return true;
	}
	
	public function buildEiMappingFilterField(N2nContext $n2nContext) {		
		return null;
		$targetEiMask = $this->eiFieldRelation->getTargetEiMask();
		
		return new ToOneEiMappingFilterField($this->getLabelLstr(), $this->getEntityProperty(),
				new GlobalEiUtils($this->getEiFieldRelation()->getTargetEiMask(), $n2nContext),
				$this->createAdvTargetFilterDef($n2nContext));
	}
}
