<?php
//namespace rocket\op\ei\manage\gui;
//
//use n2n\core\container\N2nContext;
//use rocket\op\ei\EiType;
//use rocket\op\ei\mask\EiMask;
//use rocket\ui\gui\GuiValueBoundary;
//use rocket\op\ei\manage\entry\EiEntry;
//use rocket\op\ei\manage\frame\EiFrame;
//use rocket\op\spec\TypePath;
//use rocket\op\ei\UnknownEiTypeExtensionException;
//use rocket\op\ei\UnknownEiTypeException;
//
//class EiGuiEntryFactory {
//
//	function __construct(private readonly EiFrame $eiFrame) {
//	}
//
////	/**
////	 * @throws UnknownEiTypeExtensionException
////	 * @throws UnknownEiTypeException
////	 */
////	function createGuiValueBoundaryForEiMask(TypePath $eiTypePath, int $viewMode, EiEntry $eiEntry, ?int $treeLevel = null): GuiValueBoundary {
////		$eiGuiMaskDeclaration = $this->eiFrame->getContextEiEngine()->getEiMask()->determineEiMaskByEiTypePath($eiTypePath)
////				->getEiEngine()->obtainEiGuiMaskDeclaration($viewMode, null);
////
////		$guiEntry = $eiGuiMaskDeclaration->createGuiEntry($this->eiFrame, $eiEntry, true);
////
////		$guiValueBoundary = new GuiValueBoundary($treeLevel);
////		$guiValueBoundary->putGuiEntry($guiEntry);
////		return $guiValueBoundary;
////	}
//
//	function getContextEiMask(): EiMask {
//		return $this->eiFrame->getContextEiEngine()->getEiMask();
//	}
//
//	/**
//	 * @param int $viewMode
//	 * @param EiEntry[] $eiEntries
//	 * @param int|null $treeLevel
//	 * @param bool $entryControlsIncluded
//	 * @return GuiValueBoundary
//	 */
//	function createGuiValueBoundary(int $viewMode, array $eiEntries, ?int $treeLevel = null,
//			bool $entryControlsIncluded = true): GuiValueBoundary {
//		$guiValueBoundary = new GuiValueBoundary($treeLevel);
//
//		foreach ($eiEntries as $eiEntry) {
//			$guiEntry = $eiEntry->getEiMask()->getEiEngine()->obtainEiGuiMaskDeclaration($viewMode, null)
//					->createGuiEntry($this->eiFrame, $eiEntry, $entryControlsIncluded);
//
//			$guiValueBoundary->putGuiEntry($guiEntry);
//		}
//
//		return $guiValueBoundary;
//	}
//
//	/**
//	 * @param int $viewMode
//	 * @param bool $nonAbstractOnly
//	 * @param array|null $defPropPaths
//	 * @return EiGuiDeclaration
//	 */
//	function createEiGuiDeclaration(int $viewMode, bool $nonAbstractOnly, ?array $defPropPaths): EiGuiDeclaration {
//		$eiGuiDeclaration = new EiGuiDeclaration($this->eiFrame->getContextEiEngine()->getEiMask(), $viewMode);
//
//		$this->applyEiGuiMaskDeclaration($eiGuiDeclaration, $nonAbstractOnly, $defPropPaths);
//
//		if (!$eiGuiDeclaration->hasEiGuiMaskDeclarations()) {
//			throw new EiGuiBuildFailedException('Can not build forge EiGuiDeclaration based on '
//					. $this->getContextEiMask() . ' because its type is abstract.');
//		}
//
//		return $eiGuiDeclaration;
//	}
//
//	/**
//	 * @param int $viewMode
//	 * @param bool $nonAbstractsOnly
//	 * @param array|null $allowedEiTypes
//	 * @param array|null $defPropPaths
//	 * @return EiGuiDeclaration
//	 */
//	function createMultiEiGuiDeclaration(int $viewMode, bool $nonAbstractsOnly, ?array $allowedEiTypes,
//			?array $defPropPaths): EiGuiDeclaration {
//		$eiGuiDeclaration = new EiGuiDeclaration($this->getContextEiMask(), $viewMode);
//
//		$this->applyPossibleEiGuiMaskDeclarations($eiGuiDeclaration, $nonAbstractsOnly, $allowedEiTypes, $defPropPaths);
//
//		if (!$eiGuiDeclaration->hasEiGuiMaskDeclarations()) {
//			throw new EiGuiBuildFailedException('Can not build forge EiGuiDeclaration based on '
//					. $this->getContextEiMask()
//					. ' because its type and sub types are abstract or do not match the allowed EiTypes: '
//					. implode(', ', $allowedEiTypes));
//		}
//
//		return $eiGuiDeclaration;
//	}
//
//	/**
//	 * @param EiGuiDeclaration $eiGuiDeclaration
//	 * @param bool $nonAbstractOnly
//	 * @param array|null $defPropPaths
//	 * @return void
//	 */
//	private function applyEiGuiMaskDeclaration(EiGuiDeclaration $eiGuiDeclaration, bool $nonAbstractOnly, ?array $defPropPaths = null): void {
//		$contextEiMask = $this->getContextEiMask();
//
//		if (!$this->testIfAllowed($contextEiMask->getEiType(), $nonAbstractOnly, null)) {
//			return;
//		}
//
//		$guiDefinition = $contextEiMask->getEiEngine()->getEiGuiDefinition();
//		$eiGuiDeclaration->putEiGuiMaskDeclaration(
//				$guiDefinition->createEiGuiMaskDeclaration($this->eiFrame->getN2nContext(), $eiGuiDeclaration->getViewMode(), $defPropPaths));
//	}
//
//	private function applyPossibleEiGuiMaskDeclarations(EiGuiDeclaration $eiGuiDeclaration, bool $nonAbstractsOnly,
//			array $allowedEiTypes = null, ?array $defPropPaths = null): void {
//		$contextEiMask = $this->getContextEiMask();
//		$contextEiType = $contextEiMask->getEiType();
//
//		if ($this->testIfAllowed($contextEiType, $nonAbstractsOnly, $allowedEiTypes)) {
//			$eiGuiMaskDeclaration = $contextEiMask->determineEiMask($contextEiType)->getEiEngine()
//					->obtainEiGuiMaskDeclaration($eiGuiDeclaration->getViewMode(), $defPropPaths);
//			$eiGuiDeclaration->putEiGuiMaskDeclaration($eiGuiMaskDeclaration);
//		}
//
//		foreach ($contextEiType->getAllSubEiTypes() as $eiType) {
//			if (!$this->testIfAllowed($eiType, $nonAbstractsOnly, $allowedEiTypes)) {
//				continue;
//			}
//
//			$eiGuiMaskDeclaration = $contextEiMask->determineEiMask($eiType)->getEiEngine()
//					->obtainEiGuiMaskDeclaration($eiGuiDeclaration->getViewMode(), $defPropPaths);
//			$eiGuiDeclaration->putEiGuiMaskDeclaration($eiGuiMaskDeclaration);
//		}
//	}
//
//	/**
//	 * @param EiType $eiType
//	 * @param bool $nonAbstractsOnly
//	 * @param array|null $allowedEiTypes
//	 * @return bool
//	 */
//	private function testIfAllowed(EiType $eiType, bool $nonAbstractsOnly, ?array $allowedEiTypes): bool {
//		if ($nonAbstractsOnly && $eiType->isAbstract()) {
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
//}