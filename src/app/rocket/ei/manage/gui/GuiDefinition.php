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
namespace rocket\ei\manage\gui;

use n2n\l10n\N2nLocale;
use rocket\ei\EiPropPath;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\entry\EiEntry;
use n2n\reflection\ArgUtils;
use rocket\ei\manage\entry\EiFieldWrapper;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\ui\DisplayStructure;
use n2n\util\ex\NotYetImplementedException;

class GuiDefinition {	
	private $identityStringPattern;
	private $levelGuiProps = array();
	private $levelEiPropPaths = array();
	private $levelGuiPropForks = array();
	private $levelIds = array();
	
	/**
	 * @param string|null $identityStringPattern
	 */
	public function setIdentityStringPattern(?string $identityStringPattern) {
		$this->identityStringPattern = $identityStringPattern;
	}
	
	/**
	 * @return string|null
	 */
	public function getIdentityStringPattern() {
		return $this->identityStringPattern;
	}
	
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
		if (!isset($this->levelEiPropPaths[$id])) {
			throw new GuiException('No EiPropPath with id \'' . $id . '\' registered');
		}
		
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
	public function putLevelGuiPropFork(string $id, GuiPropFork $guiPropFork, EiPropPath $eiPropPath) {
		$this->levelGuiPropForks[$id] = $guiPropFork;
		$this->levelIds[$id] = $id;
		$this->levelEiPropPaths[$id] = $eiPropPath;
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
	 * @return \rocket\ei\manage\gui\GuiIdPath[]
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
						$displayDefinition->getDisplayItemType(),
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
						$displayItem->getType(), $displayItem->getLabel());
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
					$displayItem->getType() ?? $displayDefinition->getDisplayItemType(),
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
	 * @return \rocket\ei\manage\gui\GuiProp
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
	 * @param EiPropPath $eiPropPath
	 * @throws NotYetImplementedException
	 * @return \rocket\ei\manage\gui\GuiIdPath|NULL
	 */
	public function eiPropPathToGuiIdPath(EiPropPath $eiPropPath) {
		if ($eiPropPath->hasMultipleIds()) {
			throw new NotYetImplementedException();
		}
		
		$id = $eiPropPath->getFirstId();
		if (isset($this->levelGuiProps[$id])) {
			return new GuiIdPath([$id]);
		}
		
		return null;
	}
	
	/**
	 * @param GuiIdPath $guiIdPath
	 * @return \rocket\ei\EiPropPath|NULL
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
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	private function createDefaultIdentityString(EiObject $eiObject, N2nLocale $n2nLocale) {
		$eiType = $eiObject->getEiEntityObj()->getEiType();
		
		$idPatternPart = null;
		$namePatternPart = null;
		
		foreach ($this->getStringRepresentableGuiProps() as $guiIdPathStr => $guiProp) {
			if ($guiIdPathStr == $eiType->getEntityModel()->getIdDef()->getPropertyName()) {
				$idPatternPart = SummarizedStringBuilder::createPlaceholder($guiIdPathStr);
			} else {
				$namePatternPart = SummarizedStringBuilder::createPlaceholder($guiIdPathStr);
			}
			
			if ($namePatternPart !== null) break;
		}
		
		if ($idPatternPart === null) {
			$idPatternPart = $eiObject->getEiEntityObj()->hasId() ?
			$eiType->idToPid($eiObject->getEiEntityObj()->getId()) : 'new';
		}
		
		if ($namePatternPart === null) {
			$namePatternPart = $this->getLabelLstr()->t($n2nLocale);
		}
		
		return $this->createIdentityStringFromPattern($namePatternPart . ' #' . $idPatternPart, $eiObject, $n2nLocale);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(EiObject $eiObject, N2nLocale $n2nLocale) {
		if ($this->identityStringPattern === null) {
			return $this->createDefaultIdentityString($eiObject, $n2nLocale);
		}
		
		return $this->createIdentityStringFromPattern($this->identityStringPattern, $eiObject, $n2nLocale);
	}
	
	/**
	 * @param $entity
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityStringFromPattern(string $identityStringPattern, EiObject $eiObject, N2nLocale $n2nLocale): string {
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
}