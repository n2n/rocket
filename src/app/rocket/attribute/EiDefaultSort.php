<?php

namespace rocket\attribute;

use rocket\op\ei\manage\critmod\sort\SortSetting;
use rocket\op\ei\EiPropPath;
use n2n\util\type\ArgUtils;
use n2n\persistence\orm\criteria\Criteria;

#[\Attribute(\Attribute::TARGET_CLASS)]
class EiDefaultSort {
	/**
	 * @var SortSetting[]
	 */
	private array $sortSettings;

	function __construct(array $propDirections) {
		$this->sortSettings = [];
		foreach ($propDirections as $prop => $direction) {
			$direction = mb_strtoupper($direction);
			ArgUtils::valEnum($direction, Criteria::getOrderDirections());
			$this->sortSettings[] = new SortSetting(EiPropPath::create($prop), $direction);
		}
	}

	/**
	 * @return SortSetting[]
	 */
	function getSortSettings(): array {
		return $this->sortSettings;
	}
}