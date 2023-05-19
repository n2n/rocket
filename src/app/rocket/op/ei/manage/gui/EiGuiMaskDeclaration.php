<?php
namespace rocket\op\ei\manage\gui;

use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\manage\entry\EiEntry;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\op\ei\component\GuiFactory;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\manage\gui\control\GuiControlPath;
use rocket\op\ei\manage\gui\control\UnknownGuiControlException;
use rocket\op\ei\manage\gui\control\GuiControl;
use rocket\op\ei\manage\api\ApiControlCallId;
use rocket\si\content\SiEntry;
use rocket\op\ei\EiPropPath;
use rocket\si\meta\SiProp;
use rocket\si\meta\SiMask;
use n2n\l10n\N2nLocale;
use rocket\si\meta\SiMaskDeclaration;
use rocket\si\meta\SiStructureDeclaration;
use rocket\op\ei\manage\api\ApiController;
use rocket\op\ei\manage\gui\control\GuiControlMap;
use rocket\op\ei\mask\EiMask;

/**
 * @author andreas
 *
 */
class EiGuiMaskDeclaration {
	/**
	 * @var GuiStructureDeclaration[]
	 */
	private $guiStructureDeclarations;
	/**
	 * @var EiPropPath[]
	 */
	private $eiPropPaths = [];
	/**
	 * @var GuiFieldAssembler[]
	 */
	private $guiFieldAssemblers = [];
	/**
	 * @var DefPropPath[]
	 */
	private $defPropPaths = [];
	/**
	 * @var DisplayDefinition[]
	 */
	private $displayDefinitions = [];
	/**
	 * @var EiGuiListener[]
	 */
	private $eiGuiMaskDeclarationListeners = array();
	/**
	 * @var bool
	 */
	private bool $init = false;

	/**
	 * @param int $viewMode Use constants from {@see ViewMode}
	 * @param GuiDefinition $guiDefinition
	 * @param array|null $guiStructureDeclarations
	 */
	function __construct(private readonly int $viewMode, private readonly GuiDefinition $guiDefinition,
			?array $guiStructureDeclarations) {
		$this->setGuiStructureDeclarations($guiStructureDeclarations);
	}
	
//	function getEiType() {
//		return $this->guiDefinition->getEiMask()->getEiType();
//	}

	function getEiMask(): EiMask {
		return $this->guiDefinition->getEiMask();
	}

	function getViewMode(): int {
		return $this->viewMode;
	}

	function getGuiDefinition(): GuiDefinition {
		return $this->guiDefinition;
	}
	
	/**
	 * @param GuiStructureDeclaration[]|null $guiStructureDeclarations
	 */
	function setGuiStructureDeclarations(?array $guiStructureDeclarations): void {
		ArgUtils::valArray($guiStructureDeclarations, GuiStructureDeclaration::class, true);
		$this->guiStructureDeclarations = $guiStructureDeclarations;
	}
	
