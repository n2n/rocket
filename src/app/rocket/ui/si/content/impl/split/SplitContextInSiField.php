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
namespace rocket\ui\si\content\impl\split;

use n2n\util\type\attrs\DataSet;
use rocket\ui\si\content\impl\InSiFieldAdapter;
use n2n\util\uri\Url;
use rocket\ui\si\content\SiValueBoundary;
use n2n\util\ex\IllegalStateException;
use rocket\ui\si\meta\SiDeclaration;
use rocket\ui\si\api\request\SiEntryInput;
use rocket\ui\si\err\CorruptedSiDataException;
use n2n\util\type\ArgUtils;
use rocket\ui\si\meta\SiFrame;
use rocket\ui\si\api\request\SiValueBoundaryInput;

class SplitContextInSiField extends InSiFieldAdapter  {
	/**
	 * @var SplitStyle
	 */
	private $style;
	/**
	 * @var SplitStyle
	 */
	private $managerStyle;
	/**
	 * @var int
	 */
	private $min;
	/**
	 * @var string[]
	 */
	private $mandatoryKeys = [];
	/**
	 * @var string[]
	 */
	private $activeKeys = [];
	/**
	 * @var SiDeclaration
	 */
	private $declaration;
	/**
	 * @var SiSplitContent[]
	 */
	private $splitContents = [];
	
	/**
	 * @var \Closure[]
	 */
	private $siEntryCallbacks = [];
	
	/**
	 *
	 */
	function __construct(?SiDeclaration $declaration, private ?SiFrame $frame) {
		$this->declaration = $declaration;
		$this->style = new SplitStyle(null, null);
		$this->managerStyle = new SplitStyle(null, null);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'split-context-in';
	}
	
	/**
	 * @param SplitStyle $splitStyle
	 * @return SplitContextInSiField
	 */
	function setStyle(SplitStyle $splitStyle) {
		$this->style = $splitStyle;
		return $this;
	}
	
	/**
	 * @return \rocket\ui\si\content\impl\split\SplitStyle
	 */
	function getStyle() {
		return $this->style;
	}
	
	/**
	 * @param SplitStyle $splitStyle
	 * @return SplitContextInSiField
	 */
	function setManagerStyle(SplitStyle $splitStyle) {
		$this->managerStyle = $splitStyle;
		return $this;
	}
	
	/**
	 * @return \rocket\ui\si\content\impl\split\SplitStyle
	 */
	function getManagerStyle() {
		return $this->managerStyle;
	}
	
	/**
	 * @return int
	 */
	function getMin() {
		return $this->min;
	}
	
	/**
	 * @param int $min
	 * @return SplitContextInSiField
	 */
	function setMin(int $min) {
		$this->min = $min;
		return $this;
	}
	
	/**
	 * @return string[]
	 */
	function getActiveKeys() {
		return $this->activeKeys;
	}
	
	/**
	 * @param array $activeKeys
	 * @return SplitContextInSiField
	 */
	function setActiveKeys(array $activeKeys) {
		$this->activeKeys = $activeKeys;
		return $this;
	}
	
	/**
	 * @return string[]
	 */
	function getMandatoryKeys() {
		return $this->mandatoryKeys;
	}
	
	/**
	 * @param string[] $mandatoryKeys
	 * @return SplitContextInSiField
	 */
	function setMandatoryKeys(array $mandatoryKeys) {
		$this->mandatoryKeys = $mandatoryKeys;
		return $this;
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @param SiValueBoundary $entry
	 * @return \rocket\ui\si\content\impl\split\SiSplitContent
	 */
	function putEntry(string $key, string $label, SiValueBoundary $entry) {
		IllegalStateException::assertTrue($this->declaration !== null, 'No SiDeclaration defined.');
		return $this->splitContents[$key] = SiSplitContent::createValueBoundary($label, $entry);
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @param Url $apiUrl
	 * @param string $entryId
	 * @param bool $bulky
	 * @return \rocket\ui\si\content\impl\split\SiSplitContent
	 */
	function putLazy(string $key, string $label, Url $apiUrl, string $maskId, ?string $entryId/*, bool $bulky, bool $readOnly*/, \Closure $siEntryCallback) {
		IllegalStateException::assertTrue($this->declaration !== null, 'No SiDeclaration defined.');
		$this->siEntryCallbacks[$key] = $siEntryCallback;
		return $this->splitContents[$key] = SiSplitContent::createLazy($label, $apiUrl, $maskId, $entryId/*, new SiStyle($bulky, $readOnly)*/);
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @return SiSplitContent
	 */
	function putUnavailable(string $key, string $label): SiSplitContent {
		return $this->splitContents[$key] = SiSplitContent::createUnavailable($label);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			'style' => $this->style,
			'managerStyle' => $this->managerStyle,
			'min' => $this->min,
			'activeKeys' => $this->activeKeys,
			'mandatoryKeys' => $this->mandatoryKeys,
			'declaration' => $this->declaration,
			'frame' => $this->frame,
			'splitContents' => array_map(fn (SiSplitContent $c) => $c->toJsonStruct($n2nContext), $this->splitContents),
			...parent::toJsonStruct($n2nContext)
		];
	}

	function getValue(): array {
		return $this->activeKeys;
	}

	function handleInputValue(array $data, \n2n\core\container\N2nContext $n2nContext): bool {
		$ds = (new DataSet($data));

		$this->activeKeys = [];
		foreach ($ds->reqArray('activeKeys', 'string') as $key) {
			if (!isset($this->splitContents[$key])) {
				throw new CorruptedSiDataException('Unknown or unavailable key: ' . $key);
			}
			
			$this->activeKeys[] = $key;
		}

		$valid = true;
		foreach ($ds->reqArray('entryInputs', 'array') as $key => $entryInputData) {
			if (!in_array($key, $this->activeKeys)) {
				throw new CorruptedSiDataException('Unknown or active key: ' . $key);
			}
			
			$lazy = false;
			$siValueBoundary = $this->splitContents[$key]->getEntry(); 
			if ($siValueBoundary === null && isset($this->siEntryCallbacks[$key])) {
				$siValueBoundary = $this->siEntryCallbacks[$key]();
				ArgUtils::valTypeReturn($siValueBoundary, SiValueBoundary::class, null, $this->siEntryCallbacks[$key]);
				unset($this->siEntryCallbacks[$key]);
				$lazy = true;
			}
			
			if ($siValueBoundary === null) {
				throw new CorruptedSiDataException('No SiEntry available for key: ' . $key);
			}
			
			if (!$siValueBoundary->handleInput(SiValueBoundaryInput::parse($entryInputData), $n2nContext)) {
				$valid = false;
			}

			if ($lazy) {
				$preSplitContent = $this->splitContents[$key];
				$this->splitContents[$key] = SiSplitContent::createValueBoundary($preSplitContent->getLabel(), $siValueBoundary)
						->setShortLabel($preSplitContent->getShortLabel());
			}
		}

		return $valid;
	}
}
