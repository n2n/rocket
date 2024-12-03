<?php
namespace rocket\op\spec\result;

use rocket\op\spec\TypePath;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\EiModPath;
use rocket\op\ei\EiCmdPath;
use n2n\util\type\CastUtils;

class EiErrorResult {
	private $eiPropErrors = [];
	private $eiModificatorErrors = [];
	private $eiCmdErrors = [];
	
	/**
	 * @param EiPropError $eiPropError
	 */
	public function putEiPropError(EiPropError $eiPropError) {
		$this->eiPropErrors[spl_object_hash($eiPropError)] = $eiPropError;
	}
	
// 	/**
// 	 * @param EiProp $eiProp
// 	 * @return EiPropError|null
// 	 */
// 	public function errorOfEiProp(EiProp $eiProp) {
// 		return $this->findEiPropError($eiProp->getWrapper()->getEiPropCollection()->getEiMask()->getEiTypePath(), 
// 				$eiProp->getWrapper()->getEiPropPath());
// 	}
	
// 	/**
// 	 * @param TypePath $typePath
// 	 * @param EiPropPath $eiPropPath
// 	 * @return EiPropError|null
// 	 */
// 	public function findEiPropError(TypePath $typePath, EiPropPath $eiPropPath) {
// 		return ArrayUtils::first(array_filter($this->eiPropErrors, function (EiPropError $eiPropError) use ($typePath, $eiPropPath){
// 			return $eiPropError->getEiPropPath()->equals($eiPropPath)
// 					&& $eiPropError->getEiTypePath()->equals($typePath);
// 		}));
// 	}
	
	/**
	 * @param EiModificatorError $eiModificatorError
	 */
	public function putEiModificatorError(EiModificatorError $eiModificatorError) {
		$this->eiModificatorErrors[spl_object_hash($eiModificatorError)] = $eiModificatorError;
	}
	
// 	/**
// 	 * @param EiProp $eiModificator
// 	 * @return EiModificatorError|null
// 	 */
// 	public function errorOfEiModificator(EiModificator $eiModificator) {
// 		return $this->findEiModificatorError($eiModificator->getWrapper()->getEiModCollection()->getEiMask()->getEiTypePath(), 
// 				$eiModificator->getWrapper()->getEiModificatorPath());
// 	}
	
// 	/**
// 	 * @param TypePath $typePath
// 	 * @param EiModificatorPath $eiModificatorPath
// 	 * @return EiModificatorError|null
// 	 */
// 	public function findEiModificatorError(TypePath $typePath, EiModificatorPath $eiModificatorPath) {
// 		return ArrayUtils::first(array_filter($this->eiModificatorErrors, function (EiModificatorError $eiModificatorError) use ($typePath, $eiModificatorPath){
// 			return $eiModificatorError->getEiModificatorPath()->equals($eiModificatorPath)
// 					&& $eiModificatorError->getEiTypePath()->equals($typePath);
// 		}));
// 	}
	
	/**
	 * @param EiCommandError $eiCmdError
	 */
	public function putEiCommandError(EiCommandError $eiCmdError) {
		$this->eiCmdErrors[spl_object_hash($eiCmdError)] = $eiCmdError;
	}
	
// 	/**
// 	 * @param EiCommand $eiCmd
// 	 * @return EiCommandSetupError|null
// 	 */
// 	public function errorOfEiCommand(EiCommand $eiCmd) {
// 		return $this->findEiCommandError($eiCmd->getWrapper()->getEiCommandCollection()->getEiMask()->getEiTypePath(), 
// 				$eiCmd->getWrapper()->getEiCmdPath());
// 	}
	
// 	/**
// 	 * @param TypePath $typePath
// 	 * @param EiCmdPath $eiCmdPath
// 	 * @return EiCommandSetupError|null
// 	 */
// 	public function findEiCommandError(TypePath $typePath, EiCmdPath $eiCmdPath) {
// 		return ArrayUtils::first($this->getEiCommandErrors($typePath, $eiCmdPath));
// 	}
	
	public function getThrowables(?TypePath $typePath = null) {
		$throwables = [];
		array_walk($this->eiPropErrors, function (EiPropError $eiPropError) use ($typePath, $throwables) {
			if ($typePath === null || !$eiPropError->getEiTypePath()->equals($typePath)) return;
			$throwables[] = $eiPropError->getThrowable();
		});
			
		array_walk($this->eiModificatorErrors, function (EiModificatorError $eiModificatorError) use ($typePath, $throwables) {
			if ($typePath === null || !$eiModificatorError->getEiTypePath()->equals($typePath)) return;
			$throwables[] = $eiModificatorError->getThrowable();
		});
			
		array_walk($this->eiCmdErrors, function (EiCommandError $eiCmdError) use ($typePath, $throwables) {
			if ($typePath === null || !$eiCmdError->getEiTypePath()->equals($typePath)) return;
			
			$throwables[] = $eiCmdError->getThrowable();
		});
				
		return $throwables;
	}
	
	public function hasErrors(?TypePath $typePath = null) {
		return !empty($this->getThrowables($typePath));
	}
	
	/**
	 * @param TypePath $typePath
	 * @return EiPropError[]
	 */
	public function getEiPropErrors(TypePath $typePath, ?EiPropPath $eiPropPath = null) {
		return array_filter($this->eiPropErrors, function (EiPropError $eiPropError) use ($typePath, $eiPropPath) {
			return $eiPropError->getEiTypePath()->equals($typePath) 
					&& (null === $eiPropPath || $eiPropError->getEiPropPath()->equals($eiPropPath));
		});
	}
	
	/**
	 * @param TypePath $typePath
	 * @return EiModificatorError[]
	 */
	public function getEiModificatorErrors(TypePath $typePath, ?EiModPath $eiModificatorPath = null) {
		return array_filter($this->eiModificatorErrors, function (EiModificatorError $eiModificatorError) use ($typePath, $eiModificatorPath) {
			return $eiModificatorError->getEiTypePath()->equals($typePath) 
					&& (null === $eiModificatorPath || $eiModificatorError->getEiModificatorPath()->equals($eiModificatorPath));
		});
	}
	
	/**
	 * @param TypePath $typePath
	 * @return EiCommandError[]
	 */
	public function getEiCommandErrors(TypePath $typePath, ?EiCmdPath $eiCmdPath = null) {
		return array_filter($this->eiCmdErrors, function (EiCommandError $eiCmdError) use ($typePath, $eiCmdPath) {
			return $eiCmdError->getEiTypePath()->equals($typePath) 
					&& (null === $eiCmdPath || $eiCmdError->getEiCmdPath()->equals($eiCmdPath));
		});
	}
}