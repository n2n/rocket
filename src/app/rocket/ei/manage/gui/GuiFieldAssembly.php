<?php
namespace rocket\ei\manage\gui;

use rocket\ei\manage\entry\EiFieldWrapper;

class GuiFieldAssembly {
	private $guiProp;
	private $displayable;
	private $eiFieldWrapper;
	private $magAssembly;
	private $savable;
	
	public function __construct(GuiProp $guiProp, Displayable $displayable, EiFieldWrapper $eiFieldWrapper = null,
			MagAssembly $magAssembly = null, Savable $savable = null) {
		$this->guiProp = $guiProp;
		$this->displayable = $displayable;
		$this->eiFieldWrapper = $eiFieldWrapper;
		$this->magAssembly = $magAssembly;
		$this->savable = $savable;
	}
	
	/**
	 * @return GuiProp
	 */
	function getGuiProp() {
		return $this->guiProp;
	}
	
	/**
	 * @return Displayable
	 */
	public function getDisplayable(): Displayable {
		return $this->displayable;
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
	 * @return Savable|null
	 */
	public function getSavable() {
		return $this->savable;
	}
}