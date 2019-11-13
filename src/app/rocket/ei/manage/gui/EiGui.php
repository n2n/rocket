<?php
namespace rocket\ei\manage\gui;

use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\entry\EiEntry;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\GuiFactory;
use rocket\ei\manage\gui\field\GuiFieldPath;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\gui\control\UnknownGuiControlException;
use rocket\ei\manage\gui\control\GeneralGuiControl;
use rocket\ei\manage\api\ApiControlCallId;
use rocket\si\meta\SiDeclaration;
use rocket\si\meta\SiProp;

/**
 * @author andreas
 *
 */
class EiGui {
	/**
	 * @var EiFrame
	 */
	private $eiFrame;
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
	 * @var GuiFieldPath[]|null
	 */
	private $guiFieldPaths = null;
	/**
	 * @var EiGuiListener[]
	 */
	private $eiGuiListeners = array();
	/**
	 * @var EiEntryGui[]
	 */
	private $eiEntryGuis = array();
	
	/**
	 * @param EiFrame $eiFrame
	 * @param GuiDefinition $guiDefinition
	 * @param int $viewMode Use constants from {@see ViewMode}
	 */
	function __construct(EiFrame $eiFrame, GuiDefinition $guiDefinition, int $viewMode) {
		$this->eiFrame = $eiFrame;
		$this->guiDefinition = $guiDefinition;
		ArgUtils::valEnum($viewMode, ViewMode::getAll());
		$this->viewMode = $viewMode;
	}
	
