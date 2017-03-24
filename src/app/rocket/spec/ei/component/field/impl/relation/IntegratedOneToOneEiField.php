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

use rocket\spec\ei\manage\util\model\EiuFrame;

use rocket\spec\ei\manage\gui\GuiFieldFork;
use rocket\spec\ei\manage\mapping\MappableSource;
use rocket\spec\ei\manage\gui\GuiElementFork;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\AssembleResult;
use rocket\spec\ei\manage\gui\GuiElementAssembler;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\manage\EiObject;
use n2n\util\ex\NotYetImplementedException;
use rocket\spec\ei\component\field\impl\relation\model\ToOneMappable;
use rocket\spec\ei\component\field\impl\relation\model\relation\EmbeddedEiFieldRelation;
use rocket\spec\ei\manage\DraftEiEntry;
use rocket\spec\ei\manage\LiveEiEntry;
use n2n\reflection\CastUtils;
use n2n\impl\web\dispatch\mag\model\ObjectMagAdapter;
use n2n\web\dispatch\Dispatchable;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\component\field\impl\relation\model\RelationEntry;
use n2n\web\ui\Raw;
use n2n\web\ui\UiComponent;
use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\ArgUtils;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\EiEntry;

class IntegratedOneToOneEiField extends RelationEiFieldAdapter implements GuiFieldFork {

	public function __construct() {
		parent::__construct();
	
		$this->initialize(new EmbeddedEiFieldRelation($this, false, false));
	}
	
