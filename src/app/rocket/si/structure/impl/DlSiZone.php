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
namespace rocket\si\structure\impl;

use n2n\util\uri\Url;
use rocket\si\structure\SiZone;
use rocket\si\structure\SiBulkyDeclaration;
use rocket\si\content\SiEntry;
use n2n\util\type\ArgUtils;
use rocket\si\control\SiControl;

class DlSiZone implements SiZone {
	private $apiUrl;
	private $bulkyDeclaration;
	private $entries;
	private $controls;
	
	public function __construct(Url $apiUrl, SiBulkyDeclaration $bulkyDeclaration,
			array $entries = [], array $controls = []) {
		$this->apiUrl = $apiUrl;
		$this->bulkyDeclaration = $bulkyDeclaration;
		$this->setEntries($entries);
		$this->setControls($controls);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\structure\SiZone::getTypeName()
	 */
	public function getTypeName(): string {
		return 'dl';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\structure\SiZone::getApiUrl()
	 */
	public function getApiUrl(): Url {
		return $this->apiUrl;
	}
	
	/**
	 * @param SiEntry[] $siEntries
	 * @return \rocket\si\structure\SiCompactDeclaration
	 */
	function setEntries(array $entries) {
		ArgUtils::valArray($entries, SiEntry::class);
		$this->entries = $entries;
		return $this;
	}
	
	/**
	 * @return SiEntry[]
	 */
	function getEntries() {
		return $this->entries;
	}
	
	/**
	 * @param SiControl[] $controls
	 * @return \rocket\si\structure\SiBulkyDeclaration
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
	
	public function getData(): array {
		$controlsArr = array();
		foreach ($this->controls as $id => $control) {
			$controlsArr[$id] = [
					'type' => $control->getType(),
					'data' => $control->getData()
			];
		}
		
		return [ 
			'bulkyDeclaration' => $this->bulkyDeclaration,
			'entries' => $this->entries,
			'controls' => $controlsArr
		];
	}
}
