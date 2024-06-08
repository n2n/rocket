<?php
namespace rocket\ui\gui;

use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;

interface EiGuiListener {
	
	/**
	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 */
	public function onInitialized(EiGuiMaskDeclaration $eiGuiMaskDeclaration);


	public function onNewEiGuiEntry(GuiEntry $eiGuiEntry): void;

	/**
	 * 
	 */
	public function onGiBuild(EiGuiMaskDeclaration $eiGuiMaskDeclaration);
}