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

use rocket\ei\manage\EiObject;
use n2n\util\ex\NotYetImplementedException;
use n2n\reflection\ArgUtils;
use rocket\impl\ei\component\prop\relation\model\ToManyEditable;
use rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use rocket\ei\manage\draft\DraftManager;
use n2n\core\container\N2nContext;
use rocket\ei\manage\draft\DraftValueSelection;
use rocket\ei\EiPropPath;
use rocket\impl\ei\component\prop\relation\model\ToManySelectGuiField;
use rocket\ei\manage\LiveEiObject;
use rocket\ei\util\model\Eiu;
use rocket\ei\manage\draft\RemoveDraftAction;
use rocket\ei\manage\draft\stmt\RemoveDraftStmtBuilder;
use n2n\reflection\CastUtils;
use rocket\impl\ei\component\prop\relation\model\relation\SelectEiPropRelation;
use rocket\ei\manage\gui\ui\DisplayItem;
use rocket\ei\manage\critmod\CriteriaConstraint;
use rocket\ei\manage\gui\GuiField;

abstract class ToManySelectEiPropAdapter extends ToManyEiPropAdapter {
	
	protected function getDisplayItemType() {
		return DisplayItem::TYPE_ITEM;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\impl\Readable::read()
	 */
	public function read(EiObject $eiObject) {
		$targetEntityObjs = null;
		if ($this->isDraftable() && $eiObject->isDraft()) {
			throw new NotYetImplementedException();
			$targetEntityObjs = $eiObject->getDraftValueMap()->getValue(EiPropPath::from($this),
					$this->getObjectPropertyAccessProxy()->getConstraint());
		} else {
			$targetEntityObjs = $this->getObjectPropertyAccessProxy()->getValue($eiObject->getLiveObject());
		}
	
		if ($targetEntityObjs === null) {
			return array();
		}
		
		$targetEiType = $this->eiPropRelation->getTargetEiType();
		
		$targetEiObjects = array();
		foreach ($targetEntityObjs as $targetEntityObj) {
			$targetEiObjects[] = LiveEiObject::create($targetEiType, $targetEntityObj);
		}
		return $targetEiObjects;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\mapping\impl\Writable::write()
	 */
	public function write(EiObject $eiObject, $value) {
		ArgUtils::valArray($value, EiObject::class);
		
		$targetEntityObjs = new \ArrayObject();
		foreach ($value as $targetEiObject) {
			$targetEntityObjs[] = $targetEiObject->getLiveObject();
		}
		
		if ($this->isDraftable() && $eiObject->isDraft()) {
			throw new NotYetImplementedException();
			$eiObject->getDraftValueMap()->setValue(EiPropPath::from($this), $targetEntityObjs);
		} else {
			$this->getObjectPropertyAccessProxy()->setValue($eiObject->getLiveObject(), $targetEntityObjs);
		}
	}
	
	public function copy(EiObject $eiObject, $value, Eiu $copyEiu) {
		return $value;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiField()
	 */
	public function buildGuiField(Eiu $eiu): ?GuiField {
		$mapping = $eiu->entry()->getEiEntry();
		$eiFrame = $eiu->frame()->getEiFrame();
		$targetReadEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame, $mapping);
		
		$targetEiu = new Eiu($targetReadEiFrame);
		$eiPropRelation = $this->eiPropRelation;
		CastUtils::assertTrue($eiPropRelation instanceof SelectEiPropRelation);
		
		if ($eiPropRelation->isHiddenIfTargetEmpty() 
				&& 0 == $targetEiu->frame()->countEntries(CriteriaConstraint::NON_SECURITY_TYPES)) {
			return null;
		}
	
		$toManyEditable = null;
		if (!$this->eiPropRelation->isReadOnly($mapping, $eiFrame)) {
			$targetEditEiFrame = $this->eiPropRelation->createTargetEditPseudoEiFrame($eiFrame, $mapping);
			$toManyEditable = new ToManyEditable($this->getLabelLstr(), 
					$mapping->getEiField(EiPropPath::from($this)), $targetReadEiFrame,
					$targetEditEiFrame, $this->getRealMin(), $this->getMax());
			$toManyEditable->setSelectOverviewToolsUrl($eiPropRelation->buildTargetOverviewToolsUrl(
					$eiFrame, $eiu->frame()->getHttpContext()));
				
			if ($eiPropRelation->isEmbeddedAddActivated($eiFrame)
					&& $targetEditEiFrame->getEiExecution()->isGranted()) {
				$toManyEditable->setNewMappingFormUrl($this->eiPropRelation->buildTargetNewEiuEntryFormUrl(
						$mapping, false, $eiFrame, $eiu->frame()->getHttpContext()));
			}
		}
			
		return new ToManySelectGuiField($this, $eiu, $targetReadEiFrame, $toManyEditable);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\draft\DraftProperty::createDraftValueSelection($selectDraftStmtBuilder, $dm, $n2nContext)
	 */
	public function createDraftValueSelection(FetchDraftStmtBuilder $selectDraftStmtBuilder, DraftManager $dm, 
			N2nContext $n2nContext): DraftValueSelection {
		throw new NotYetImplementedException();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\draft\DraftProperty::supplyPersistDraftStmtBuilder($value, $oldValue, $persistDraftStmtBuilder, $persistDraftAction)
	 */
	public function supplyPersistDraftStmtBuilder($value, $oldValue, \rocket\ei\manage\draft\stmt\PersistDraftStmtBuilder $persistDraftStmtBuilder, \rocket\ei\manage\draft\PersistDraftAction $persistDraftAction) {
		throw new NotYetImplementedException();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\draft\DraftProperty::supplyRemoveDraftStmtBuilder($value, $oldValue, $removeDraftAction)
	 */
	public function supplyRemoveDraftStmtBuilder($value, $oldValue, RemoveDraftStmtBuilder $removeDraftStmtBuilder, 
			RemoveDraftAction $removeDraftAction) {
		throw new NotYetImplementedException();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\draft\DraftProperty::writeDraftValue($object, $value)
	 */
	public function writeDraftValue($object, $value) {
		throw new NotYetImplementedException();
	}
}
