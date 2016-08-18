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
namespace rocket\spec\ei\component\field\impl\enum;

use rocket\spec\ei\component\field\impl\TranslatableEiFieldAdapter;
use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\property\impl\ScalarEntityProperty;
use n2n\reflection\property\TypeConstraint;
use n2n\dispatch\mag\MagCollectionArrayMag;
use n2n\dispatch\mag\impl\model\StringMag;
use n2n\dispatch\mag\MagCollection;
use n2n\dispatch\mag\impl\model\MultiSelectChoice;
use n2n\ui\view\impl\html\HtmlView;
use rocket\spec\ei\component\EiSetupProcess;
use n2n\reflection\property\ConstraintsConflictException;
use rocket\spec\core\CompatibilityTest;
use rocket\spec\ei\manage\mapping\EiMapping;
use n2n\dispatch\mag\impl\model\IntegerOption;
use rocket\spec\ei\manage\gui\EntrySourceInfo;
use n2n\reflection\ArgUtils;
use n2n\persistence\orm\property\impl\DateTimeEntityProperty;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\TypeConstraint;
use rocket\spec\ei\component\field\impl\adapter\DraftableEiFieldAdapter;

class MultiSelectEiField extends DraftableEiFieldAdapter {
	const OPTION_OPTIONS = 'options';
	const OPTION_OPTIONS_LABEL = 'label';
	const OPTION_OPTIONS_VALUE = 'value';
	const OUTPUT_SEPARATOR = ', ';
	const ATTR_MIN_KEY = 'min';
	const ATTR_MAX_KEY = 'max';

	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('scalar', 
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
	
	public function setup(EiSetupProcess $setupProcess) {
		try {
			$this->objectPropertyAccessProxy->setConstraints(TypeConstraint::createSimple(null, true));
		} catch (ConstraintsConflictException $e) {
			$setupProcess->failedE($this, $e);
		}
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof ScalarEntityProperty;
	}
	
	public function checkCompatibility(CompatibilityTest $compatibilityTest) {
		if (!$this->isCompatibleWith($compatibilityTest->getEntityProperty())) {
			$compatibilityTest->entityPropertyTestFailed();
			return;
		}
	
		$propertyConstraints = $compatibilityTest->getPropertyAccessProxy()->getConstraint();
		$requiredConstraints = TypeConstraint::createSimple(null);
		if ($propertyConstraints !== null && !$propertyConstraints->isPassableBy($requiredConstraints)) {
			$compatibilityTest->propertyTestFailed('EiField can not pass Type ' . $requiredConstraints->__toString()
					. ' to property due to incompatible TypeConstraint ' . $propertyConstraints->__toString());
		}
	}
	
	public function createMagCollection() {
		$magCollection = parent::createMagCollection();
		$magCollection->addMag(self::OPTION_OPTIONS, new MagCollectionArrayMag('Options', function() {
			$magCollection = new MagCollection();
			$magCollection->addMag(self::OPTION_OPTIONS_LABEL, new StringMag('Label'));
			$magCollection->addMag(self::OPTION_OPTIONS_VALUE, new StringMag('Value'));
			return $magCollection;
		}));
		$magCollection->addMag(self::OPTION_MIN_KEY, new IntegerOption('Min'));
		$magCollection->addMag(self::OPTION_MAX_KEY, new IntegerOption('Max'));
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
	
	public function isMandatory(EntrySourceInfo $entrySourceInfo) {
		return $this->getMin() > 0;
	}
	
	public function createMag(string $propertyName, FieldSourceInfo $entrySourceInfo): Mag {
		return new MultiSelectChoice($this->getLabelCode(), $this->getOptions(), array(), 
				$this->getMin(), $this->getMax());
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\EiField::getTypeName()
	 */
	public function getTypeName(): string {
		return 'MultiSelect';
		
	}

	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\gui\Displayable::createOutputUiComponent()
	 */
	public function createOutputUiComponent(HtmlView $view,
			EntrySourceInfo $entrySourceInfo) {
		return $view->getHtmlBuilder()->getEsc(
				implode(self::OUTPUT_SEPARATOR, (array)$eiMapping->getValue(EiFieldPath::from($this))));
	}
	
}
