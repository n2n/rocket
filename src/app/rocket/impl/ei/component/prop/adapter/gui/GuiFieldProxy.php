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
namespace rocket\impl\ei\component\prop\adapter\gui;

use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\util\Eiu;
use rocket\si\content\SiField;

class GuiFieldProxy implements GuiField {
	private $eiu;
	private $statelessGuiFieldDisplayable;
	private $statelessGuiFieldEditable;
	
	private $siField;
	
	/**
	 * @param StatelessGuiFieldDisplayable $statelessGuiFieldDisplayable
	 * @param Eiu $eiu
	 */
	public function __construct(Eiu $eiu, StatelessGuiFieldDisplayable $statelessGuiFieldDisplayable, 
			StatelessGuiFieldEditable $statelessGuiFieldEditable = null) {
		$this->eiu = $eiu;
		$this->statelessGuiFieldDisplayable = $statelessGuiFieldDisplayable;
		$this->statelessGuiFieldEditable = $statelessGuiFieldEditable;
		
		if ($this->statelessGuiFieldEditable === null || $eiu->gui()->isReadOnly()
				|| (null !== $eiu->field(false) && !$eiu->field(false)->isWritable())) {
			$this->siField = $this->statelessGuiFieldDisplayable->createOutSiField($eiu);
			return;
		}
		
		$this->siField = $this->statelessGuiFieldEditable->createInSiField($eiu);
	}
	
	function getSiField(): SiField {
		return $this->siField;
	}
	
// 	/**
// 	 * @return bool
// 	 */
// 	public function isMandatory(): bool {
// 		if ($this->statelessGuiFieldEditable === null) {
// 			return false;
// 		}
		
// 		return $this->statelessGuiFieldEditable->isMandatory($this->eiu);
// 	}

// 	public function getEditable(): GuiFieldEditable {
// 		if ($this->statelessGuiFieldEditable === null) {
// 			throw new IllegalStateException();
// 		}
		
// 		return $this;
// 	}
	
// 	public function getSiField(): SiField {
// 		if ($this->siField !== null) {
// 			return $this->siField;
// 		}
		
// 		if ($this->statelessGuiFieldEditable === null || $this->eiu->gui()->isReadOnly()) {
// 			$siField = $this->statelessGuiFieldEditable->createOutSiField($this->eiu);
// 			ArgUtils::valTypeReturn($siField, SiField::class, $this->statelessGuiFieldDisplayable, 'createOutSiField');
// 			return $this->siField = $siField;
// 		}
		
// 		$siField = $this->statelessGuiFieldEditable->createInSiField($this->eiu);
// 		ArgUtils::valTypeReturn($siField, SiField::class, $this->statelessGuiFieldEditable, 'createInSiField');
		
// 		return $this->siField = $siField;
// 	}
	
	public function save() {
		if ($this->siField->isReadOnly() || $this->statelessGuiFieldEditable === null) {
			throw new IllegalStateException('Can not save ready only GuiField');
		}
		
		$this->statelessGuiFieldEditable->saveSiField($this->siField, $this->eiu);
	}
	

}
