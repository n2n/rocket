<?php
namespace rocket\impl\ei\component\prop\string;

use n2n\impl\web\dispatch\mag\model\MagArrayMag;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\TypeConstraint;
use n2n\web\ui\UiComponent;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use rocket\ei\util\Eiu;
use rocket\si\content\SiField;

class StringArrayEiProp extends DraftablePropertyEiPropAdapter {

	public function __construct() {
		parent::__construct();
	}
	
	public function isEntityPropertyRequired(): bool {
		return false;
	}

	public function setObjectPropertyAccessProxy(AccessProxy $objectPropertyAccessProxy = null) {
		parent::setObjectPropertyAccessProxy($objectPropertyAccessProxy);

		$objectPropertyAccessProxy->setConstraint(TypeConstraint::createArrayLike('array', false,
				TypeConstraint::createSimple('scalar')));
	}

	/**
	 * @param HtmlView $view
	 * @param Eiu $eiu
	 * @return UiComponent
	 */
	public function createOutSiField(Eiu $eiu): SiField {
		return implode(', ', $eiu->field()->getValue());
	}

	public function createInSiField(Eiu $eiu): SiField {
		return new MagArrayMag($this->getLabelLstr(), function () {
			return new StringMag('Huii');
		});
		//return new StringArrayMag($this->getDisplayLabelLstr(), $eiu->field()->getValue());
	}
}