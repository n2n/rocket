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
use rocket\ei\manage\gui\GuiFieldPath;

// @todo validate if attributes are arrays

class EnumEiPropConfigurator extends AdaptableEiPropConfigurator {
	const OPTION_OPTIONS_KEY = 'options';
	const ASSOCIATED_GUI_FIELD_KEY = 'associatedGuiProps';
	
	private $enumEiProp;
	
	public function __construct(EnumEiProp $enumEiProp) {
		parent::__construct($enumEiProp);
		
		$this->enumEiProp = $enumEiProp;
		
		$this->autoRegister();
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		CastUtils::assertTrue($this->eiComponent instanceof EnumEiProp);
		
		$lar = new LenientAttributeReader($this->attributes);
		
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		
		$guiProps = null;
		try {
			$guiProps = $this->eiComponent->getEiMask()->getEiEngine()->createGuiDefinition($n2nContext)->getGuiProps();
		} catch (\Throwable $e) {
			$guiProps = $this->eiComponent->getEiMask()->getEiEngine()->createGuiDefinition($n2nContext)->getLevelGuiProps();
		}
		
		$assoicatedGuiPropOptions = array();
		foreach ($guiProps as $eiPropPathStr => $guiProp) {
			$assoicatedGuiPropOptions[$eiPropPathStr] = $guiProp->getDisplayLabelLstr()->t($n2nContext->getN2nLocale());
		}
		
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
		foreach ($lar->getArray(self::OPTION_OPTIONS_KEY, TypeConstraint::createSimple('scalar')) 
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
		
		$magDispatchable->getMagCollection()->addMag(self::OPTION_OPTIONS_KEY, $optionsMag);
		return $magDispatchable;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$options = array();
		$eiPropPathMap = array();
		foreach ($magDispatchable->getMagCollection()->getMagByPropertyName(self::OPTION_OPTIONS_KEY)->getValue() 
				as $valueLabelMap) {
			$options[$valueLabelMap['value']] = $valueLabelMap['label'];
			
			if ($valueLabelMap['bindGuiPropsToValue']) {
				$eiPropPathMap[$valueLabelMap['value']] = $valueLabelMap['assoicatedGuiFieldPaths'];
			}
		}
		$this->attributes->set(self::OPTION_OPTIONS_KEY, $options);
		$this->attributes->set(self::ASSOCIATED_GUI_FIELD_KEY, $eiPropPathMap);
	}
	
	public function setup(EiSetup $eiSetupProcess) {
		parent::setup($eiSetupProcess);
	
		CastUtils::assertTrue($this->eiComponent instanceof EnumEiProp);
		
		if ($this->attributes->contains(self::OPTION_OPTIONS_KEY)) {
			$options = $this->attributes->getArray(self::OPTION_OPTIONS_KEY, false, array(), 
					TypeConstraint::createSimple('scalar'));
			
			$this->eiComponent->setOptions(array_filter($options));
		}
		
		if ($this->attributes->contains(self::ASSOCIATED_GUI_FIELD_KEY)) {
			$eiPropPathMap = $this->attributes->getArray(self::ASSOCIATED_GUI_FIELD_KEY, false, array(), 
					TypeConstraint::createArrayLike('array', false, TypeConstraint::createSimple('scalar')));
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
