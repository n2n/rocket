<?php
namespace rocket\op\ei\manage\gui;

interface EiGuiListener {
	
	/**
	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 */
	public function onInitialized(EiGuiMaskDeclaration $eiGuiMaskDeclaration);

	/**
	 * @param EiGuiValueBoundary $eiGuiValueBoundary
	 */
	public function onNewEiGuiValueBoundary(EiGuiValueBoundary $eiGuiValueBoundary);

	/**
	 * 
	 */
	public function onGiBuild(EiGuiMaskDeclaration $eiGuiMaskDeclaration);
}