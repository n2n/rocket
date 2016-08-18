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
namespace rocket\spec\ei\component\command\impl\common\model;

use n2n\web\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnoInit;
use rocket\spec\ei\manage\util\model\EntryForm;
use rocket\spec\ei\manage\util\model\EntryManager;
use n2n\l10n\MessageContainer;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use n2n\web\dispatch\map\bind\BindingDefinition;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\util\model\EiStateUtils;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\persistence\orm\util\NestedSetStrategy;
use n2n\util\ex\IllegalStateException;

class AddModel implements Dispatchable  {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('entryForm'));
	}
	
	private $eiState;
	private $entryForm;
	private $nestedSetStrategy;
	private $parentEntityObj;
	private $beforeEntityObj;
	private $afterEntityObj;
	
	public function __construct(EiState $eiState, EntryForm $entryForm, NestedSetStrategy $nestedSetStrategy = null) {
		$this->eiState = $eiState;
		$this->entryForm = $entryForm;
		$this->nestedSetStrategy = $nestedSetStrategy;
	}
	
	public function setParentEntityObj($parentEntityObj) {
		$this->parentEntityObj = $parentEntityObj;
	}
	
	public function setBeforeEntityObj($beforeEntityObj) {
		$this->beforeEntityObj = $beforeEntityObj;
	}
	
	public function setAfterEntityObj($afterEntityObj) {
		$this->afterEntityObj = $afterEntityObj;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\command\impl\common\model\EntryCommandModel::getEntryModel()
	 */
// 	public function getCurrentEntryModel() {
// 		return $this->entryForm->getEntryModelForm();
// 	}
	
// 	public function getEiState() {
// 		return $this->entryManager->getEiState();
// 	}
	
	public function getEntryForm() {
		return $this->entryForm;
	}
	
	public function setEntryForm(EntryForm $entryForm) {
		$this->entryForm = $entryForm;
	}
	
	private function _validation(BindingDefinition $bd) {
	}
	
	private function persist($entityObj) {
		$em = $this->eiState->getManageState()->getEntityManager();
		if ($this->nestedSetStrategy === null) {
			$em->persist($entityObj);
			$em->flush();
			return;
		}
			
		$nsu = new NestedSetUtils($em, $this->eiState->getContextEiMask()->getEiEngine()->getEiSpec()->getEntityModel()->getClass(),
				$this->nestedSetStrategy);
		
		if ($this->beforeEntityObj !== null) {
			$nsu->insertBefore($entityObj, $this->beforeEntityObj);
		} else if ($this->afterEntityObj !== null) {
			$nsu->insertAfter($entityObj, $this->afterEntityObj);
		} else {
			$nsu->insert($entityObj, $this->parentEntityObj);
		}
	}
		
	public function create(MessageContainer $messageContainer) {
		$eiMapping = $this->entryForm->buildEiMapping();
		
		if (!$eiMapping->save()) {
			return false;
		}
		
		// @todo think!!!
		$eiSelection = $eiMapping->getEiSelection();
		
		if (!$eiSelection->isDraft()) {
			$liveEntry = $eiSelection->getLiveEntry();
			$entityObj = $liveEntry->getEntityObj();
			$this->persist($entityObj);
			
			$liveEntry->refreshId();
			$liveEntry->setPersistent(false);
			
			$identityString = (new EiStateUtils($this->eiState))->createIdentityString($eiSelection);
			$messageContainer->addInfoCode('ei_impl_added_info', array('entry' => $identityString));
			
			return $eiSelection;
		}
		
		IllegalStateException::assertTrue($this->nestedSetStrategy === null);
		
		$draft = $eiSelection->getDraft();
		$draftDefinition = $this->entryForm->getChosenEntryModelForm()->getEntryGuiModel()->getEiMask()->getEiEngine()
				->getDraftDefinition();
		$draftManager = $this->eiState->getManageState()->getDraftManager();
		$draftManager->persist($draft, $draftDefinition);
		
		$identityString = (new EiStateUtils($this->eiState))->createIdentityString($eiSelection);
		$messageContainer->addInfoCode('ei_impl_added_draft_info', array('entry' => $identityString));
		
		return $eiSelection;
	}
	
	public function createAndRepeate(MessageContainer $messageContainer) {
		$this->create($messageContainer);
	}
}
