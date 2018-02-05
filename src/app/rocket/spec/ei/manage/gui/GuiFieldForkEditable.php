<?php

namespace rocket\spec\ei\manage\gui;

use n2n\web\dispatch\mag\Mag;

interface GuiFieldForkEditable extends Savable {
	
	/**
	 * Mag for group toolbar
	 * @return Mag
	 */
	public function getForkMag(): Mag;
	
	/**
	 * @return \n2n\web\dispatch\map\PropertyPath[]
	 */
	public function getAdditionalForkMagPropertyPaths(): array;
}