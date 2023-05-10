<?php
namespace rocket\op\ei\manage\gui;

interface EiGuiSiFactory {
	
	/**
	 * @return \rocket\si\meta\SiStructureDeclaration
	 */
	public function getSiStructureDeclarations(): array;
}
