<?php
namespace rocket\impl\ei\component\prop\bool\conf;

use n2n\util\type\attrs\LenientAttributeReader;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use n2n\util\type\TypeConstraint;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use n2n\util\StringUtils;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\type\attrs\DataSet;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\config\ConfigAdaption;
use n2n\util\type\ArgUtils;

class BooleanConfig extends ConfigAdaption {
	const ATTR_BIND_GUI_PROPS_KEY = 'associatedGuiProps';
	const ATTR_ON_ASSOCIATED_GUI_PROP_KEY = 'onAssociatedGuiProps';
	const ATTR_OFF_ASSOCIATED_GUI_PROP_KEY = 'offAssociatedGuiProps';
	
	private static $booleanNeedles = ['Available', 'Enabled'];
	
	private $onAssociatedGuiPropPaths = array();
	private $offAssociatedGuiPropPaths = array();
	
	/**
	 * @param GuiPropPath[] $onAssociatedGuiPropPaths
	 */
	public function setOnAssociatedGuiPropPaths(array $onAssociatedGuiPropPaths) {
		ArgUtils::valArray($onAssociatedGuiPropPaths, GuiPropPath::class);
		$this->onAssociatedGuiPropPaths = $onAssociatedGuiPropPaths;
	}
	
	/**
	 * @return GuiPropPath[]
	 */
	public function getOnAssociatedGuiPropPaths() {
		return $this->onAssociatedGuiPropPaths;
	}
	
	/**
	 * @param GuiPropPath[] $offAssociatedGuiPropPaths
	 */
	public function setOffAssociatedGuiPropPaths(array $offAssociatedGuiPropPaths) {
		ArgUtils::valArray($offAssociatedGuiPropPaths, GuiPropPath::class);
		$this->offAssociatedGuiPropPaths = $offAssociatedGuiPropPaths;
	}
	
	/**
	 * @return GuiPropPath[]
	 */
	public function getOffAssociatedGuiPropPaths() {
		return $this->offAssociatedGuiPropPaths;
	}
	
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		$propertyName = $this->requirePropertyName();
		foreach (self::$booleanNeedles as $booleanNeedle) {
			if (StringUtils::endsWith($booleanNeedle, $propertyName)) {
				return CompatibilityLevel::COMMON;
			}
		}
		
		return null;
	}
	
	
	public function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$lar = new LenientAttributeReader($dataSet);
		
		$assoicatedGuiPropOptions = $eiu->mask()->engine()->getGuiPropOptions();
		
		$onGuiPropPathStrs = $lar->getScalarArray(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY);
		$offGuiPropPathStrs = $lar->getScalarArray(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY);
		
		$eMag = new TogglerMag('Bind GuiProps to value', !empty($onGuiPropPathStrs) || !empty($offGuiPropPathStrs));
		
		$magCollection->addMag(self::ATTR_BIND_GUI_PROPS_KEY, $eMag);
		$eMag->setOnAssociatedMagWrappers(array(
				$magCollection->addMag(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, 
						new MultiSelectMag('Associated Gui Fields when on', $assoicatedGuiPropOptions, $onGuiPropPathStrs)),
				$magCollection->addMag(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, 
						new MultiSelectMag('Associated Gui Fields when off', $assoicatedGuiPropOptions, $offGuiPropPathStrs))));
	}
	
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		if (!$magCollection->readValue(self::ATTR_BIND_GUI_PROPS_KEY)) {
			return;
		}
		
		$onGuiPropPathStrs = $magCollection->readValue(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY);
		$offGuiPropPathsStrs = $magCollection->readValue(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY);
		
		$dataSet->set(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, $onGuiPropPathStrs);
		$dataSet->set(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, $offGuiPropPathsStrs);
	}
	
	function setup(Eiu $eiu, DataSet $dataSet) {
		if ($dataSet->contains(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY)) {
			$onGuiPropPathStrs = $dataSet->getArray(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, false, array(), 
					TypeConstraint::createSimple('scalar'));
			$onGuiPropPaths = array();
			foreach ($onGuiPropPathStrs as $eiPropPathStr) {
				$onGuiPropPaths[] = GuiPropPath::create($eiPropPathStr);
			}
			
			$this->setOnAssociatedGuiPropPaths($onGuiPropPaths);
		}
		
		if ($dataSet->contains(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY)) {
			$offGuiPropPathStrs = $dataSet->getArray(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, false, array(),
					TypeConstraint::createSimple('scalar'));
			$offGuiPropPaths = array();
			foreach ($offGuiPropPathStrs as $eiPropPathStr) {
				$offGuiPropPaths[] = GuiPropPath::create($eiPropPathStr);
			}
			
			$this->setOffAssociatedGuiPropPaths($offGuiPropPaths);
		}
	}
}