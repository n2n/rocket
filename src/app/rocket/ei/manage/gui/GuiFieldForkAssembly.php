<?php
namespace rocket\ei\manage\gui;

use n2n\util\type\ArgUtils;

class GuiFieldForkAssembly {
	private $magAssemblies;
	private $editable;
	
	/**
	 * @param MagAssembly[] $magAssemblies
	 * @param GuiFieldEditable $guiFieldForkEditable
	 */
	public function __construct(array $magAssemblies, GuiFieldForkEditable $editable = null) {
		ArgUtils::valArray($magAssemblies, MagAssembly::class);
		$this->magAssemblies = $magAssemblies;
		$this->editable = $editable;
		
		ArgUtils::assertTrue(!empty($this->magAssemblies) == ($this->editable !== null),
				'readonly need GuiFieldEditable and editable need GuiFieldEditable');
	}
	
	/**
	 * @return MagAssembly[]
	 */
	public function getMagAssemblies() {
		return $this->magAssemblies;
	}
	
	/**
	 * @return GuiFieldForkEditable|null
	 */
	public function getEditable() {
		return $this->editable;
	}
}