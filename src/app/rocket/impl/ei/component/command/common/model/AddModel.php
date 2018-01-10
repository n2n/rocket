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
namespace rocket\impl\ei\component\command\common\model;

use n2n\web\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnoInit;
use rocket\spec\ei\manage\util\model\EntryForm;
use n2n\l10n\MessageContainer;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use n2n\web\dispatch\map\bind\BindingDefinition;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\persistence\orm\util\NestedSetStrategy;
use n2n\util\ex\IllegalStateException;
use rocket\core\model\Rocket;

class AddModel implements Dispatchable  {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('entryForm'));
	}
	
	private $eiFrame;
	private $entryForm;
	private $nestedSetStrategy;
	private $parentEntityObj;
	private $beforeEntityObj;
	private $afterEntityObj;
	
	public function __construct(EiFrame $eiFrame, EntryForm $entryForm, NestedSetStrategy $nestedSetStrategy = null) {
		$this->eiFrame = $eiFrame;
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
	 * @see \rocket\impl\ei\component\command\common\model\EntryCommandModel::getEntryModel()
	 */
// 	public function getCurrentEntryModel() {
// 		return $this->entryForm->getEntryTypeForm();
// 	}
	
// 	public function getEiFrame() {
// 		return $this->entryManager->getEiFrame();
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
		$em = $this->eiFrame->getManageState()->getEntityManager();
		if ($this->nestedSetStrategy === null) {
			$em->persist($entityObj);
			$em->flush();
			return;
		}
			
		$nsu = new NestedSetUtils($em, $this->eiFrame->getContextEiMask()->getEiEngine()->getEiType()->getEntityModel()->getClass(),
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
		$eiuEntry = $this->entryForm->buildEiuEntry();
		
		if (!$eiuEntry->getEiEntry()->save()) {
			$messageContainer->addErrorCode('common_form_err', null, null, Rocket::NS);
			return false;
		}
		
		// @todo think!!!
		$eiObject = $eiuEntry->getEiEntry()->getEiObject();
		
		if (!$eiObject->isDraft()) {
			$eiEntityObj = $eiObject->getEiEntityObj();
			$entityObj = $eiEntityObj->getEntityObj();
			$this->persist($entityObj);
			
			$eiEntityObj->refreshId();
			$eiEntityObj->setPersistent(true);
			
			$identityString = (new EiuFrame($this->eiFrame))->createIdentityString($eiObject);
			$messageContainer->addInfoCode('ei_impl_added_info', array('entry' => $identityString));
			
			return $eiObject;
		}
		
		IllegalStateException::assertTrue($this->nestedSetStrategy === null);
		
		$draft = $eiObject->getDraft();
		$draftDefinition = $this->entryForm->getChosenEntryTypeForm()->getEntryGuiModel()->getEiMask()->getEiEngine()
				->getDraftDefinition();
		$draftManager = $this->eiFrame->getManageState()->getDraftManager();
		$draftManager->persist($draft, $draftDefinition);
		$draftManager->flush();
		
		$identityString = (new EiuFrame($this->eiFrame))->createIdentityString($eiObject);
		$messageContainer->addInfoCode('ei_impl_added_draft_info', array('entry' => $identityString));
		
		return $eiObject;
	}
	
	public function createAndRepeate(MessageContainer $messageContainer) {
		$this->create($messageContainer);
	}
}
