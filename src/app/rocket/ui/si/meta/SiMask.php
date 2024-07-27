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
use rocket\ui\si\control\SiControl;
use rocket\ui\si\SiPayloadFactory;

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
	 * @var SiControl[]
	 */
	private array $controls = [];

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

	function putProp(string $controlName, SiProp $prop): static {
		$this->props[$controlName] = $prop;
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

	function putControl(string $controlName, SiControl $control): static {
		$this->controls[$controlName] = $control;
		return $this;
	}

	function getControl(string $controlName): ?SiControl {
		return $this->controls[$controlName] ?? null;
	}

	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize(): mixed {
		return [
			'qualifier' => $this->qualifier,
			'props' => $this->props,
			'controls' => SiPayloadFactory::createDataFromControls($this->controls),
			'structureDeclarations' => $this->structureDeclarations
		];
	}
}