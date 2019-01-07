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
namespace rocket\impl\ei\component\prop\relation;

use n2n\util\type\ArgUtils;
use rocket\impl\ei\component\prop\relation\model\relation\EmbeddedEiPropRelation;
use rocket\ei\manage\EiObject;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\impl\ei\component\prop\relation\model\ToOneEditable;
use rocket\impl\ei\component\prop\relation\model\EmbeddedOneToOneGuiField;
use rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use rocket\ei\manage\draft\DraftManager;
use n2n\core\container\N2nContext;
use rocket\ei\manage\draft\SimpleDraftValueSelection;
use rocket\ei\manage\LiveEiObject;
use rocket\ei\manage\DraftEiObject;
use n2n\util\type\CastUtils;
use rocket\ei\manage\draft\stmt\PersistDraftStmtBuilder;
use rocket\ei\manage\draft\DraftDefinition;
use rocket\ei\manage\draft\Draft;
use rocket\ei\manage\draft\RemoveDraftAction;
use rocket\impl\ei\component\prop\relation\conf\RelationEiPropConfigurator;
use rocket\ei\manage\draft\DraftValueSelection;
use rocket\ei\manage\draft\PersistDraftAction;
use rocket\ei\EiPropPath;
use rocket\ei\util\Eiu;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\manage\draft\stmt\RemoveDraftStmtBuilder;
use rocket\impl\ei\component\prop\relation\model\RelationEntry;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\GuiField;
use rocket\ei\manage\gui\ui\DisplayItem;
use rocket\ei\manage\security\InaccessibleEiCommandPathException;

class EmbeddedOneToOneEiProp extends ToOneEiPropAdapter {
	private $replaceable = true;
	private $reduced = true;
	
