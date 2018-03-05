<?php
namespace rocket\impl\ei\component\modificator\constraint;

use rocket\impl\ei\component\EiConfiguratorAdapter;
use n2n\core\container\N2nContext;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\config\LenientAttributeReader;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\util\config\Attributes;
use rocket\ei\component\EiSetupProcess;
use n2n\reflection\CastUtils;
use rocket\ei\EiPropPath;

class UniqueEiConfigurator extends EiConfiguratorAdapter {
	const ATTR_UNIQUE_PROPS_KEY = 'uniqueProps';
	const ATTR_UNIQUE_PER_PROPS_KEY = 'uniquePerProps';
	
	function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$lar = new LenientAttributeReader($this->attributes);
		
		$eiu = $this->eiu($n2nContext);
		$options = $eiu->engine()->getGenericEiPropertyOptions();
		
		$magCollection = new MagCollection();
		$magCollection->addMag(self::ATTR_UNIQUE_PROPS_KEY,
				new MultiSelectMag($label, $options, $lar->getScalarArray(self::ATTR_UNIQUE_PROPS_KEY)));
		
		$magCollection->addMag(self::ATTR_UNIQUE_PER_PROPS_KEY,
				new MultiSelectMag($label, $options, $lar->getScalarArray(self::ATTR_UNIQUE_PER_PROPS_KEY)));
		
		return new MagForm($magCollection);
	}
	
	function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		$this->attributes = new Attributes();
		$this->attributes->set(self::ATTR_UNIQUE_PROPS_KEY,
				$magDispatchable->getPropertyValue(SELF::ATTR_UNIQUE_PROPS_KEY));
		$this->attributes->set(self::ATTR_UNIQUE_PER_PROPS_KEY,
				$magDispatchable->getPropertyValue(SELF::ATTR_UNIQUE_PROPS_KEY));
	}
	
	function setup(EiSetupProcess $eiSetupProcess) {
		$uniqueEiModificator = $this->eiComponent;
		CastUtils::assertTrue($uniqueEiModificator instanceof UniqueEiModificator);
		
		$eiuEngine = $eiSetupProcess->eiu()->engine();
		
		$uniqueEiPropPaths = array();
		foreach ($this->attributes->getScalarArray(self::ATTR_UNIQUE_PROPS_KEY, false) as $eiPropPathStr) {
			$eiPropPath = EiPropPath::create($eiPropPathStr);
			
			if ($eiuEngine->containsGenericEiProperty($eiPropPath)) {
				$uniqueEiPropPaths[] = $eiPropPath;
			}
		}
		$uniqueEiModificator->setUniqueEiPropPaths($uniqueEiPropPaths);
		
		$uniquePerEiPropPaths = array();
		foreach ($this->attributes->getScalarArray(self::ATTR_UNIQUE_PER_PROPS_KEY, false) as $eiPropPathStr) {
			$eiPropPath = EiPropPath::create($eiPropPathStr);
			
			if ($eiuEngine->containsGenericEiProperty($eiPropPath)) {
				$uniquePerEiPropPaths[] = $eiPropPath;
			}
		}
		$uniqueEiModificator->setUniquePerEiPropPaths($uniquePerEiPropPaths);
	}
}