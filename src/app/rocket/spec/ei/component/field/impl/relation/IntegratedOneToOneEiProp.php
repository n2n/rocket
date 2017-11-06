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

use rocket\spec\ei\manage\gui\GuiPropFork;
use rocket\spec\ei\manage\mapping\EiFieldSource;
use rocket\spec\ei\manage\gui\GuiFieldFork;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\AssembleResult;
use rocket\spec\ei\manage\gui\GuiFieldAssembler;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\EiObject;
use n2n\util\ex\NotYetImplementedException;
use rocket\spec\ei\component\field\impl\relation\model\ToOneEiField;
use rocket\spec\ei\component\field\impl\relation\model\relation\EmbeddedEiPropRelation;
use rocket\spec\ei\manage\DraftEiObject;
use rocket\spec\ei\manage\LiveEiObject;
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
use rocket\spec\ei\manage\mapping\EiEntry;

class IntegratedOneToOneEiProp extends RelationEiPropAdapter implements GuiPropFork {
	private $orphansAllowed = false;
	
	public function __construct() {
		parent::__construct();
	
		$this->initialize(new EmbeddedEiPropRelation($this, false, false));
	}
	
	public function getOrphansAllowed() {
		return $this->orphansAllowed;
	}
	
	public function setOrphansAllowed(bool $orphansAllowed) {
		$this->orphansAllowed = $orphansAllowed;
	}
	
	
	public function buildEiField(Eiu $eiu) {
		$readOnly = $this->eiPropRelation->isReadOnly($eiu->entry()->getEiEntry(), $eiu->frame()->getEiFrame());
	
		return new ToOneEiField($eiu->entry()->getEiObject(), $this, $this,
				($readOnly ? null : $this));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Readable::read()
	 */
	public function read(EiObject $eiObject) {
		if ($this->isDraftable() && $eiObject->isDraft()) {
			$targetDraft = $eiObject->getDraftValueMap()->getValue(EiPropPath::from($this));
			if ($targetDraft === null) return null;
				
			return new DraftEiObject($targetDraft);
		}
	
		$targetEntityObj = $this->getObjectPropertyAccessProxy()->getValue($eiObject->getLiveObject());
		if ($targetEntityObj === null) return null;

		return LiveEiObject::create($this->eiPropRelation->getTargetEiType(), $targetEntityObj);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Writable::write()
	 */
	public function write(EiObject $eiObject, $value) {
		CastUtils::assertTrue($value === null || $value instanceof EiObject);
	
		if ($this->isDraftable() && $eiObject->isDraft()) {
			$targetDraft = null;
			if ($value !== null) $targetDraft = $value->getDraft();
	
			$eiObject->getDraftValueMap()->setValue(EiPropPath::from($this), $targetDraft);
			return;
		}
	
		$targetEntityObj = null;
		if ($value !== null) $targetEntityObj = $value->getLiveObject();
	
		$this->getPropertyAccessProxy()->setValue($eiObject->getLiveObject(), $targetEntityObj);
	}
	
	public function copy(EiObject $eiObject, $value, Eiu $copyEiu) {
		if ($value === null) return $value;
	
		$targetEiuFrame = new EiuFrame($this->embeddedEiPropRelation->createTargetEditPseudoEiFrame(
				$copyEiu->frame()->getEiFrame(), $copyEiu->entry()->getEiEntry()));
		return RelationEntry::fromM($targetEiuFrame->createEiEntryCopy($value->toEiEntry($targetEiuFrame)));
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ToOneEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_ONE);
	
		parent::setEntityProperty($entityProperty);
	}
	
	public function getGuiPropFork() {
		return $this;
	}
	
	public function getForkedGuiDefinition() {
		return $this->eiPropRelation->getTargetEiMask()->getEiEngine()->getGuiDefinition();
	}
	
	/**
	 * @param Eiu $eiu
	 * @return GuiFieldFork
	 */
	public function createGuiFieldFork(Eiu $eiu): GuiFieldFork {
		$eiFrame = $eiu->frame()->getEiFrame();
		$eiEntry = $eiu->entry()->getEiEntry();
		
		$targetEiFrame = null;
		if ($eiu->entryGui()->isReadOnly()) {
			$targetEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame, $eiEntry);
		} else {
			$targetEiFrame = $this->eiPropRelation->createTargetEditPseudoEiFrame($eiFrame, $eiEntry);
		}
		
		$targetUtils = new EiuFrame($targetEiFrame);
		
		$toOneEiField = $eiEntry->getEiField(EiPropPath::from($this));
		$targetRelationEntry = $toOneEiField->getValue();
		
		if ($targetRelationEntry === null) {
			$targetEiObject = $targetUtils->createNewEiObject();
			$targetRelationEntry = RelationEntry::fromM($targetUtils->createEiEntry($targetEiObject));
		} else if (!$targetRelationEntry->hasEiEntry()) {
			$targetEiObject = $targetRelationEntry->getEiObject();
			$targetRelationEntry = RelationEntry::fromM($targetUtils->createEiEntry($targetEiObject));
		}
				
		$targetGuiFieldAssembler = new GuiFieldAssembler($this->getForkedGuiDefinition(), 
				new Eiu($targetRelationEntry->getEiEntry(), $targetUtils->getEiFrame(), $eiu->getViewMode()));
		
		return new OneToOneGuiFieldFork($toOneEiField, $targetRelationEntry, $targetGuiFieldAssembler);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return EiFieldSource or null if not available
	 */
	public function determineForkedEiObject(EiObject $eiObject) {
		$targetEiObject = $this->read($eiObject);
		if ($targetEiObject === null) {
			return null;
		}
		return $targetEiObject;
	}
	
	public function determineEiFieldWrapper(EiEntry $eiEntry, GuiIdPath $guiIdPath) {
		$eiFieldWrappers = array();
		$targetRelationEntry = $eiEntry->getValue(EiPropPath::from($this->eiPropRelation->getRelationEiProp()));
		if ($targetRelationEntry === null || !$targetRelationEntry->hasEiEntry()) return null;
	
		if (null !== ($eiFieldWrapper = $this->guiDefinition
				->determineEiFieldWrapper($targetRelationEntry->getEiEntry(), $guiIdPath))) {
			return $eiFieldWrapper;
		}
	
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\GuiEiProp::getGuiProp()
	 */
	public function getGuiProp() {
		return null;	
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\component\field\DraftableEiProp::getDraftProperty()
	 */
	public function getDraftProperty() {
		throw new NotYetImplementedException();
		
	}

}

class OneToOneGuiFieldFork implements GuiFieldFork {
	private $toOneEiField;
	private $targetRelationEntry;
	private $targetGuiFieldAssembler;
	
	public function __construct(ToOneEiField $toOneEiField, RelationEntry $targetRelationEntry, GuiFieldAssembler $targetGuiFieldAssembler) {
		$this->toOneEiField = $toOneEiField;
		$this->targetRelationEntry = $targetRelationEntry;
		$this->targetGuiFieldAssembler = $targetGuiFieldAssembler;
	}
	
	public function assembleGuiField(GuiIdPath $guiIdPath): AssembleResult {
		return $this->targetGuiFieldAssembler->assembleGuiField($guiIdPath);
	}
	
	public function buildForkMag(string $propertyName) {
		$dispatchable = $this->targetGuiFieldAssembler->getDispatchable();
		
		if ($dispatchable !== null) {
			return new OneToOneForkMag($propertyName, $dispatchable);
		}
		
		return null;
	}
	
	public function save() {
		$this->targetGuiFieldAssembler->save();
		$this->toOneEiField->setValue($this->targetRelationEntry);
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
