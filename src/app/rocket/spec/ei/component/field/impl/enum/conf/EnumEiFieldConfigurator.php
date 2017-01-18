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
namespace rocket\spec\ei\component\field\impl\enum\conf;

use rocket\spec\ei\component\field\impl\adapter\AdaptableEiFieldConfigurator;
use n2n\reflection\CastUtils;
use rocket\spec\ei\component\field\impl\enum\EnumEiField;
use rocket\spec\ei\component\EiSetupProcess;
use n2n\core\container\N2nContext;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\spec\ei\component\IndependentEiComponent;
use n2n\util\config\LenientAttributeReader;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\MagCollectionArrayMag;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\reflection\property\TypeConstraint;
use n2n\impl\web\dispatch\mag\model\group\EnablerMag;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;

// @todo validate if attributes are arrays

class EnumEiFieldConfigurator extends AdaptableEiFieldConfigurator {
	const OPTION_OPTIONS_KEY = 'options';
	const ASSOCIATED_GUI_FIELD_KEY = 'associatedGuiFields';
	
	public function __construct(IndependentEiComponent $eiComponent) {
		parent::__construct($eiComponent);
		
		$this->autoRegister();
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		CastUtils::assertTrue($this->eiComponent instanceof EnumEiField);
		
		$lar = new LenientAttributeReader($this->attributes);
		
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		
		$guiFields = null;
		try {
			$guiFields = $this->eiComponent->getEiEngine()->getGuiDefinition()->getGuiFields();
		} catch (\Throwable $e) {
			$guiFields = $this->eiComponent->getEiEngine()->getGuiDefinition()->getLevelGuiFields();
		}
		
		$assoicatedGuiFieldOptions = array();
		foreach ($guiFields as $guiIdPathStr => $guiField) {
			$assoicatedGuiFieldOptions[$guiIdPathStr] = $guiField->getDisplayLabel();
		}
		
		$optionsMag = new MagCollectionArrayMag(self::OPTION_OPTIONS_KEY, 'Options',
				function() use ($assoicatedGuiFieldOptions) {
					$magCollection = new MagCollection();
					$magCollection->addMag(new StringMag('value', 'Value'));
					$magCollection->addMag(new StringMag('label', 'Label'));
					
					$eMag = new EnablerMag('bindGuiFieldsToValue', 'Bind GuiFields to value', false);
					$magCollection->addMag($eMag);
					$eMag->setAssociatedMags(array(
							$magCollection->addMag(new MultiSelectMag('assoicatedGuiIdPaths', 'Associated Gui Fields', $assoicatedGuiFieldOptions))));
					return new MagForm($magCollection);
				});
		
		$valueLabelMap = array();
		foreach ($lar->getArray(self::OPTION_OPTIONS_KEY, array(), TypeConstraint::createSimple('scalar')) 
				as $value => $label) {
			$valueLabelMap[$value] = array('value' => $value, 'label' => $label, 'bindGuiFieldsToValue' => false);
		}
		
		foreach ($lar->getArray(self::ASSOCIATED_GUI_FIELD_KEY, array(), 
				TypeConstraint::createArrayLike('array', false, TypeConstraint::createSimple('scalar'))) 
						as $value => $assoicatedGuiIdPaths) {
			if (array_key_exists($value, $valueLabelMap)) {
				$valueLabelMap[$value]['bindGuiFieldsToValue'] = true;
				$valueLabelMap[$value]['assoicatedGuiIdPaths'] = $assoicatedGuiIdPaths;
			}
		}
		
		$optionsMag->setValue($valueLabelMap);
		
		$magDispatchable->getMagCollection()->addMag($optionsMag);
		return $magDispatchable;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$options = array();
		$guiIdPathMap = array();
		foreach ($magDispatchable->getMagCollection()->getMagByPropertyName(self::OPTION_OPTIONS_KEY)->getValue() 
				as $valueLabelMap) {
			$options[$valueLabelMap['value']] = $valueLabelMap['label'];
			
			if ($valueLabelMap['bindGuiFieldsToValue']) {
				$guiIdPathMap[$valueLabelMap['value']] = $valueLabelMap['assoicatedGuiIdPaths'];
			}
		}
		$this->attributes->set(self::OPTION_OPTIONS_KEY, $options);
		$this->attributes->set(self::ASSOCIATED_GUI_FIELD_KEY, $guiIdPathMap);
	}
	
	public function setup(EiSetupProcess $eiSetupProcess) {
		parent::setup($eiSetupProcess);
	
		CastUtils::assertTrue($this->eiComponent instanceof EnumEiField);
		
		if ($this->attributes->contains(self::OPTION_OPTIONS_KEY)) {
			$options = $this->attributes->getArray(self::OPTION_OPTIONS_KEY, false, array(), 
					TypeConstraint::createSimple('scalar'));
			
			$this->eiComponent->setOptions($options);
		}
	}
}
