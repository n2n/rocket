<?php
namespace rocket\impl\ei\component\modificator\constraint;

use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\component\EiConfigurator;
use rocket\impl\ei\component\EiConfiguratorAdapter;
use n2n\core\container\N2nContext;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use n2n\util\config\LenientAttributeReader;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\config\Attributes;
use rocket\spec\ei\component\EiSetupProcess;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;
use n2n\l10n\MessageCode;
use n2n\persistence\orm\criteria\Criteria;
use rocket\spec\ei\manage\util\model\EiuEngine;
use rocket\spec\ei\EiPropPath;
use n2n\reflection\ArgUtils;
use rocket\impl\ei\component\modificator\adapter\IndependentEiModificatorAdapter;

class UniqueEiModificator extends IndependentEiModificatorAdapter {
	private $uniqueEiPropPaths = array();
	private $uniquePerEiPropPaths = array();
	
	/**
	 * @return EiPropPath[]
	 */
	function getUniqueEiPropPaths() {
		return $this->uniqueEiPropPaths;
	}

	/**
	 * @param EiPropPath[] $uniqueEiPropPaths
	 */
	function setUniqueEiPropPaths(array $uniqueEiPropPaths) {
		ArgUtils::valArray($uniqueEiPropPaths, EiPropPath::class);
		$this->uniqueEiPropPaths = $uniqueEiPropPaths;
	}
	
	/**
	 * @return EiPropPath[]
	 */
	function getUniquePerEiPropPaths() {
		return $this->uniquePerEiPropPaths;
	}
	
	/**
	 * @param EiPropPath[] $uniquePerEiPropPaths
	 */
	function setUniquePerEiPropPaths(array $uniquePerEiPropPaths) {
		ArgUtils::valArray($uniquePerEiPropPaths, EiPropPath::class);
		$this->uniquePerEiPropPaths = $uniquePerEiPropPaths;
	}

	function createEiConfigurator(): EiConfigurator {
		return new UniqueEiConfigurator($this);		
	}
	
	function setupEiEntry(Eiu $eiu) {
		if ($eiu->entry()->isDraft() 
				|| (empty($this->uniqueEiPropPaths) && empty($this->uniquePerEiPropPaths))) {
			return;
		}
		
		
		$eiu->entry()->onValidate(function () use ($eiu) {
			$this->validate($eiu);
		});
	}
		
	private function validate(Eiu $eiu) {
		$eiuEntry = $eiu->entry();
		$eiuEngine = $eiu->engine();
		
		$criteria = $eiu->frame()->createCountCriteria('e', CriteriaConstraint::ALL_TYPES);
		
		$this->restrictCriteria($criteria, $eiuEngine, $this->uniqueEiPropPaths);
		$this->restrictCriteria($criteria, $eiuEngine, $this->uniquePerEiPropPaths);
		
		if (!$eiuEntry->isNew()) {
			$criteria->where()->match('e', '!=', $eiuEntry->getEntityObj());
		}
		
		if (0 == $criteria->toQuery()->fetchSingle()) {
			return;
		}
		
		foreach ($this->uniqueEiPropPaths as $eiPropPath) {
			$eiuEntry->field($eiPropPath)->addError(new MessageCode('ei_impl_field_not_unique'));
		}
	}
	
	/**
	 * @param Criteria $criteria
	 * @param EiuEngine $eiuEngine
	 * @param EiPropPath[] $eiPropPaths
	 */
	private function restrictCriteria($criteria, $eiuEngine, $eiPropPaths) {
		foreach ($eiPropPaths as $eiPropPath) {
			$eiuProp = $eiuEngine->prop($eiPropPath, false);
			if ($eiuProp === null || $eiuProp->isGeneric()) continue;
			
			$ci = $eiuProp->createGenericCriteriaItem('e');
			$cv = $eiuProp->createGenericEntityValue($eiuEntry);
			
			$criteria->where()->match($ci, '=', $cv);
		}
	}
}

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
		
		$uniqueEiPropPaths = array();
		foreach ($this->attributes->getScalarArray(self::ATTR_UNIQUE_PROPS_KEY, false) as $eiPropPathStr) {
			$uniqueEiPropPaths[] = EiPropPath::create($eiPropPathStr);
		}
		$uniqueEiModificator->setUniqueEiPropPaths($uniqueEiPropPaths);
		
		$uniquePerEiPropPaths = array();
		foreach ($this->attributes->getScalarArray(self::ATTR_UNIQUE_PER_PROPS_KEY, false) as $eiPropPathStr) {
			$uniquePerEiPropPaths[] = EiPropPath::create($eiPropPathStr);
		}
		$uniqueEiModificator->setUniquePerEiPropPaths($uniquePerEiPropPaths);
	}
}