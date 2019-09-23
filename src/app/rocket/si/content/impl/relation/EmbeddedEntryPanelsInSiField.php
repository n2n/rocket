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
namespace rocket\si\content\impl\relation;

use n2n\util\type\ArgUtils;
use n2n\util\type\attrs\DataSet;
use n2n\util\uri\Url;
use rocket\si\content\impl\InSiFieldAdapter;

class EmbeddedEntryPanelsInSiField extends InSiFieldAdapter {
	
	/**
	 * @var Url
	 */
	private $apiUrl;
	/**
	 * @var EmbeddedEntryPanelInputHandler
	 */
	private $inputHandler;
	/**
	 * @var SiPanel[]
	 */
	private $panels;
	
	/**
	 * @param Url $apiUrl
	 * @param EmbeddedEntryPanelInputHandler $inputHandler
	 * @param SiPanel[] $panels
	 */
	function __construct(Url $apiUrl, EmbeddedEntryPanelInputHandler $inputHandler, array $panels = []) {
		$this->apiUrl = $apiUrl;
		$this->inputHandler = $inputHandler;
		$this->setPanels($panels);
	}
	
	/**
	 * @param Url $apiUrl
	 * @return EmbeddedEntryPanelsInSiField
	 */
	function setApiUrl(Url $apiUrl) {
		$this->apiUrl = $apiUrl;
		return $this;
	}
	
	/**
	 * @return Url|null
	 */
	function getApiUrl() {
		return $this->apiUrl;
	}
	
	/**
	 * @param SiPanel[] $panels
	 * @return EmbeddedEntryPanelsInSiField
	 */
	function setPanels(array $panels) {
		ArgUtils::valArray($panels, SiPanel::class);
		$this->panels = $panels;
		return $this;
	}
	
	/**
	 * @return SiPanel[]
	 */
	function getPanels() {
		return $this->panels;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'embedded-entry-panels-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'panels' => $this->panels,
			'apiUrl' => (string) $this->apiUrl,
			'reduced' => $this->reduced,
			'sortable' => $this->sortable,
			'pasteCategory' => $this->pasteCategory,
		];
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$siPanelInputs = [];
		foreach ((new DataSet($data))->reqArray('panelInputs', 'array') as $panelInputData) {
			$siPanelInputs[] = SiPanelInput::parse($panelInputData);
		}
		$panels = $this->inputHandler->handleInput($siPanelInputs);
		ArgUtils::valArrayReturn($panels, $this->inputHandler, 'handleInput', SiPanelInput::class);
		$this->panels = $panels;
	}
}
