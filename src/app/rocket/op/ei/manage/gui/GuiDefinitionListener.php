<?php
namespace rocket\op\ei\manage\gui;

interface GuiDefinitionListener {

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