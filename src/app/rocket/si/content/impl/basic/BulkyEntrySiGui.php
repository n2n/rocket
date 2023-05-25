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
use rocket\si\content\SiValueBoundary;
use rocket\si\control\SiControl;
use n2n\util\type\ArgUtils;
use rocket\si\SiPayloadFactory;
use rocket\si\meta\SiFrame;
use rocket\si\input\SiInput;
use rocket\si\input\CorruptedSiInputDataException;

class BulkyEntrySiGui implements SiGui {
	private $frame;
	private $declaration;
	private $valueBoundary;
	private $controls = [];
	private $entryControlsIncluded = true;
	
	function __construct(?SiFrame $frame, SiDeclaration $declaration, SiValueBoundary $valueBoundary = null) {
		$this->frame = $frame;
		$this->declaration = $declaration;
		$this->setValueBoundary($valueBoundary);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiGui::getTypeName()
	 */
	function getTypeName(): string {
		return 'bulky-entry';
	}

	function setValueBoundary(?SiValueBoundary $entry): static {
		$this->valueBoundary = $entry;
		return $this;
	}

	function getValueBoundary(): ?SiValueBoundary {
		return $this->valueBoundary;
	}
	
	/**
	 * @param SiControl[] $controls
	 * @return BulkyEntrySiGui
	 */
	function setControls(array $controls): static {
		ArgUtils::valArray($controls, SiControl::class);
		$this->controls = $controls;
		return $this;
	}
	
	/**
	 * @return SiControl[]
	 */
	function getControls(): array {
		return $this->controls;
	}
	
	/**
	 * @param bool $entryControlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiGui
	 */
	function setEntryControlsIncluded(bool $entryControlsIncluded) {
		$this->entryControlsIncluded = $entryControlsIncluded;
		return $this;
	}
	
	/**
	 * Whether this SiGui will request the entry controls of the entry when it has to be reloaded.
	 * @param bool $entryControlsIncluded
	 * @return bool
	 */
	function getEntryControlsIncluded(bool $entryControlsIncluded) {
		return $this->entryControlsIncluded;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiGui::getData()
	 */
	function getData(): array {
		return [ 
			'frame' => $this->frame,
			'declaration' => $this->declaration,
			'valueBoundary' => $this->valueBoundary,
			'controls' => SiPayloadFactory::createDataFromControls($this->controls),
			'entryControlsIncluded' => $this->entryControlsIncluded
		];
	}
}
