<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\spec\ei\component\field\impl\meta\model;

use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\critmod\filter\impl\field\FilterFieldAdapter;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\util\config\Attributes;
use rocket\spec\ei\manage\critmod\filter\impl\model\SimpleComparatorConstraint;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\l10n\DynamicTextCollection;

class UserFilterField extends FilterFieldAdapter {
	protected $currentUserId;
	/**
	 * @param string $entityPropertyName
	 * @param string $label
	 * @param N2nLocale $n2nLocale
	 * @param User $currentUser
	 */
	public function __construct($entityPropertyName, $label, N2nLocale $n2nLocale, $currentUserId = null) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		parent::__construct($entityPropertyName, $label, self::createOperatorOptions($n2nLocale, 
				array(CriteriaComparator::OPERATOR_EQUAL, CriteriaComparator::OPERATOR_NOT_EQUAL)), 
				new EnumMag('Value', array(null => $dtc->translate('ei_impl_current_user_label'))));
		$this->currentUserId = $currentUserId;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\critmod\filter\impl\field\FilterField::createComparatorConstraint()
	 */
	public function createComparatorConstraint(Attributes $attributes) {
		$operator = $attributes->get(self::OPERATOR_OPTION);
		if ($operator === null) return null;
	
		return new SimpleComparatorConstraint($this->criteriaProperty,
				$this->currentUserId, $operator);
	}
}