	/**
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	function getEiFrame() {
		return $this->eiFrame;
	}
	
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
	 * @return GuiFieldPath[]
	 */
	function getGuiFieldPaths() {
		$this->ensureInit();
		
		return $this->guiFieldPaths;
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return bool
	 */
	function containsGuiFieldPath(GuiFieldPath $guiFieldPath) {
		return isset($this->guiFieldPaths[(string) $guiFieldPath]);
	}
	
	
	function getRootEiPropPaths() {
		$eiPropPaths = [];
		foreach ($this->getGuiFieldPaths() as $guiFieldPath) {
			$eiPropPath = $guiFieldPath->getFirstEiPropPath();
			$eiPropPaths[(string) $eiPropPath] = $eiPropPath;
		}
		return $eiPropPaths;
	}
	
// 	/**
// 	 * @param GuiFieldPath $guiFieldPath
// 	 * @throws GuiException
// 	 * @return \rocket\ei\manage\gui\GuiPropAssembly
// 	 */
// 	function getGuiPropAssemblyByGuiFieldPath(GuiFieldPath $guiFieldPath) {
// 		$guiFieldPathStr = (string) $guiFieldPath;
		
// 		if (isset($this->guiPropAssemblies[$guiFieldPathStr])) {
// 			return $this->guiPropAssemblies[$guiFieldPathStr];
// 		}
		
// 		throw new GuiException('No GuiPropAssembly for GuiFieldPath available: ' . $guiFieldPathStr);
// 	}
	
	/**
	 * @param GuiStructureDeclaration[]|null $guiStructureDeclarations
	 * @param GuiFieldPath[]|null $guiFieldPaths
	 */
	function init(array $guiFieldPaths) {
		if ($this->isInit()) {
			throw new IllegalStateException('EiGui already initialized.');
		}
		
		$this->guiFieldPaths = $guiFieldPaths;
		
		foreach ($this->eiGuiListeners as $listener) {
			$listener->onInitialized($this);
		}
	}
	
	/**
	 * @return boolean
	 */
	function isInit() {
		return $this->guiFieldPaths !== null;
	}
	
	/**
	 * @throws IllegalStateException
	 */
	private function ensureInit() {
		if ($this->guiFieldPaths !== null) return;
		
		throw new IllegalStateException('EiGui not yet initialized.');
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
// 	 * @return GuiFieldPath[]
// 	 */
// 	function getForkGuiFieldPaths(EiPropPath $forkEiPropPath) {
// 		$forkGuiFieldPaths = [];
// 		foreach ($this->getGuiFieldPaths() as $guiFieldPath) {
// 			if ($guiFieldPath->getFirstEiPropPath()->equals($eiPropPath)) {
// 				continue;
// 			}
			
// 			$forkGuiFieldPaths[] = $guiFieldPath->getShifted();
// 		}
// 		return $forkGuiFieldPaths;
// 	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @param bool $makeEditable
	 * @param int $treeLevel
	 * @param bool $append
	 * @return EiEntryGui
	 */
	function createEiEntryGui(EiEntry $eiEntry, int $treeLevel = null): EiEntryGui {
		$this->ensureInit();
		
		$eiEntryGui = GuiFactory::createEiEntryGui($this, $eiEntry, $this->getGuiFieldPaths(), $treeLevel);
		
		foreach ($this->eiGuiListeners as $eiGuiListener) {
			$eiGuiListener->onNewEiEntryGui($eiEntryGui);
		}
		
		return $eiEntryGui;
	}
	
	/**
	 * @return \rocket\si\control\SiControl[]
	 */
	function createSelectionSiControls() {
		$siControls = [];
		foreach ($this->guiDefinition->createSelectionGuiControls($this)
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
	function createGeneralSiControls() {
		$siControls = [];
		foreach ($this->guiDefinition->createGeneralGuiControls($this)
				as $guiControlPathStr => $generalGuiControl) {
			$siControls[$guiControlPathStr] = $generalGuiControl->toSiControl(
					new ApiControlCallId(GuiControlPath::create($guiControlPathStr), 
							$this->guiDefinition->getEiMask()->getEiTypePath(),
							$this->viewMode, null));
		}
		return $siControls;
	}
	
	/**
	 * @param GuiControlPath $guiControlPath
	 * @return GeneralGuiControl
	 * @throws UnknownGuiControlException
	 */
	function createGeneralGuiControl(GuiControlPath $guiControlPath) {
		return $this->guiDefinition->createGeneralGuiControl($this, $guiControlPath);
	}
	
	/**
	 * @param EiGuiListener $eiGuiListener
	 */
	function registerEiGuiListener(EiGuiListener $eiGuiListener) {
		$this->eiGuiListeners[spl_object_hash($eiGuiListener)] = $eiGuiListener;
	}
	
	/**
	 * @param EiGuiListener $eiGuiListener
	 */
	function unregisterEiGuiListener(EiGuiListener $eiGuiListener) {
		unset($this->eiGuiListeners[spl_object_hash($eiGuiListener)]);
	}
	
	
// 	/**
// 	 * @return \rocket\ei\manage\gui\EiGuiNature
// 	 */
// 	function getEiGuiNature()  {
// 		return $this->eiGuiNature;
// 	}
	
	
}

// class GuiPropAssembly {
// 	private $guiFieldPath;
// 	private $displayDefinition;
	
// 	/**
// 	 * @param GuiProp $guiProp
// 	 * @param GuiFieldPath $guiFieldPath
// 	 * @param DisplayDefinition $displayDefinition
// 	 */
// 	function __construct(GuiFieldPath $guiFieldPath, DisplayDefinition $displayDefinition) {
// 		$this->guiFieldPath = $guiFieldPath;
// 		$this->displayDefinition = $displayDefinition;
// 	}
	
// // 	/**
// // 	 * @return \rocket\ei\manage\gui\GuiProp
// // 	 */
// // 	function getGuiProp() {
// // 		return $this->guiProp;
// // 	}
	

	
// 	/**
// 	 * @return \rocket\ei\manage\gui\DisplayDefinition
// 	 */
// 	function getDisplayDefinition() {
// 		return $this->displayDefinition;
// 	}
// }


// class EiGuiNature {
// 	private $entryControlsRendered = false;
// 	private $collectionForced = false;
// // 	private $forkControlsRendered = true;
	
// 	/**
// 	 * @return bool
// 	 */
// 	function areEntryGuiControlsRendered() {
// 		return $this->entryControlsRendered;
// 	}
	
// 	/**
// 	 * @param bool $entryControlsRendered
// 	 * @return \rocket\ei\manage\gui\EiGuiNature
// 	 */
// 	function setEntryGuiControlsRendered(bool $entryControlsRendered) {
// 		$this->entryControlsRendered = $entryControlsRendered;
// 		return $this;
// 	}
// 	/**
//  	 * @return bool
//  	 */
// 	function isCollectionForced() {
// 		return $this->collectionForced;
// 	}

// 	/**
// 	 * @param bool $forkControlsRedenered
// 	 * @return \rocket\ei\manage\gui\EiGuiNature
// 	 */
// 	function setCollectionForced(bool $collectionForced) {
// 		$this->collectionForced = $collectionForced;
// 		return $this;
// 	}
// // 	/**
// // 	 * @return bool
// // 	 */
// // 	function areForkControlsRendered() {
// // 		return $this->forkControlsRendered;
// // 	}
	
// // 	/**
// // 	 * @param bool $forkControlsRedenered
// // 	 * @return \rocket\ei\manage\gui\EiGuiNature
// // 	 */
// // 	function setForkControlsRendered(bool $forkControlsRedenered) {
// // 		$this->forkControlsRendered = $forkControlsRedenered;
// // 		return $this;
// // 	}
// }