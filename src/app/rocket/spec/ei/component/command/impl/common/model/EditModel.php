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

use n2n\reflection\annotation\AnnoInit;
use rocket\spec\ei\manage\util\model\EntryForm;
use n2n\web\dispatch\Dispatchable;
use n2n\l10n\MessageContainer;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use n2n\web\dispatch\map\bind\BindingDefinition;
use rocket\spec\ei\manage\mapping\MappingValidationResult;
use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\manage\mapping\EiMapping;
use n2n\util\ex\IllegalStateException;
use rocket\core\model\Rocket;

class EditModel implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('entryForm'));
	}
		
	private $draftingAllowed;
	private $publishingAllowed;
		
	private $eiFrameUtils;
	private $entryForm;
	private $entryModel;
		
	public function __construct(EiuFrame $eiFrameUtils, $draftingAllowed, $publishingAllowed) {
		$this->eiFrameUtils = $eiFrameUtils;
	}
	
	public function initialize(EiMapping $eiMapping) {
		$this->entryForm = $this->eiFrameUtils->createEntryFormFromMapping($eiMapping);

		IllegalStateException::assertTrue(!$this->entryForm->isChoosable());
		$this->entryModel = $this->entryForm->getChosenEntryModelForm();
	}
	
// 	public function getEiFrame() {
// 		return $this->entryManager->getEiFrame();
// 	}
	
	public function setPublishAllowed($publishAllowed) {
		$this->publishAllowed = $publishAllowed;
	}
	
	public function isPublishAllowed() {
		return $this->publishAllowed;
	}
	
	public function isDraftable() {
		return $this->draftingAllowed && $this->entryModel->getEiMask()->isDraftingEnabled();
	}
	
	public function isPublishable() {
		return $this->publishingAllowed && $this->entryModel
				->getEiMapping()->getEiEntry()->isDraft();
	}
	
	public function getEntryModel() {
		return $this->entryModel;
	}
		
	public function getEntryForm() {
		return $this->entryForm;
	}
	
	public function setEntryForm(EntryForm $entryForm) {
		$this->entryForm = $entryForm;
	}
	
	private function _validation(BindingDefinition $bd) {
	
	}
	
	public function save(MessageContainer $messageContainer) {
		$eiMapping = $this->entryForm->buildEiMapping();
		
		if ($eiMapping->save()) {
			$this->eiFrameUtils->persist($eiMapping);
			return true;
		}
		
		$messageContainer->addErrorCode('common_form_err', null, null, Rocket::NS);
		return false;
	}
	
	public function quicksave(MessageContainer $messageContainer) {
		return $this->save($messageContainer);
	}
	
	
	
	public function saveAsNewDraft(MessageContainer $messageContainer) {
		if (!$this->isDraftable()) return null;
		
		$eiMapping = $this->entryForm->buildEiMapping();
		$draftedEiMapping = $eiMapping->createDraftedCopy();
		
		if ($draftedEiMapping->save()) {
			return $draftedEiMapping->getDraft();
		}
		
		$this->initialize($draftedEiMapping);
		
		$messageContainer->addAll($mappingValidationResult->getMessages());
		return null;
	}
	
	public function saveAndPublish(MessageContainer $messageContainer) {
		if (!$this->isPublishable()) {
			return false;
		}
		
		$eiMapping = $this->entryForm->buildEiMapping();
		IllegalStateException::assertTrue($eiMapping->getEiEntry()->isDraft());
		
		return $eiMapping->save();
	}
}
