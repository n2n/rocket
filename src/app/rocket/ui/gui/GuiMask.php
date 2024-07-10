<?php
namespace rocket\ui\gui;

use rocket\op\ei\manage\frame\EiFrame;
use rocket\op\ei\manage\entry\EiEntry;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\op\ei\manage\DefPropPath;
use rocket\ui\gui\control\GuiControlPath;
use rocket\ui\gui\control\UnknownGuiControlException;
use rocket\ui\gui\control\GuiControl;
use rocket\op\ei\manage\api\ApiControlCallId;
use rocket\ui\si\meta\SiMask;
use n2n\l10n\N2nLocale;
use rocket\ui\si\meta\SiStructureDeclaration;
use rocket\op\ei\manage\api\ApiController;
use rocket\ui\si\meta\SiMaskQualifier;
use rocket\ui\gui\control\GuiControlMap;
use rocket\ui\gui\field\GuiFieldPath;

/**
 * @author andreas
 *
 */
class GuiMask {
	private SiMask $siMask;
	/**
	 * @var GuiStructureDeclaration[]
	 */
	private ?array $guiStructureDeclarations = null;

//	private array $defPropPaths = [];
//	/**
//	 * @var GuiProp[]
//	 */
//	private array $guiProps = [];
//	/**
//	 * @var EiGuiListener[]
//	 */
//	private array $eiGuiMaskDeclarationListeners = array();


	/**
	 * @param SiMaskQualifier $siMaskQualifier
	 * @param GuiStructureDeclaration[]|null $guiStructureDeclarations
	 */
	function __construct(private readonly SiMaskQualifier $siMaskQualifier, ?array $guiStructureDeclarations) {
		$this->siMask = new SiMask($siMaskQualifier);
		$this->setGuiStructureDeclarations($guiStructureDeclarations);
	}
	
