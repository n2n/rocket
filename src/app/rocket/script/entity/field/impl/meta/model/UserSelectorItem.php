<?php

namespace rocket\script\entity\field\impl\meta\model;

use rocket\script\entity\filter\item\SelectorItem;
use n2n\util\Attributes;
use rocket\script\entity\filter\item\StringSelectorConstraint;

class UserSelectorItem extends UserFilterItem implements SelectorItem {
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\filter\item\SelectorItem::createSelectorConstraint()
	 */
	public function createSelectorConstraint(Attributes $attributes) {
		$operator = $attributes->get(self::OPERATOR_OPTION);
		if ($operator === null) return null;
		
		return new StringSelectorConstraint($operator, $this->currentUserId);
	}

}