<?php

namespace rocket\impl\ei\component\prop\numeric;

use n2n\reflection\property\PropertyAccessProxy;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\ui\gui\ViewMode;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use rocket\op\ei\util\Eiu;
use n2n\persistence\orm\util\NestedSetStrategy;
use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\impl\ei\component\cmd\tree\TreeMoveEiModNature;

class NestedSetEiPropNature extends IntegerEiPropNature {

	function __construct(EntityProperty $entityProperty, PropertyAccessProxy $accessProxy,
			public readonly NestedSetAttribute $attribute) {

		parent::__construct($accessProxy);
		$this->setEntityProperty($entityProperty);

		$this->displayConfig = new DisplayConfig(ViewMode::none());
		$this->editConfig = new EditConfig(true, false, true, false);
	}

	public function setup(Eiu $eiu): void {
		parent::setup($eiu);

		if ($this->attribute === NestedSetAttribute::LEFT) {
			return;
		}

		$props = $eiu->mask()->findPropsByNatureClass(NestedSetEiPropNature::class);
		foreach ($props as $prop) {
			$nature = $prop->getEiProp()->getNature();
			assert($nature instanceof NestedSetEiPropNature);
			if ($this === $nature || $nature->attribute === NestedSetAttribute::RIGHT) {
				continue;
			}

			$eiu->mask()->type()->getEiType()->setNestedSetStrategy(
					new NestedSetStrategy(CrIt::p($nature->getEntityProperty()), CrIt::p($this->getEntityProperty())));


			$eiu->mask()->addMod(new TreeMoveEiModNature());
			break;
		}

	}

}

enum NestedSetAttribute {
	case LEFT;
	case RIGHT;
}

