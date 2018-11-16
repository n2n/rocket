<?php
namespace rocket\ei\manage\gui;

use rocket\ei\manage\gui\EiFieldAbstraction;

class GuiFieldAssembly {
	private $guiProp;
	private $guiField;
	private $eiFieldWrapper;
	private $magAssembly;
	private $editable;
	
	public function __construct(GuiProp $guiProp, GuiField $guiField, EiFieldAbstraction $eiFieldWrapper = null,
			MagAssembly $magAssembly = null, GuiFieldEditable $editable = null) {
		$this->guiProp = $guiProp;
		$this->guiField = $guiField;
		$this->eiFieldWrapper = $eiFieldWrapper;
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
	public function getGuiField(): GuiField {
		return $this->guiField;
	}
	
	/**
	 * @return \rocket\ei\manage\entry\EiFieldWrapper|null
	 */
	public function getEiFieldWrapper() {
		return $this->eiFieldWrapper;
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