<?php
namespace rocket\ei\manage\gui;

use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\entry\EiEntry;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\GuiFactory;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\gui\control\UnknownGuiControlException;
use rocket\ei\manage\gui\control\GeneralGuiControl;
use rocket\ei\manage\api\ApiControlCallId;
use rocket\si\content\SiEntry;
use rocket\si\content\SiEntryBuildup;
use rocket\ei\EiPropPath;
use rocket\si\meta\SiProp;
use rocket\si\meta\SiType;
use n2n\l10n\N2nLocale;

/**
 * @author andreas
 *
 */
class EiGuiFrame {
	/**
	 * @var GuiDefinition
	 */
	private $guiDefinition;
// 	/**
// 	 * @var GuiDefinition
// 	 */
// 	private $guiDefinition;
	/**
	 * @var int
	 */
	private $viewMode;
	/**
	 * @var EiPropPath[]
	 */
	private $eiPropPaths = [];
	/**
	 * @var GuiFieldAssembler[]
	 */
	private $guiFieldAssemblers = [];
	/**
	 * @var GuiPropPath[]
	 */
	private $guiPropPaths = [];
	/**
	 * @var DisplayDefinition[]
	 */
	private $displayDefinitions = [];
	/**
	 * @var EiGuiListener[]
	 */
	private $eiGuiFrameListeners = array();
	/**
	 * @var bool
	 */
	private $init = false;
	
	/**
	 * @param EiFrame $eiFrame
	 * @param GuiDefinition $guiDefinition
	 * @param int $viewMode Use constants from {@see ViewMode}
	 */
	function __construct(GuiDefinition $guiDefinition, int $viewMode) {
		$this->guiDefinition = $guiDefinition;
		ArgUtils::valEnum($viewMode, ViewMode::getAll());
		$this->viewMode = $viewMode;
	}
	
// 	/**
// 	 * @return \rocket\ei\manage\frame\EiFrame
// 	 */
// 	function getEiFrame() {
// 		return $this->eiFrame;
// 	}
	
	/**
	 * @return \rocket\ei\manage\gui\GuiDefinition
	 */
	function getGuiDefinition() {
		return $this->guiDefinition;
	}
	
