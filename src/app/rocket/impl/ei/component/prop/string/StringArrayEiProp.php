<?php
namespace rocket\impl\ei\component\prop\string;

use n2n\impl\web\dispatch\mag\model\MagArrayMag;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use n2n\web\dispatch\mag\Mag;
use n2n\web\ui\UiComponent;
use rocket\impl\ei\component\prop\adapter\DraftableEiPropAdapter;
use rocket\ei\util\Eiu;

class StringArrayEiProp extends DraftableEiPropAdapter {

	public function __construct() {
		parent::__construct();

		$this->entityPropertyRequired = false;
	}

	public function setObjectPropertyAccessProxy(AccessProxy $objectPropertyAccessProxy = null) {
		parent::setObjectPropertyAccessProxy($objectPropertyAccessProxy);

		$objectPropertyAccessProxy->setConstraint(TypeConstraint::createArrayLike('array',false,
				TypeConstraint::createSimple('scalar')));
	}

	/**
	 * @param HtmlView $view
	 * @param Eiu $eiu
	 * @return UiComponent
	 */
	public function createOutputUiComponent(HtmlView $view, Eiu $eiu) {
		return implode(', ', $eiu->field()->getValue());
	}

	public function createMag(Eiu $eiu): Mag {
		return new MagArrayMag($this->getDisplayLabelLstr(), function () {
			return new StringMag('Huii');
		});
		//return new StringArrayMag($this->getDisplayLabel(), $eiu->field()->getValue());
	}
}