	/**
	 * @param GuiStructureDeclaration[]|null $guiStructureDeclarations
	 */
	function setGuiStructureDeclarations(?array $guiStructureDeclarations): void {
		ArgUtils::valArray($guiStructureDeclarations, GuiStructureDeclaration::class, true);
//		$this->guiStructureDeclarations = $guiStructureDeclarations;
		$this->siMask->setStructureDeclarations($this->createSiStructureDeclarations($guiStructureDeclarations));
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
	
//	/**
//	 * @return GuiStructureDeclaration[]|null
//	 */
//	function getGuiStructureDeclarations(): ?array {
//		return $this->guiStructureDeclarations;
//	}
	
	function putGuiProp(GuiFieldPath $guiFieldPath, GuiProp $guiProp): void {
		$this->ensureNotInit();

//		$this->guiProps[(string) $guiFieldPath] = $guiProp;
		$this->siMask->putProp((string) $guiFieldPath, $guiProp->getSiProp());
	}

	function putGuiControl(GuiControlPath $guiControlPath, GuiControl $guiControl): void {
		$this->ensureNotInit();

		$this->siMask->putControl((string) $guiControlPath, $guiControl->getSiControl());
	}

//	/**
//	 * @param GuiFieldPath $guiFieldPath
//	 * @return bool
//	 */
//	function containsGuiFieldPath(GuiFieldPath $guiFieldPath): bool {
//		return isset($this->guiProps[(string) $guiFieldPath]);
//	}
//
//	function getGuiProp(GuiFieldPath $guiFieldPath): GuiProp {
//		$guiFieldPathStr = (string) $guiFieldPath;
//		if (isset($this->guiProps[$guiFieldPathStr])) {
//			return $this->guiProps[$guiFieldPathStr];
//		}
//
//		throw new UnresolvableDefPropPathExceptionEi('Unknown GuiFieldPath for ' . $guiFieldPath);
//	}
	
//	/**
//	 * @return DefPropPath[]
//	 */
//	function getDefPropPaths(): array {
//		return $this->defPropPaths;
//	}
	
//	/**
//	 * @param DefPropPath $guiFieldPath
//	 * @return bool
//	 */
//	function containsGuiPath(GuiFieldPath $guiFieldPath): bool {
//		return isset($this->guiProps[(string) $guiFieldPath]);
//	}

	function getSiMask(): SiMask {
		IllegalStateException::assertTrue($this->siMask->getStructureDeclarations() !== null,
				'EiGuiMaskDeclaration has no GuiStructureDeclarations.');

		return $this->siMask;
	}
	
//	/**
//	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
//	 * @return SiStructureDeclaration[]
//	 */
//	private function createSiStructureDeclarations(array $guiStructureDeclarations): array {
//		$siStructureDeclarations = [];
//
//		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
//			if ($guiStructureDeclaration->hasDefPropPath()) {
//				$siStructureDeclarations[] = SiStructureDeclaration::createProp(
//						$guiStructureDeclaration->getSiStructureType(),
//						$guiStructureDeclaration->getDefPropPath());
//				continue;
//			}
//
//			$siStructureDeclarations[] = SiStructureDeclaration
//					::createGroup($guiStructureDeclaration->getSiStructureType(), $guiStructureDeclaration->getLabel(),
//							$guiStructureDeclaration->getHelpText())
//					->setChildren($this->createSiStructureDeclarations($guiStructureDeclaration->getChildren()));
//		}
//
//		return $siStructureDeclarations;
//	}
	
	/**
	 * @return SiMask
	 */
	function createSiMask(N2nLocale $n2nLocale): SiMask {
		$siMaskQualifier = $this->getEiGuiDefinition()->getEiMask()->createSiMaskQualifier($n2nLocale);
		return new SiMask($siMaskQualifier, $this->createSiProps($n2nLocale));
	}

//	/**
//	 * @param N2nLocale $n2nLocale
//	 * @return SiProp[]
//	 */
//	private function createSiProps(N2nLocale $n2nLocale): array {
//		$deter = new ContextSiFieldDeterminer();
//
//		$siProps = [];
//		foreach ($this->defPropPaths as $defPropPath) {
//			$guiProp = $this->getGuiPropByDefPropPath($defPropPath);
//			$label = $eiPropNature->getLabelLstr()->t($n2nLocale);
//			$helpText = null;
//			if (null !== ($helpTextLstr = $guiProp->getHelpTextLstr())) {
//				$helpText = $helpTextLstr->t($n2nLocale);
//			}
//
//			$siProps[] = (new SiProp((string) $defPropPath, $label))->setHelpText($helpText);
//
//			$deter->reportDefPropPath($defPropPath);
//		}
//
//		return array_merge($deter->createContextSiProps($n2nLocale, $this), $siProps);
//	}

	function createEntryGuiControlsMap(EiFrame $eiFrame, EiEntry $eiEntry): GuiControlMap {
		return $this->guiDefinition->createEntryGuiControlsMap($eiFrame, $this, $eiEntry);
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
//		if ($this->init) return;
//
//		throw new IllegalStateException('EiGuiMaskDeclaration not yet initialized.');
	}
	
	/**
	 * @throws IllegalStateException
	 */
	private function ensureNotInit(): void {
//		if (!$this->init) return;
//
//		throw new IllegalStateException('EiGuiMaskDeclaration is already initialized.');
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
// 	 * @return \rocket\si\meta\SiMask
// 	 */
// 	function createSiTypDeclaration() {
// 		$siMaskQualifier = $this->guiDefinition->getEiMask()->createSiMaskQualifier($this->eiFrame->getN2nContext()->getN2nLocale());
// 		$siType = new SiType($siMaskQualifier, $this->getSiProps());
		
// 		return new SiMask($siType, $this->createSiStructureDeclarations($this->guiStructureDeclarations)); 
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

//	function createEiGuiEntry(EiFrame $eiFrame, EiEntry $eiEntry, bool $entryGuiControlsIncluded): GuiEntry {
//		$this->ensureInit();
//
//		$eiGuiEntry = EiGuiEntryFactory::createGuiEntry($eiFrame, $this, $eiEntry, $entryGuiControlsIncluded);
//
//		foreach ($this->eiGuiMaskDeclarationListeners as $eiGuiMaskDeclarationListener) {
//			$eiGuiMaskDeclarationListener->onNewEiGuiEntry($eiGuiEntry);
//		}
//
//		return $eiGuiEntry;
//	}

//	/**
//	 * @param EiFrame $eiFrame
//	 * @param bool $entryGuiControlsIncluded
//	 * @return GuiEntry
//	 */
//	function createNewEiGuiEntry(EiFrame $eiFrame, bool $entryGuiControlsIncluded): GuiEntry {
//		$this->ensureInit();
//
//		$eiObject = $this->getEiGuiDefinition()->getEiMask()->getEiType()->createNewEiObject();
//		$eiEntry = $eiFrame->createEiEntry($eiObject);
//
//		$eiGuiEntry = EiGuiEntryFactory::createGuiEntry($eiFrame, $this, $eiEntry, $entryGuiControlsIncluded);
//
//		foreach ($this->eiGuiMaskDeclarationListeners as $eiGuiMaskDeclarationListener) {
//			$eiGuiMaskDeclarationListener->onNewEiGuiEntry($eiGuiEntry);
//		}
//
//		return $eiGuiEntry;
//	}
	
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
	 * @return \rocket\ui\si\control\SiControl[]
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
	 * @return \rocket\ui\gui\control\GuiControl
	 * @throws UnknownGuiControlException
	 */
	function createEntryGuiControl(EiFrame $eiFrame, EiEntry $eiEntry, GuiControlPath $guiControlPath) {
		return $this->guiDefinition->createEntryGuiControl($eiFrame, $this, $eiEntry, $guiControlPath);
	}
	
	
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

}
