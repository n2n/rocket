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

use n2n\reflection\annotation\AnnoInit;
use n2n\persistence\orm\NestedSetUtils;
use rocket\spec\ei\component\command\impl\common\model\AddModel;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\util\model\EntryForm;
use rocket\spec\ei\manage\util\model\EntryManager;
use rocket\spec\ei\manage\mapping\MappingValidationResult;
use n2n\l10n\MessageContainer;
use n2n\persistence\orm\model\EntityModel;
use n2n\web\dispatch\annotation\AnnoDispProperties;

class TreeAddModel extends AddModel {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('entryForm'));
	}
	
	private $entryManager;
	private $entryForm;
	private $treeEntityModel;
	private $leftPropertyName;
	private $rightPropertyName;
	private $parentEiObject;
	
	public function __construct(EntryManager $entryManager, EntryForm $entryForm, 
			EntityModel $treeEntityModel, $leftPropertyName, $rightPropertyName) {
		$this->entryManager = $entryManager;
		$this->entryForm = $entryForm;
		
		$this->treeEntityModel = $treeEntityModel;
		$this->leftPropertyName = $leftPropertyName;
		$this->rightPropertyName = $rightPropertyName;
	}	
	
	public function setParentEntity(EiObject $parentEiObject) {
		return $this->parentEiObject = $parentEiObject;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\command\impl\common\model\EntryCommandModel::getEntryModel()
	 */
	public function getCurrentEntryModel() {
		return $this->entryForm->getMainEntryFormPart();
	}
	
	public function getEntryForm() {
		return $this->entryForm;
	}
	
	public function setEntryForm(EntryForm $entryForm) {
		$this->entryForm = $entryForm;
	}
	
	public function create(MessageContainer $messageContainer) {
		$parentEntity = null;
		if ($this->parentEiObject !== null) {
			$parentEntity = $this->parentEiObject->getLiveEntityObj();
		}
		
		$eiMapping = $this->entryForm->buildEiMapping();
		
		$this->entryManager->create($eiMapping);

		$mappingValidationResult = new MappingValidationResult();
		if (!$eiMapping->save($mappingValidationResult)) {
			$messageContainer->addAll($mappingValidationResult->getMessages());
			return false;
		}
		
		$entity = $eiMapping->getEiObject()->getEntityObj();
		$eiFrame = $this->getCurrentEntryModel()->getEiFrame();
		$em = $eiFrame->getEntityManager();
		
		$nestedSetUtils = new NestedSetUtils($em, $this->treeEntityModel->getClass());
		$nestedSetUtils->setRootIdPropertyName($this->rootIdPropertyName);
		$nestedSetUtils->setLeftPropertyName($this->leftPropertyName);
		$nestedSetUtils->setRightPropertyName($this->rightPropertyName);
		$nestedSetUtils->insert($entity, $parentEntity);
		
		$em->flush();

		return new EiObject($eiFrame->getContextEiMask()->getEiEngine()->getEiSpec()->extractId($entity), $entity);
	}
}
