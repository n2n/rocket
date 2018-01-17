<?php
namespace rocket\spec\ei\manage\gui;

use rocket\spec\ei\manage\gui\ui\DisplayStructure;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlView;

interface EiGuiViewFactory {
	const MODE_NO_GROUPS = 1;
	const MODE_ROOT_GROUPED = 2;
	
	public function applyMode(int $rule);
	
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
	public function createView(array $eiEntryGuis, HtmlView $contextView = null): UiComponent;
}