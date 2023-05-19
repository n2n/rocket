<?php
namespace rocket\op\ei\manage\gui;

use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\DefPropPath;
use rocket\si\meta\SiDeclaration;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\security\InaccessibleEiEntryException;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\si\content\SiValueBoundary;
use rocket\op\ei\EiType;
use rocket\op\ei\manage\gui\control\GuiControlPath;
use rocket\op\ei\manage\gui\control\UnknownGuiControlException;
use rocket\si\meta\SiStyle;
use n2n\util\ex\IllegalStateException;
use n2n\l10n\N2nLocale;
use rocket\op\spec\TypePath;

class EiGuiDeclaration {
	/**
	 * @var EiMask
	 */
	private $contextEiMask;
	/**
	 * @var int
	 */
	private int $viewMode;
	/**
	 * @var EiGuiMaskDeclaration[]
	 */
	private $eiGuiMaskDeclarations = [];
	
	/**
	 * @param EiMask $eiMask
	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 */
	function __construct(EiMask $contextEiMask, int $viewMode) {
		$this->contextEiMask = $contextEiMask;
		ArgUtils::valEnum($viewMode, ViewMode::getAll());
		$this->viewMode = $viewMode;
	}
	
//	/**
//	 * @return \rocket\op\ei\mask\EiMask
//	 */
//	function getContextEiMask() {
//		return $this->contextEiMask;
//	}
	
	/**
	 * @return int
	 */
	function getViewMode(): int {
		return $this->viewMode;
	}

	function getEiGuiMaskDeclarations(): array {
		return $this->eiGuiMaskDeclarations;
	}

	function hasEiGuiMaskDeclarations(): bool {
		return !empty($this->eiGuiMaskDeclarations);
	}

	function containsEiTypePath(TypePath $eiTypePath): bool {
		return isset($this->eiGuiMaskDeclarations[(string) $eiTypePath]);
	}

	function containsEiGuiMaskDeclaration(EiGuiMaskDeclaration $eiGuiMaskDeclaration): bool {
		$eiTypePathStr = (string) $eiGuiMaskDeclaration->getEiMask()->getEiTypePath();

		return isset($this->eiGuiMaskDeclarations[$eiTypePathStr])
				&& $this->eiGuiMaskDeclarations[$eiTypePathStr] === $eiGuiMaskDeclaration;
	}
//	/**
//	 * @return EiType[]
//	 */
//	function getEiTypes() {
//		return array_map(
//				function ($arg) { return $arg->getGuiDefinition()->getEiMask()->getEiType(); },
//				$this->eiGuiMaskDeclarations);
//	}
	
