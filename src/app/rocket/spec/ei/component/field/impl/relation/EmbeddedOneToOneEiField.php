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

use n2n\reflection\ArgUtils;
use rocket\spec\ei\component\field\impl\relation\model\relation\EmbeddedEiFieldRelation;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\EiFrame;

use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\manage\gui\GuiField;
use rocket\spec\ei\component\field\impl\relation\model\ToOneEditable;
use rocket\spec\ei\component\field\impl\relation\model\EmbeddedOneToOneGuiElement;
use rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use rocket\spec\ei\manage\draft\DraftManager;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\draft\SimpleDraftValueSelection;
use rocket\spec\ei\manage\LiveEiSelection;
use rocket\spec\ei\manage\DraftEiSelection;
use rocket\spec\ei\manage\EiObject;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\draft\stmt\PersistDraftStmtBuilder;
use rocket\spec\ei\manage\draft\DraftDefinition;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\draft\RemoveDraftAction;
use rocket\spec\ei\component\field\impl\relation\conf\RelationEiFieldConfigurator;
use rocket\spec\ei\manage\draft\DraftValueSelection;
use rocket\spec\ei\manage\draft\PersistDraftAction;
use rocket\spec\ei\component\field\impl\relation\model\RelationMappable;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\manage\util\model\Eiu;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use rocket\spec\ei\manage\draft\stmt\RemoveDraftStmtBuilder;
use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\component\field\impl\relation\model\RelationEntry;

class EmbeddedOneToOneEiField extends ToOneEiFieldAdapter {
	private $replaceable = true;
	private $orphansAllowed = false;
	
	public function __construct() {
		parent::__construct();
		
		$this->displayDefinition = new DisplayDefinition(DisplayDefinition::BULKY_VIEW_MODES);
		$this->initialize(new EmbeddedEiFieldRelation($this, false, false));
	}
	
	public function getOrphansAllowed() {
		return $this->orphansAllowed;
	}
	
	public function setOrphansAllowed(bool $orphansAllowed) {
		$this->orphansAllowed = $orphansAllowed;
	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ToOneEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_ONE);
	
