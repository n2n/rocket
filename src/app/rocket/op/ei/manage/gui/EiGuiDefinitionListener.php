<?php
namespace rocket\op\ei\manage\gui;

interface EiGuiDefinitionListener {

	public function onNewEiGuiMaskDeclaration(EiGuiMaskDeclaration $eiGuiMaskDeclaration): void;
	
	
// 	/**
// 	 * @param EiGuiValueBoundary $eiGuiValueBoundary
// 	 */
// 	public function onNewEiGuiValueBoundary(EiGuiValueBoundary $eiGuiValueBoundary);
	
// 	/**
// 	 * @param HtmlView $view
// 	 */
// 	public function onNewView(HtmlView $view);
}