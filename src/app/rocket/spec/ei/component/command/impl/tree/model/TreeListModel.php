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
namespace rocket\spec\ei\component\command\impl\tree\model;

use n2n\persistence\orm\util\NestedSetUtils;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\component\command\impl\common\model\ListEntryModel;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\model\EntryTreeListModel;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\mask\EiMask;
 
class TreeListModel implements EntryTreeListModel {
	private $eiState;
	private $guiDefinition;
	private $entryModels = array();
	private $entryLevels = array();
	
	public function __construct(EiState $eiState) {
		$this->eiState = $eiState;
	}
				
	public function getEiState(): EiState {
		return $this->eiState;
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\model\ManageModel::getEiMask()
	 */
	public function getEiMask(): EiMask {
		return $this->eiState->getContextEiMask();
	}
	
	public function getEntryModels() {
		return $this->entryModels;
	}
	
	public function getEntryLevels() {
		return $this->entryLevels;
	}
	
	public function initialize() {
		$em = $this->eiState->getEntityManager();
		$eiSpec = $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec();
		
		$nestedSetUtils = new NestedSetUtils($em, $eiSpec->getEntityModel()->getClass());
		$criteria = $this->eiState->createCriteria(NestedSetUtils::NODE_ALIAS);
		$eiMask = $this->getEiMask();

		foreach ($nestedSetUtils->fetch(null, false, $criteria) as $nestedSetItem) {
			$entity = $nestedSetItem->getEntityObj();
			$id = $eiSpec->extractId($entity);
			$eiSelection = new EiSelection($id, $entity);
			$eiMapping = $eiMask->createEiMapping($this->eiState, $eiSelection);
			
			$this->entryModels[$id] = new ListEntryModel($eiMask, 
					$eiMask->createEiSelectionGui($this->eiState, $eiMapping, DisplayDefinition::VIEW_MODE_TREE, false),
					$eiMapping);
			$this->entryLevels[$id] = $nestedSetItem->getLevel();
		}
	}

}
