<?php

namespace rocket\ei\manage\gui;

use n2n\web\dispatch\mag\Mag;

interface GuiFieldForkEditable {
	
	/**
	 * @return bool
	 */
	public function isForkMandatory(): bool;
	
	/**
	 * Mag for group toolbar
	 * @return Mag
	 */
	public function getForkMag(): Mag;
	
	/**
	 * @return MagAssembly[]
	 */
	public function getInheritForkMagAssemblies(): array;
	
	/**
	 * 
	 */
	public function save();
}