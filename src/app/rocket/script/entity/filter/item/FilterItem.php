<?php

namespace rocket\script\entity\filter\item;

use n2n\util\Attributes;

interface FilterItem {
	/**
	 * @return string
	 */
	public function getLabel();
	/**
	 * @param Attributes $attributes
	 * @return ComparatorConstraint
	 */
	public function createComparatorConstraint(Attributes $attributes);
	/**
	 * @return \n2n\dispatch\option\OptionCollection 
	 */
	public function createOptionCollection();
}