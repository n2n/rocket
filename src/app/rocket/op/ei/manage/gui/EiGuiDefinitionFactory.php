<?php

namespace rocket\op\ei\manage\gui;

use n2n\core\container\N2nContext;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\EiLaunch;
use rocket\op\ei\mask\model\DisplayStructure;
use rocket\ui\si\meta\SiStructureType;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\EiPropPath;
use rocket\ui\gui\GuiStructureDeclaration;

class EiGuiDefinitionFactory {

	function __construct(private EiMask $eiMask, private N2nContext $n2nContext) {

	}

	function createEiGuiDefinition(int $viewMode, ?array $defPropPaths): EiGuiDefinition {
		$eiGuiDefinition = new EiGuiDefinition($this->eiMask, $viewMode);

		$this->eiMask->getEiPropCollection()->supplyEiGuiDefinition($eiGuiDefinition, $this->n2nContext);
		$this->eiMask->getEiCmdCollection()->supplyEiGuiDefinition($eiGuiDefinition, $this->n2nContext);
//		$this->eiMask->getEiModCollection()->supplyEiGuiDefinition($eiGuiDefinition);

		if ($defPropPaths === null) {
			$guiStructureDeclarations = $this->initEiGuiMaskDeclarationFromDisplayScheme($eiGuiDefinition);
		} else {
			$guiStructureDeclarations = $this->semiAutoInitEiGuiMaskDeclaration($eiGuiDefinition, $defPropPaths);
		}

		$eiGuiDefinition->setGuiStructureDeclarations($guiStructureDeclarations);

// 		if (ViewMode::isBulky($eiGuiDeclaration->getViewMode())) {
// 			$guiStructureDeclarations = $this->groupGsds($guiStructureDeclarations);
// 		}



		return $eiGuiDefinition;
	}

//	/**
//	 * @param EiGuiMaskDeclaration $eiGuiMaskDeclaration
//	 * @param DefPropPath[] $defPropPaths
//	 * @return DefPropPath[]
//	 */
//	private function filterDefPropPaths($eiGuiMaskDeclaration, $defPropPaths) {
//		$filteredDefPropPaths = [];
//		foreach ($defPropPaths as $key => $defPropPath) {
//			if ($this->containsGuiProp($defPropPath)) {
//				$filteredDefPropPaths[$key] = $defPropPath;
//			}
//		}
//		return $filteredDefPropPaths;
//	}

	/**
	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiDefinition
	 */
	private function initEiGuiDefinition(EiGuiDefinition $eiGuiDefinition): void {
		$this->eiMask->getEiPropCollection()->supplyEiGuiDefinition($eiGuiDefinition);
		$this->eiMask->getEiCmdCollection()->supplyEiGuiDefinition($eiGuiDefinition);
		$this->eiMask->getEiModCollection()->setupEiGuiMaskDeclaration($eiGuiDefinition);

		$eiGuiDefinition->markInitialized();
	}

	/**
	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 * @return GuiStructureDeclaration
	 */
	private function initEiGuiMaskDeclarationFromDisplayScheme(EiGuiDefinition $eiGuiDefinition): array {
		$displayScheme = $this->eiMask->getDisplayScheme();

		$displayStructure = null;
		switch ($eiGuiDefinition->getViewMode()) {
			case \rocket\ui\gui\ViewMode::BULKY_READ:
				$displayStructure = $displayScheme->getDetailDisplayStructure() ?? $displayScheme->getBulkyDisplayStructure();
				break;
			case \rocket\ui\gui\ViewMode::BULKY_EDIT:
				$displayStructure = $displayScheme->getEditDisplayStructure() ?? $displayScheme->getBulkyDisplayStructure();
				break;
			case \rocket\ui\gui\ViewMode::BULKY_ADD:
				$displayStructure = $displayScheme->getAddDisplayStructure() ?? $displayScheme->getBulkyDisplayStructure();
				break;
			case \rocket\ui\gui\ViewMode::COMPACT_READ:
			case \rocket\ui\gui\ViewMode::COMPACT_EDIT:
			case \rocket\ui\gui\ViewMode::COMPACT_ADD:
				$displayStructure = $displayScheme->getOverviewDisplayStructure();
				break;
		}

		if ($displayStructure === null) {
			return $this->autoInitEiGuiMaskDeclaration($eiGuiDefinition);
		}

		return $this->nonAutoInitEiGuiMaskDeclaration($eiGuiDefinition, $displayStructure);
	}