	/**
	 * @return int
	 */
	function getViewMode() {
		return $this->viewMode;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws GuiException
	 * @return GuiFieldAssembler
	 */
	function getGuiFieldAssembler(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		if (isset($this->guiFieldAssemblers[$eiPropPathStr])) {
			return $this->guiFieldAssemblers[$eiPropPathStr];
		}
		
		throw new GuiException('Unknown GuiFieldAssembler for ' . $eiPropPath);
	}
	
	function putGuiFieldAssembler(EiPropPath $eiPropPath, GuiFieldAssembler $guiFieldAssembler) {
		$this->ensureNotInit();
		
		$this->guiFieldAssemblers[(string) $eiPropPath] = $guiFieldAssembler;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return bool
	 */
	function containsGuiFieldAssembler(EiPropPath $eiPropPath) {
		return isset($this->guiFieldAssemblers[(string) $eiPropPath]);
	}
	
	/**
	 * @return \rocket\ei\EiPropPath[]
	 */
	function getEiPropPaths() {
		return $this->eiPropPaths;
	}
	
	function putDisplayDefintion(GuiPropPath $guiPropPath, DisplayDefinition $displayDefinition) {
		$this->ensureNotInit();

		$eiPropPath = $guiPropPath->getFirstEiPropPath();
		$this->eiPropPaths[(string) $eiPropPath] = $eiPropPath;
		
		$guiPropPathStr = (string) $guiPropPath;
		$this->guiPropPaths[$guiPropPathStr] = $guiPropPath;
		$this->displayDefinitions[$guiPropPathStr] = $displayDefinition;
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return bool
	 */
	function containsDisplayDefintion(GuiPropPath $guiPropPath) {
		return isset($this->displayDefinitions[(string) $guiPropPath]);
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @throws UnresolvableGuiPropPathException
	 * @return DisplayDefinition
	 */
	function getDisplayDefintion(GuiPropPath $guiPropPath) {
		$guiPropPathStr = (string) $guiPropPath;
		if (isset($this->displayDefinitions[$guiPropPathStr])) {
			return $this->displayDefinitions[$guiPropPathStr];
		}
		
		throw new UnresolvableGuiPropPathException('Unknown GuiPropPath for ' . $guiPropPath);
	}
	
	/**
	 * @return GuiPropPath[]
	 */
	function getGuiPropPaths() {
		return $this->guiPropPaths;
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return bool
	 */
	function containsGuiPropPath(GuiPropPath $guiPropPath) {
		return isset($this->guiPropPaths[(string) $guiPropPath]);
	}
	
	
	/**
	 * @return \rocket\si\meta\SiType
	 */
	function createSiType(N2nLocale $n2nLocale) {
		$siTypeQualifier = $this->getGuiDefinition()->getEiMask()
				->createSiTypeQualifier($n2nLocale);
		return new SiType($siTypeQualifier, $this->createSiProps($n2nLocale));
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @return SiProp[]
	 */
	private function createSiProps(N2nLocale $n2nLocale) {
		$deter = new ContextSiFieldDeterminer();
		
		$siProps = [];
		foreach ($this->guiPropPaths as $guiPropPath) {
			$eiProp = $this->guiDefinition->getGuiPropWrapperByGuiPropPath($guiPropPath)->getEiProp();
			$label = $eiProp->getLabelLstr()->t($n2nLocale);
			$helpText = null;
			if (null !== ($helpTextLstr = $eiProp->getHelpTextLstr())) {
				$helpText = $helpTextLstr->t($n2nLocale);
			}
			
			$siProps[] = (new SiProp((string) $guiPropPath, $label))->setHelpText($helpText);
			
			$deter->reportGuiPropPath($guiPropPath);
		}
				
		return array_merge($deter->createContextSiProps($n2nLocale, $this), $siProps);
	}
	
	
// 	function getRootEiPropPaths() {
// 		$eiPropPaths = [];
// 		foreach ($this->getGuiPropPaths() as $guiPropPath) {
// 			$eiPropPath = $guiPropPath->getFirstEiPropPath();
// 			$eiPropPaths[(string) $eiPropPath] = $eiPropPath;
// 		}
// 		return $eiPropPaths;
// 	}
	
// 	/**
// 	 * @param GuiPropPath $guiPropPath
// 	 * @throws GuiException
// 	 * @return \rocket\ei\manage\gui\GuiPropAssembly
// 	 */
// 	function getGuiPropAssemblyByGuiPropPath(GuiPropPath $guiPropPath) {
// 		$guiPropPathStr = (string) $guiPropPath;
		
// 		if (isset($this->guiPropAssemblies[$guiPropPathStr])) {
// 			return $this->guiPropAssemblies[$guiPropPathStr];
// 		}
		
// 		throw new GuiException('No GuiPropAssembly for GuiPropPath available: ' . $guiPropPathStr);
// 	}
	
	/**
	 * @throws IllegalStateException
	 */
	function markInitialized() {
		if ($this->isInit()) {
			throw new IllegalStateException('EiGuiFrame already initialized.');
		}
		
		$this->init = true;
		
		foreach ($this->eiGuiFrameListeners as $listener) {
			$listener->onInitialized($this);
		}
	}
	
	/**
	 * @return boolean
	 */
	function isInit() {
		return $this->init;
	}
	
	/**
	 * @throws IllegalStateException
	 */
	private function ensureInit() {
		if ($this->init) return;
		
		throw new IllegalStateException('EiGuiFrame not yet initialized.');
	}
	
	/**
	 * @throws IllegalStateException
	 */
	private function ensureNotInit() {
		if (!$this->init) return;
		
		throw new IllegalStateException('EiGuiFrame is already initialized.');
	}
	
// 	/**
// 	 * @param GuiStructureDeclaration $guiStructureDeclaration
// 	 * @return SiProp
// 	 */
// 	private function createSiProp(GuiStructureDeclaration $guiStructureDeclaration) {
// 		return new SiProp($guiStructureDeclaration->getGuiPropPath(),
// 				$guiStructureDeclaration->getLabel(), $guiStructureDeclaration->getHelpText());
// 	}
	
// 	/**
// 	 * @return \rocket\si\meta\SiTypeDeclaration
// 	 */
// 	function createSiTypDeclaration() {
// 		$siTypeQualifier = $this->guiDefinition->getEiMask()->createSiTypeQualifier($this->eiFrame->getN2nContext()->getN2nLocale());
// 		$siType = new SiType($siTypeQualifier, $this->getSiProps());
		
// 		return new SiTypeDeclaration($siType, $this->createSiStructureDeclarations($this->guiStructureDeclarations)); 
// 	}
	
// 	/**
// 	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
// 	 * @return SiStructureDeclaration[]
// 	 */
// 	private function createSiStructureDeclarations($guiStructureDeclarations) {
// 		$siStructureDeclarations = [];
		
// 		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
// 			if ($guiStructureDeclaration->hasGuiPropPath()) {
// 				$siStructureDeclarations[] = new SiStructureDeclaration($guiStructureDeclaration->getSiStructureType(),
// 						$guiStructureDeclaration->getGuiPropPath(), $guiStructureDeclaration->getLabel(), 
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
// 	 * @return GuiPropPath[]
// 	 */
// 	function getForkedGuiPropPathsByEiPropPath(EiPropPath $forkEiPropPath) {
// 		$forkGuiPropPaths = [];
// 		foreach ($this->getGuiPropPaths() as $guiPropPath) {
// 			if ($guiPropPath->getFirstEiPropPath()->equals($eiPropPath)) {
// 				continue;
// 			}
			
// 			$forkGuiPropPaths[] = $guiPropPath->getShifted();
// 		}
// 		return $forkGuiPropPaths;
// 	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @param bool $makeEditable
	 * @param int $treeLevel
	 * @param bool $append
	 * @return EiEntryGui
	 */
	function createEiEntryGui(EiFrame $eiFrame, EiEntry $eiEntry, int $treeLevel = null): EiEntryGui {
		$this->ensureInit();
		
		$eiEntryGui = GuiFactory::createEiEntryGui($eiFrame, $this, $eiEntry, $this->getGuiPropPaths(), $treeLevel);
		
		foreach ($this->eiGuiFrameListeners as $eiGuiFrameListener) {
			$eiGuiFrameListener->onNewEiEntryGui($eiEntryGui);
		}
		
		return $eiEntryGui;
	}
	
	/**
	 * @return \rocket\si\control\SiControl[]
	 */
	function createSelectionSiControls(EiFrame $eiFrame) {
		$siControls = [];
		foreach ($this->guiDefinition->createSelectionGuiControls($eiFrame, $this)
				as $guiControlPathStr => $selectionGuiControl) {
			$siControls[$guiControlPathStr] = $selectionGuiControl->toSiControl(
					new ApiControlCallId(GuiControlPath::create($guiControlPathStr), $this->eiMask->getEiTypePath(),
							$this->viewMode, null));
		}
		return $siControls;
	}
	
	/**
	 * @return \rocket\si\control\SiControl[]
	 */
	function createGeneralSiControls(EiFrame $eiFrame) {
		$siControls = [];
		foreach ($this->guiDefinition->createGeneralGuiControls($eiFrame, $this)
				as $guiControlPathStr => $generalGuiControl) {
			$siControls[$guiControlPathStr] = $generalGuiControl->toSiControl(
					new ApiControlCallId(GuiControlPath::create($guiControlPathStr), 
							$this->guiDefinition->getEiMask()->getEiTypePath(),
							$this->viewMode, null, null));
		}
		return $siControls;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param GuiControlPath $guiControlPath
	 * @return GeneralGuiControl
	 * @throws UnknownGuiControlException
	 */
	function createGeneralGuiControl(EiFrame $eiFrame, GuiControlPath $guiControlPath) {
		return $this->guiDefinition->createGeneralGuiControl($eiFrame, $this, $guiControlPath);
	}
	
	/**
	 * @return \rocket\si\content\SiEntry
	 */
	function createSiEntry(EiFrame $eiFrame, EiEntryGui $eiEntryGui, bool $siControlsIncluded = true) {
		$eiEntry = $eiEntryGui->getEiEntry();
		$eiType = $eiEntry->getEiType();
		$siIdentifier = $eiEntry->getEiObject()->createSiEntryIdentifier();
		$viewMode = $this->getViewMode();
		
		$siEntry = new SiEntry($siIdentifier, ViewMode::isReadOnly($viewMode), ViewMode::isBulky($viewMode));
		$siEntry->putBuildup($eiType->getId(), $this->createSiEntryBuildup($eiFrame, $eiEntryGui, $siControlsIncluded));
		$siEntry->setSelectedTypeId($eiType->getId());
		
		return $siEntry;
	}
	
	/**
	 * @return SiEntryBuildup
	 */
	function createSiEntryBuildup(EiFrame $eiFrame, EiEntryGui $eiEntryGui, bool $siControlsIncluded = true) {
		$eiEntry = $eiEntryGui->getEiEntry();
		
		$n2nLocale = $eiFrame->getN2nContext()->getN2nLocale();
		$typeId = $eiEntry->getEiMask()->getEiType()->getId();
		$idName = null;
		if (!$eiEntry->isNew()) {
			$deterIdNameDefinition = $eiFrame->getManageState()->getDef()
					->getIdNameDefinition($eiEntry->getEiMask());
			$idName = $deterIdNameDefinition->createIdentityString($eiEntry->getEiObject(), 
					$eiFrame->getN2nContext(), $n2nLocale);
		}
		
		$siEntryBuildup = new SiEntryBuildup($typeId, $idName);
		
		foreach ($eiEntryGui->getGuiFieldMap()->getAllGuiFields() as $guiPropPathStr => $guiField) {
			if (null !== ($siField = $guiField->getSiField())) {
				$siEntryBuildup->putField($guiPropPathStr, $siField);
			}
			
// 			$siEntry->putContextFields($guiPropPathStr, $guiField->getContextSiFields());
		}
		
		if (!$siControlsIncluded) {
			return $siEntryBuildup;
		}
		
		foreach ($this->guiDefinition->createEntryGuiControls($eiFrame, $this, $eiEntry)
				as $guiControlPathStr => $entryGuiControl) {
			$siEntryBuildup->putControl($guiControlPathStr, $entryGuiControl->toSiControl(
					new ApiControlCallId(GuiControlPath::create($guiControlPathStr),
							$this->guiDefinition->getEiMask()->getEiTypePath(),
							$this->viewMode, $eiEntry->getPid(),
							($eiEntry->isNew() ? $eiEntry->getEiType()->getId() : null))));
		}
		
		return $siEntryBuildup;
	}
	
	
	
	/**
	 * @param GuiPropPath $prefixGuiPropPath
	 * @return \rocket\ei\manage\gui\field\GuiPropPath[]
	 */
	function filterGuiPropPaths(GuiPropPath $prefixGuiPropPath) {
		$guiPropPaths = [];

		foreach ($this->guiPropPaths as $guiPropPathStr => $guiPropPath) {
			$guiPropPath = GuiPropPath::create($guiPropPathStr);
			if ($guiPropPath->equals($prefixGuiPropPath)
					|| !$guiPropPath->startsWith($prefixGuiPropPath, false)) {
				continue;
			}

			$guiPropPaths[] = $guiPropPath;
		}

		return $guiPropPaths;
	}
	
	/**
	 * @param EiGuiListener $eiGuiFrameListener
	 */
	function registerEiGuiListener(EiGuiListener $eiGuiFrameListener) {
		$this->eiGuiFrameListeners[spl_object_hash($eiGuiFrameListener)] = $eiGuiFrameListener;
	}
	
	/**
	 * @param EiGuiListener $eiGuiFrameListener
	 */
	function unregisterEiGuiListener(EiGuiListener $eiGuiFrameListener) {
		unset($this->eiGuiFrameListeners[spl_object_hash($eiGuiFrameListener)]);
	}
}

class ContextSiFieldDeterminer {
	private $guiPropPaths = [];
	private $forkGuiPropPaths = [];
	private $forkedGuiPropPaths = [];
	
	/**
	 * @param GuiPropPath $guiPropPath
	 */
	function reportGuiPropPath(GuiPropPath $guiPropPath) {
		$guiPropPathStr = (string) $guiPropPath;
		
		$this->guiPropPaths[$guiPropPathStr] = $guiPropPath;
		unset($this->forkGuiPropPaths[$guiPropPathStr]);
		unset($this->forkedGuiPropPaths[$guiPropPathStr]);
		
		$forkGuiPropPath = $guiPropPath;
		while ($forkGuiPropPath->hasMultipleEiPropPaths()) {
			$forkGuiPropPath = $forkGuiPropPath->getPoped();
			$this->reportFork($forkGuiPropPath, $guiPropPath);
		}
	}
	
	/**
	 * @param GuiPropPath $forkGuiPropPath
	 * @param GuiPropPath $guiPropPath
	 */
	private function reportFork(GuiPropPath $forkGuiPropPath, GuiPropPath $guiPropPath) {
		$forkGuiPropPathStr = (string) $forkGuiPropPath;
		
		if (isset($this->guiPropPaths[$forkGuiPropPathStr])) {
			return;
		}
		
		if (!isset($this->forkGuiPropPaths[$forkGuiPropPathStr])) {
			$this->forkGuiPropPaths[$forkGuiPropPathStr] = [];
		}
		$this->forkedGuiPropPaths[$forkGuiPropPathStr][] = $guiPropPath;
		$this->forkGuiPropPaths[$forkGuiPropPathStr] = $forkGuiPropPath;
		
		if ($forkGuiPropPath->hasMultipleEiPropPaths()) {
			$this->reportFork($forkGuiPropPath->getPoped(), $forkGuiPropPath);
		}
	}
	
	/**
	 * @return SiProp[]
	 */
	function createContextSiProps(N2nLocale $n2nLocale, EiGuiFrame $eiGuiFrame) {
		
		$siProps = [];
		
		foreach ($this->forkGuiPropPaths as $forkGuiPropPath) {
			$eiProp = $eiGuiFrame->getGuiDefinition()->getGuiPropWrapperByGuiPropPath($forkGuiPropPath)->getEiProp();
			
			$siProp = (new SiProp((string) $forkGuiPropPath, $eiProp->getLabelLstr()->t($n2nLocale)))
					->setDescendantPropIds(array_map(
							function ($guiPropPath) { return (string) $guiPropPath; },
							$this->forkedGuiPropPaths[(string) $forkGuiPropPath]));
			
			if (null !== ($helpTextLstr = $eiProp->getHelpTextLstr())) {
				$siProp->setHelpText($helpTextLstr);
			}
			
			$siProps[] = $siProp;
		}
		
		return $siProps;
	}
}