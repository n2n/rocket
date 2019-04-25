<?php
namespace rocket\ei\manage\gui;

use rocket\si\structure\SiFieldStructureDeclaration;

interface EiGuiSiFactory {

	/**
	 * @return \rocket\si\structure\SiFieldDeclaration[]
	 */
	public function getSiFieldDeclarations(): array;	
	
	/**
	 * @return \rocket\si\structure\SiFieldStructureDeclaration
	 */
	public function getSiFieldStructureDeclaration(): SiFieldStructureDeclaration;
}
