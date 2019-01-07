<?php
namespace rocket\ei\manage\gui;

use n2n\util\type\ArgUtils;

class GuiFieldAssembly {
// 	private $guiProp;
	private $displayable;
	private $magAssembly;
	private $editable;
	
	public function __construct(/*GuiProp $guiProp,*/ GuiFieldDisplayable $displayable, 
			MagAssembly $magAssembly = null, GuiFieldEditable $editable = null) {
// 		$this->guiProp = $guiProp;
		$this->displayable = $displayable;
		$this->magAssembly = $magAssembly;
		$this->editable = $editable;
		
		ArgUtils::assertTrue(($this->magAssembly !== null) == ($this->editable !== null),
				'readonly need GuiFieldEditable and editable need GuiFieldEditable');
	}
	
// 	/**
// 	 * @return GuiProp
// 	 */
// 	function getGuiProp() {
// 		return $this->guiProp;
// 	}
	
	/**
	 * @return GuiFieldDisplayable
	 */
	public function getDisplayable() {
		return $this->displayable;
	}
	
	public function isReadOnly() {
		return $this->magAssembly === null;
	}
	
	/**
	 * @return MagAssembly|null
	 */
	public function getMagAssembly() {
		return $this->magAssembly;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\GuiFieldEditable
	 */
	public function getEditable() {
		return $this->editable;
	}
}