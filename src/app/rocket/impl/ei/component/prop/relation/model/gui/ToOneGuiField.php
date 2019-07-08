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
namespace rocket\impl\ei\component\prop\relation\model\gui;

use rocket\ei\manage\gui\field\GuiField;
use rocket\si\content\SiField;
use rocket\ei\util\Eiu;
use rocket\si\content\impl\SiFields;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use n2n\util\type\CastUtils;
use rocket\ei\util\entry\EiuEntry;

class ToOneGuiField implements GuiField {
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var SiField
	 */
	private $siField;
	
	function __construct(Eiu $eiu, RelationModel $relationModel, EditConfig $editConfig) {
		$this->eiu = $eiu;
		
		$targetEiuFrame = $eiu->frame()->forkSelect($eiu->prop()->getPath());
		
		$values = [];
		if (null !== ($eiuEntry = $eiu->field()->getValue())) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$values[] = $eiuEntry->createSiQualifier();
		}
		
		$this->siField = SiFields::apiSelectIn(
				$targetEiuFrame->getApiUrl($relationModel->getTargetReadEiCommandPath()),
				$values, ($editConfig->isMandatory() ? 1 : 0), 1);
	}
	
	function save() {
		
	}

	function getSiField(): SiField {
		return $this->siField;
	}	
}