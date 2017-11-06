<?php
namespace rocket\spec\ei\manage\gui;

use n2n\impl\web\ui\view\html\HtmlView;

interface EiGuiListener {

	public function onNewEiEntryGui(EiEntryGui $eiEntryGui);

	public function onNewView(HtmlView $view);
}