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

use rocket\spec\ei\manage\EiObject;
use n2n\util\ex\NotYetImplementedException;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\EiSelection;

use rocket\spec\ei\component\field\impl\relation\model\ToManyEditable;
use rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use rocket\spec\ei\manage\draft\DraftManager;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\draft\DraftValueSelection;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\component\field\impl\relation\model\ToManySelectGuiElement;
use rocket\spec\ei\manage\LiveEiSelection;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\draft\RemoveDraftAction;
use rocket\spec\ei\manage\draft\stmt\RemoveDraftStmtBuilder;

abstract class ToManySelectEiFieldAdapter extends ToManyEiFieldAdapter {
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Readable::read()
	 */
	public function read(EiObject $eiObject) {
		$targetEntityObjs = null;
		if ($this->isDraftable() && $eiObject->isDraft()) {
			throw new NotYetImplementedException();
			$targetEntityObjs = $eiObject->getDraftValueMap()->getValue(EiFieldPath::from($this),
					$this->getObjectPropertyAccessProxy()->getConstraint());
		} else {
			$targetEntityObjs = $this->getObjectPropertyAccessProxy()->getValue($eiObject->getLiveObject());
		}
	
		if ($targetEntityObjs === null) {
			return array();
		}
		
		$targetEiSpec = $this->eiFieldRelation->getTargetEiSpec();
		
		$targetEiSelections = array();
		foreach ($targetEntityObjs as $targetEntityObj) {
			$targetEiSelections[] = LiveEiSelection::create($targetEiSpec, $targetEntityObj);
		}
		return $targetEiSelections;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\mapping\impl\Writable::write()
	 */
	public function write(EiObject $eiObject, $value) {
		ArgUtils::valArray($value, EiSelection::class);
		
		$targetEntityObjs = new \ArrayObject();
		foreach ($value as $targetEiSelection) {
			$targetEntityObjs[] = $targetEiSelection->getLiveObject();
		}
		
		if ($this->isDraftable() && $eiObject->isDraft()) {
			throw new NotYetImplementedException();
			$eiObject->getDraftValueMap()->setValue(EiFieldPath::from($this), $targetEntityObjs);
		} else {
			$this->getObjectPropertyAccessProxy()->setValue($eiObject->getLiveObject(), $targetEntityObjs);
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::buildGuiElement()
	 */
	public function buildGuiElement(Eiu $eiu) {
		$mapping = $eiu->entry()->getEiMapping();
		$eiFrame = $eiu->frame()->getEiFrame();
		$targetReadEiFrame = $this->eiFieldRelation->createTargetReadPseudoEiFrame($eiFrame, $mapping);
	
		$toManyEditable = null;
		if (!$this->eiFieldRelation->isReadOnly($mapping, $eiFrame)) {
			$targetEditEiFrame = $this->eiFieldRelation->createTargetEditPseudoEiFrame($eiFrame, $mapping);
			$toManyEditable = new ToManyEditable($this->getLabelLstr(), 
					$mapping->getMappable(EiFieldPath::from($this)), $targetReadEiFrame,
					$targetEditEiFrame, $this->getRealMin(), $this->getMax());
				
			$toManyEditable->setSelectOverviewToolsUrl($this->eiFieldRelation->buildTargetOverviewToolsUrl(
					$eiFrame, $eiu->frame()->getHttpContext()));
				
			if ($this->eiFieldRelation->isEmbeddedAddActivated($eiFrame)
					&& $targetEditEiFrame->getEiExecution()->isGranted()) {
				$toManyEditable->setNewMappingFormUrl($this->eiFieldRelation->buildTargetNewEntryFormUrl(
						$mapping, false, $eiFrame, $eiu->frame()->getHttpContext()));
			}
		}
			
		return new ToManySelectGuiElement($this, $eiu, $targetReadEiFrame, $toManyEditable);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::createDraftValueSelection($selectDraftStmtBuilder, $dm, $n2nContext)
	 */
	public function createDraftValueSelection(FetchDraftStmtBuilder $selectDraftStmtBuilder, DraftManager $dm, 
			N2nContext $n2nContext): DraftValueSelection {
		throw new NotYetImplementedException();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::supplyPersistDraftStmtBuilder($value, $oldValue, $persistDraftStmtBuilder, $persistDraftAction)
	 */
	public function supplyPersistDraftStmtBuilder($value, $oldValue, \rocket\spec\ei\manage\draft\stmt\PersistDraftStmtBuilder $persistDraftStmtBuilder, \rocket\spec\ei\manage\draft\PersistDraftAction $persistDraftAction) {
		throw new NotYetImplementedException();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::supplyRemoveDraftStmtBuilder($value, $oldValue, $removeDraftAction)
	 */
	public function supplyRemoveDraftStmtBuilder($value, $oldValue, RemoveDraftStmtBuilder $removeDraftStmtBuilder, 
			RemoveDraftAction $removeDraftAction) {
		throw new NotYetImplementedException();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\draft\DraftProperty::writeDraftValue($object, $value)
	 */
	public function writeDraftValue($object, $value) {
		throw new NotYetImplementedException();
	}
}
