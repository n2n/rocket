<?php
namespace rocket\ei\manage\gui;

interface EiGuiSiFactory {

	/**
	 * @return \rocket\si\structure\SiFieldDeclaration[]
	 */
	public function getSiFieldDeclarations(): array;	
	
	/**
	 * @return \rocket\si\structure\SiFieldStructureDeclaration[]
	 */
	public function getSiFieldStructureDeclarations(): array;
}