	public function buildMappable(Eiu $eiu) {
		$readOnly = $this->eiFieldRelation->isReadOnly($eiu->entry()->getEiMapping(), $eiu->frame()->getEiFrame());
	
		return new ToOneMappable($eiu->entry()->getEiEntry(), $this, $this,
				($readOnly ? null : $this));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Readable::read()
	 */
	public function read(EiObject $eiObject) {
		if ($this->isDraftable() && $eiObject->isDraft()) {
			$targetDraft = $eiObject->getDraftValueMap()->getValue(EiFieldPath::from($this));
			if ($targetDraft === null) return null;
				
			return new DraftEiEntry($targetDraft);
		}
	
		$targetEntityObj = $this->getObjectPropertyAccessProxy()->getValue($eiObject->getLiveObject());
		if ($targetEntityObj === null) return null;

		return LiveEiEntry::create($this->eiFieldRelation->getTargetEiSpec(), $targetEntityObj);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Writable::write()
	 */
	public function write(EiObject $eiObject, $value) {
		CastUtils::assertTrue($value === null || $value instanceof EiEntry);
	
		if ($this->isDraftable() && $eiObject->isDraft()) {
			$targetDraft = null;
			if ($value !== null) $targetDraft = $value->getDraft();
	
			$eiObject->getDraftValueMap()->setValue(EiFieldPath::from($this), $targetDraft);
			return;
		}
	
		$targetEntityObj = null;
		if ($value !== null) $targetEntityObj = $value->getLiveObject();
	
		$this->getPropertyAccessProxy()->setValue($eiObject->getLiveObject(), $targetEntityObj);
	}
	
	public function copy(EiObject $eiObject, $value, Eiu $copyEiu) {
		if ($value === null) return $value;
	
		$targetEiuFrame = new EiuFrame($this->embeddedEiFieldRelation->createTargetEditPseudoEiFrame(
				$copyEiu->frame()->getEiFrame(), $copyEiu->entry()->getEiMapping()));
		return RelationEntry::fromM($targetEiuFrame->createEiMappingCopy($value->toEiMapping($targetEiuFrame)));
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ToOneEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_ONE);
	
		parent::setEntityProperty($entityProperty);
	}
	
	public function getGuiFieldFork() {
		return $this;
	}
	
	public function getForkedGuiDefinition() {
		return $this->eiFieldRelation->getTargetEiMask()->getEiEngine()->getGuiDefinition();
	}
	
	/**
	 * @param Eiu $eiu
	 * @return GuiElementFork
	 */
	public function createGuiElementFork(Eiu $eiu): GuiElementFork {
		$eiFrame = $eiu->frame()->getEiFrame();
		$eiMapping = $eiu->entry()->getEiMapping();
		
		$targetEiFrame = null;
		if ($eiu->entryGui()->isReadOnly()) {
			$targetEiFrame = $this->eiFieldRelation->createTargetReadPseudoEiFrame($eiFrame, $eiMapping);
		} else {
			$targetEiFrame = $this->eiFieldRelation->createTargetEditPseudoEiFrame($eiFrame, $eiMapping);
		}
		
		$targetUtils = new EiuFrame($targetEiFrame);
		
		$toOneMappable = $eiMapping->getMappable(EiFieldPath::from($this));
		$targetRelationEntry = $toOneMappable->getValue();
		
		if ($targetRelationEntry === null) {
			$targetEiEntry = $targetUtils->createNewEiEntry();
			$targetRelationEntry = RelationEntry::fromM($targetUtils->createEiMapping($targetEiEntry));
		} else if (!$targetRelationEntry->hasEiMapping()) {
			$targetEiEntry = $targetRelationEntry->getEiEntry();
			$targetRelationEntry = RelationEntry::fromM($targetUtils->createEiMapping($targetEiEntry));
		}
				
		$targetGuiElementAssembler = new GuiElementAssembler($this->getForkedGuiDefinition(), 
				new Eiu($targetRelationEntry->getEiMapping(), $targetUtils->getEiFrame(), $eiu->getViewMode()));
		
		return new OneToOneGuiElementFork($toOneMappable, $targetRelationEntry, $targetGuiElementAssembler);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return MappableSource or null if not available
	 */
	public function determineForkedEiObject(EiObject $eiObject) {
		$targetEiEntry = $this->read($eiObject);
		if ($targetEiEntry === null) {
			return null;
		}
		return $targetEiEntry;
	}
	
	public function determineMappableWrapper(EiMapping $eiMapping, GuiIdPath $guiIdPath) {
		$mappableWrappers = array();
		$targetRelationEntry = $eiMapping->getValue(EiFieldPath::from($this->eiFieldRelation->getRelationEiField()));
		if ($targetRelationEntry === null || !$targetRelationEntry->hasEiMapping()) return null;
	
		if (null !== ($mappableWrapper = $this->guiDefinition
				->determineMappableWrapper($targetRelationEntry->getEiMapping(), $guiIdPath))) {
			return $mappableWrapper;
		}
	
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\GuiEiField::getGuiField()
	 */
	public function getGuiField() {
		return null;	
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\DraftableEiField::getDraftProperty()
	 */
	public function getDraftProperty() {
		throw new NotYetImplementedException();
		
	}

}

class OneToOneGuiElementFork implements GuiElementFork {
	private $toOneMappable;
	private $targetRelationEntry;
	private $targetGuiElementAssembler;
	
	public function __construct(ToOneMappable $toOneMappable, RelationEntry $targetRelationEntry, GuiElementAssembler $targetGuiElementAssembler) {
		$this->toOneMappable = $toOneMappable;
		$this->targetRelationEntry = $targetRelationEntry;
		$this->targetGuiElementAssembler = $targetGuiElementAssembler;
	}
	
	public function assembleGuiElement(GuiIdPath $guiIdPath, $makeEditable): AssembleResult {
		return $this->targetGuiElementAssembler->assembleGuiElement($guiIdPath, $makeEditable);
	}
	
	public function buildForkMag(string $propertyName) {
		$dispatchable = $this->targetGuiElementAssembler->getDispatchable();
		
		if ($dispatchable !== null) {
			return new OneToOneForkMag($propertyName, $dispatchable);
		}
		
		return null;
	}
	
	public function save() {
		$this->targetGuiElementAssembler->save();
		$this->toOneMappable->setValue($this->targetRelationEntry);
	}
}


class OneToOneForkMag extends ObjectMagAdapter {
	private $dispatchable;

	public function __construct($propertyName, Dispatchable $dispatchable) {
		parent::__construct($propertyName, '', $dispatchable);
	}
	
	public function createUiField(PropertyPath $propertyPath, HtmlView $view): UiComponent {
		return new Raw();
	}
}
