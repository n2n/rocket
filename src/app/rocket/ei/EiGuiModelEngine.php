<?php
namespace rocket\ei;

use n2n\core\container\N2nContext;
use rocket\ei\EiType;
use rocket\ei\mask\EiMask;
use rocket\ei\EiPropPath;
use rocket\ei\manage\ManageState;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\EiGuiModelFactory;
use rocket\ei\manage\gui\EiGuiModel;
use rocket\ei\manage\gui\GuiBuildFailedException;

class EiGuiModelEngine {
	
	function __construct(private EiGuiModelFactory $eiGuiModelFactory) {
	}

	/**
	 * @param int $viewMode
	 * @param EiType|null $allowedEiTypes
	 * @param EiPropPath|null $defPropPaths
	 * @return string
	 */
	private function createCacheKey(int $viewMode, ?array $allowedEiTypes, ?array $defPropPaths): string {
		$allowedEiTypesHashPart = '';
		if ($allowedEiTypes !== null) {
			$allowedEiTypesHashPart = json_encode(array_map(function($eiType) { return $eiType->getId(); }, $allowedEiTypes));
		}
		
		$defPropPathsHashPart = '';
		if ($defPropPaths !== null) {
			$defPropPathsHashPart = json_encode(array_map(function($defPropPath) { return (string) $defPropPath; }, $defPropPaths));
		}
		
		return $viewMode . ' ' . $allowedEiTypesHashPart . ' '
				. $defPropPathsHashPart;
	}
	
	private array $eiGuiModels = [];

	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array|null $defPropPaths
	 * @return EiGuiModel
	 */
	function obtainEiGuiModel(int $viewMode, ?array $defPropPaths): EiGuiModel {
		$key = $this->createCacheKey($viewMode, null, $defPropPaths);
		
		if (isset($this->eiGuiModels[$key])) {
			return $this->eiGuiModels[$key];
		}
		
		return $this->eiGuiModels[$key] = $this->eiGuiModelFactory
				->createEiGuiModel($viewMode, $defPropPaths, true);
	}
	
	
	private array $forgeEiGuiModels = [];

	/**
	 * @param int $viewMode
	 * @param array|null $defPropPaths
	 * @return EiGuiModel
	 * @throws GuiBuildFailedException
	 */
	function obtainForgeEiGuiModel(int $viewMode, ?array $defPropPaths): EiGuiModel {
		$key = $this->createCacheKey($viewMode, null, $defPropPaths);
		
		if (isset($this->forgeEiGuiModels[$key])) {
			return $this->forgeEiGuiModels[$key];
		}
		
		return $this->forgeEiGuiModels[$key] = $this->eiGuiModelFactory
				->createForgeEiGuiModel($viewMode, $defPropPaths, true);
	}
	
	
	private array $multiEiGuiModels = [];

	/**
	 * @param int $viewMode
	 * @param array|null $allowedEiTypes
	 * @param array|null $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return EiGuiModel
	 * @throws GuiBuildFailedException
	 */
	function obtainMultiEiGuiModel(int $viewMode, ?array $allowedEiTypes,
			?array $defPropPaths, bool $guiStructureDeclarationsRequired) {
		ArgUtils::valArray($allowedEiTypes, EiType::class, true);
		$key = $this->createCacheKey($contextEiMask, $viewMode, $allowedEiTypes, $defPropPaths);
		
		if (isset($this->multiEiGuiModels[$key])) {
			return $this->multiEiGuiModels[$key];
		}
				
		return $this->multiEiGuiModels[$key] = $this->eiGuiModelFactory
				->createMultiEiGuiModel($contextEiMask, $viewMode, $allowedEiTypes, $defPropPaths, true);
		
	}
	
