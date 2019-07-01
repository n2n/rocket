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

use rocket\ei\util\Eiu;
use rocket\si\content\SiField;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ei\manage\gui\field\GuiField;
use n2n\util\ex\UnsupportedOperationException;
use rocket\si\content\impl\SiFields;
use n2n\util\type\CastUtils;
use rocket\ei\util\entry\EiuEntry;

class RelationLinkGuiField implements GuiField {
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var SiField
	 */
	private $siField;
	
	function __construct(Eiu $eiu, RelationModel $relationModel) {
		$this->eiu = $eiu;
		
		if ($relationModel->isTargetMany()) {
			$this->siField = $this->createToManySiField();
		} else {
			$this->siField = $this->createToOneSiField();
		}
	}
	
	private function createToManySiField() {
		$targetEiuFrame = $this->eiu->frame()->forkDiscover($this->eiu->prop());
		
		$num = $targetEiuFrame->countEntries();
		$label = null;
		if ($num == 1) {
			$label = $num . ' ' . $targetEiuFrame->engine()->mask()->getLabel();
		} else {
			$label = $num . ' ' . $targetEiuFrame->engine()->mask()->getPluralLabel();
		}
		
		if (null !== ($overviewUrl = $targetEiuFrame->getOverviewUrl(false))) {
			return SiFields::linkOut($overviewUrl, $label);
		}
		
		return SiFields::stringOut($label);
	}
	
	private function createToOneSiField() {
		$value = $this->eiu->field()->getValue();
		if ($value === null) {
			return SiFields::stringOut(null);
		}
		
		$label = $value->createIdentityString();
		
		CastUtils::assertTrue($value instanceof EiuEntry);
		$targetEiuFrame = $this->eiu->frame()->forkDiscover($this->eiu->prop(), $value);
		
		if (null !== ($detailUrl = $targetEiuFrame->getDetailUrl($value, false))) {
			return SiFields::linkOut($detailUrl, $label);
		}
		
		return SiFields::stringOut($label);
	}
	
	function getSiField(): SiField {
		return $this->siField;
	}
	
	public function save() {
		throw new UnsupportedOperationException();
	}

}