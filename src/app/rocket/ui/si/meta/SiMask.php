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
namespace rocket\ui\si\meta;

use n2n\util\type\ArgUtils;

class SiMask implements \JsonSerializable {

	/**
	 * @var SiProp[]|null
	 */
	private ?array $props = null;

	/**
	 * @var SiStructureDeclaration[]|null
	 */
	private ?array $structureDeclarations = null;

	/**
	 * @param SiMaskQualifier $qualifier
	 * @param SiProp[]|null $props
	 * @param SiStructureDeclaration[]|null $structureDeclarations
	 */
	function __construct(private SiMaskQualifier $qualifier, ?array $props = [], ?array $structureDeclarations = []) {
		$this->setProps($props);
		$this->setStructureDeclarations($structureDeclarations);
	}


	/**
	 * @param SiMaskQualifier $qualifier
	 * @return SiMask
	 */
	function setQualifier(SiMaskQualifier $qualifier): static {
		$this->qualifier = $qualifier;
		return $this;
	}

	/**
	 * @return SiMaskQualifier
	 */
	function getQualifier(): SiMaskQualifier {
		return $this->qualifier;
	}

	/**
	 * @param SiProp[] $props
	 * @return \rocket\ui\si\meta\SiProp
	 */
	function setProps(?array $props): static {
		ArgUtils::valArray($props, SiProp::class, true);
		$this->props = $props;
		return $this;
	}

	function hasProps(): bool {
		return $this->props !== null;
	}

	function addProp(SiProp $prop): static {
		$this->props[] = $prop;
		return $this;
	}

	/**
	 * @return SiProp[]|null
	 */
	function getProps(): ?array {
		return $this->props;
	}

	/**
	 * @param SiStructureDeclaration[] $structureDeclarations
	 * @return SiMask
	 */
	function setStructureDeclarations(?array $structureDeclarations): static {
		ArgUtils::valArray($structureDeclarations, SiStructureDeclaration::class, true);
		$this->structureDeclarations = $structureDeclarations;
		return $this;
	}

	/**
	 * @param SiStructureDeclaration $structureDeclaration
	 * @return SiMask
	 */
	function addStructureDeclaration(SiStructureDeclaration $structureDeclaration): static {
		$this->structureDeclarations[] = $structureDeclaration;
		return $this;
	}
	
	function hasStructureDeclarations(): bool {
		return $this->structureDeclarations !== null;
	}
	
	/**
	 * @return SiStructureDeclaration[]|null
	 */
	function getStructureDeclarations(): ?array {
		return $this->structureDeclarations;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize(): mixed {
		return [
			'qualifier' => $this->qualifier,
			'props' => $this->props,
			'structureDeclarations' => $this->structureDeclarations
		];
	}
}