	/**
	 * @return GuiStructureDeclaration[]|null
	 */
	function getGuiStructureDeclarations(): ?array {
		return $this->guiStructureDeclarations;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws GuiException
	 * @return GuiFieldAssembler
	 */
	function getGuiFieldAssembler(EiPropPath $eiPropPath): GuiFieldAssembler {
		$eiPropPathStr = (string) $eiPropPath;
		if (isset($this->guiFieldAssemblers[$eiPropPathStr])) {
			return $this->guiFieldAssemblers[$eiPropPathStr];
		}
		
		throw new GuiException('Unknown GuiFieldAssembler for ' . $eiPropPath);
	}
	
	function putGuiFieldAssembler(EiPropPath $eiPropPath, GuiFieldAssembler $guiFieldAssembler): void {
		$this->ensureNotInit();
		
		$this->guiFieldAssemblers[(string) $eiPropPath] = $guiFieldAssembler;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return bool
	 */
	function containsGuiFieldAssembler(EiPropPath $eiPropPath): bool {
		return isset($this->guiFieldAssemblers[(string) $eiPropPath]);
	}
	
	/**
	 * @return EiPropPath[]
	 */
	function getEiPropPaths(): array {
		return $this->eiPropPaths;
	}
	
	function putDisplayDefintion(DefPropPath $defPropPath, DisplayDefinition $displayDefinition): void {
		$this->ensureNotInit();
		
		$eiPropPath = $defPropPath->getFirstEiPropPath();
		$this->eiPropPaths[(string) $eiPropPath] = $eiPropPath;
		
		$defPropPathStr = (string) $defPropPath;
		$this->defPropPaths[$defPropPathStr] = $defPropPath;
		$this->displayDefinitions[$defPropPathStr] = $displayDefinition;
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return bool
	 */
	function containsDisplayDefintion(DefPropPath $defPropPath): bool {
		return isset($this->displayDefinitions[(string) $defPropPath]);
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @throws UnresolvableDefPropPathException
	 * @return DisplayDefinition
	 */
	function getDisplayDefintion(DefPropPath $defPropPath): DisplayDefinition {
		$defPropPathStr = (string) $defPropPath;
		if (isset($this->displayDefinitions[$defPropPathStr])) {
			return $this->displayDefinitions[$defPropPathStr];
		}
		
		throw new UnresolvableDefPropPathException('Unknown DefPropPath for ' . $defPropPath);
	}
	
	/**
	 * @return DefPropPath[]
	 */
	function getDefPropPaths(): array {
		return $this->defPropPaths;
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return bool
	 */
	function containsDefPropPath(DefPropPath $defPropPath): bool {
		return isset($this->defPropPaths[(string) $defPropPath]);
	}

	function createSiMaskDeclaration(N2nLocale $n2nLocale): SiMaskDeclaration {
		IllegalStateException::assertTrue($this->guiStructureDeclarations !== null, 
				'EiGuiMaskDeclaration has no GuiStructureDeclarations.');

		return new SiMaskDeclaration(
				$this->createSiMask($n2nLocale),
				$this->createSiStructureDeclarations($this->guiStructureDeclarations));
	}
	
	/**
	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
	 * @return SiStructureDeclaration[]
	 */
	private function createSiStructureDeclarations(array $guiStructureDeclarations): array {
		$siStructureDeclarations = [];
		
		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
			if ($guiStructureDeclaration->hasDefPropPath()) {
				$siStructureDeclarations[] = SiStructureDeclaration::createProp(
						$guiStructureDeclaration->getSiStructureType(),
						$guiStructureDeclaration->getDefPropPath());
				continue;
			}
			
			$siStructureDeclarations[] = SiStructureDeclaration
					::createGroup($guiStructureDeclaration->getSiStructureType(), $guiStructureDeclaration->getLabel(),
							$guiStructureDeclaration->getHelpText())
					->setChildren($this->createSiStructureDeclarations($guiStructureDeclaration->getChildren()));
		}
		
		return $siStructureDeclarations;
	}
	
	/**
	 * @return SiMask
	 */
	function createSiMask(N2nLocale $n2nLocale): SiMask {
		$siMaskQualifier = $this->getGuiDefinition()->getEiMask()->createSiMaskQualifier($n2nLocale);
		return new SiMask($siMaskQualifier, $this->createSiProps($n2nLocale));
	}

	/**
	 * @param N2nLocale $n2nLocale
	 * @return SiProp[]
	 */
	private function createSiProps(N2nLocale $n2nLocale): array {
		$deter = new ContextSiFieldDeterminer();
		
		$siProps = [];
		foreach ($this->defPropPaths as $defPropPath) {
			$eiProp = $this->guiDefinition->getGuiPropWrapperByDefPropPath($defPropPath)->getEiProp();
			$eiPropNature = $eiProp->getNature();
			$label = $eiPropNature->getLabelLstr()->t($n2nLocale);
			$helpText = null;
			if (null !== ($helpTextLstr = $eiPropNature->getHelpTextLstr())) {
				$helpText = $helpTextLstr->t($n2nLocale);
			}
			
			$siProps[] = (new SiProp((string) $defPropPath, $label))->setHelpText($helpText);
			
			$deter->reportDefPropPath($defPropPath);
		}
				
		return array_merge($deter->createContextSiProps($n2nLocale, $this), $siProps);
	}

	function createEntryGuiControlsMap(EiFrame $eiFrame, EiEntry $eiEntry): GuiControlMap {
		return $this->guiDefinition->createEntryGuiControlsMap($eiFrame, $this, $eiEntry);
	}

	function createGeneralGuiControlsMap(EiFrame $eiFrame): GuiControlMap {
		return $this->guiDefinition->createGeneralGuiControlsMap($eiFrame, $this);
	}
	
	
// 	function getRootEiPropPaths() {
// 		$eiPropPaths = [];
// 		foreach ($this->getDefPropPaths() as $defPropPath) {
// 			$eiPropPath = $defPropPath->getFirstEiPropPath();
// 			$eiPropPaths[(string) $eiPropPath] = $eiPropPath;
// 		}
// 		return $eiPropPaths;
// 	}
	
// 	/**
// 	 * @param DefPropPath $defPropPath
// 	 * @throws GuiException
// 	 * @return \rocket\op\ei\manage\gui\GuiPropAssembly
// 	 */
// 	function getGuiPropAssemblyByDefPropPath(DefPropPath $defPropPath) {
// 		$defPropPathStr = (string) $defPropPath;
		
// 		if (isset($this->guiPropAssemblies[$defPropPathStr])) {
// 			return $this->guiPropAssemblies[$defPropPathStr];
// 		}
		
// 		throw new GuiException('No GuiPropAssembly for DefPropPath available: ' . $defPropPathStr);
// 	}
	
	/**
	 * @throws IllegalStateException
	 */
	function markInitialized(): void {
		if ($this->isInit()) {
			throw new IllegalStateException('EiGuiMaskDeclaration already initialized.');
		}
		
		$this->init = true;
		
		foreach ($this->eiGuiMaskDeclarationListeners as $listener) {
			$listener->onInitialized($this);
		}
	}
	
	/**
	 * @return boolean
	 */
	function isInit(): bool {
		return $this->init;
	}
	
	/**
	 * @throws IllegalStateException
	 */
	private function ensureInit(): void {
		if ($this->init) return;
		
		throw new IllegalStateException('EiGuiMaskDeclaration not yet initialized.');
	}
	
	/**
	 * @throws IllegalStateException
	 */
	private function ensureNotInit(): void {
		if (!$this->init) return;
		
		throw new IllegalStateException('EiGuiMaskDeclaration is already initialized.');
	}
	
// 	/**
// 	 * @param GuiStructureDeclaration $guiStructureDeclaration
// 	 * @return SiProp
// 	 */
// 	private function createSiProp(GuiStructureDeclaration $guiStructureDeclaration) {
// 		return new SiProp($guiStructureDeclaration->getDefPropPath(),
// 				$guiStructureDeclaration->getLabel(), $guiStructureDeclaration->getHelpText());
// 	}
	
// 	/**
// 	 * @return \rocket\si\meta\SiMaskDeclaration
// 	 */
// 	function createSiTypDeclaration() {
// 		$siMaskQualifier = $this->guiDefinition->getEiMask()->createSiMaskQualifier($this->eiFrame->getN2nContext()->getN2nLocale());
// 		$siType = new SiType($siMaskQualifier, $this->getSiProps());
		
// 		return new SiMaskDeclaration($siType, $this->createSiStructureDeclarations($this->guiStructureDeclarations)); 
// 	}
	
// 	/**
// 	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
// 	 * @return SiStructureDeclaration[]
// 	 */
// 	private function createSiStructureDeclarations($guiStructureDeclarations) {
// 		$siStructureDeclarations = [];
		
// 		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
// 			if ($guiStructureDeclaration->hasDefPropPath()) {
// 				$siStructureDeclarations[] = new SiStructureDeclaration($guiStructureDeclaration->getSiStructureType(),
// 						$guiStructureDeclaration->getDefPropPath(), $guiStructureDeclaration->getLabel(), 
// 						$guiStructureDeclaration->getHelpText());
// 				continue;
// 			}
			
// 			$siStructureDeclarations[] = new SiStructureDeclaration($guiStructureDeclaration->getSiStructureType(),
// 					null, $guiStructureDeclaration->getLabel(), $guiStructureDeclaration->getHelpText(),
// 					$this->createSiStructureDeclarations($guiStructureDeclaration->getChildren()));
// 		}
			
// 		return $siStructureDeclarations;
// 	}
	
// 	/**
// 	 * @param EiPropPath $forkEiPropPath
// 	 * @return DefPropPath[]
// 	 */
// 	function getForkedDefPropPathsByEiPropPath(EiPropPath $forkEiPropPath) {
// 		$forkDefPropPaths = [];
// 		foreach ($this->getDefPropPaths() as $defPropPath) {
// 			if ($defPropPath->getFirstEiPropPath()->equals($eiPropPath)) {
// 				continue;
// 			}
			
// 			$forkDefPropPaths[] = $defPropPath->getShifted();
// 		}
// 		return $forkDefPropPaths;
// 	}

	function createEiGuiEntry(EiFrame $eiFrame, EiEntry $eiEntry, bool $entryGuiControlsIncluded): EiGuiEntry {
		$this->ensureInit();
		
		$eiGuiEntry = GuiFactory::createEiGuiEntry($eiFrame, $this, $eiEntry, $entryGuiControlsIncluded);

		foreach ($this->eiGuiMaskDeclarationListeners as $eiGuiMaskDeclarationListener) {
			$eiGuiMaskDeclarationListener->onNewEiGuiEntry($eiGuiEntry);
		}
		
		return $eiGuiEntry;
	}

	/**
	 * @param EiFrame $eiFrame
	 * @param bool $entryGuiControlsIncluded
	 * @return EiGuiEntry
	 */
	function createNewEiGuiEntry(EiFrame $eiFrame, bool $entryGuiControlsIncluded): EiGuiEntry {
		$this->ensureInit();
		
		$eiObject = $this->getGuiDefinition()->getEiMask()->getEiType()->createNewEiObject();
		$eiEntry = $eiFrame->createEiEntry($eiObject);
		
		$eiGuiEntry = GuiFactory::createEiGuiEntry($eiFrame, $this, $eiEntry, $entryGuiControlsIncluded);

		foreach ($this->eiGuiMaskDeclarationListeners as $eiGuiMaskDeclarationListener) {
			$eiGuiMaskDeclarationListener->onNewEiGuiEntry($eiGuiEntry);
		}
		
		return $eiGuiEntry;
	}
	
//	/**
//	 * @return \rocket\si\control\SiControl[]
//	 */
//	function createSelectionSiControls(EiFrame $eiFrame): array {
//		$siControls = [];
//		foreach ($this->guiDefinition->createSelectionGuiControls($eiFrame, $this)
//				as $guiControlPathStr => $selectionGuiControl) {
//			$guiControlPath = GuiControlPath::create($guiControlPathStr);
//			$siControls[$guiControlPathStr] = $selectionGuiControl->toSiControl(
//					$eiFrame->getApiUrl($guiControlPath->getEiCmdPath(), ApiController::API_CONTROL_SECTION),
//					new ApiControlCallId($guiControlPath,
//							$this->guiDefinition->getEiMask()->getEiTypePath(),
//							$this->eiGuiDeclaration->getViewMode(), null));
//		}
//		return $siControls;
//	}
	
	/**
	 * @return \rocket\si\control\SiControl[]
	 */
	function createGeneralSiControls(EiFrame $eiFrame): array {
		$siControls = [];
		foreach ($this->guiDefinition->createGeneralGuiControls($eiFrame, $this)
				as $guiControlPathStr => $generalGuiControl) {
			$guiControlPath = GuiControlPath::create($guiControlPathStr);
			$siControls[$guiControlPathStr] = $generalGuiControl->toSiControl(
					$eiFrame->getApiUrl($guiControlPath->getEiCmdPath(), ApiController::API_CONTROL_SECTION),
					new ApiControlCallId($guiControlPath,
							$this->guiDefinition->getEiMask()->getEiTypePath(),
							$this->viewMode, null, null));
		}
		return $siControls;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param GuiControlPath $guiControlPath
	 * @return GuiControl
	 * @throws UnknownGuiControlException
	 */
	function createGeneralGuiControl(EiFrame $eiFrame, GuiControlPath $guiControlPath) {
		return $this->guiDefinition->createGeneralGuiControl($eiFrame, $this, $guiControlPath);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @param GuiControlPath $guiControlPath
	 * @return \rocket\op\ei\manage\gui\control\GuiControl
	 * @throws UnknownGuiControlException
	 */
	function createEntryGuiControl(EiFrame $eiFrame, EiEntry $eiEntry, GuiControlPath $guiControlPath) {
		return $this->guiDefinition->createEntryGuiControl($eiFrame, $this, $eiEntry, $guiControlPath);
	}
	
// 	/**
// 	 * @return \rocket\si\content\SiEntry
// 	 */
// 	function createSiEntry(EiFrame $eiFrame, EiGuiValueBoundary $eiGuiValueBoundary, bool $siControlsIncluded = true) {
// 		$eiEntry = $eiGuiValueBoundary->getEiEntry();
// 		$eiType = $eiEntry->getEiType();
// 		$siIdentifier = $eiEntry->getEiObject()->createSiEntryIdentifier();
// 		$viewMode = $this->getViewMode();
		
// 		$siValueBoundary = new SiEntry($siIdentifier, ViewMode::isReadOnly($viewMode), ViewMode::isBulky($viewMode));
// 		$siValueBoundary->putBuildup($eiType->getId(), $this->createSiEntry($eiFrame, $eiGuiValueBoundary, $siControlsIncluded));
// 		$siValueBoundary->setSelectedTypeId($eiType->getId());
		
// 		return $siValueBoundary;
// 	}

	
	
	/**
	 * @param DefPropPath $prefixDefPropPath
	 * @return DefPropPath[]
	 */
	function filterDefPropPaths(DefPropPath $prefixDefPropPath) {
		$defPropPaths = [];

		foreach ($this->defPropPaths as $defPropPathStr => $defPropPath) {
			$defPropPath = DefPropPath::create($defPropPathStr);
			if ($defPropPath->equals($prefixDefPropPath)
					|| !$defPropPath->startsWith($prefixDefPropPath, false)) {
				continue;
			}

			$defPropPaths[] = $defPropPath;
		}

		return $defPropPaths;
	}
	
	/**
	 * @param EiGuiListener $eiGuiMaskDeclarationListener
	 */
	function registerEiGuiListener(EiGuiListener $eiGuiMaskDeclarationListener) {
		$this->eiGuiMaskDeclarationListeners[spl_object_hash($eiGuiMaskDeclarationListener)] = $eiGuiMaskDeclarationListener;
	}
	
	/**
	 * @param EiGuiListener $eiGuiMaskDeclarationListener
	 */
	function unregisterEiGuiListener(EiGuiListener $eiGuiMaskDeclarationListener): void {
		unset($this->eiGuiMaskDeclarationListeners[spl_object_hash($eiGuiMaskDeclarationListener)]);
	}
}

class ContextSiFieldDeterminer {
	private $defPropPaths = [];
	private $forkDefPropPaths = [];
	private $forkedDefPropPaths = [];
	
	/**
	 * @param DefPropPath $defPropPath
	 */
	function reportDefPropPath(DefPropPath $defPropPath) {
		$defPropPathStr = (string) $defPropPath;
		
		$this->defPropPaths[$defPropPathStr] = $defPropPath;
		unset($this->forkDefPropPaths[$defPropPathStr]);
		unset($this->forkedDefPropPaths[$defPropPathStr]);
		
		$forkDefPropPath = $defPropPath;
		while ($forkDefPropPath->hasMultipleEiPropPaths()) {
			$forkDefPropPath = $forkDefPropPath->getPoped();
			$this->reportFork($forkDefPropPath, $defPropPath);
		}
	}
	
	/**
	 * @param DefPropPath $forkDefPropPath
	 * @param DefPropPath $defPropPath
	 */
	private function reportFork(DefPropPath $forkDefPropPath, DefPropPath $defPropPath) {
		$forkDefPropPathStr = (string) $forkDefPropPath;
		
		if (isset($this->defPropPaths[$forkDefPropPathStr])) {
			return;
		}
		
		if (!isset($this->forkDefPropPaths[$forkDefPropPathStr])) {
			$this->forkDefPropPaths[$forkDefPropPathStr] = [];
		}
		$this->forkedDefPropPaths[$forkDefPropPathStr][] = $defPropPath;
		$this->forkDefPropPaths[$forkDefPropPathStr] = $forkDefPropPath;
		
		if ($forkDefPropPath->hasMultipleEiPropPaths()) {
			$this->reportFork($forkDefPropPath->getPoped(), $forkDefPropPath);
		}
	}
	
	/**
	 * @return SiProp[]
	 */
	function createContextSiProps(N2nLocale $n2nLocale, EiGuiMaskDeclaration $eiGuiMaskDeclaration): array {
		
		$siProps = [];
		
		foreach ($this->forkDefPropPaths as $forkDefPropPath) {
			$eiProp = $eiGuiMaskDeclaration->getGuiDefinition()->getGuiPropWrapperByDefPropPath($forkDefPropPath)->getEiProp();
			
			$siProp = (new SiProp((string) $forkDefPropPath, $eiProp->getNature()->getLabelLstr()->t($n2nLocale)))
					->setDescendantPropIds(array_map(
							function ($defPropPath) { return (string) $defPropPath; },
							$this->forkedDefPropPaths[(string) $forkDefPropPath]));
			
			if (null !== ($helpTextLstr = $eiProp->getNature()->getHelpTextLstr())) {
				$siProp->setHelpText($helpTextLstr);
			}
			
			$siProps[] = $siProp;
		}
		
		return $siProps;
	}
}