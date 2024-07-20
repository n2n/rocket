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

use n2n\util\uri\Url;
use rocket\ui\si\content\SiValueBoundary;
use rocket\ui\si\content\impl\OutSiFieldAdapter;
use rocket\ui\si\meta\SiDeclaration;
use n2n\util\ex\IllegalStateException;
use rocket\ui\si\meta\SiStyle;

class SplitContextOutSiField extends OutSiFieldAdapter {
	/**
	 * @var SplitStyle|null
	 */
	private $style;
	/**
	 * @var SiDeclaration
	 */
	private $declaration;
	/**
	 * @var SiSplitContent[]
	 */
	private $splitContents = [];
	
	/**
	 * 
	 */
	function __construct(?SiDeclaration $declaration, private ?SiFrame $frame) {
		$this->declaration = $declaration;
		$this->style = new SplitStyle(null, null);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'split-context-out';
	}
	
	/**
	 * @param SplitStyle $splitStyle
	 * @return SplitContextOutSiField
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
	 * @param string $key
	 * @param string $label
	 * @param SiValueBoundary $valueBoundary
	 * @return SiSplitContent
	 */
	function putValueBoundary(string $key, string $label, SiValueBoundary $valueBoundary): SiSplitContent {
		IllegalStateException::assertTrue($this->declaration !== null, 'No SiDeclaration defined.');
		return $this->splitContents[$key] = SiSplitContent::createValueBoundary($label, $valueBoundary);
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @param Url $apiUrl
	 * @param string $entryId
	 * @param bool $bulky
	 * @return SiSplitContent
	 */
	function putLazy(string $key, string $label, Url $apiUrl, string $entryId, bool $bulky, bool $readOnly) {
		IllegalStateException::assertTrue($this->declaration !== null, 'No SiDeclaration defined.');
		return $this->splitContents[$key] = SiSplitContent::createLazy($label, $apiUrl, $entryId, new SiStyle($bulky, $readOnly));
	}
	
	/**
	 * @param string $key
	 * @param string $label
	 * @return SiSplitContent
	 */
	function putUnavailable(string $key, string $label) {
		return $this->splitContents[$key] = SiSplitContent::createUnavailable($label);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'style' => $this->style,
			'declaration' => $this->declaration,
			'frame' => $this->frame,
			'splitContents' => $this->splitContents,
			...parent::getData()
		];
	}
}
