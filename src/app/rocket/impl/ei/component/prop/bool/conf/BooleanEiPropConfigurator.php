<?php
namespace rocket\impl\ei\component\prop\bool\conf;

use rocket\impl\ei\component\prop\adapter\AdaptableEiPropConfigurator;
use rocket\impl\ei\component\prop\bool\BooleanEiProp;
use n2n\util\config\LenientAttributeReader;
use n2n\reflection\CastUtils;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use rocket\ei\component\EiSetup;
use n2n\reflection\property\TypeConstraint;
use rocket\ei\manage\gui\GuiIdPath;

class BooleanEiPropConfigurator extends AdaptableEiPropConfigurator {
	const ATTR_BIND_GUI_PROPS_KEY = 'associatedGuiProps';
	const ATTR_ON_ASSOCIATED_GUI_PROP_KEY = 'onAssociatedGuiProps';
	const ATTR_OFF_ASSOCIATED_GUI_PROP_KEY = 'offAssociatedGuiProps';
	
	public function __construct(BooleanEiProp $eiComponent) {
		parent::__construct($eiComponent);
		
		$this->addMandatory = false;

		$this->autoRegister();
	}
	
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$eiComponent = $this->eiComponent;
		CastUtils::assertTrue($eiComponent instanceof BooleanEiProp);
		
		$lar = new LenientAttributeReader($this->attributes);
		
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
		
		$guiProps = null;
		try {
			$guiProps = $eiComponent->getEiMask()->getEiEngine()->getGuiDefinition()->getGuiProps();
		} catch (\Throwable $e) {
			$guiProps = $eiComponent->getEiMask()->getEiEngine()->getGuiDefinition()->getLevelGuiProps();
		}
		
		$assoicatedGuiPropOptions = array();
		foreach ($guiProps as $guiIdPathStr => $guiProp) {
			$assoicatedGuiPropOptions[$guiIdPathStr] = $guiProp->getDisplayLabel();
		}
		
		$onGuiIdPathStrs = $lar->getScalarArray(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY);
		$offGuiIdPathStrs = $lar->getScalarArray(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY);
		
		$eMag = new TogglerMag('Bind GuiProps to value', !empty($onGuiIdPathStrs) || !empty($offGuiIdPathStrs));
		
		$magCollection->addMag(self::ATTR_BIND_GUI_PROPS_KEY, $eMag);
		$eMag->setOnAssociatedMagWrappers(array(
				$magCollection->addMag(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, 
						new MultiSelectMag('Associated Gui Fields when on', $assoicatedGuiPropOptions, $onGuiIdPathStrs)),
				$magCollection->addMag(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, 
						new MultiSelectMag('Associated Gui Fields when off', $assoicatedGuiPropOptions, $offGuiIdPathStrs))));
		
		return $magDispatchable;
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		if (!$magDispatchable->getPropertyValue(self::ATTR_BIND_GUI_PROPS_KEY)) return;
		
		$onGuiIdPathStrs = $magDispatchable->getPropertyValue(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY);
		$$offGuiIdPathsStrs = $magDispatchable->getPropertyValue(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY);
		
		$this->attributes->set(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, $onGuiIdPathStrs);
		$this->attributes->set(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, $offGuiIdPathsStrs);
	}
	
	public function setup(EiSetup $eiSetupProcess) {
		parent::setup($eiSetupProcess);
		
		$eiComponent = $this->eiComponent;
		CastUtils::assertTrue($eiComponent instanceof BooleanEiProp);
		
		if ($this->attributes->contains(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY)) {
			$onGuiIdPathStrs = $this->attributes->getArray(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, false, array(), 
					TypeConstraint::createSimple('scalar'));
			$onGuiIdPaths = array();
			foreach ($onGuiIdPathStrs as $guiIdPathStr) {
				$onGuiIdPaths[] = GuiIdPath::create($guiIdPathStr);
			}
			
			$eiComponent->setOnAssociatedGuiIdPaths($onGuiIdPaths);
		}
		
		if ($this->attributes->contains(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY)) {
			$offGuiIdPathStrs = $this->attributes->getArray(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, false, array(),
					TypeConstraint::createSimple('scalar'));
			$offGuiIdPaths = array();
			foreach ($offGuiIdPathStrs as $guiIdPathStr) {
				$offGuiIdPaths[] = GuiIdPath::create($guiIdPathStr);
			}
			
			$eiComponent->setOffAssociatedGuiIdPaths($offGuiIdPaths);
		}
	}
}