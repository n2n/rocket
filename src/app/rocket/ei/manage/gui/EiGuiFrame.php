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
use rocket\si\content\impl\basic\CompactEntrySiComp;
use rocket\ei\EiPropPath;
use rocket\si\content\impl\basic\BulkyEntrySiComp;

/**
 * @author andreas
 *
 */
class EiGuiFrame {
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
		
		$eiPropPathStr = (string) $eiPropPath;
		$this->eiPropPaths[$eiPropPathStr] = $eiPropPath;
		$this->guiFieldAssemblers[$eiPropPathStr] = $guiFieldAssembler;
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
		
		$guiPropPathStr = (string) $guiPropPath;
		$this->guiPropPaths[$guiPropPathStr] = $guiPropPath;
		$this->displayDefinitions[$guiPropPathStr] = $displayDefinition;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return bool
	 */
	function containsDisplayDefintion(DisplayDefinition $guiPropPath) {
		return isset($this->displayDefinition[(string) $guiPropPath]);
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @throws GuiException
	 * @return DisplayDefinition
	 */
	function getDisplayDefintion(GuiPropPath $guiPropPath) {
		$guiPropPathStr = (string) $guiPropPath;
		if (isset($this->displayDefinitions[$guiPropPathStr])) {
			return $this->displayDefinitions[$guiPropPathStr];
		}
		
		throw new GuiException('Unknown GuiPropPath for ' . $guiPropPath);
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
	function createEiEntryGui(EiEntry $eiEntry, int $treeLevel = null): EiEntryGui {
		$this->ensureInit();
		
		$eiEntryGui = GuiFactory::createEiEntryGui($this, $eiEntry, $this->getGuiPropPaths(), $treeLevel);
		
		foreach ($this->eiGuiFrameListeners as $eiGuiFrameListener) {
			$eiGuiFrameListener->onNewEiEntryGui($eiEntryGui);
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
	 * @return \rocket\si\content\SiEntry
	 */
	function createSiEntry(EiEntryGui $eiEntryGui, bool $siControlsIncluded = true) {
		$eiEntry = $eiEntryGui->getEiEntry();
		$eiType = $eiEntry->getEiType();
		$siIdentifier = $eiEntry->getEiObject()->createSiEntryIdentifier();
		$viewMode = $this->getViewMode();
		
		$siEntry = new SiEntry($siIdentifier, ViewMode::isReadOnly($viewMode), ViewMode::isBulky($viewMode));
		$siEntry->putBuildup($eiType->getId(), $this->createSiEntryBuildup($eiEntryGui, $siControlsIncluded));
		$siEntry->setSelectedTypeId($eiType->getId());
		
		return $siEntry;
	}
	
	/**
	 * @return SiEntryBuildup
	 */
	function createSiEntryBuildup(EiEntryGui $eiEntryGui, bool $siControlsIncluded = true) {
		$eiEntry = $eiEntryGui->getEiEntry();
		
		$n2nLocale = $this->eiFrame->getN2nContext()->getN2nLocale();
		$typeId = $eiEntry->getEiMask()->getEiType()->getId();
		$idName = null;
		if (!$eiEntry->isNew()) {
			$deterIdNameDefinition = $this->eiFrame->getManageState()->getDef()
					->getIdNameDefinition($eiEntry->getEiMask());
			$idName = $deterIdNameDefinition->createIdentityString($eiEntry->getEiObject(), 
					$this->eiFrame->getN2nContext(), $n2nLocale);
		}
		
		$siEntry = new SiEntryBuildup($typeId, $idName);
		
		foreach ($eiEntryGui->getGuiFieldMap()->getAllGuiFields() as $guiPropPathStr => $guiField) {
			if (null !== ($siField = $guiField->getSiField())) {
				$siEntry->putField($guiPropPathStr, $siField);
			}
			
// 			$siEntry->putContextFields($guiPropPathStr, $guiField->getContextSiFields());
		}
		
		if (!$siControlsIncluded) {
			return $siEntry;
		}
		
		foreach ($this->guiDefinition->createEntryGuiControls($this, $eiEntry)
				as $guiControlPathStr => $entryGuiControl) {
			$siEntry->putControl($guiControlPathStr, $entryGuiControl->toSiControl(
					new ApiControlCallId(GuiControlPath::create($guiControlPathStr),
							$this->guiDefinition->getEiMask()->getEiTypePath(),
							$this->viewMode, $eiEntry->getPid())));
		}
		
		return $siEntry;
	}
	
	/**
	 * @param bool $controlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiComp
	 */
	function createCompactEntrySiComp(EiEntryGui $eiEntryGui, bool $generalSiControlsIncluded = true,
			bool $entrySiControlsIncluded = true) {
		$siContent = new CompactEntrySiComp($this->eiGuiFrame->createSiDeclaration(),
				$this->createSiEntry($entrySiControlsIncluded));
		
		if ($generalSiControlsIncluded) {
			$siContent->setControls($this->eiGuiFrame->createGeneralSiControls());
		}
		
		return $siContent;
	}
	
	/**
	 * @param bool $controlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiComp
	 */
	function createBulkyEntrySiComp(EiEntryGui $eiEntryGui, bool $generalSiControlsIncluded = true,
			bool $entrySiControlsIncluded = true) {
		$siContent = new BulkyEntrySiComp($this->eiGuiFrame->createSiDeclaration(),
				$this->createSiEntry($entrySiControlsIncluded));
		
		if ($generalSiControlsIncluded) {
			$siContent->setControls($this->eiGuiFrame->createGeneralSiControls());
		}
		
		return $siContent;
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
	
	
// 	/**
// 	 * @return \rocket\ei\manage\gui\EiGuiFrameNature
// 	 */
// 	function getEiGuiFrameNature()  {
// 		return $this->eiGuiFrameNature;
// 	}
	
	
}

// class GuiPropAssembly {
// 	private $guiPropPath;
// 	private $displayDefinition;
	
// 	/**
// 	 * @param GuiProp $guiProp
// 	 * @param GuiPropPath $guiPropPath
// 	 * @param DisplayDefinition $displayDefinition
// 	 */
// 	function __construct(GuiPropPath $guiPropPath, DisplayDefinition $displayDefinition) {
// 		$this->guiPropPath = $guiPropPath;
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


// class EiGuiFrameNature {
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
// 	 * @return \rocket\ei\manage\gui\EiGuiFrameNature
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
// 	 * @return \rocket\ei\manage\gui\EiGuiFrameNature
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
// // 	 * @return \rocket\ei\manage\gui\EiGuiFrameNature
// // 	 */
// // 	function setForkControlsRendered(bool $forkControlsRedenered) {
// // 		$this->forkControlsRendered = $forkControlsRedenered;
// // 		return $this;
// // 	}
// }