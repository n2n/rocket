<?php

namespace rocket\spec\ei\manage\gui;

use n2n\web\dispatch\mag\Mag;

interface GuiFieldForkEditable extends Savable {
	
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
}