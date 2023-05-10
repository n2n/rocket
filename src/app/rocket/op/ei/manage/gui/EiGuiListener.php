<?php
namespace rocket\op\ei\manage\gui;

interface EiGuiListener {
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 */
	public function onInitialized(EiGuiFrame $eiGuiFrame);

	/**
	 * @param EiEntryGui $eiEntryGui
	 */
	public function onNewEiEntryGui(EiEntryGui $eiEntryGui);

	/**
	 * 
	 */
	public function onGiBuild(EiGuiFrame $eiGuiFrame);
}