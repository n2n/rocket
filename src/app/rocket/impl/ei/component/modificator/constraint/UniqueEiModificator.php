<?php
namespace rocket\impl\ei\component\modificator\constraint;

use rocket\ei\manage\util\model\Eiu;
use rocket\ei\component\EiConfigurator;
use rocket\ei\manage\critmod\CriteriaConstraint;
use n2n\l10n\MessageCode;
use n2n\persistence\orm\criteria\Criteria;
use rocket\ei\manage\util\model\EiuEngine;
use rocket\ei\EiPropPath;
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

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\modificator\adapter\IndependentEiModificatorAdapter::createEiConfigurator()
	 */
	function createEiConfigurator(): EiConfigurator {
		return new UniqueEiConfigurator($this);		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\modificator\adapter\IndependentEiModificatorAdapter::setupEiEntry()
	 */
	function setupEiEntry(Eiu $eiu) {
		if ($eiu->entry()->isDraft() 
				|| (empty($this->uniqueEiPropPaths) && empty($this->uniquePerEiPropPaths))) {
			return;
		}
		
		$eiu->entry()->onValidate(function () use ($eiu) {
			$this->validate($eiu);
		});
	}
	
	/**
	 * @param Eiu $eiu
	 */
	private function validate(Eiu $eiu) {
		$eiuEntry = $eiu->entry();
		$eiuEngine = $eiu->engine();
		
		$criteria = $eiu->frame()->createCountCriteria('e', CriteriaConstraint::ALL_TYPES);
		
		$this->restrictCriteria($criteria, $eiuEngine, $this->uniqueEiPropPaths, $eiuEntry);
		$this->restrictCriteria($criteria, $eiuEngine, $this->uniquePerEiPropPaths, $eiuEntry);
		
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
	 * @param \rocket\ei\manage\util\model\EiuEntry $eiuEntry
	 */
	private function restrictCriteria($criteria, $eiuEngine, $eiPropPaths, $eiuEntry) {
		foreach ($eiPropPaths as $eiPropPath) {
			$eiuProp = $eiuEngine->prop($eiPropPath, false);
			
			$ci = $eiuProp->createGenericCriteriaItem('e');
			$cv = $eiuProp->createGenericEntityValue($eiuEntry);
			
			$criteria->where()->match($ci, '=', $cv);
		}
	}
}