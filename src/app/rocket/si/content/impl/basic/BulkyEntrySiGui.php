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
namespace rocket\si\content\impl\basic;

use rocket\si\content\SiGui;
use rocket\si\meta\SiDeclaration;
use rocket\si\content\SiEntry;
use rocket\si\control\SiControl;
use n2n\util\type\ArgUtils;
use rocket\si\SiPayloadFactory;

class BulkyEntrySiGui implements SiGui {
	private $declaration;
	private $entry;
	private $controls = [];
	
	function __construct(SiDeclaration $declaration, SiEntry $entry = null) {
		$this->declaration = $declaration;
		$this->setEntry($entry);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiGui::getTypeName()
	 */
	function getTypeName(): string {
		return 'bulky-entry';
	}
	
	/**
	 * @param SiEntry[] $siEntries
	 * @return BulkyEntrySiGui
	 */
	function setEntry(?SiEntry $entry) {
		$this->entry = $entry;
		return $this;
	}
	
	/**
	 * @return SiEntry[]
	 */
	function getEntry() {
		return $this->entry;
	}
	
	/**
	 * @param SiControl[] $controls
	 * @return BulkyEntrySiGui
	 */
	function setControls(array $controls) {
		ArgUtils::valArray($controls, SiControl::class);
		$this->controls = $controls;
		return $this;
	}
	
	/**
	 * @return SiControl[]
	 */
	function getControls() {
		return $this->controls;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiGui::getData()
	 */
	function getData(): array {
		return [ 
			'declaration' => $this->declaration,
			'entry' => $this->entry,
			'controls' => SiPayloadFactory::createDataFromControls($this->controls)
		];
	}
}