<?php
namespace rocket\op\ei\manage\gui;

interface GuiDefinitionListener {
	/**
	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 */
	public function onNewEiGuiMaskDeclaration(EiGuiMaskDeclaration $eiGuiMaskDeclaration);
	
	
// 	/**
// 	 * @param EiGuiValueBoundary $eiGuiValueBoundary
// 	 */
// 	public function onNewEiGuiValueBoundary(EiGuiValueBoundary $eiGuiValueBoundary);
	
// 	/**
// 	 * @param HtmlView $view
// 	 */
// 	public function onNewView(HtmlView $view);
}