	/**
	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 * @return EiGuiDeclaration
	 */
	function putEiGuiMaskDeclaration(EiGuiMaskDeclaration $eiGuiMaskDeclaration): static {
		ArgUtils::assertTrue($this->viewMode === $eiGuiMaskDeclaration->getViewMode());
		$eiType = $eiGuiMaskDeclaration->getGuiDefinition()->getEiMask()->getEiType();
		
		ArgUtils::assertTrue($eiType->isA($eiGuiMaskDeclaration->getGuiDefinition()->getEiMask()->getEiType()));
		
		$this->eiGuiMaskDeclarations[(string) $eiGuiMaskDeclaration->getEiMask()->getEiTypePath()] = $eiGuiMaskDeclaration;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	function hasMultipleEiGuiMaskDeclarations(): bool {
		return count($this->eiGuiMaskDeclarations) > 1;
	}
	
	/**
	 * @return boolean
	 */
	function hasSingleEiGuiMaskDeclaration(): bool {
		return count($this->eiGuiMaskDeclarations) === 1;
	}

	function getSingleEiGuiMaskDeclaration(): EiGuiMaskDeclaration {
		IllegalStateException::assertTrue($this->hasSingleEiGuiMaskDeclaration());

		return current($this->eiGuiMaskDeclarations);
	}
	
//	/**
//	 * @return DefPropPath[]
//	 */
//	function getDefPropPaths() {
//		$this->ensureInit();
//
//		$defPropPaths = [];
//		foreach ($this->guiStructureDeclarations as $guiStructureDeclaration) {
//			$defPropPaths = array_merge($defPropPaths, $guiStructureDeclaration->getAllDefPropPaths());
//		}
//		return $defPropPaths;
//	}

	function createSiDeclaration(N2nLocale $n2nLocale): SiDeclaration {
		$siDeclaration = new SiDeclaration(ViewMode::createSiStyle($this->viewMode));
		
		foreach ($this->eiGuiMaskDeclarations as $eiGuiMaskDeclaration) {
			$siDeclaration->addMaskDeclaration($eiGuiMaskDeclaration->createSiMaskDeclaration($n2nLocale));
		}
		
		return $siDeclaration;
	}
	

	
// 	/**
// 	 * @return SiProp[]
// 	 */
// 	private function getSiProps() {
// 		IllegalStateException::assertTrue($this->guiStructureDeclarations !== null,
// 				'EiGuiMaskDeclaration is forked.');
		
		
// 		$deter = new ContextSiFieldDeterminer();
		
// 		$siProps = [];
// 		foreach ($this->filterFieldGuiStructureDeclarations($this->guiStructureDeclarations) 
// 				as $guiStructureDeclaration) {
// 			$defPropPath = $guiStructureDeclaration->getDefPropPath();
			
// 			$siProps[] = $this->createSiProp($guiStructureDeclaration);
			
// 			$deter->reportDefPropPath($defPropPath);
// 		}
		
// 		return array_merge($deter->createContextSiProps($this->eiGuiMaskDeclaration), $siProps);
// 	}
	
	
	
	
	
// 	/**
// 	 * @param GuiStructureDeclaration $guiStructureDeclaration
// 	 * @return SiProp
// 	 */
// 	private function createSiProp(GuiStructureDeclaration $guiStructureDeclaration) {
// 		return new SiProp($guiStructureDeclaration->getDefPropPath(),
// 				$guiStructureDeclaration->getLabel(), $guiStructureDeclaration->getHelpText());
// 	}
	
	
	
	/**
	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
	 * @return GuiStructureDeclaration[]
	 */
	private function filterFieldGuiStructureDeclarations(array $guiStructureDeclarations): array {
		$filtereds = [];
		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
			if ($guiStructureDeclaration->hasDefPropPath()) {
				$filtereds[] = $guiStructureDeclaration;
				continue;
			}
			
			array_push($filtereds, ...$this->filterFieldGuiStructureDeclarations(
					$guiStructureDeclaration->getChildren()));
		}
		return $filtereds;
	}
	
// 	function appendEiGuiValueBoundary(EiFrame $eiFrame, array $eiEntries, int $treeLevel = null) {
// 		ArgUtils::valArray($eiEntries, EiEntry::class);
		
// 		$eiGuiValueBoundary = new EiGuiValueBoundary($this->contextEiMask->getEiType(), $treeLevel);
		
// 		$eiEntries = $this->catEiEntries($eiEntries);
		
// 		foreach ($this->eiGuiMaskDeclarations as $eiGuiMaskDeclaration) {
// 			$eiType = $eiGuiMaskDeclaration->getEiType();
			
// 			if (isset($eiEntries[$eiType->getId()])) {
// 				$eiGuiMaskDeclaration->applyEiGuiEntry($eiFrame, $eiGuiValueBoundary, $eiEntries[$eiType->getId()]);
// 				continue;
// 			}
			
// 			throw new \InvalidArgumentException('No EiEntry provieded for ' . $eiType);
// 		}
	
// 		$this->finalizeEiGuiValueBoundary($eiGuiValueBoundary);
		
// 		return $eiGuiValueBoundary;
// 	}

	function createEiGuiValueBoundary(EiFrame $eiFrame, array $eiEntries, bool $entryGuiControlsIncluded,
			int $treeLevel = null): EiGuiValueBoundary {
		ArgUtils::valArray($eiEntries, EiEntry::class);
		
		$eiGuiValueBoundary = new EiGuiValueBoundary($this, $treeLevel);
		
		$eiEntries = $this->catEiEntries($eiEntries);

		foreach ($this->eiGuiMaskDeclarations as $eiGuiMaskDeclaration) {
			$eiType = $eiGuiMaskDeclaration->getEiMask()->getEiType();
			$eiTypeId = $eiType->getId();

			if (isset($eiEntries[$eiTypeId])) {
				$eiGuiValueBoundary->putEiGuiEntry($eiGuiMaskDeclaration
						->createEiGuiEntry($eiFrame, $eiEntries[$eiTypeId], $entryGuiControlsIncluded));
				continue;
			}
			
			throw new \InvalidArgumentException('No EiEntry provided for ' . $eiType);
		}
		
		$this->finalizeEiGuiValueBoundary($eiGuiValueBoundary);
		
		return $eiGuiValueBoundary;
	}
	
