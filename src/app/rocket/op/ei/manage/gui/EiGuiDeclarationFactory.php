<?php
namespace rocket\op\ei\manage\gui;

use n2n\core\container\N2nContext;
use rocket\op\ei\EiType;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\EiLaunch;

class EiGuiDeclarationFactory {

	function __construct(private readonly EiMask $contextEiMask, private N2nContext $n2nContext) {
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return EiGuiDeclaration
	 */
	function createEiGuiDeclaration(int $viewMode, ?array $defPropPaths,
			bool $guiStructureDeclarationsRequired) {
		$eiGuiDeclaration = new EiGuiDeclaration($this->contextEiMask, $viewMode);
		
		$this->applyEiGuiMaskDeclaration($eiGuiDeclaration, false, $defPropPaths, $guiStructureDeclarationsRequired);
		
		return $eiGuiDeclaration;
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return EiGuiDeclaration
	 * @throws GuiBuildFailedException
	 */
	function createForgeEiGuiDeclaration(int $viewMode, ?array $defPropPaths,
			bool $guiStructureDeclarationsRequired) {
		$eiGuiDeclaration = new EiGuiDeclaration($this->contextEiMask, $viewMode);
		
		$this->applyEiGuiMaskDeclaration($eiGuiDeclaration, true, $defPropPaths, $guiStructureDeclarationsRequired);
		
		if (!$eiGuiDeclaration->hasEiGuiMaskDeclarations()) {
			throw new GuiBuildFailedException('Can not build forge EiGuiDeclaration based on ' . $eiGuiDeclaration->getContextEiMask() 
					. ' because its type is abstract.');
		}
		
		return $eiGuiDeclaration;
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $allowedEiTypeIds
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return EiGuiDeclaration
	 * @throws GuiBuildFailedException
	 */
	function createMultiEiGuiDeclaration(int $viewMode, ?array $allowedEiTypes,
			?array $defPropPaths, bool $guiStructureDeclarationsRequired) {
		$eiGuiDeclaration = new EiGuiDeclaration($this->contextEiMask, $viewMode);
	
		$this->applyPossibleEiGuiMaskDeclarations($eiGuiDeclaration, false, $allowedEiTypes, $defPropPaths, $guiStructureDeclarationsRequired);
		
		if (!$eiGuiDeclaration->hasEiGuiMaskDeclarations()) {
			throw new GuiBuildFailedException('Can not build forge EiGuiDeclaration based on ' . $eiGuiDeclaration->getContextEiMask()
					. ' because its type and sub types do not match the allowed EiTypes: ' . implode(', ', $allowedEiTypes));
		}
		
		return $eiGuiDeclaration;
		
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $allowedEiTypeIds
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return EiGuiDeclaration
	 *@throws GuiBuildFailedException
	 */
	function createForgeMultiEiGuiDeclaration(int $viewMode, ?array $allowedEiTypes,
			?array $defPropPaths, bool $guiStructureDeclarationsRequired) {
		$eiGuiDeclaration = new EiGuiDeclaration($this->contextEiMask, $viewMode);
		
		$this->applyPossibleEiGuiMaskDeclarations($eiGuiDeclaration, true, $allowedEiTypes, $defPropPaths, $guiStructureDeclarationsRequired);
		
		if (!$eiGuiDeclaration->hasEiGuiMaskDeclarations()) {
			throw new GuiBuildFailedException('Can not build forge EiGuiDeclaration based on ' . $eiGuiDeclaration->getContextEiMask()
					. ' because its type and sub types are either abstract or do not match the allowed EiTypes: ' 
					. implode(', ', array_map(function ($arg) { return (string) $arg; }, (array) $allowedEiTypes)));
		}
		
		return $eiGuiDeclaration;
	}

	/**
	 * @param EiGuiDeclaration $eiGuiDeclaration
	 * @param bool $nonAbstractOnly
	 * @param array|null $defPropPaths
	 * @return void
	 */
	private function applyEiGuiMaskDeclaration(EiGuiDeclaration $eiGuiDeclaration, bool $nonAbstractOnly, array $defPropPaths = null): void {
		$contextEiMask = $eiGuiDeclaration->getContextEiMask();
				
		if (!$this->testIfAllowed($contextEiMask->getEiType(), $nonAbstractOnly, null)) {
			return;
		}
		
		$guiDefinition = $contextEiMask->getEiEngine()->getGuiDefinition();
		$guiDefinition->createEiGuiMaskDeclaration($this->n2nContext, $eiGuiDeclaration, $defPropPaths);
	}

	private function applyPossibleEiGuiMaskDeclarations(EiGuiDeclaration $eiGuiDeclaration, bool $creatablesOnly, array $allowedEiTypes = null,
			array $defPropPaths = null): void {
		$contextEiMask = $eiGuiDeclaration->getContextEiMask();
		$contextEiType = $contextEiMask->getEiType();
		
		if ($this->testIfAllowed($contextEiType, $creatablesOnly, $allowedEiTypes)) {
			$guiDefinition = $contextEiMask->determineEiMask($contextEiType)->getEiEngine()->getGuiDefinition();
			$eiGuiMaskDeclaration = $guiDefinition->createEiGuiMaskDeclaration($this->n2nContext, $eiGuiDeclaration, $defPropPaths);
			$eiGuiDeclaration->putEiGuiMaskDeclaration($eiGuiMaskDeclaration);
		}

		foreach ($contextEiType->getAllSubEiTypes() as $eiType) {
			if (!$this->testIfAllowed($eiType, $creatablesOnly, $allowedEiTypes)) {
				continue;
			}
			
			$guiDefinition = $contextEiMask->determineEiMask($eiType)->getEiEngine()->getGuiDefinition();
			$eiGuiMaskDeclaration = $guiDefinition->createEiGuiMaskDeclaration($this->n2nContext, $eiGuiDeclaration, $defPropPaths);
			$eiGuiDeclaration->putEiGuiMaskDeclaration($eiGuiMaskDeclaration);
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