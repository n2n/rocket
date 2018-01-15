<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\spec\ei\manage\gui;

use n2n\l10n\N2nLocale;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\mapping\EiEntry;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\mapping\EiFieldWrapper;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\gui\ui\DisplayStructure;

class GuiDefinition {	
	private $levelGuiProps = array();
	private $levelEiPropPaths = array();
	private $levelGuiPropForks = array();
	private $levelIds = array();
	
	/**
	 * @param string $id
	 * @param GuiProp $guiProp
	 * @param EiPropPath $eiPropPath
	 * @throws GuiException
	 */
	public function putLevelGuiProp(string $id, GuiProp $guiProp, EiPropPath $eiPropPath) {
		if (isset($this->levelGuiProps[$id])) {
			throw new GuiException('GuiProp with id \'' . $id . '\' is already registered');
		}
		
		$this->levelGuiProps[$id] = $guiProp;
		$this->levelEiPropPaths[$id] = $eiPropPath;
		$this->levelIds[$id] = $id;
	}
	
	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsLevelGuiPropId(string $id) {
		return isset($this->levelGuiProps[$id]);
	}
	
	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiProp
	 */
	public function getLevelGuiPropById(string $id) {
		if (!isset($this->levelGuiProps[$id])) {
			throw new GuiException('No GuiProp with id \'' . $id . '\' registered');
		}
		
		return $this->levelGuiProps[$id];
	}

	/**
	 * @param string $id
	 * @throws GuiException
	 * @return EiPropPath
	 */
	public function getLevelEiPropPathById(string $id) {
		$this->getLevelGuiPropById($id);
		return $this->levelEiPropPaths[$id];
	}
	
	/**
	 * @return GuiProp[]
	 */
	public function getLevelGuiProps() {
		return $this->levelGuiProps;
	}
	
