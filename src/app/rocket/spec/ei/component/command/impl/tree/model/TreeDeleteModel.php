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

use n2n\persistence\orm\NestedSetUtils;
use rocket\spec\ei\component\command\impl\common\model\CommandEntryModelAdapter;
use rocket\spec\ei\manage\EiSelection;


class TreeDeleteModel extends CommandEntryModelAdapter {	
	private $rootIdPropertyName;
	private $leftPropertyName;
	private $rightPropertyName;
	
	public function __construct($rootIdPropertyName, $leftPropertyName, $rightPropertyName) {
		$this->rootIdPropertyName = $rootIdPropertyName;
		$this->leftPropertyName = $leftPropertyName;
		$this->rightPropertyName = $rightPropertyName;
	}	
	
	public function delete() {
		if ($this->eiSelection->isDraft()) {
			$this->draftModel->removeDraft($this->eiSelection->getDraft());
			return;
		}
		
		$class = $this->eiSpec->getEntityModel()->getTopEntityModel()->getClass();
		$entity = $this->eiSelection->getLiveEntityObj();
	
		$nestedSetUtils = new NestedSetUtils($this->em, $class);
		$nestedSetUtils->setRootIdPropertyName($this->rootIdPropertyName);
		$nestedSetUtils->setLeftPropertyName($this->leftPropertyName);
		$nestedSetUtils->setRightPropertyName($this->rightPropertyName);
		
		$nestedSetItemsToDelete = $nestedSetUtils->fetch($entity);
		
		foreach ($nestedSetItemsToDelete as $nesteSetItem) {
			$entity = $nesteSetItem->getObject();
			$this->eiState->triggerOnRemoveObject($this->em, 
					new EiSelection($this->eiSpec->extractId($entity), $entity));
			$nestedSetUtils->remove($nesteSetItem->getObject());
		}
	}	
}
