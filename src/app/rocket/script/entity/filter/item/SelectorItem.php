<?php

namespace rocket\script\entity\filter\item;

use n2n\util\Attributes;

interface SelectorItem extends FilterItem {
	/**
	 * @param Attributes $attributes
	 * @return \rocket\script\entity\filter\SelectorConstraint
	 */
	public function createSelectorConstraint(Attributes $attributes);
}