<?php

namespace rocket\ei\util\si;

use rocket\si\meta\SiMaskDeclaration;
use rocket\si\meta\SiStructureDeclaration;
use rocket\si\content\SiField;
use rocket\si\meta\SiProp;
use rocket\si\content\SiEntryBuildup;
use rocket\si\meta\SiStructureType;

class EifSiStructure {

	function __construct(private readonly SiEntryBuildup $siEntryBuildup,
			private readonly SiMaskDeclaration $siMaskDeclaration,
			private readonly ?SiStructureDeclaration $siStructureDeclaration) {
	}

	function addField(string $propId, string $label, SiField $field, string $helpText = null,
			string $siStructureType = SiStructureType::ITEM): static {
		$siProp = new SiProp($propId, $label);
		$siProp->setHelpText($helpText);
		$this->siMaskDeclaration->getMask()->addProp($siProp);

		$this->addSiStructureDeclaration(SiStructureDeclaration::createProp($siStructureType, $propId));

		$this->siEntryBuildup->putField($propId, $field);

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