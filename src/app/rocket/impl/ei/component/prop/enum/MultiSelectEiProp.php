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
namespace rocket\impl\ei\component\prop\enum;

use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\util\type\TypeConstraint;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\component\EiSetup;
use n2n\reflection\property\ConstraintsConflictException;
use n2n\util\type\ArgUtils;
use n2n\reflection\property\AccessProxy;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use rocket\ei\util\Eiu;
use n2n\impl\web\dispatch\mag\model\MagCollectionArrayMag;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\web\dispatch\mag\Mag;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use rocket\ei\EiPropPath;

class MultiSelectEiProp extends DraftablePropertyEiPropAdapter {
	const OPTION_OPTIONS = 'options';
	const OPTION_OPTIONS_LABEL = 'label';
	const OPTION_OPTIONS_VALUE = 'value';
	const OUTPUT_SEPARATOR = ', ';
	const ATTR_MIN_KEY = 'min';
	const ATTR_MAX_KEY = 'max';

	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('scalar', 
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
	
	public function setup(EiSetup $setupProcess) {
		try {
			$this->objectPropertyAccessProxy->setConstraints(TypeConstraint::createSimple(null, true));
		} catch (ConstraintsConflictException $e) {
			$setupProcess->failedE($this, $e);
		}
	}
	
// 	public function checkCompatibility(CompatibilityTest $compatibilityTest) {
// 		if (!$this->isCompatibleWith($compatibilityTest->getEntityProperty())) {
// 			$compatibilityTest->entityPropertyTestFailed();
// 			return;
// 		}
	
// 		$propertyConstraints = $compatibilityTest->getPropertyAccessProxy()->getConstraint();
// 		$requiredConstraints = TypeConstraint::createSimple(null);
// 		if ($propertyConstraints !== null && !$propertyConstraints->isPassableBy($requiredConstraints)) {
// 			$compatibilityTest->propertyTestFailed('EiProp can not pass Type ' . $requiredConstraints->__toString()
// 					. ' to property due to incompatible TypeConstraint ' . $propertyConstraints->__toString());
// 		}
// 	}
	
	public function createMagCollection() {
		$magCollection = parent::createMagCollection();
		$magCollection->addMag(self::OPTION_OPTIONS, new MagCollectionArrayMag('Options', function() {
			$magCollection = new MagCollection();
			$magCollection->addMag(self::OPTION_OPTIONS_LABEL, new StringMag('Label'));
			$magCollection->addMag(self::OPTION_OPTIONS_VALUE, new StringMag('Value'));
			return $magCollection;
		}));
		$magCollection->addMag(self::OPTION_MIN_KEY, new NumericMag('Min'));
		$magCollection->addMag(self::OPTION_MAX_KEY, new NumericMag('Max'));
		return $magCollection;
	}
	
	public function getOptions() {
		$options = array();
		foreach ((array) $this->attributes->get(self::OPTION_OPTIONS) as $attrs) {
			if (isset($attrs[self::OPTION_OPTIONS_VALUE]) && isset($attrs[self::OPTION_OPTIONS_LABEL])) {
				$options[$attrs[self::OPTION_OPTIONS_VALUE]] = $attrs[self::OPTION_OPTIONS_LABEL];
			}
		}
		return $options;
	}
	
	public function getMin() {
		return $this->attributes->get(self::OPTION_MIN_KEY, 0);
	}
	
	public function getMax() {
		return $this->attributes->get(self::OPTION_MAX_KEY);
	}
	
	public function isMandatory(Eiu $eiu) {
		return $this->getMin() > 0;
	}
	
	public function createMag(Eiu $eiu): Mag {
		return new MultiSelectMag($this->getLabelCode(), $this->getOptions(), array(), 
				$this->getMin(), $this->getMax());
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\component\prop\EiProp::getTypeName()
	 */
	public function getTypeName(): string {
		return 'MultiSelect';
		
	}

	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\gui\GuiField::createUiComponent()
	 */
	public function createUiComponent(HtmlView $view,
			Eiu $eiu) {
		return $view->getHtmlBuilder()->getEsc(
				implode(self::OUTPUT_SEPARATOR, (array)$eiEntry->getValue(EiPropPath::from($this))));
	}
	
}
