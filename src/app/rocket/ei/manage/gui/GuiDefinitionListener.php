<?php
namespace rocket\ei\manage\gui;

interface GuiDefinitionListener {
	/**
	 * @param EiGui $eiGui
	 */
	public function onNewEiGui(EiGui $eiGui);
	
	
// 	/**
// 	 * @param EiEntryGui $eiEntryGui
// 	 */
// 	public function onNewEiEntryGui(EiEntryGui $eiEntryGui);
	
// 	/**
// 	 * @param HtmlView $view
// 	 */
// 	public function onNewView(HtmlView $view);
}