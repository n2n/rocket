<?php
namespace rocket\ei\manage\gui;

interface EiGuiListener {
	
	/**
	 * @param EiGui $eiGui
	 */
	public function onInitialized(EiGui $eiGui);

	/**
	 * @param EiEntryGui $eiEntryGui
	 */
	public function onNewEiEntryGui(EiEntryGui $eiEntryGui);

	/**
	 * 
	 */
	public function onSerialize();
}