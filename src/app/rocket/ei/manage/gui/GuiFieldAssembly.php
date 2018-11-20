<?php
namespace rocket\ei\manage\gui;

class GuiFieldAssembly {
	private $guiProp;
	private $guiField;
	private $magAssembly;
	private $editable;
	
	public function __construct(GuiProp $guiProp, GuiField $guiField, 
			MagAssembly $magAssembly = null, GuiFieldEditable $editable = null) {
		$this->guiProp = $guiProp;
		$this->guiField = $guiField;
		$this->magAssembly = $magAssembly;
		$this->editable = $editable;
	}
	
	/**
	 * @return GuiProp
	 */
	function getGuiProp() {
		return $this->guiProp;
	}
	
	/**
	 * @return GuiField
	 */
	public function getGuiField() {
		return $this->guiField;
	}
	
	/**
	 * @return MagAssembly|null
	 */
	public function getMagAssembly() {
		return $this->magAssembly;
	}
	
	/**
	 * @return GuiFieldEditable|null
	 */
	public function getEditable() {
		return $this->editable;
	}
}