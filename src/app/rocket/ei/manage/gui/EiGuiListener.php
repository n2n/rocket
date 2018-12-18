<?php
namespace rocket\ei\manage\gui;

use n2n\impl\web\ui\view\html\HtmlView;

interface EiGuiListener {
	
	/**
	 * @param EiGui $eiGui
	 */
	public function onInitialized(EiGui $eiGui);

	public function onNewEiEntryGui(EiEntryGui $eiEntryGui);

	public function onNewView(HtmlView $view);
}