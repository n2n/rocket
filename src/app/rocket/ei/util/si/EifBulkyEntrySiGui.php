<?php

namespace rocket\ei\util\si;

use rocket\ei\util\Eiu;
use rocket\si\content\impl\basic\BulkyEntrySiGui;
use rocket\si\meta\SiDeclaration;
use rocket\si\content\SiField;
use rocket\si\meta\SiMaskDeclaration;

class EifBulkyEntrySiGui {

	private SiMaskDeclaration $siMaskDeclaration;

	function __construct(private Eiu $eiu) {
		$this->siMaskDeclaration = new SiMaskDeclaration();
	}

	function addSiFiled(string $propId, string $label, SiField $siField) {
		$this->siMaskDeclaration->addStructureDeclaration(new SiStructureDelc)
	}

	function asdf() {
		new BulkyEntrySiGui(null, new SiDeclaration());
	}





}