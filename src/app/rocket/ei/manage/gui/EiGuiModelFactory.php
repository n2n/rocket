<?php
namespace rocket\ei\manage\gui;

use n2n\core\container\N2nContext;
use rocket\ei\EiType;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\EiLaunch;

class EiGuiModelFactory {

	function __construct(private EiLaunch $eiLaunch) {
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return EiGuiModel
	 */
	function createEiGuiModel(EiMask $contextEiMask, int $viewMode, ?array $defPropPaths, 
			bool $guiStructureDeclarationsRequired) {
		$eiGuiModel = new EiGuiModel($contextEiMask, $viewMode);
		
		$this->applyEiGuiFrame($eiGuiModel, false, $defPropPaths, $guiStructureDeclarationsRequired);
		
		return $eiGuiModel;
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return EiGuiModel
	 * @throws GuiBuildFailedException
	 */
	function createForgeEiGuiModel(EiMask $contextEiMask, int $viewMode, ?array $defPropPaths, 
			bool $guiStructureDeclarationsRequired) {
		$eiGuiModel = new EiGuiModel($contextEiMask, $viewMode);
		
		$this->applyEiGuiFrame($eiGuiModel, true, $defPropPaths, $guiStructureDeclarationsRequired);
		
		if (!$eiGuiModel->hasEiGuiFrames()) {
			throw new GuiBuildFailedException('Can not build forge EiGuiModel based on ' . $eiGuiModel->getContextEiMask() 
					. ' because its type is abstract.');
		}
		
		return $eiGuiModel;
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $allowedEiTypeIds
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return EiGuiModel
	 * @throws GuiBuildFailedException
	 */
	function createMultiEiGuiModel(EiMask $contextEiMask, int $viewMode, ?array $allowedEiTypes, 
			?array $defPropPaths, bool $guiStructureDeclarationsRequired) {
		$eiGuiModel = new EiGuiModel($contextEiMask, $viewMode);
	
		$this->applyPossibleEiGuiFrames($eiGuiModel, false, $allowedEiTypes, $defPropPaths, $guiStructureDeclarationsRequired);
		
		if (!$eiGuiModel->hasEiGuiFrames()) {
			throw new GuiBuildFailedException('Can not build forge EiGuiModel based on ' . $eiGuiModel->getContextEiMask()
					. ' because its type and sub types do not match the allowed EiTypes: ' . implode(', ', $allowedEiTypes));
		}
		
		return $eiGuiModel;
		
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $allowedEiTypeIds
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return EiGuiModel
	 *@throws GuiBuildFailedException
	 */
	function createForgeMultiEiGuiModel(EiMask $contextEiMask, int $viewMode, ?array $allowedEiTypes,
			?array $defPropPaths, bool $guiStructureDeclarationsRequired) {
		$eiGuiModel = new EiGuiModel($contextEiMask, $viewMode);
		
		$this->applyPossibleEiGuiFrames($eiGuiModel, true, $allowedEiTypes, $defPropPaths, $guiStructureDeclarationsRequired);
		
		if (!$eiGuiModel->hasEiGuiFrames()) {
			throw new GuiBuildFailedException('Can not build forge EiGuiModel based on ' . $eiGuiModel->getContextEiMask()
					. ' because its type and sub types are either abstract or do not match the allowed EiTypes: ' 
					. implode(', ', array_map(function ($arg) { return (string) $arg; }, (array) $allowedEiTypes)));
		}
		
		return $eiGuiModel;
	}

	/**
	 * @param EiGuiModel $eiGuiModel
	 * @param bool $nonAbstractOnly
	 * @param array|null $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return EiGuiFrame
	 */
	private function applyEiGuiFrame(EiGuiModel $eiGuiModel, bool $nonAbstractOnly, array $defPropPaths = null,
			bool $guiStructureDeclarationsRequired = true) {
		$contextEiMask = $eiGuiModel->getContextEiMask();
				
		if (!$this->testIfAllowed($contextEiMask->getEiType(), $nonAbstractOnly, null)) {
			return;
		}
		
		$guiDefinition = $contextEiMask->getEiEngine()->getGuiDefinition();
		$guiDefinition->createEiGuiFrame($this->eiLaunch, $eiGuiModel, $defPropPaths,
				$guiStructureDeclarationsRequired);
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @param EiGuiModel $eiGuiModel
	 * @param array $allowedTypeIds
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return \rocket\ei\manage\gui\EiGuiFrame[]
	 */
	private function applyPossibleEiGuiFrames(EiGuiModel $eiGuiModel, bool $creatablesOnly, array $allowedEiTypes = null,
			array $defPropPaths = null, bool $guiStructureDeclarationsRequired = true) {
		$contextEiMask = $eiGuiModel->getContextEiMask();
		$contextEiType = $contextEiMask->getEiType();
		
		if ($this->testIfAllowed($contextEiType, $creatablesOnly, $allowedEiTypes)) {
			$guiDefinition = $contextEiMask->determineEiMask($contextEiType)->getEiEngine()->getGuiDefinition();
			$eiGuiFrame = $guiDefinition->createEiGuiFrame($this->n2nContext, $eiGuiModel, $defPropPaths,
					$guiStructureDeclarationsRequired);
			$eiGuiModel->putEiGuiFrame($eiGuiFrame);
		}

		foreach ($contextEiType->getAllSubEiTypes() as $eiType) {
			if (!$this->testIfAllowed($eiType, $creatablesOnly, $allowedEiTypes)) {
				continue;
			}
			
			$guiDefinition = $contextEiMask->determineEiMask($eiType)->getEiEngine()->getGuiDefinition();
			$eiGuiFrame = $guiDefinition->createEiGuiFrame($this->n2nContext, $eiGuiModel, $defPropPaths,
					$guiStructureDeclarationsRequired);
			$eiGuiModel->putEiGuiFrame($eiGuiFrame);
		}
	}
	
	/**
	 * @param EiType $eiType
	 * @param bool $creatablesOnly
	 * @param EiType[] $allowedTypeIds
	 */
	private function testIfAllowed($eiType, $creatablesOnly, $allowedEiTypes) {
		if ($creatablesOnly && $eiType->isAbstract()) {
			return false;
		}
		
		if ($allowedEiTypes === null) {
			return true;
		}
		
		foreach ($allowedEiTypes as $allowedEiType) {
			if ($eiType->isA($allowedEiType)) {
				return true;
			}
		}
		
		return false;
	}
}