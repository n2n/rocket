<?php
namespace rocket\ei\manage\gui;

use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlView;

interface EiGuiViewFactory {
// 	/**
// 	 * @return GuiDefinition
// 	 */
// 	public function getGuiDefinition(): GuiDefinition;
	
// 	/**
// 	 * @return GuiFieldPath[];
// 	 */
// 	public function getGuiFieldPaths(): array;
	
// 	/**
// 	 * @return DisplayStructure
// 	 */
// 	public function getDisplayStructure(): DisplayStructure;
	
// 	/**
// 	 * @param DisplayStructure $displayStructure
// 	 */
// 	public function setDisplayStructure(DisplayStructure $displayStructure);
	
	/**
	 * @return UiComponent
	 */
	public function createUiComponent(array $eiEntryGuis, ?HtmlView $contextView): UiComponent;
}