	/**
	 * @param EiLaunch $eiLaunch ;
	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 * @param DisplayStructure $displayStructure
	 * @return GuiStructureDeclaration
	 */
	private function nonAutoInitEiGuiMaskDeclaration(N2nContext $n2nContext, $eiGuiMaskDeclaration, $displayStructure): array {
		$assemblerCache = new EiFieldAssemblerCache($n2nContext, $eiGuiMaskDeclaration, $displayStructure->getAllDefPropPaths());
		$guiStructureDeclarations = $this->assembleDisplayStructure($assemblerCache, $eiGuiMaskDeclaration, $displayStructure);
//		$this->initEiGuiDefinition($eiGuiMaskDeclaration);
		return $guiStructureDeclarations;
	}

	/**
	 * @param \rocket\op\gui\EiFieldAssemblerCache $assemblerCache
	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiMaskDeclaration
	 * @param DisplayStructure $displayStructure
	 */
	private function assembleDisplayStructure($assemblerCache, $eiGuiMaskDeclaration, $displayStructure) {
		$guiStructureDeclarations = [];

		foreach ($displayStructure->getDisplayItems() as $displayItem) {
			if ($displayItem->hasDisplayStructure()) {
				$guiStructureDeclarations[] = GuiStructureDeclaration::createGroup(
						$this->assembleDisplayStructure($assemblerCache, $eiGuiMaskDeclaration, $displayItem->getDisplayStructure()),
						$displayItem->getSiStructureType(), $displayItem->getLabel(), $displayItem->getHelpText());
				continue;
			}

			$defPropPath = $displayItem->getDefPropPath();
			$displayDefinition = $assemblerCache->assignDefPropPath($defPropPath);
			if (null === $displayDefinition) {
				continue;
			}

			$guiStructureDeclarations[] = GuiStructureDeclaration::createField($defPropPath,
					$displayItem->getSiStructureType() ?? $displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
		}

		return $guiStructureDeclarations;
	}

	private function autoInitEiGuiMaskDeclaration(EiGuiDefinition $eiGuiDefinition): array {
// 		$n2nLocale = $eiGuiMaskDeclaration->getEiFrame()->getN2nContext()->getN2nLocale();

		$guiStructureDeclarations = [];
		foreach ($eiGuiDefinition->getDefPropPaths() as $defPropPath) {
			$eiGuiPropWrapper = $eiGuiDefinition->getEiGuiPropWrapperByDefPropPath($defPropPath);

			$displayDefinition = $eiGuiPropWrapper->getDisplayDefinition();
			if (null === $displayDefinition || !$displayDefinition->isDefaultDisplayed()) {
				continue;
			}

			$guiStructureDeclarations[(string) $defPropPath] = GuiStructureDeclaration
					::createField($defPropPath, $displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
		}

		return $guiStructureDeclarations;

//		foreach ($this->eiGuiPropWrappers as $eiGuiPropWrapper) {
//			$eiPropPath = $eiGuiPropWrapper->getEiPropPath();
//			$eiGuiPropSetup = $eiGuiPropWrapper->buildGuiPropSetup($this->n2nContext, $eiGuiDefinition, null);
//
//			if ($eiGuiPropSetup === null) {
//				continue;
//			}
//
//			$eiGuiDefinition->putEiGuiField($eiPropPath, $eiGuiPropSetup->getEiGuiField());
//
//			$defPropPath = new DefPropPath([$eiPropPath]);
//
//			$displayDefinition = $eiGuiPropSetup->getDisplayDefinition();
//			if (null !== $displayDefinition && $displayDefinition->isDefaultDisplayed()) {
//				$eiGuiDefinition->putDisplayDefintion($defPropPath, $displayDefinition);
//				$guiStructureDeclarations[(string) $defPropPath] = \rocket\ui\gui\GuiStructureDeclaration
//						::createField($defPropPath, $displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
//			}
//
//			foreach ($eiGuiPropWrapper->getForkedDefPropPaths() as $forkedDefPropPath) {
//				$absDefPropPath = $defPropPath->ext($forkedDefPropPath);
//				$displayDefinition = $eiGuiPropSetup->getForkedDisplayDefinition($forkedDefPropPath);
//
//				if ($displayDefinition === null/* || !$displayDefinition->isDefaultDisplayed()*/) {
//					continue;
//				}
//				$eiGuiDefinition->putDisplayDefintion($absDefPropPath, $displayDefinition);
//
//				$guiStructureDeclarations[(string) $absDefPropPath] = \rocket\ui\gui\GuiStructureDeclaration
//						::createField($absDefPropPath, $displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
//			}
//		}
//
//		$this->initEiGuiMaskDeclaration($eiGuiDefinition);

		return $guiStructureDeclarations;
	}

}




class EiFieldAssemblerCache {
	private $eiGuiDefinition;
	private $displayStructure;
	/**
	 * @var DefPropPath[]
	 */
	private $possibleDefPropPaths = [];
	/**
	 * @var DefPropPath[]
	 */
	private $defPropPaths = [];
	/**
	 * @var EiGuiPropSetup[]
	 */
	private $eiGuiPropSetups = [];

	/**
	 * @param N2nContext $n2nContext
	 * @param \rocket\ui\gui\EiGuiMaskDeclaration $eiGuiDefinition
	 * @param array $possibleDefPropPaths
	 */
	function __construct(private N2nContext $n2nContext, EiGuiDefinition $eiGuiDefinition, array $possibleDefPropPaths) {
		$this->eiGuiDefinition = $eiGuiDefinition;
		$this->possibleDefPropPaths = $possibleDefPropPaths;
	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @return EiGuiPropSetup|null
	 */
	private function assemble(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;

		if (array_key_exists($eiPropPathStr, $this->eiGuiPropSetups)) {
			return $this->eiGuiPropSetups[$eiPropPathStr];
		}

		$guiDefinition = $this->eiGuiDefinition->getEiGuiDefinition();

		if (!$guiDefinition->containsEiPropPath($eiPropPath)) {
			$this->eiGuiPropSetups[$eiPropPathStr] = null;
			return null;
		}

		$eiGuiPropWrapper = $this->eiGuiDefinition->getEiGuiDefinition()->getGuiPropWrapper($eiPropPath);
		$eiGuiPropSetup = $eiGuiPropWrapper->buildGuiPropSetup($this->n2nContext, $this->eiGuiDefinition,
				$this->filterForkedDefPropPaths($eiPropPath));
		$this->eiGuiDefinition->putEiGuiField($eiPropPath, $eiGuiPropSetup->getEiGuiField());
		$this->eiGuiPropSetups[$eiPropPathStr] = $eiGuiPropSetup;

		return $eiGuiPropSetup;
	}

	/**
	 * @param DefPropPath $defPropPath
	 * @return DisplayDefinition|null
	 */
	function assignDefPropPath(DefPropPath $defPropPath) {
		$eiGuiPropSetup = $this->assemble($defPropPath->getFirstEiPropPath());

		if ($eiGuiPropSetup === null) {
			return null;
		}

		$displayDefinition = null;
		if (!$defPropPath->hasMultipleEiPropPaths()) {
			$displayDefinition = $eiGuiPropSetup->getDisplayDefinition();
		} else {
			$displayDefinition = $eiGuiPropSetup->getForkedDisplayDefinition($defPropPath->getShifted());
		}

		if ($displayDefinition !== null) {
			$this->eiGuiDefinition->putDisplayDefintion($defPropPath, $displayDefinition);
		}

		return $displayDefinition;
	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @return DefPropPath[]
	 */
	private function filterForkedDefPropPaths($eiPropPath) {
		$forkedDefPropPaths = [];
		foreach ($this->possibleDefPropPaths as $possibleDefPropPath) {
			if ($possibleDefPropPath->hasMultipleEiPropPaths()
					&& $possibleDefPropPath->getFirstEiPropPath()->equals($eiPropPath)) {
				$forkedDefPropPaths[] = $possibleDefPropPath->getShifted();
			}
		}
		return $forkedDefPropPaths;
	}

	/**
	 * @return DefPropPath[]
	 */
	function getDefPropPaths() {
		return $this->defPropPaths;
	}
}