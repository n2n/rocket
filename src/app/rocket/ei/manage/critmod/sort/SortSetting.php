<?php

namespace rocket\ei\manage\critmod\sort;

use n2n\util\type\ArgUtils;
use n2n\persistence\orm\criteria\Criteria;
use rocket\ei\EiPropPath;
use rocket\ei\component\prop\EiProp;

class SortSetting {
	private string $direction;

	public function __construct(private EiPropPath $eiPropPath, string $direction) {
		$this->setDirection($direction);
	}

	public function getEiPropPath(): string {
		return $this->eiPropPath;
	}

	public function setEiPropPath(EiPropPath $eiPropPath): void {
		$this->eiPropPath = $eiPropPath;
	}

	public function getDirection(): string {
		return $this->direction;
	}

	public function setDirection(string $direction): void {
		ArgUtils::valEnum($direction, Criteria::getOrderDirections());
		$this->direction = $direction;
	}
}