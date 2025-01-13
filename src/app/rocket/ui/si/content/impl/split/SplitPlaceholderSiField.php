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

use rocket\ui\si\content\impl\SiFieldAdapter;
use n2n\core\container\N2nContext;

class SplitPlaceholderSiField extends SiFieldAdapter {

	/**
	 * @var SplitStyle
	 */
	private $copyStyle;
	
// 	/**
// 	 * @var \Closure|null
// 	 */
// 	private $saveCallback = null;
	
	/**
	 * @param string $refPropName
	 */
	function __construct(private string $refPropName) {
		$this->copyStyle = new SplitStyle(null, null);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'split-placeholder';
	}
	
	/**
	 * @param SplitStyle $splitStyle
	 * @return SplitPlaceholderSiField
	 */
	function setCopyStyle(SplitStyle $splitStyle) {
		$this->copyStyle = $splitStyle;
		return $this;
	}
	
	/**
	 * @return SplitStyle
	 */
	function getCopyStyle(): SplitStyle {
		return $this->copyStyle;
	}
	
// 	/**
// 	 * @param string $key
// 	 * @param SiField $field
// 	 * @return \rocket\si\content\impl\split\SplitPlaceholderSiField
// 	 */
// 	function putInputHandler(string $key, SiLazyInputHandler $inputHandler) {
// 		$this->inputHandlers[$key] = $inputHandler;
// 		return $this;
// 	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::toJsonStruct()
	 */
	function toJsonStruct(\n2n\core\container\N2nContext $n2nContext): array {
		return [
			'refPropId' => $this->refPropName,
			'copyStyle' => $this->copyStyle,
			...parent::toJsonStruct($n2nContext)
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::isReadOnly()
	 */
	function isReadOnly(): bool {
		return empty($this->inputHandlers);
	}
	
	/**
	 * {@inheritDoc}
	 * @param array $data
	 * @param N2nContext $n2nContext
	 * @see \rocket\ui\si\content\SiField::handleInput()
	 */
	function handleInput(array $data, N2nContext $n2nContext): bool {
//		$dataMap = (new DataSet($data))->reqArray('value', 'array');
//
//		foreach ($this->inputHandlers as $key => $inputHandler) {
//			if (isset($dataMap[$key])) {
//				$inputHandler->handleInput($dataMap[$key]);
//			}
//		}
		return true;
	}

	function flush(N2nContext $n2nContext): void {
		// TODO: Implement flush() method.
	}
}