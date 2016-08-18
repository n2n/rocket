<?php

namespace rocket\script\entity\field\impl\meta\model;

use n2n\l10n\Locale;
use rocket\script\entity\filter\item\SimpleFilterItem;
use n2n\persistence\orm\criteria\CriteriaComparator;
use rocket\user\bo\User;
use n2n\util\Attributes;
use rocket\script\entity\filter\SimpleComparatorConstraint;
use n2n\dispatch\option\impl\EnumOption;
use n2n\core\DynamicTextCollection;

class UserFilterItem extends SimpleFilterItem {
	protected $currentUserId;
	/**
	 * @param string $entityPropertyName
	 * @param string $label
	 * @param Locale $locale
	 * @param User $currentUser
	 */
	public function __construct($entityPropertyName, $label, Locale $locale, $currentUserId = null) {
		$dtc = new DynamicTextCollection('rocket', $locale);
		parent::__construct($entityPropertyName, $label, self::createOperatorOptions($locale, 
				array(CriteriaComparator::OPERATOR_EQUAL, CriteriaComparator::OPERATOR_NOT_EQUAL)), 
				new EnumOption('Value', array(null => $dtc->translate('script_impl_current_user_label'))));
		$this->currentUserId = $currentUserId;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\filter\item\FilterItem::createComparatorConstraint()
	 */
	public function createComparatorConstraint(Attributes $attributes) {
		$operator = $attributes->get(self::OPERATOR_OPTION);
		if ($operator === null) return null;
	
		return new SimpleComparatorConstraint($this->propertyName,
				$this->currentUserId, $operator);
	}
}