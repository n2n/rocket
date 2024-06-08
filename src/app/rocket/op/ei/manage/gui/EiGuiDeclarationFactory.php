<?php
namespace rocket\op\ei\manage\gui;

use n2n\core\container\N2nContext;
use rocket\op\ei\EiType;
use rocket\op\ei\mask\EiMask;

class EiGuiDeclarationFactory {

	function __construct(private readonly EiMask $contextEiMask, private N2nContext $n2nContext) {
	}

	/**
	 * @param int $viewMode
	 * @param bool $nonAbstractOnly
	 * @param array|null $defPropPaths
	 * @return EiGuiDeclaration
	 */
	function createEiGuiDeclaration(int $viewMode, bool $nonAbstractOnly, ?array $defPropPaths): EiGuiDeclaration {
		$eiGuiDeclaration = new EiGuiDeclaration($this->contextEiMask, $viewMode);

		$this->applyEiGuiMaskDeclaration($eiGuiDeclaration, $nonAbstractOnly, $defPropPaths);

		if (!$eiGuiDeclaration->hasEiGuiMaskDeclarations()) {
			throw new EiGuiBuildFailedException('Can not build forge EiGuiDeclaration based on '
					. $this->contextEiMask . ' because its type is abstract.');
		}

		return $eiGuiDeclaration;
	}

	/**
	 * @param int $viewMode
	 * @param bool $nonAbstractsOnly
	 * @param array|null $allowedEiTypes
	 * @param array|null $defPropPaths
	 * @return EiGuiDeclaration
	 */
	function createMultiEiGuiDeclaration(int $viewMode, bool $nonAbstractsOnly, ?array $allowedEiTypes,
			?array $defPropPaths): EiGuiDeclaration {
		$eiGuiDeclaration = new EiGuiDeclaration($this->contextEiMask, $viewMode);
	
		$this->applyPossibleEiGuiMaskDeclarations($eiGuiDeclaration, $nonAbstractsOnly, $allowedEiTypes, $defPropPaths);
		
		if (!$eiGuiDeclaration->hasEiGuiMaskDeclarations()) {
			throw new EiGuiBuildFailedException('Can not build forge EiGuiDeclaration based on '
					. $this->contextEiMask
					. ' because its type and sub types are abstract or do not match the allowed EiTypes: '
					. implode(', ', $allowedEiTypes));
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
		$contextEiMask = $this->contextEiMask;
				
		if (!$this->testIfAllowed($contextEiMask->getEiType(), $nonAbstractOnly, null)) {
			return;
		}

		$guiDefinition = $contextEiMask->getEiEngine()->getEiGuiDefinition();
		$eiGuiDeclaration->putEiGuiMaskDeclaration(
				$guiDefinition->createEiGuiMaskDeclaration($this->n2nContext, $eiGuiDeclaration->getViewMode(), $defPropPaths));
	}

	private function applyPossibleEiGuiMaskDeclarations(EiGuiDeclaration $eiGuiDeclaration, bool $nonAbstractsOnly,
			array $allowedEiTypes = null, array $defPropPaths = null): void {
		$contextEiMask = $this->contextEiMask;
		$contextEiType = $contextEiMask->getEiType();
		
		if ($this->testIfAllowed($contextEiType, $nonAbstractsOnly, $allowedEiTypes)) {
			$eiGuiMaskDeclaration = $contextEiMask->determineEiMask($contextEiType)->getEiEngine()
					->obtainEiGuiMaskDeclaration($eiGuiDeclaration->getViewMode(), $defPropPaths);
			$eiGuiDeclaration->putEiGuiMaskDeclaration($eiGuiMaskDeclaration);
		}

		foreach ($contextEiType->getAllSubEiTypes() as $eiType) {
			if (!$this->testIfAllowed($eiType, $nonAbstractsOnly, $allowedEiTypes)) {
				continue;
			}

			$eiGuiMaskDeclaration = $contextEiMask->determineEiMask($eiType)->getEiEngine()
					->obtainEiGuiMaskDeclaration($eiGuiDeclaration->getViewMode(), $defPropPaths);
			$eiGuiDeclaration->putEiGuiMaskDeclaration($eiGuiMaskDeclaration);
		}
	}

	/**
	 * @param EiType $eiType
	 * @param bool $nonAbstractsOnly
	 * @param array|null $allowedEiTypes
	 * @return bool
	 */
	private function testIfAllowed(EiType $eiType, bool $nonAbstractsOnly, ?array $allowedEiTypes): bool {
		if ($nonAbstractsOnly && $eiType->isAbstract()) {
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