	/**
	 * @param EiEntry[] $eiEntries
	 * @return EiEntry[]
	 */
	private function catEiEntries($eiEntries) {
		$catEiEntries = [];
		foreach ($eiEntries as $eiEntry) {
			$eiType = $eiEntry->getEiMask()->getEiType();
			if (isset($this->eiGuiMaskDeclarations[$eiType->getId()])) {
				$catEiEntries[$eiType->getId()] = $eiEntry;
				continue;
			}
			
			while (null !== ($eiType = $eiType->getSuperEiType())) {
				if (!isset($this->eiGuiMaskDeclarations[$eiType->getId()])) {
					continue;
				}
				
				if (!isset($catEiEntries[$eiType->getId()])) {
					$catEiEntries[$eiType->getId()] = $eiEntry;
				}
				
				break;
			}
		}
		return $catEiEntries;
	}

	/**
	 * @param EiFrame $eiFrame
	 * @param bool $entryGuiControlsIncluded
	 * @param int|null $treeLevel
	 * @return EiGuiValueBoundary
	 */
	function createNewEiGuiValueBoundary(EiFrame $eiFrame, bool $entryGuiControlsIncluded, int $treeLevel = null): EiGuiValueBoundary {
		$eiGuiValueBoundary = new EiGuiValueBoundary($this, $treeLevel);

		$eiGuiValueBoundary = new EiGuiValueBoundary($this, $treeLevel);

		foreach ($this->eiGuiMaskDeclarations as $eiGuiMaskDeclaration) {
			$eiGuiValueBoundary->putEiGuiEntry(
					$eiGuiMaskDeclaration->createNewEiGuiEntry($eiFrame, $entryGuiControlsIncluded));
		}
		
		$this->finalizeEiGuiValueBoundary($eiGuiValueBoundary);
		
		return $eiGuiValueBoundary;
	}
	
	/**
	 * @param EiGuiValueBoundary $eiGuiValueBoundary
	 */
	private function finalizeEiGuiValueBoundary(EiGuiValueBoundary $eiGuiValueBoundary): void {
		if ($this->hasSingleEiGuiMaskDeclaration()) {
			$eiGuiValueBoundary->selectEiGuiEntryByEiMaskId(key($this->eiGuiMaskDeclarations));
		}
	}

	
	/**
	 * @param EiFrame $eiFrame
	 * @return \rocket\si\control\SiControl[]
	 */
	function createGeneralSiControls(EiFrame $eiFrame) {
		$contextEiTypeId = $this->contextEiMask->getEiType()->getId();
		
		if (isset($this->eiGuiMaskDeclarations[$contextEiTypeId])) {
			return $this->eiGuiMaskDeclarations[$contextEiTypeId]->createGeneralSiControls($eiFrame);
		}
		
		return [];
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param GuiControlPath $guiControlPath
	 * @throws UnknownGuiControlException
	 */
	function createGeneralGuiControl(EiFrame $eiFrame, GuiControlPath $guiControlPath) {
		$contextEiTypeId = $this->contextEiMask->getEiType()->getId();
		
		if (isset($this->eiGuiMaskDeclarations[$contextEiTypeId])) {
			return $this->eiGuiMaskDeclarations[$contextEiTypeId]->createGeneralGuiControl($eiFrame, $guiControlPath);
		}
		
		throw new UnknownGuiControlException('Unknown GuiControlPath: ' . $guiControlPath);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param GuiControlPath $guiControlPath
	 * @throws UnknownGuiControlException
	 */
	function createEntryGuiControl(EiFrame $eiFrame, EiEntry $eiEntry, GuiControlPath $guiControlPath) {
		$eiTypeId = $eiEntry->getEiType()->getId();
		
		if (isset($this->eiGuiMaskDeclarations[$eiTypeId])) {
			return $this->eiGuiMaskDeclarations[$eiTypeId]->createEntryGuiControl($eiFrame, $eiEntry, $guiControlPath);
		}
		
		throw new UnknownGuiControlException('Unknown GuiControlPath: ' . $guiControlPath);
	}
}
