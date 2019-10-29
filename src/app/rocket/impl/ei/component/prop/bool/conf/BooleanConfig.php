<?php
namespace rocket\impl\ei\component\prop\bool\conf;

use rocket\impl\ei\component\prop\bool\BooleanEiProp;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use n2n\util\type\TypeConstraint;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use n2n\util\StringUtils;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\util\type\CastUtils;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\type\attrs\DataSet;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\config\ConfigAdaption;

class BooleanConfig extends ConfigAdaption {
	const ATTR_BIND_GUI_PROPS_KEY = 'associatedGuiProps';
	const ATTR_ON_ASSOCIATED_GUI_PROP_KEY = 'onAssociatedGuiProps';
	const ATTR_OFF_ASSOCIATED_GUI_PROP_KEY = 'offAssociatedGuiProps';
	
	private static $booleanNeedles = ['Available', 'Enabled'];
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$level = parent::testCompatibility($propertyAssignation);
		
		if (!$level) return $level;
		
		$propertyName = $this->requirePropertyName();
		foreach (self::$booleanNeedles as $booleanNeedle) {
			if (StringUtils::endsWith($booleanNeedle, $propertyName)) {
				return CompatibilityLevel::COMMON;
			}
		}
		
		return $level;
	}
	
	
	public function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$lar = new LenientAttributeReader($this->dataSet);
		
		
		$guiProps = null;
		try {
			$eiu->mask()->engine()->guiPropPaths();
		} catch (\Throwable $e) {
			$guiProps = $this->eiComponent->getEiMask()->getEiEngine()->createGuiDefinition($n2nContext)->getLevelGuiProps();
		}
		
		$assoicatedGuiPropOptions = array();
		foreach ($guiProps as $eiPropPathStr => $guiProp) {
			$assoicatedGuiPropOptions[$eiPropPathStr] = $guiProp->getDisplayLabelLstr()->t($n2nContext->getN2nLocale());
		}
		
		$onGuiFieldPathStrs = $lar->getScalarArray(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY);
		$offGuiFieldPathStrs = $lar->getScalarArray(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY);
		
		$eMag = new TogglerMag('Bind GuiProps to value', !empty($onGuiFieldPathStrs) || !empty($offGuiFieldPathStrs));
		
		$magCollection->addMag(self::ATTR_BIND_GUI_PROPS_KEY, $eMag);
		$eMag->setOnAssociatedMagWrappers(array(
				$magCollection->addMag(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, 
						new MultiSelectMag('Associated Gui Fields when on', $assoicatedGuiPropOptions, $onGuiFieldPathStrs)),
				$magCollection->addMag(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, 
						new MultiSelectMag('Associated Gui Fields when off', $assoicatedGuiPropOptions, $offGuiFieldPathStrs))));
	}
	
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		if (!$magCollection->readValue(self::ATTR_BIND_GUI_PROPS_KEY)) {
			return;
		}
		
		$onGuiFieldPathStrs = $magCollection->readValue(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY);
		$offGuiFieldPathsStrs = $magCollection->readValue(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY);
		
		$this->dataSet->set(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, $onGuiFieldPathStrs);
		$this->dataSet->set(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, $offGuiFieldPathsStrs);
	}
	
	function setup(Eiu $eiu, DataSet $dataSet) {
		parent::setup($eiSetupProcess);
		
		$eiComponent = $this->eiComponent;
		CastUtils::assertTrue($eiComponent instanceof BooleanEiProp);
		
		if ($this->dataSet->contains(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY)) {
			$onGuiFieldPathStrs = $this->dataSet->getArray(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, false, array(), 
					TypeConstraint::createSimple('scalar'));
			$onGuiFieldPaths = array();
			foreach ($onGuiFieldPathStrs as $eiPropPathStr) {
				$onGuiFieldPaths[] = GuiFieldPath::create($eiPropPathStr);
			}
			
			$eiComponent->setOnAssociatedGuiFieldPaths($onGuiFieldPaths);
		}
		
		if ($this->dataSet->contains(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY)) {
			$offGuiFieldPathStrs = $this->dataSet->getArray(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, false, array(),
					TypeConstraint::createSimple('scalar'));
			$offGuiFieldPaths = array();
			foreach ($offGuiFieldPathStrs as $eiPropPathStr) {
				$offGuiFieldPaths[] = GuiFieldPath::create($eiPropPathStr);
			}
			
			$eiComponent->setOffAssociatedGuiFieldPaths($offGuiFieldPaths);
		}
	}
}