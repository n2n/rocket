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
namespace rocket\si\content\impl;

use n2n\util\ex\IllegalStateException;
use n2n\util\uri\Url;

class LinkOutSiField extends OutSiFieldAdapter {
	private $url;
	private $label;
	private $href = false;
	
	function __construct(Url $url, string $label, bool $href) {
		$this->url = $url;
		$this->label = $label;
		$this->href = $href;
	}
	
	/**
	 * @return string
	 */
	function getLabel() {
		return $this->label;
	}
	
	/**
	 * @param string|null $label
	 * @return \rocket\si\content\impl\StringOutSiField
	 */
	function setLabel(string $label) {
		$this->label = $label;;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isHref() {
		return $this->href;
	}
	
	/**
	 * @param bool $href
	 * @return \rocket\si\content\impl\StringOutSiField
	 */
	function setHref(bool $href) {
		$this->href = $href;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'link-out';
	}
	
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'ref' => (string) $this->ref,
			'label' => $this->label,
			'href' => $this->href
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\impl\OutSiFieldAdapter::isReadOnly()
	 */
	function isReadOnly(): bool {
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\impl\OutSiFieldAdapter::handleInput()
	 */
	function handleInput(array $data) {
		throw new IllegalStateException();
	}
}
