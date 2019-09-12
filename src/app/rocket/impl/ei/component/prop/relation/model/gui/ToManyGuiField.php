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
use rocket\si\content\impl\QualifierSelectInSiField;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\si\content\impl\SiFields;
use n2n\util\type\CastUtils;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\EiPropPath;

class ToManyGuiField implements GuiField {
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var Eiu
	 */
	private $targetEiu;
	/**
	 * @var QualifierSelectInSiField
	 */
	private $siField;
	
	/**
	 * @param Eiu $eiu
	 * @param RelationModel $relationModel
	 */
	function __construct(Eiu $eiu, RelationModel $relationModel) {
		$this->eiu = $eiu;
		
		$this->targetEiuFrame = $eiu->frame()->forkSelect($eiu->prop()->getPath());
		
		$values = $this->readValues();
		
		$this->siField = SiFields::qualifierSelectIn(
				$this->targetEiuFrame->getApiUrl($relationModel->getTargetReadEiCommandPath()),
				$values, (int) $relationModel->getMin(), $relationModel->getMax());
	}
	
	private function readValues() {
		$values = [];
		foreach ($this->eiu->field()->getValue() as $eiuEntry) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$values[] = $eiuEntry->createSiQualifier();
		}
		
		if ($this->targetOrderEiPropPath === null) {
			return $values;
		}
		
		uasort($values, function(EiuEntry $a, EiuEntry $b) {
			$aValue = $a->getScalarValue($this->targetOrderEiPropPath);
			$bValue = $b->getScalarValue($this->targetOrderEiPropPath);
			
			if ($aValue == $bValue) {
				return 0;
			}
			
			return ($aValue < $bValue) ? -1 : 1;
		});
		
		return $values;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\field\GuiField::save()
	 */
	function save() {
		$values = [];
		foreach ($this->siField->getValues() as $siQualifier) {
			$id = $this->targetEiuFrame->siQualifierToId($siQualifier);
			$values[] = $this->targetEiuFrame->lookupEntry($id);
		}
		
		$this->eiu->field()->setValue($values);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\field\GuiField::getSiField()
	 */
	function getSiField(): SiField {
		return $this->siField;
	}	
}