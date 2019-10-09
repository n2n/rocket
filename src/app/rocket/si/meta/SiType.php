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
namespace rocket\si\meta;

use n2n\util\type\ArgUtils;

class SiType implements \JsonSerializable {
	/**
	 * @var SiTypeQualifier
	 */	
	private $qualifier;
	/**
	 * @var SiProp[]
	 */
	private $props;
	
	/**
	 * @param SiProp[] $fieldDeclarations
	 */
	function __construct(SiTypeQualifier $qualifier, array $props = []) {
		$this->qualifier = $qualifier;
		$this->setProps($props);
	}
	
	/**
	 * @param SiProp[] $props
	 * @return \rocket\si\meta\SiProp
	 */
	function setProps(array $props) {
		ArgUtils::valArray($props, SiProp::class);
		$this->props = $props;
		return $this;
	}
	
	/**
	 * @param string $typeId
	 * @param SiProp[] $fieldDeclarations
	 * @return SiType
	 */
	function addProp(SiProp $prop) {
	    $this->props[] = $prop;
		return $this;
	}
	
	/**
	 * @return SiProp[]
	 */
	function getProps() {
		return $this->props;
	}

	
	/**
	 * @param SiTypeQualifier $qualifier
	 * @return SiType
	 */
	function setQualifier(SiTypeQualifier $qualifier) {
		$this->qualifier = $qualifier;
		return $this;
	}
	
	
	
	/**
	 * @return array
	 */
	function getQualifier() {
		return $this->qualifier;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'props' => $this->props,
			'qualifier' => $this->qualifier
		];
	}
}