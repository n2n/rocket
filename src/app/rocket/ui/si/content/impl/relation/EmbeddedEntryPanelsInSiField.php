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
namespace rocket\ui\si\content\impl\relation;

use n2n\util\type\ArgUtils;
use n2n\util\type\attrs\DataSet;
use n2n\util\uri\Url;
use rocket\ui\si\content\impl\InSiFieldAdapter;
use rocket\ui\si\meta\SiFrame;

class EmbeddedEntryPanelsInSiField extends InSiFieldAdapter {
	/**
	 * @var SiFrame
	 */
	private $frame;
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
	 * @param SiPanel[] $panels
	 */
	function __construct(SiFrame $frame, array $panels = []) {
		$this->frame = $frame;
		$this->setPanels($panels);
	}

	/**
	 * @param SiPanel[] $panels
	 * @return EmbeddedEntryPanelsInSiField
	 */
	function setPanels(array $panels): static {
		ArgUtils::valArray($panels, SiPanel::class);
		$this->panels = $panels;
		return $this;
	}

	function putPanel(SiPanel $panel): static {
		$this->panels[] = $panel;
		return $this;
	}

	/**
	 * @return SiPanel[]
	 */
	function getPanels(): array {
		return $this->panels;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'embedded-entries-panels-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			'panels' => array_map(fn (SiPanel $p) => $p->toJsonStruct($n2nContext), $this->panels),
			'frame' => $this->frame,
			...parent::toJsonStruct($n2nContext)
		];
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::handleInput()
	 */
	function handleInputValue(array $data, \n2n\core\container\N2nContext $n2nContext): bool {
		$valueBoundaryInputsMap = [];
		foreach ((new DataSet($data))->reqArray('panelInputs', 'array') as $panelInputData) {
			$panelInput = SiPanelInput::parse($panelInputData);
			$valueBoundaryInputsMap[$panelInput->getName()] = $panelInput->getValueBoundaryInputs();
		}

		$valid = true;
		foreach ($this->panels as $panel) {
			if (!$panel->handleInput($valueBoundaryInputsMap[$panel->getName()] ?? [], $n2nContext)) {
				$valid = false;
			}
		}

		return $valid;
	}

	function getValue(): array {
		return $this->panels;
	}
}
