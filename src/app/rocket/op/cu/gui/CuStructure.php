<?php

namespace rocket\op\cu\gui;

use rocket\ui\si\meta\SiMask;
use rocket\ui\si\meta\SiStructureDeclaration;
use rocket\ui\si\meta\SiProp;
use rocket\ui\si\meta\SiStructureType;
use n2n\util\ex\DuplicateElementException;
use rocket\op\cu\gui\field\CuField;

class CuStructure {

	function __construct(private readonly CuGuiEntry $cuGuiEntry,
			private readonly SiMask $siMaskDeclaration,
			private readonly ?SiStructureDeclaration $siStructureDeclaration) {
	}

	function addCuField(string $propId, string $label, CuField $cuField, string $helpText = null,
			string $siStructureType = SiStructureType::ITEM): static {
		if ($this->cuGuiEntry->containsCuField($propId)) {
			throw new DuplicateElementException('Property id already exist: ' . $propId);
		}

		$siProp = new SiProp($propId, $label);
		$siProp->setHelpText($helpText);
		$this->siMaskDeclaration->getMask()->addProp($siProp);

		$this->addSiStructureDeclaration(SiStructureDeclaration::createProp($siStructureType, $propId));

		$this->cuGuiEntry->putCuField($propId, $cuField);

		return $this;
	}

	private function addSiStructureDeclaration(SiStructureDeclaration $siStructureDeclaration): void {
		if ($this->siStructureDeclaration !== null) {
			$this->siStructureDeclaration->addChild($siStructureDeclaration);
			return;
		}

		$this->siMaskDeclaration->addStructureDeclaration($siStructureDeclaration);
	}

}