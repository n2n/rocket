<?php
namespace rocket\ei\manage\gui;

use n2n\reflection\ArgUtils;

class GuiFieldForkAssembly {
	private $magAssemblies;
	private $guiFieldEditable;
	
	/**
	 * @param MagAssembly[] $magAssemblies
	 * @param GuiFieldEditable $guiFieldEditable
	 */
	public function __construct(array $magAssemblies, GuiFieldEditable $guiFieldEditable = null) {
		ArgUtils::valArray($magAssemblies, MagAssembly::class);
		$this->magAssemblies = $magAssemblies;
		$this->guiFieldEditable = $guiFieldEditable;
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
	public function getSavable() {
		return $this->guiFieldEditable;
	}
}