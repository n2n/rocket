<?php
namespace rocket\ei\manage\gui;

use n2n\core\container\N2nContext;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\ManagedDef;
use rocket\ei\EiType;
use rocket\ei\mask\EiMask;

class EiGuiFactory {
	/**
	 * @var N2nContext
	 */
	private $n2nContext;
	/**
	 * @var ManagedDef
	 */
	private $def;
	
	function __construct(N2nContext $n2nContext) {
		$this->n2nContext = $n2nContext;
		$this->def = $n2nContext->lookup(ManageState::class)->getDef();
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $guiPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	function createEiGui(EiMask $contextEiMask, int $viewMode, ?array $guiPropPaths, 
			bool $guiStructureDeclarationsRequired) {
		$eiGui = new EiGui($contextEiMask, $viewMode);
		
		$this->applyEiGuiFrame($eiGui, false, $guiPropPaths, $guiStructureDeclarationsRequired);
		
		return $eiGui;
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $guiPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @throws GuiBuildFailedException
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	function createForgeEiGui(EiMask $contextEiMask, int $viewMode, ?array $guiPropPaths, 
			bool $guiStructureDeclarationsRequired) {
		$eiGui = new EiGui($contextEiMask, $viewMode);
		
		$this->applyEiGuiFrame($eiGui, true, $guiPropPaths, $guiStructureDeclarationsRequired);
		
		if (!$eiGui->hasEiGuiFrames()) {
			throw new GuiBuildFailedException('Can not build forge EiGui based on ' . $eiGui->getContextEiMask() 
					. ' because its type is abstract.');
		}
		
		return $eiGui;
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $allowedEiTypeIds
	 * @param array $guiPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @throws GuiBuildFailedException
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	function createMultiEiGui(EiMask $contextEiMask, int $viewMode, ?array $allowedEiTypes, 
			?array $guiPropPaths, bool $guiStructureDeclarationsRequired) {
		$eiGui = new EiGui($contextEiMask, $viewMode);
		
		$this->applyPossibleEiGuiFrames($eiGui, false, $allowedEiTypes, $guiPropPaths, $guiStructureDeclarationsRequired);
		
		if (!$eiGui->hasEiGuiFrames()) {
			throw new GuiBuildFailedException('Can not build forge EiGui based on ' . $eiGui->getContextEiMask()
					. ' because its type and sub types do not match the allowed EiTypes: ' . implode(', ', $allowedEiTypes));
		}
		
		return $eiGui;
		
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $allowedEiTypeIds
	 * @param array $guiPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @throws GuiBuildFailedException
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	function createForgeMultiEiGui(EiMask $contextEiMask, int $viewMode, ?array $allowedEiType,
			?array $guiPropPaths, bool $guiStructureDeclarationsRequired) {
		$eiGui = new EiGui($contextEiMask, $viewMode);
		
		$this->applyPossibleEiGuiFrames($eiGui, false, $allowedEiType, $guiPropPaths, $guiStructureDeclarationsRequired);
		
		if (!$eiGui->hasEiGuiFrames()) {
			throw new GuiBuildFailedException('Can not build forge EiGui based on ' . $eiGui->getContextEiMask()
					. ' because its type and sub types are either abstract or do not match the allowed EiTypes: ' 
					. implode(', ', array_map(function ($arg) { return (string) $arg; }, (array) $allowedEiType)));
		}
		
		return $eiGui;
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @param EiGui $eiGui
	 * @param array $guiPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return \rocket\ei\manage\gui\EiGuiFrame
	 */
	private function applyEiGuiFrame(EiGui $eiGui, bool $nonAbstractOnly, array $guiPropPaths = null,
			bool $guiStructureDeclarationsRequired = true) {
		$contextEiMask = $eiGui->getContextEiMask();
				
		if (!$this->testIfAllowed($contextEiMask->getEiType(), $nonAbstractOnly, null)) {
			return;
		}
		
		$guiDefinition = $this->def->getGuiDefinition($contextEiMask);
		$guiDefinition->createEiGuiFrame($this->n2nContext, $eiGui, $guiPropPaths,
				$guiStructureDeclarationsRequired);
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @param EiGui $eiGui
	 * @param array $allowedTypeIds
	 * @param array $guiPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return \rocket\ei\manage\gui\EiGuiFrame[]
	 */
	private function applyPossibleEiGuiFrames(EiGui $eiGui, bool $creatablesOnly, array $allowedTypes = null,
			array $guiPropPaths = null, bool $guiStructureDeclarationsRequired = true) {
		$contextEiMask = $eiGui->getContextEiMask();
		$contextEiType = $contextEiMask->getEiType();
		
		if ($this->testIfAllowed($contextEiType, $creatablesOnly, $allowedTypes)) {
			$guiDefinition = $this->def->getGuiDefinition($contextEiMask->determineEiMask($contextEiType));
			$eiGuiFrame = $guiDefinition->createEiGuiFrame($this->n2nContext, $eiGui, $guiPropPaths,
					$guiStructureDeclarationsRequired);
			$eiGui->putEiGuiFrame($eiGuiFrame);
		}
		
		foreach ($contextEiType->getAllSubEiTypes() as $eiType) {
			if (!$this->testIfAllowed($eiType, $creatablesOnly, $allowedTypes)) {
				continue;
			}
			
			$guiDefinition = $this->def->getGuiDefinition($contextEiMask->determineEiMask($contextEiType));
			$eiGuiFrame = $guiDefinition->createEiGuiFrame($this->n2nContext, $eiGui, $guiPropPaths,
					$guiStructureDeclarationsRequired);
			$eiGui->putEiGuiFrame($eiGuiFrame);
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