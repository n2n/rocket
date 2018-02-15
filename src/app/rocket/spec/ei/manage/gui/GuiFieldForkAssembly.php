<?php
namespace rocket\spec\ei\manage\gui;

use n2n\reflection\ArgUtils;

class GuiFieldForkAssembly {
	private $magAssemblies;
	private $savable;
	
	/**
	 * @param MagAssembly[] $magAssemblies
	 * @param Savable $savable
	 */
	public function __construct(array $magAssemblies, Savable $savable = null) {
		ArgUtils::valArray($magAssemblies, MagAssembly::class);
		$this->magAssemblies = $magAssemblies;
		$this->savable = $savable;
	}
	
	/**
	 * @return MagAssembly[]
	 */
	public function getMagAssemblies() {
		return $this->magAssemblies;
	}
	
	/**
	 * @return Savable|null
	 */
	public function getSavable() {
		return $this->savable;
	}
}