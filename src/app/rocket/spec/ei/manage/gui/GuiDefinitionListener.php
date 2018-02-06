<?php
namespace rocket\spec\ei\manage\gui;

use n2n\impl\web\ui\view\html\HtmlView;

interface GuiDefinitionListener {
	/**
	 * @param EiGui $eiGui
	 */
	public function onNewEiGui(EiGui $eiGui);
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 */
	public function onNewEiEntryGui(EiEntryGui $eiEntryGui);
	
	/**
	 * @param HtmlView $view
	 */
	public function onNewView(HtmlView $view);
}