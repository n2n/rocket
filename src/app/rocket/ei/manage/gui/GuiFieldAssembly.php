<?php
namespace rocket\ei\manage\gui;

use rocket\ei\manage\mapping\EiFieldWrapper;

class GuiFieldAssembly {
	private $displayable;
	private $eiFieldWrapper;
	private $magAssembly;
	private $savable;
	
	public function __construct(Displayable $displayable, EiFieldWrapper $eiFieldWrapper = null,
			MagAssembly $magAssembly = null, Savable $savable = null) {
		$this->displayable = $displayable;
		$this->eiFieldWrapper = $eiFieldWrapper;
		$this->magAssembly = $magAssembly;
		$this->savable = $savable;
	}
	
	/**
	 * @return Displayable
	 */
	public function getDisplayable(): Displayable {
		return $this->displayable;
	}
	
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