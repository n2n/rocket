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
use rocket\ei\util\factory\EifGuiField;
use rocket\si\content\impl\SiFields;

class StringArrayEiProp extends DraftablePropertyEiPropAdapter {

	
	protected function prepare() {
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
	function createOutEifGuiField(Eiu $eiu): EifGuiField {
		return $eiu->factory()->newGuiField(SiFields::stringOut(implode(', ', $eiu->field()->getValue()))) ;
	}

	public function createInEifGuiField(Eiu $eiu): EifGuiField {
		
		return new MagArrayMag($this->getLabelLstr(), function () {
			return new StringMag('Huii');
		});
		//return new StringArrayMag($this->getDisplayLabelLstr(), $eiu->field()->getValue());
	}

	public function saveSiField(SiField $siField, Eiu $eiu) {
	}

}