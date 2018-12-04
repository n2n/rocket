<?php
namespace rocket\ei\manage\gui;

use n2n\reflection\ArgUtils;

class GuiFieldForkAssembly {
	private $magAssemblies;
	private $guiFieldForkEditable;
	
	/**
	 * @param MagAssembly[] $magAssemblies
	 * @param GuiFieldEditable $guiFieldForkEditable
	 */
	public function __construct(array $magAssemblies, GuiFieldForkEditable $guiFieldForkEditable = null) {
		ArgUtils::valArray($magAssemblies, MagAssembly::class);
		$this->magAssemblies = $magAssemblies;
		$this->guiFieldForkEditable = $guiFieldForkEditable;
	}
	
	/**
	 * @return MagAssembly[]
	 */
	public function getMagAssemblies() {
		return $this->magAssemblies;
	}
	
	/**
	 * @return GuiFieldEditable|null
	 */
	public function getEditable() {
		return $this->guiFieldForkEditable;
	}
}