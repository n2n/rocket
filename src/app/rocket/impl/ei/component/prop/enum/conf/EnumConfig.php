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
namespace rocket\impl\ei\component\prop\enum\conf;

use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use n2n\util\type\CastUtils;
use rocket\impl\ei\component\prop\enum\EnumEiProp;
use rocket\ei\component\EiSetup;
use n2n\core\container\N2nContext;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\MagCollectionArrayMag;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\util\type\TypeConstraint;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\impl\ei\component\prop\adapter\config\ConfigAdaption;
use rocket\ei\util\Eiu;
use n2n\util\type\attrs\DataSet;
use n2n\util\type\ArgUtils;
use n2n\util\type\TypeConstraints;

// @todo validate if dataSet are arrays

class EnumConfig extends ConfigAdaption {
	const ATTR_OPTIONS_KEY = 'options';
	const ASSOCIATED_GUI_FIELD_KEY = 'associatedGuiProps';
	
	private $options = array();
	private $associatedGuiFieldPathMap = array();
	
	
	
	public function setOptions(array $options) {
		ArgUtils::valArray($options, 'scalar');
		$this->options = $options;
	}
	
	public function getOptions() {
		return $this->options;
	}
	
	
	
	public function setAssociatedGuiFieldPathMap(array $associatedGuiFieldPathMap) {
		ArgUtils::valArray($associatedGuiFieldPathMap,
				TypeConstraint::createArrayLike('array', false, TypeConstraint::createSimple(GuiFieldPath::class)));
		$this->associatedGuiFieldPathMap = $associatedGuiFieldPathMap;
	}
	
	/**
	 * @return array
	 */
	public function getAssociatedGuiFieldPathMap() {
		return $this->associatedGuiFieldPathMap;
	}
	
	public function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection): MagDispatchable {
		$lar = new LenientAttributeReader($dataSet);
		
		$assoicatedGuiPropOptions = $eiu->engine()->getGuiPropOptions();
		
		$optionsMag = new MagCollectionArrayMag('Options',
				function() use ($assoicatedGuiPropOptions) {
					$magCollection = new MagCollection();
					$magCollection->addMag('value', new StringMag('Value'));
					$magCollection->addMag('label', new StringMag('Label'));
					
					$eMag = new TogglerMag('Bind GuiProps to value', false);
					$magCollection->addMag('bindGuiPropsToValue', $eMag);
					$eMag->setOnAssociatedMagWrappers(array(
							$magCollection->addMag('assoicatedGuiFieldPaths', new MultiSelectMag('Associated Gui Fields', $assoicatedGuiPropOptions))));
					return new MagForm($magCollection);
				});
		
		$valueLabelMap = array();
		foreach ($lar->getArray(self::ATTR_OPTIONS_KEY, TypeConstraint::createSimple('scalar')) 
				as $value => $label) {
			$valueLabelMap[$value] = array('value' => $value, 'label' => $label, 'bindGuiPropsToValue' => false);
		}
		
		foreach ($lar->getArray(self::ASSOCIATED_GUI_FIELD_KEY,  
				TypeConstraint::createArrayLike('array', false, TypeConstraint::createSimple('scalar'))) 
						as $value => $assoicatedGuiFieldPaths) {
			if (array_key_exists($value, $valueLabelMap)) {
				$valueLabelMap[$value]['bindGuiPropsToValue'] = true;
				$valueLabelMap[$value]['assoicatedGuiFieldPaths'] = $assoicatedGuiFieldPaths;
			}
		}
		
		$optionsMag->setValue($valueLabelMap);
		
		$magCollection->addMag(self::ATTR_OPTIONS_KEY, $optionsMag);
	}
	
	public function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$options = array();
		$eiPropPathMap = array();
		foreach ($magCollection->getMagByPropertyName(self::ATTR_OPTIONS_KEY)->getValue() 
				as $valueLabelMap) {
			$options[$valueLabelMap['value']] = $valueLabelMap['label'];
			
			if ($valueLabelMap['bindGuiPropsToValue']) {
				$eiPropPathMap[$valueLabelMap['value']] = $valueLabelMap['assoicatedGuiFieldPaths'];
			}
		}
		$dataSet->set(self::ATTR_OPTIONS_KEY, $options);
		$dataSet->set(self::ASSOCIATED_GUI_FIELD_KEY, $eiPropPathMap);
	}
	
	public function setup(Eiu $eiu, DataSet $dataSet) {
		if ($dataSet->contains(self::ATTR_OPTIONS_KEY)) {
			$options = $dataSet->optArray(self::ATTR_OPTIONS_KEY, TypeConstraints::scalar());
			
			$this->eiComponent->setOptions(array_filter($options));
		}
		
		if ($dataSet->contains(self::ASSOCIATED_GUI_FIELD_KEY)) {
			$eiPropPathMap = $dataSet->optArray(self::ASSOCIATED_GUI_FIELD_KEY,  
					TypeConstraints::array(false, TypeConstraints::scalar()));
			foreach ($eiPropPathMap as $value => $eiPropPathStrs) {
				$eiPropPaths = array();
				foreach ($eiPropPathStrs as $eiPropPathStr) {
					$eiPropPaths[] = GuiFieldPath::create($eiPropPathStr);
				}
				$eiPropPathMap[$value] = $eiPropPaths;
			}
			
			$this->enumEiProp->setAssociatedGuiFieldPathMap($eiPropPathMap);
		}
	}
}
