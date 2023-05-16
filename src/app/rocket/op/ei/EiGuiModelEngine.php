<?php
namespace rocket\op\ei;

use n2n\core\container\N2nContext;
use rocket\op\ei\EiType;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\ManageState;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\gui\EiGuiDeclarationFactory;
use rocket\op\ei\manage\gui\EiGuiDeclaration;
use rocket\op\ei\manage\gui\GuiBuildFailedException;

class EiGuiDeclarationEngine {
	
	function __construct(private EiGuiDeclarationFactory $eiGuiDeclarationFactory) {
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
	
	private array $eiGuiDeclarations = [];

	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array|null $defPropPaths
	 * @return EiGuiDeclaration
	 */
	function obtainEiGuiDeclaration(int $viewMode, ?array $defPropPaths): EiGuiDeclaration {
		$key = $this->createCacheKey($viewMode, null, $defPropPaths);
		
		if (isset($this->eiGuiDeclarations[$key])) {
			return $this->eiGuiDeclarations[$key];
		}
		
		return $this->eiGuiDeclarations[$key] = $this->eiGuiDeclarationFactory
				->createEiGuiDeclaration($viewMode, $defPropPaths, true);
	}
	
	
	private array $forgeEiGuiDeclarations = [];

	/**
	 * @param int $viewMode
	 * @param array|null $defPropPaths
	 * @return EiGuiDeclaration
	 * @throws GuiBuildFailedException
	 */
	function obtainForgeEiGuiDeclaration(int $viewMode, ?array $defPropPaths): EiGuiDeclaration {
		$key = $this->createCacheKey($viewMode, null, $defPropPaths);
		
		if (isset($this->forgeEiGuiDeclarations[$key])) {
			return $this->forgeEiGuiDeclarations[$key];
		}
		
		return $this->forgeEiGuiDeclarations[$key] = $this->eiGuiDeclarationFactory
				->createForgeEiGuiDeclaration($viewMode, $defPropPaths, true);
	}
	
	
	private array $multiEiGuiDeclarations = [];

	/**
	 * @param int $viewMode
	 * @param array|null $allowedEiTypes
	 * @param array|null $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return EiGuiDeclaration
	 * @throws GuiBuildFailedException
	 */
	function obtainMultiEiGuiDeclaration(int $viewMode, ?array $allowedEiTypes,
			?array $defPropPaths, bool $guiStructureDeclarationsRequired) {
		ArgUtils::valArray($allowedEiTypes, EiType::class, true);
		$key = $this->createCacheKey($viewMode, $allowedEiTypes, $defPropPaths);
		
		if (isset($this->multiEiGuiDeclarations[$key])) {
			return $this->multiEiGuiDeclarations[$key];
		}
				
		return $this->multiEiGuiDeclarations[$key] = $this->eiGuiDeclarationFactory
				->createMultiEiGuiDeclaration($viewMode, $allowedEiTypes, $defPropPaths, true);
		
	}
	
	private array $forgeMultiEiGuiDeclarations = [];
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $allowedEiTypeIds
	 * @param array $defPropPaths
	 * @return EiGuiDeclaration
	 *@throws GuiBuildFailedException
	 */
	function obtainForgeMultiEiGuiDeclaration(int $viewMode, ?array $allowedEiTypes,
			?array $defPropPaths): EiGuiDeclaration {
		ArgUtils::valArray($allowedEiTypes, EiType::class, true);
		$key = $this->createCacheKey($viewMode, $allowedEiTypes, $defPropPaths);
		
		if (isset($this->forgeMultiEiGuiDeclarations[$key])) {
			return $this->forgeMultiEiGuiDeclarations[$key];
		}
				
		return $this->forgeMultiEiGuiDeclarations[$key] = $this->eiGuiDeclarationFactory
				->createForgeMultiEiGuiDeclaration($viewMode, $allowedEiTypes, $defPropPaths, true);
	}
//
//	/**
//	 * @param N2nContext $n2nContext
//	 * @param EiGuiDeclaration $eiGuiDeclaration
//	 * @param array $defPropPaths
//	 * @param bool $guiStructureDeclarationsRequired
//	 * @return \rocket\op\ei\manage\gui\EiGuiMaskDeclaration
//	 */
//	private function applyEiGuiMaskDeclaration(EiGuiDeclaration $eiGuiDeclaration, bool $nonAbstractOnly, array $defPropPaths = null,
//			bool $guiStructureDeclarationsRequired = true) {
//		$contextEiMask = $eiGuiDeclaration->getContextEiMask();
//
//		if (!$this->testIfAllowed($contextEiMask->getEiType(), $nonAbstractOnly, null)) {
//			return;
//		}
//
//		$guiDefinition = $this->def->getGuiDefinition($contextEiMask);
//		$guiDefinition->createEiGuiMaskDeclaration($this->n2nContext, $eiGuiDeclaration, $defPropPaths,
//				$guiStructureDeclarationsRequired);
//	}
//
//	/**
//	 * @param N2nContext $n2nContext
//	 * @param EiGuiDeclaration $eiGuiDeclaration
//	 * @param array $allowedTypeIds
//	 * @param array $defPropPaths
//	 * @param bool $guiStructureDeclarationsRequired
//	 * @return \rocket\op\ei\manage\gui\EiGuiMaskDeclaration[]
//	 */
//	private function applyPossibleEiGuiMaskDeclarations(EiGuiDeclaration $eiGuiDeclaration, bool $creatablesOnly, array $allowedTypes = null,
//			array $defPropPaths = null, bool $guiStructureDeclarationsRequired = true) {
//		$contextEiMask = $eiGuiDeclaration->getContextEiMask();
//		$contextEiType = $contextEiMask->getEiType();
//
//		if ($this->testIfAllowed($contextEiType, $creatablesOnly, $allowedTypes)) {
//			$guiDefinition = $this->def->getGuiDefinition($contextEiMask->determineEiMask($contextEiType));
//			$eiGuiMaskDeclaration = $guiDefinition->createEiGuiMaskDeclaration($this->n2nContext, $eiGuiDeclaration, $defPropPaths,
//					$guiStructureDeclarationsRequired);
//			$eiGuiDeclaration->putEiGuiMaskDeclaration($eiGuiMaskDeclaration);
//		}
//
//		foreach ($contextEiType->getAllSubEiTypes() as $eiType) {
//			if (!$this->testIfAllowed($eiType, $creatablesOnly, $allowedTypes)) {
//				continue;
//			}
//
//			$guiDefinition = $this->def->getGuiDefinition($contextEiMask->determineEiMask($contextEiType));
//			$eiGuiMaskDeclaration = $guiDefinition->createEiGuiMaskDeclaration($this->n2nContext, $eiGuiDeclaration, $defPropPaths,
//					$guiStructureDeclarationsRequired);
//			$eiGuiDeclaration->putEiGuiMaskDeclaration($eiGuiMaskDeclaration);
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