		parent::setEntityProperty($entityProperty);
	}
	
	/**
	 * @return bool
	 */
	public function isReplaceable() {
		return $this->replaceable;
	}
	
	/**
	 * @param bool $replaceable
	 */
	public function setReplaceable(bool $replaceable) {
		$this->replaceable = $replaceable;
	}
	
	public function createEiFieldConfigurator(): EiFieldConfigurator {
		return new RelationEiFieldConfigurator($this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Readable::read()
	 */
	public function read(EiObject $eiObject) {
		if ($this->isDraftable() && $eiObject->isDraft()) {
			$targetDraft = $eiObject->getDraftValueMap()->getValue(EiFieldPath::from($this));
			if ($targetDraft === null) return null;
			
			return new DraftEiSelection($targetDraft);
		}
		
		$targetEntityObj = $this->getObjectPropertyAccessProxy()->getValue($eiObject->getLiveObject());
		if ($targetEntityObj === null) return null;
		
		return LiveEiSelection::create($this->eiFieldRelation->getTargetEiSpec(), $targetEntityObj);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Writable::write()
	 */
	public function write(EiObject $eiObject, $value) {
		CastUtils::assertTrue($value === null || $value instanceof EiSelection);
	
		if ($this->isDraftable() && $eiObject->isDraft()) {
			$targetDraft = null;
			if ($value !== null) $targetDraft = $value->getDraft();
				
			$eiObject->getDraftValueMap()->setValue(EiFieldPath::from($this), $targetDraft);
			return;
		} 
		
		$targetEntityObj = null;
		if ($value !== null) $targetEntityObj = $value->getLiveObject();
		
		$this->getObjectPropertyAccessProxy()->setValue($eiObject->getLiveObject(), $targetEntityObj);
	}
	
	public function copy(EiObject $eiObject, $value, Eiu $copyEiu) {
		if ($value === null) return $value;
		
		$targetEiuFrame = new EiuFrame($this->eiFieldRelation->createTargetEditPseudoEiFrame(
				$copyEiu->frame()->getEiFrame(), $copyEiu->entry()->getEiMapping()));
		return RelationEntry::fromM($targetEiuFrame->createEiMappingCopy($value->toEiMapping($targetEiuFrame)));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::buildGuiElement()
	 */
	public function buildGuiElement(Eiu $eiu) {
		$mapping = $eiu->entry()->getEiMapping();
		
		$eiFrame = $eiu->frame()->getEiFrame();
		$relationMappable = $mapping->getMappable(EiFieldPath::from($this));
		$targetReadEiFrame = $this->eiFieldRelation->createTargetReadPseudoEiFrame($eiFrame, $mapping);
		
		$toOneEditable = null;
		if (!$this->eiFieldRelation->isReadOnly($mapping, $eiFrame)) {
			$targetEditEiFrame = $this->eiFieldRelation->createTargetEditPseudoEiFrame($eiFrame, $mapping);
			$toOneEditable = new ToOneEditable($this->getLabelLstr(), $this->standardEditDefinition->isMandatory(), 
					$relationMappable, $targetReadEiFrame, $targetEditEiFrame);
			
			if ($targetEditEiFrame->getEiExecution()->isGranted() 
					&& ($this->isReplaceable() || $relationMappable->getValue() === null)) {
				$toOneEditable->setNewMappingFormUrl($this->eiFieldRelation->buildTargetNewEntryFormUrl(
						$mapping, $mapping->getEiSelection()->isDraft(), $eiFrame, $eiu->frame()->getHttpContext()));
			}
			
			$toOneEditable->setDraftMode($mapping->getEiSelection()->isDraft());
		}
				
		return new EmbeddedOneToOneGuiElement($this->getLabelLstr(), $relationMappable, $targetReadEiFrame, $toOneEditable);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::createDraftValueSelection()
	 */
	public function createDraftValueSelection(FetchDraftStmtBuilder $selectDraftStmtBuilder, DraftManager $dm,
			N2nContext $n2nContext): DraftValueSelection {
		return new EmbeddedToOneDraftValueSelection($selectDraftStmtBuilder->requestColumn(EiFieldPath::from($this)),
				$dm, $this->eiFieldRelation->getTargetEiMask()->getDraftDefinition());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::supplyPersistDraftStmtBuilder()
	 */
	public function supplyPersistDraftStmtBuilder($targetDraft, $oldTargetDraft, 
			PersistDraftStmtBuilder $persistDraftStmtBuilder, PersistDraftAction $persistDraftAction) {
		ArgUtils::assertTrue($targetDraft === null || $targetDraft instanceof Draft);
		ArgUtils::assertTrue($oldTargetDraft === null || $oldTargetDraft instanceof Draft);
		
		if ($oldTargetDraft !== null && $oldTargetDraft !== $targetDraft) {
			$persistDraftAction->getQueue()->remove($oldTargetDraft);
		}
		
		if ($targetDraft === null) {
			$persistDraftStmtBuilder->registerColumnRawValue(EiFieldPath::from($this), null);
			return;
		}

		$targetDraftAction = $persistDraftAction->getQueue()->persist($targetDraft, $this->eiFieldRelation->getTargetEiMask()
				->getDraftDefinition());
		
		if (!$targetDraft->isNew()) {
			$persistDraftStmtBuilder->registerColumnRawValue(EiFieldPath::from($this), $targetDraft->getId());
			return;
		}
		
		$persistDraftAction->addDependent($targetDraftAction);
		$targetDraftAction->executeAtEnd(function () use ($persistDraftStmtBuilder, $targetDraft) {
			$persistDraftStmtBuilder->registerColumnRawValue(EiFieldPath::from($this), $targetDraft->getId());
		});
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::supplyRemoveDraftStmtBuilder()
	 */
	public function supplyRemoveDraftStmtBuilder($targetDraft, $oldTargetDraft, 
			RemoveDraftStmtBuilder $removeDraftStmtBuilder, RemoveDraftAction $removeDraftAction) {
		ArgUtils::assertTrue($oldTargetDraft === null || $oldTargetDraft instanceof Draft);
		
		if ($oldTargetDraft !== null) {
			$targetDraft->getQueue()->remove($oldTargetDraft);
		}
	}
	
	public function writeDraftValue($object, $value) {
		if ($value === null) {
			$this->getPropertyAccessProxy()->setValue($object, null);
			return;
		}
		
		throw new \n2n\util\ex\NotYetImplementedException('BUILD TARGET DRAFTED OBJECT');
// 		ArgUtils::assertTrue($value instanceof Draft);
// 		$this->getPropertyAccessProxy()->setValue($object, $value->);
	}
}

class EmbeddedToOneDraftValueSelection  extends SimpleDraftValueSelection {
	private $dm;
	private $targetEntityModel;
	private $targetDraftDefinition;
	
	public function __construct($columnAlias, DraftManager $dm, DraftDefinition $targetDraftDefinition) {
		parent::__construct($columnAlias);
		$this->dm = $dm;
		$this->targetDraftDefinition = $targetDraftDefinition;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\draft\DraftValueSelection::buildDraftValue()
	 */
	public function buildDraftValue() {
		if ($this->rawValue === null) return null;
	
		return $this->dm->find($baseEntityObj, $this->rawValue, $this->targetDraftDefinition);
	}
}