	private array $forgeMultiEiGuiModels = [];
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $allowedEiTypeIds
	 * @param array $defPropPaths
	 * @return EiGuiModel
	 *@throws GuiBuildFailedException
	 */
	function obtainForgeMultiEiGuiModel(int $viewMode, ?array $allowedEiTypes,
			?array $defPropPaths): EiGuiModel {
		ArgUtils::valArray($allowedEiTypes, EiType::class, true);
		$key = $this->createCacheKey($viewMode, $allowedEiTypes, $defPropPaths);
		
		if (isset($this->forgeMultiEiGuiModels[$key])) {
			return $this->forgeMultiEiGuiModels[$key];
		}
				
		return $this->forgeMultiEiGuiModels[$key] = $this->eiGuiModelFactory
				->createForgeMultiEiGuiModel($viewMode, $allowedEiTypes, $defPropPaths, true);
	}
//
//	/**
//	 * @param N2nContext $n2nContext
//	 * @param EiGuiModel $eiGuiModel
//	 * @param array $defPropPaths
//	 * @param bool $guiStructureDeclarationsRequired
//	 * @return \rocket\ei\manage\gui\EiGuiFrame
//	 */
//	private function applyEiGuiFrame(EiGuiModel $eiGuiModel, bool $nonAbstractOnly, array $defPropPaths = null,
//			bool $guiStructureDeclarationsRequired = true) {
//		$contextEiMask = $eiGuiModel->getContextEiMask();
//
//		if (!$this->testIfAllowed($contextEiMask->getEiType(), $nonAbstractOnly, null)) {
//			return;
//		}
//
//		$guiDefinition = $this->def->getGuiDefinition($contextEiMask);
//		$guiDefinition->createEiGuiFrame($this->n2nContext, $eiGuiModel, $defPropPaths,
//				$guiStructureDeclarationsRequired);
//	}
//
//	/**
//	 * @param N2nContext $n2nContext
//	 * @param EiGuiModel $eiGuiModel
//	 * @param array $allowedTypeIds
//	 * @param array $defPropPaths
//	 * @param bool $guiStructureDeclarationsRequired
//	 * @return \rocket\ei\manage\gui\EiGuiFrame[]
//	 */
//	private function applyPossibleEiGuiFrames(EiGuiModel $eiGuiModel, bool $creatablesOnly, array $allowedTypes = null,
//			array $defPropPaths = null, bool $guiStructureDeclarationsRequired = true) {
//		$contextEiMask = $eiGuiModel->getContextEiMask();
//		$contextEiType = $contextEiMask->getEiType();
//
//		if ($this->testIfAllowed($contextEiType, $creatablesOnly, $allowedTypes)) {
//			$guiDefinition = $this->def->getGuiDefinition($contextEiMask->determineEiMask($contextEiType));
//			$eiGuiFrame = $guiDefinition->createEiGuiFrame($this->n2nContext, $eiGuiModel, $defPropPaths,
//					$guiStructureDeclarationsRequired);
//			$eiGuiModel->putEiGuiFrame($eiGuiFrame);
//		}
//
//		foreach ($contextEiType->getAllSubEiTypes() as $eiType) {
//			if (!$this->testIfAllowed($eiType, $creatablesOnly, $allowedTypes)) {
//				continue;
//			}
//
//			$guiDefinition = $this->def->getGuiDefinition($contextEiMask->determineEiMask($contextEiType));
//			$eiGuiFrame = $guiDefinition->createEiGuiFrame($this->n2nContext, $eiGuiModel, $defPropPaths,
//					$guiStructureDeclarationsRequired);
//			$eiGuiModel->putEiGuiFrame($eiGuiFrame);
//		}
//	}
//
//	/**
//	 * @param EiType $eiType
//	 * @param bool $creatablesOnly
//	 * @param EiType[] $allowedTypeIds
//	 */
//	private function testIfAllowed($eiType, $creatablesOnly, $allowedEiTypes) {
//		if ($creatablesOnly && $eiType->isAbstract()) {
//			return false;
//		}
//
//		if ($allowedEiTypes === null) {
//			return true;
//		}
//
//		foreach ($allowedEiTypes as $allowedEiType) {
//			if ($eiType->isA($allowedEiType)) {
//				return true;
//			}
//		}
//
//		return false;
//	}
}