	public function __construct() {
		parent::__construct();
		
		$this->displayConfig = new DisplayConfig(ViewMode::bulky());
		$this->initialize(new EmbeddedEiPropRelation($this, false, false));
	}
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ToOneEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_ONE);
	
		parent::setEntityProperty($entityProperty);
	}
	
	protected function getDisplayItemType(): string {
		return $this->reduced ? DisplayItem::TYPE_SIMPLE_GROUP : DisplayItem::TYPE_PANEL;
	}
	
	/**
	 * @return boolean
	 */
	public function isReduced() {
		return $this->reduced;
	}
	
	/**
	 * @param bool $reduced
	 */
	public function setReduced(bool $reduced) {
		$this->reduced = $reduced;
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
	
	public function createEiPropConfigurator(): EiPropConfigurator {
		$relationEiPropConfigurator =  new RelationEiPropConfigurator($this);
		$relationEiPropConfigurator->setDisplayInOverviewDefault(false);
		return $relationEiPropConfigurator;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\Readable::read()
	 */
	public function read(Eiu $eiu) {
		if ($eiu->object()->isDraftProp($this)) {
			$targetDraft = $eiu->entry()->readNativValue($this);
			if ($targetDraft === null) return null;
			
			return new DraftEiObject($targetDraft);
		}
		
		$targetEntityObj = $eiu->entry()->readNativValue($this);
		if ($targetEntityObj === null) return null;
		
		return LiveEiObject::create($this->eiPropRelation->getTargetEiType(), $targetEntityObj);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\Writable::write()
	 */
	public function write(Eiu $eiu, $value) {
		CastUtils::assertTrue($value === null || $value instanceof EiObject);
	
		if ($eiu->object()->isDraftProp($this)) {
			$targetDraft = null;
			if ($value !== null) $targetDraft = $value->getDraft();
				
			$eiu->entry()->writeNativeValue($this, $targetDraft);
			return;
		} 
		
		$targetEntityObj = null;
		if ($value !== null) $targetEntityObj = $value->getLiveObject();
		
		$eiu->entry()->writeNativeValue($this, $targetEntityObj);
	}
	
	public function copy(Eiu $eiu, $value, Eiu $copyEiu) {
		if ($value === null || ($eiu->entry()->isDraft() && !$this->isDraftable())) return null;
		
		$targetEiuFrame = (new Eiu($this->eiPropRelation->createTargetEditPseudoEiFrame(
				$copyEiu->frame()->getEiFrame(), $copyEiu->entry()->getEiEntry())))->frame();
		return RelationEntry::fromM($targetEiuFrame->copyEntry($value->toEiEntry($targetEiuFrame), $eiu->entry()->isDraft())->getEiEntry());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiField()
	 */
	public function buildGuiField(Eiu $eiu): ?GuiField {
		$mapping = $eiu->entry()->getEiEntry();
		
		$eiFrame = $eiu->frame()->getEiFrame();
		$relationEiField = $mapping->getEiField(EiPropPath::from($this));
		$targetReadEiFrame = null;
		try {
			$targetReadEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame, $mapping);
		} catch (InaccessibleEiCommandPathException $e) {
			return new InaccessibleGuiField($this->getLabelLstr());
		}
		
		$toOneEditable = null;
		if (!$this->eiPropRelation->isReadOnly($mapping, $eiFrame)) {
			$targetEditEiFrame = $this->eiPropRelation->createTargetEditPseudoEiFrame($eiFrame, $mapping);
			$toOneEditable = new ToOneEditable($this->getLabelLstr(), $this->editConfig->isMandatory(), 
					$relationEiField, $targetReadEiFrame, $targetEditEiFrame);
			$toOneEditable->setReduced($this->reduced);
			
			if ($targetEditEiFrame->getEiExecution()->isGranted() 
					&& ($this->isReplaceable() || $relationEiField->getValue() === null)) {
				$toOneEditable->setNewMappingFormUrl($this->eiPropRelation->buildTargetNewEiuEntryFormUrl(
						$mapping, $mapping->getEiObject()->isDraft(), $eiFrame, $eiu->frame()->getHttpContext()));
			}
			
			$toOneEditable->setDraftMode($mapping->getEiObject()->isDraft());
		}
		
		return new EmbeddedOneToOneGuiField($this->getLabelLstr(), $this->isReduced(), $relationEiField,
				$targetReadEiFrame, $eiu->gui()->isCompact(), $toOneEditable);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\draft\DraftProperty::createDraftValueSelection()
	 */
	public function createDraftValueSelection(FetchDraftStmtBuilder $selectDraftStmtBuilder, DraftManager $dm,
			N2nContext $n2nContext): DraftValueSelection {
		return new EmbeddedToOneDraftValueSelection($selectDraftStmtBuilder->requestColumn(EiPropPath::from($this)),
				$dm, $this->eiPropRelation->getTargetEiMask()->getDraftDefinition());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\draft\DraftProperty::supplyPersistDraftStmtBuilder()
	 */
	public function supplyPersistDraftStmtBuilder($targetDraft, $oldTargetDraft, 
			PersistDraftStmtBuilder $persistDraftStmtBuilder, PersistDraftAction $persistDraftAction) {
		ArgUtils::assertTrue($targetDraft === null || $targetDraft instanceof Draft);
		ArgUtils::assertTrue($oldTargetDraft === null || $oldTargetDraft instanceof Draft);
		
		if ($oldTargetDraft !== null && $oldTargetDraft !== $targetDraft) {
			$persistDraftAction->getQueue()->remove($oldTargetDraft);
		}
		
		if ($targetDraft === null) {
			$persistDraftStmtBuilder->registerColumnRawValue(EiPropPath::from($this), null);
			return;
		}

		$targetDraftAction = $persistDraftAction->getQueue()->persist($targetDraft, $this->eiPropRelation->getTargetEiMask()
				->getDraftDefinition());
		
		if (!$targetDraft->isNew()) {
			$persistDraftStmtBuilder->registerColumnRawValue(EiPropPath::from($this), $targetDraft->getId());
			return;
		}
		
		$persistDraftAction->addDependent($targetDraftAction);
		$targetDraftAction->executeAtEnd(function () use ($persistDraftStmtBuilder, $targetDraft) {
			$persistDraftStmtBuilder->registerColumnRawValue(EiPropPath::from($this), $targetDraft->getId());
		});
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\draft\DraftProperty::supplyRemoveDraftStmtBuilder()
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
	 * @see \rocket\ei\manage\draft\DraftValueSelection::buildDraftValue()
	 */
	public function buildDraftValue() {
		if ($this->rawValue === null) return null;
	
		return $this->dm->find($baseEntityObj, $this->rawValue, $this->targetDraftDefinition);
	}
}
