<?php
namespace rocket\impl\ei\component\prop\bool\conf;

use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\prop\bool\BooleanEiProp;
use n2n\util\config\LenientAttributeReader;
use n2n\reflection\CastUtils;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use rocket\ei\component\EiSetup;
use n2n\reflection\property\TypeConstraint;
use rocket\ei\manage\gui\GuiPropPath;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use n2n\util\StringUtils;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;

class BooleanEiPropConfigurator extends AdaptableEiPropConfigurator {
	const ATTR_BIND_GUI_PROPS_KEY = 'associatedGuiProps';
	const ATTR_ON_ASSOCIATED_GUI_PROP_KEY = 'onAssociatedGuiProps';
	const ATTR_OFF_ASSOCIATED_GUI_PROP_KEY = 'offAssociatedGuiProps';
	
	public function __construct(BooleanEiProp $eiComponent) {
		parent::__construct($eiComponent);
		
		$this->addMandatory = false;

		$this->autoRegister();
	}
	
	private static $booleanNeedles = ['Available', 'Enabled'];
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		$level = parent::testCompatibility($propertyAssignation);
		
		if (!$level) return $level;
		
		$propertyName = $this->requirePropertyName();
		foreach (self::$booleanNeedles as $booleanNeedle) {
			if (StringUtils::endsWith($booleanNeedle, $propertyName)) return CompatibilityLevel::COMMON;
		}
		
		return $level;
	}
	
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$eiComponent = $this->eiComponent;
		CastUtils::assertTrue($eiComponent instanceof BooleanEiProp);
		
		$lar = new LenientAttributeReader($this->attributes);
		
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
		
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
		
		$onGuiPropPathStrs = $lar->getScalarArray(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY);
		$offGuiPropPathStrs = $lar->getScalarArray(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY);
		
		$eMag = new TogglerMag('Bind GuiProps to value', !empty($onGuiPropPathStrs) || !empty($offGuiPropPathStrs));
		
		$magCollection->addMag(self::ATTR_BIND_GUI_PROPS_KEY, $eMag);
		$eMag->setOnAssociatedMagWrappers(array(
				$magCollection->addMag(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, 
						new MultiSelectMag('Associated Gui Fields when on', $assoicatedGuiPropOptions, $onGuiPropPathStrs)),
				$magCollection->addMag(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, 
						new MultiSelectMag('Associated Gui Fields when off', $assoicatedGuiPropOptions, $offGuiPropPathStrs))));
		
		return $magDispatchable;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		if (!$magDispatchable->getPropertyValue(self::ATTR_BIND_GUI_PROPS_KEY)) return;
		
		$onGuiPropPathStrs = $magDispatchable->getPropertyValue(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY);
		$offGuiPropPathsStrs = $magDispatchable->getPropertyValue(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY);
		
		$this->attributes->set(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, $onGuiPropPathStrs);
		$this->attributes->set(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, $offGuiPropPathsStrs);
	}
	
	public function setup(EiSetup $eiSetupProcess) {
		parent::setup($eiSetupProcess);
		
		$eiComponent = $this->eiComponent;
		CastUtils::assertTrue($eiComponent instanceof BooleanEiProp);
		
		if ($this->attributes->contains(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY)) {
			$onGuiPropPathStrs = $this->attributes->getArray(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, false, array(), 
					TypeConstraint::createSimple('scalar'));
			$onGuiPropPaths = array();
			foreach ($onGuiPropPathStrs as $eiPropPathStr) {
				$onGuiPropPaths[] = GuiPropPath::create($eiPropPathStr);
			}
			
			$eiComponent->setOnAssociatedGuiPropPaths($onGuiPropPaths);
		}
		
		if ($this->attributes->contains(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY)) {
			$offGuiPropPathStrs = $this->attributes->getArray(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, false, array(),
					TypeConstraint::createSimple('scalar'));
			$offGuiPropPaths = array();
			foreach ($offGuiPropPathStrs as $eiPropPathStr) {
				$offGuiPropPaths[] = GuiPropPath::create($eiPropPathStr);
			}
			
			$eiComponent->setOffAssociatedGuiPropPaths($offGuiPropPaths);
		}
	}
}