	/**
	 * @param string $id
	 * @param GuiPropFork $guiPropFork
	 */
	public function putLevelGuiPropFork(string $id, GuiPropFork $guiPropFork) {
		$this->levelGuiPropForks[$id] = $guiPropFork;
		$this->levelIds[$id] = $id;
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsLevelGuiPropForkId(string $id) {
		return isset($this->levelGuiPropForks[$id]);
	}
	
	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiPropFork
	 */
	public function getLevelGuiPropForkById(string $id) {
		if (!isset($this->levelGuiPropForks[$id])) {
			throw new GuiException('No GuiPropFork with id \'' . $id . '\' registered.');
		}
		
		return $this->levelGuiPropForks[$id];
	}
	
	
	
	public function getGuiProps() {
		return $this->buildGuiProps(array());
	}
	
	protected function buildGuiProps(array $baseIds) {
		$guiProps = array();
		
		foreach ($this->levelIds as $id) {
			if (isset($this->levelGuiProps[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
				$guiProps[(string) new GuiIdPath($currentIds)] = $this->levelGuiProps[$id];
			}
				
			if (isset($this->levelGuiPropForks[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
					
				$guiProps = array_merge($guiProps, $this->levelGuiPropForks[$id]->getForkedGuiDefinition()
						->buildGuiProps($currentIds));
			}
		}
		
		return $guiProps;
	}
	
	/**
	 * @deprecated use {@see GuiDefinition::getGuiIdPaths()}
	 * @return \rocket\spec\ei\manage\gui\GuiIdPath[]
	 */
	public function getAllGuiIdPaths() {
		return $this->getGuiIdPaths();
	}
	
	public function getGuiIdPaths() {
		return $this->buildGuiIdPaths(array());
	}
	
	protected function buildGuiIdPaths(array $baseIds) {
		$guiIdPaths = array();
		
		foreach ($this->levelIds as $id) {
			if (isset($this->levelGuiProps[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
				$guiIdPaths[] = new GuiIdPath($currentIds);
			}
			
			if (isset($this->levelGuiPropForks[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
				
				$guiIdPaths = array_merge($guiIdPaths, $this->levelGuiPropForks[$id]->getForkedGuiDefinition()
						->buildGuiIdPaths($currentIds));
			}
		}
		
		return $guiIdPaths;
	}
	
	public function createDefaultDisplayStructure(EiGui $eiGui) {
		$displayStructure = new DisplayStructure();
		$this->composeDisplayStructure($displayStructure, array(), new Eiu($eiGui));
		return $displayStructure;
	}
	
	/**
	 * @param array $baseIds
	 * @param Eiu $eiu
	 * @param int $minTestLevel
	 */
	protected function composeDisplayStructure(DisplayStructure $displayStructure, array $baseIds, Eiu $eiu) {
		foreach ($this->levelIds as $id) {
			$displayDefinition = null;
			if (isset($this->levelGuiProps[$id]) && 
					null !== ($displayDefinition = $this->levelGuiProps[$id]->buildDisplayDefinition($eiu))
					&& $displayDefinition->isDefaultDisplayed()) {
				
				$currentIds = $baseIds;
				$currentIds[] = $id;
				$displayStructure->addGuiIdPath(new GuiIdPath($currentIds),
						$displayDefinition->getGroupType(),
						$displayDefinition->getLabel());
			}
			
			if (isset($this->levelGuiPropForks[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
				
				$this->levelGuiPropForks[$id]->getForkedGuiDefinition()
						->composeDisplayStructure($displayStructure, $currentIds, $eiu);
			}
		}
	}
	
	public function purifyDisplayStructure(DisplayStructure $displayStructure, EiGui $eiGui) {
		return $this->rPurifyDisplayStructure($displayStructure, new Eiu($eiGui));
	}
	
	private function rPurifyDisplayStructure(DisplayStructure $displayStructure, Eiu $eiu) {
		$purifiedDisplayStructure = new DisplayStructure();
		
		foreach ($displayStructure->getDisplayItems() as $displayItem) {
			if ($displayItem->hasDisplayStructure()) {
				$purifiedDisplayStructure->addDisplayStructure(
						$this->rPurifyDisplayStructure($displayItem->getDisplayStructure(), $eiu), 
						$displayItem->getGroupType(), $displayItem->getLabel());
				continue;
			}
			
			$guiProp = null;
			try {
				$guiProp = $this->getGuiPropByGuiIdPath($displayItem->getGuiIdPath());
			} catch (GuiException $e) {
				continue;
			}
			
			$displayDefinition = $guiProp->buildDisplayDefinition($eiu);
			if ($displayDefinition === null) {
				continue;
			}
			
			$purifiedDisplayStructure->addGuiIdPath($displayItem->getGuiIdPath(),
					$displayItem->getGroupType() ?? $displayDefinition->getGroupType(),
					$displayItem->getLabel() ?? $displayDefinition->getLabel());
		}
		
		return $purifiedDisplayStructure;
	}
	
	public function completeDisplayStructure(EiGui $eiGui) {
		$displayStructure = new DisplayStructure();
		return $this->composeDisplayStructure($displayStructure, array(), new Eiu($eiGui));
	}
	
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @return \rocket\spec\ei\manage\gui\GuiProp
	 * @throws GuiException
	 */
	public function getGuiPropByGuiIdPath(GuiIdPath $guiIdPath) {
		$ids = $guiIdPath->toArray();
		$guiDefinition = $this;
		while (null !== ($id = array_shift($ids))) {
			if (empty($ids)) {
				return $guiDefinition->getLevelGuiPropById($id);
			}
			
			$guiDefinition = $guiDefinition->getLevelGuiPropForkById($id)->getForkedGuiDefinition();
		}	
		
		// @todo convert to exception
		return null;
	}
	
	public function containsGuiProp(GuiIdPath $guiIdPath) {
		$ids = $guiIdPath->toArray();
		$guiDefinition = $this;
		while (null !== ($id = array_shift($ids))) {
			if (empty($ids)) {
				return $guiDefinition->containsLevelGuiPropId($id);
			}
			
			$guiDefinition = $guiDefinition->getLevelGuiPropForkById($id)->getForkedGuiDefinition();
		}
		
		return true;
	}
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @return \rocket\spec\ei\EiPropPath|NULL
	 */
	public function guiIdPathToEiPropPath(GuiIdPath $guiIdPath) {
		$ids = $guiIdPath->toArray();
		$guiDefinition = $this;
		while (null !== ($id = array_shift($ids))) {
			if (empty($ids)) {
				return $guiDefinition->getLevelEiPropPathById($id);
			}
				
			$guiDefinition = $guiDefinition->getLevelGuiPropForkById($id)->getForkedGuiDefinition();
		}
		
		return null;
	}
	
	public function determineEiFieldWrapper(EiEntry $eiEntry, GuiIdPath $guiIdPath) {
		$ids = $guiIdPath->toArray();
		$id = array_shift($ids);
		if (empty($ids)) {
			return $eiEntry->getEiFieldWrapper(new EiPropPath(array($id)));
		}
		
		$guiPropFork = $this->getLevelGuiPropForkById($id);
		$eiFieldWrapper = $guiPropFork->determineEiFieldWrapper($eiEntry, $guiIdPath);
		ArgUtils::valTypeReturn($eiFieldWrapper, EiFieldWrapper::class, $guiPropFork, 'determineEiFieldWrapper', true);
		return $eiFieldWrapper;
	}
	
	public function getGuiPropForks() {
		return $this->levelGuiPropForks;
	}
	
	/**
	 * @param $entity
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(string $identityStringPattern, EiObject $eiObject, N2nLocale $n2nLocale): string {
		$builder = new SummarizedStringBuilder($identityStringPattern, $n2nLocale);
		$builder->replaceFields(array(), $this, $eiObject);
		return $builder->__toString();
	}
	
	public function getStringRepresentableGuiProps() {
		return $this->filterStringRepresentableGuiProps($this, array());
	}
	
	private function filterStringRepresentableGuiProps(GuiDefinition $guiDefinition, array $baseIds) {
		$guiProps = array();
		
		foreach ($guiDefinition->getLevelGuiProps() as $id => $guiProp) {
			if (!$guiProp->isStringRepresentable()) continue;
			
			$ids = $baseIds;
			$ids[] = $id;
			$guiProps[(string) new GuiIdPath($ids)] = $guiProp;
		}
		
		foreach ($guiDefinition->getGuiPropForks() as $id => $guiPropFork) {
			$ids = $baseIds;
			$ids[] = $id;
			$guiProps = array_merge($guiProps, $this->filterStringRepresentableGuiProps(
					$guiPropFork->getForkedGuiDefinition(), $ids));
		}
		
		return $guiProps;
	}
	
	private $guiDefinitionListeners = array();
	
	public function registerGuiDefinitionListener(GuiDefinitionListener $guiDefinitionListener) {
		$this->guiDefinitionListeners[spl_object_hash($guiDefinitionListener)] = $guiDefinitionListener;
	}
	
	public function unregisterGuiDefinitionListener(GuiDefinitionListener $guiDefinitionListener) {
		unset($this->guiDefinitionListeners[spl_object_hash($guiDefinitionListener)]);
	}
	
// 	public function createEiGui(EiFrame $eiFrame, int $viewMode) {
// 		return new EiGui($eiFrame, $this, $viewMode);
// 	}
	
// 	/**
// 	 * @param EiMask $eiMask
// 	 * @param EiuEntry $eiuEntry
// 	 * @param int $viewMode
// 	 * @param array $guiIdPaths
// 	 * @return EiEntryGui
// 	 */
// 	public function createEiEntryGui(EiGui $eiGui, array $guiIdPaths) {
// 		ArgUtils::valArrayLike($guiIdPaths, GuiIdPath::class);
		
// 		$eiEntryGui = new EiEntryGui($eiMask, $viewMode);
// 		$eiuEntryGui = new EiuEntryGui($eiEntryGui, $eiEntry, $eiFrame);
		
// 		$guiFieldAssembler = new GuiFieldAssembler($eiMask->getEiEngine()->getGuiDefinition(), $eiEntryGui);
		
// 		foreach ($guiIdPaths as $guiIdPath) {
// 			$result = $guiFieldAssembler->assembleGuiField($guiIdPath);
// 			if ($result === null) continue;
			 
// 			$eiEntryGui->putDisplayable($guiIdPath, $result->getDisplayable());
// 			if (null !== ($eiFieldWrapper = $result->getEiFieldWrapper())) {
// 				$eiEntryGui->putEiFieldWrapper($guiIdPath, $eiFieldWrapper);
// 			}
			
// 			if (null !== ($magPropertyPath = $result->getMagPropertyPath())) {
// 				$eiEntryGui->putEditableWrapper($guiIdPath, new EditableWrapper($result->isMandatory(), 
// 						$magPropertyPath, $result->getMagWrapper()));
// 			}
// 		}
		
// 		if (null !== ($dispatchable = $guiFieldAssembler->getDispatchable())) {
// 			$eiEntryGui->setDispatchable($guiFieldAssembler->getDispatchable());
// 			$eiEntryGui->setForkMagPropertyPaths($guiFieldAssembler->getForkedMagPropertyPaths());
// 			$eiEntryGui->setSavables($guiFieldAssembler->getSavables());
// 		}
		
// 		foreach ($this->eiModificatorCollection as $eiModificator) {
// 			$eiModificator->setupEiEntryGui($eiEntryGui);
// 		}
		
// 		$eiEntryGui->markInitialized();
		
// 		return $eiEntryGui;
// 	}
}

interface GuiDefinitionListener {
	
	public function onNewEiGui(EiGui $eiGui);
	
	public function onNewEiEntryGui(EiEntryGui $eiEntryGui);
	
	public function onNewView(HtmlView $view);
}