<?php
namespace rocket\op\ei\manage\gui;

interface EiGuiListener {
	
	/**
	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 */
	public function onInitialized(EiGuiMaskDeclaration $eiGuiMaskDeclaration);


	public function onNewEiGuiEntry(EiGuiEntry $eiGuiEntry): void;

	/**
	 * 
	 */
	public function onGiBuild(EiGuiMaskDeclaration $eiGuiMaskDeclaration);
}