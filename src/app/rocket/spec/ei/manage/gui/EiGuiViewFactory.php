<?php
namespace rocket\spec\ei\manage\gui;

use rocket\spec\ei\manage\gui\ui\DisplayStructure;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlView;

interface EiGuiViewFactory {
	
	/**
	 * @return GuiDefinition
	 */
	public function getGuiDefinition(): GuiDefinition;
	
	/**
	 * @return GuiIdPath[];
	 */
	public function getGuiIdPaths(): array;
	
	/**
	 * @return DisplayStructure|null
	 */
	public function getDisplayStructure(): ?DisplayStructure;
	
	/**
	 * @return UiComponent
	 */
	public function createView(array $eiEntryGuis, HtmlView $contextView = null, 
			bool $groupContextProvided = false): UiComponent;
}