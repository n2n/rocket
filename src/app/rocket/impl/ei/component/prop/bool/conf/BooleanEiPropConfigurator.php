<?php
namespace rocket\impl\ei\component\prop\bool\conf;

use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\prop\bool\BooleanEiProp;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use rocket\ei\component\EiSetup;
use n2n\util\type\TypeConstraint;
use rocket\ei\manage\gui\GuiFieldPath;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use n2n\util\StringUtils;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\util\type\CastUtils;

class BooleanEiPropConfigurator extends AdaptableEiPropConfigurator {
	const ATTR_BIND_GUI_PROPS_KEY = 'associatedGuiProps';
	const ATTR_ON_ASSOCIATED_GUI_PROP_KEY = 'onAssociatedGuiProps';
	const ATTR_OFF_ASSOCIATED_GUI_PROP_KEY = 'offAssociatedGuiProps';
	
	public function __construct(BooleanEiProp $eiComponent) {
		parent::__construct($eiComponent);
		
		$this->autoRegister();
		
		$this->addMandatory = false;
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
		
		$onGuiFieldPathStrs = $lar->getScalarArray(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY);
		$offGuiFieldPathStrs = $lar->getScalarArray(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY);
		
		$eMag = new TogglerMag('Bind GuiProps to value', !empty($onGuiFieldPathStrs) || !empty($offGuiFieldPathStrs));
		
		$magCollection->addMag(self::ATTR_BIND_GUI_PROPS_KEY, $eMag);
		$eMag->setOnAssociatedMagWrappers(array(
				$magCollection->addMag(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, 
						new MultiSelectMag('Associated Gui Fields when on', $assoicatedGuiPropOptions, $onGuiFieldPathStrs)),
				$magCollection->addMag(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, 
						new MultiSelectMag('Associated Gui Fields when off', $assoicatedGuiPropOptions, $offGuiFieldPathStrs))));
		
		return $magDispatchable;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		if (!$magDispatchable->getPropertyValue(self::ATTR_BIND_GUI_PROPS_KEY)) return;
		
		$onGuiFieldPathStrs = $magDispatchable->getPropertyValue(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY);
		$offGuiFieldPathsStrs = $magDispatchable->getPropertyValue(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY);
		
		$this->attributes->set(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, $onGuiFieldPathStrs);
		$this->attributes->set(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, $offGuiFieldPathsStrs);
	}
	
	public function setup(EiSetup $eiSetupProcess) {
		parent::setup($eiSetupProcess);
		
		$eiComponent = $this->eiComponent;
		CastUtils::assertTrue($eiComponent instanceof BooleanEiProp);
		
		if ($this->attributes->contains(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY)) {
			$onGuiFieldPathStrs = $this->attributes->getArray(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, false, array(), 
					TypeConstraint::createSimple('scalar'));
			$onGuiFieldPaths = array();
			foreach ($onGuiFieldPathStrs as $eiPropPathStr) {
				$onGuiFieldPaths[] = GuiFieldPath::create($eiPropPathStr);
			}
			
			$eiComponent->setOnAssociatedGuiFieldPaths($onGuiFieldPaths);
		}
		
		if ($this->attributes->contains(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY)) {
			$offGuiFieldPathStrs = $this->attributes->getArray(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, false, array(),
					TypeConstraint::createSimple('scalar'));
			$offGuiFieldPaths = array();
			foreach ($offGuiFieldPathStrs as $eiPropPathStr) {
				$offGuiFieldPaths[] = GuiFieldPath::create($eiPropPathStr);
			}
			
			$eiComponent->setOffAssociatedGuiFieldPaths($offGuiFieldPaths);
		}
	}
}