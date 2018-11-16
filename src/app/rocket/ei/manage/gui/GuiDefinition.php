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
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\ui\DisplayStructure;
use n2n\util\ex\NotYetImplementedException;
use n2n\l10n\Lstr;
use rocket\core\model\Rocket;
use n2n\core\container\N2nContext;

class GuiDefinition {	
	private $identityStringPattern;
	private $labelLstr;
	private $guiProps = array();
	private $guiPropForks = array();
	private $eiPropPaths = array();
	
	function __construct(Lstr $labelLstr) {
		$this->labelLstr = $labelLstr;
	}
	
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
	 * @param EiPropPath $guiPropPath
	 * @throws GuiException
	 */
	public function putGuiProp(EiPropPath $eiPropPath, GuiProp $guiProp) {
		$eiPropPathStr = (string) $eiPropPath;
		
		if (isset($this->guiProps[$eiPropPathStr])) {
			throw new GuiException('GuiProp for EiPropPath \'' . $eiPropPathStr . '\' is already registered');
		}
		
		$this->guiProps[$eiPropPathStr] = $guiProp;
		$this->eiPropPaths[$eiPropPathStr] = $eiPropPath;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 */
	public function removeGuiProp(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		
		unset($this->guiProps[$eiPropPathStr]);
		unset($this->eiPropPaths[$eiPropPathStr]);
	}
		
	/**
	 * @param GuiPropPath $guiPropPath
	 */
	public function removeGuiPropByPath(GuiPropPath $guiPropPath) {
		$guiDefinition = $this;
		$eiPropPaths = $guiPropPath->toArray();
		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
			if (empty($eiPropPaths)) {
				$guiDefinition->removeGuiProp($eiPropPath);
				return;
			}
			
			$guiDefinition = $guiDefinition->getGuiPropFork($eiPropPath)->getForkedGuiDefinition();
		
			if ($guiDefinition === null) {
				return;
			}
		}
	}
	
	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsEiPropPath(EiPropPath $eiPropPath) {
		return isset($this->eiPropPaths[(string) $eiPropPath]);
	}
	
	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiProp
	 */
	public function getGuiProp(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->guiProps[$eiPropPathStr])) {
			throw new GuiException('No GuiProp with id \'' . $eiPropPathStr . '\' registered');
		}
		
		return $this->guiProps[$eiPropPathStr];
	}

	/**
	 * @return GuiProp[]
	 */
	public function getGuiProps() {
		return $this->guiProps;
	}
	
	/**
	 * @param string $eiPropPath
	 * @param GuiPropFork $guiPropFork
	 */
	public function putGuiPropFork(EiPropPath $eiPropPath, GuiPropFork $guiPropFork) {
		$eiPropPathStr = (string) $eiPropPath;
		
		$this->guiPropForks[$eiPropPathStr] = $guiPropFork;
		$this->eiPropPaths[$eiPropPathStr] = $eiPropPath;
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsLevelGuiPropForkId(string $id) {
		return isset($this->guiPropForks[$id]);
	}
	
	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiPropFork
	 */
	public function getGuiPropFork(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->guiPropForks[$eiPropPathStr])) {
			throw new GuiException('No GuiPropFork with id \'' . $eiPropPathStr . '\' registered.');
		}
		
		return $this->guiPropForks[$eiPropPathStr];
	}
	
	public function getAllGuiProps() {
		return $this->buildGuiProps(array());
	}
	
	protected function buildGuiProps(array $baseIds) {
		$guiProps = array();
		
		foreach ($this->eiPropPaths as $id) {
			if (isset($this->guiProps[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
				$guiProps[(string) new GuiPropPath($currentIds)] = $this->guiProps[$id];
			}
				
			if (isset($this->guiPropForks[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
					
				$guiProps = array_merge($guiProps, $this->guiPropForks[$id]->getForkedGuiDefinition()
						->buildGuiProps($currentIds));
			}
		}
		
		return $guiProps;
	}
	
	/**
	 * @deprecated use {@see GuiDefinition::getGuiPropPaths()}
	 * @return \rocket\ei\manage\gui\GuiPropPath[]
	 */
	public function getAllGuiPropPaths() {
		return $this->getGuiPropPaths();
	}
	
	public function getGuiPropPaths() {
		return $this->buildGuiPropPaths(array());
	}
	
	protected function buildGuiPropPaths(array $baseIds) {
		$eiPropPaths = array();
		
		foreach ($this->eiPropPaths as $id) {
			if (isset($this->guiProps[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
				$eiPropPaths[] = new GuiPropPath($currentIds);
			}
			
			if (isset($this->guiPropForks[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
				
				$eiPropPaths = array_merge($eiPropPaths, $this->guiPropForks[$id]->getForkedGuiDefinition()
						->buildGuiPropPaths($currentIds));
			}
		}
		
		return $eiPropPaths;
	}
	
	public function createDefaultDisplayStructure(EiGui $eiGui) {
		$displayStructure = new DisplayStructure();
		$this->composeDisplayStructure($displayStructure, array(), new Eiu($eiGui));
		return $displayStructure;
	}
	
	/**
	 * @param array $baseEiPropPaths
	 * @param Eiu $eiu
	 * @param int $minTestLevel
	 */
	protected function composeDisplayStructure(DisplayStructure $displayStructure, array $baseEiPropPaths, Eiu $eiu) {
		foreach ($this->eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			
			$displayDefinition = null;
			if (isset($this->guiProps[$eiPropPathStr]) 
					&& null !== ($displayDefinition = $this->guiProps[$eiPropPathStr]->buildDisplayDefinition($eiu))
					&& $displayDefinition->isDefaultDisplayed()) {
				
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				$displayStructure->addGuiPropPath(new GuiPropPath($currentEiPropPaths),
						$displayDefinition->getDisplayItemType());
			}
			
			if (isset($this->guiPropForks[$eiPropPathStr])
					&& null !== ($forkedGuiDefinition = $this->guiPropForks[$eiPropPathStr]->getForkedGuiDefinition())) {
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				$forkedGuiDefinition->composeDisplayStructure($displayStructure, $currentEiPropPaths, $eiu);
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
				$guiProp = $this->getGuiPropByGuiPropPath($displayItem->getGuiPropPath());
			} catch (GuiException $e) {
				continue;
			}
			
			$displayDefinition = $guiProp->buildDisplayDefinition($eiu);
			if ($displayDefinition === null) {
				continue;
			}
			
			$purifiedDisplayStructure->addGuiPropPath($displayItem->getGuiPropPath(),
					$displayItem->getType() ?? $displayDefinition->getDisplayItemType(),
					$displayItem->getLabel(), $displayItem->getModuleNamespace());
		}
		
		return $purifiedDisplayStructure;
	}
	
	public function completeDisplayStructure(EiGui $eiGui) {
		$displayStructure = new DisplayStructure();
		return $this->composeDisplayStructure($displayStructure, array(), new Eiu($eiGui));
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return \rocket\ei\manage\gui\GuiProp
	 * @throws GuiException
	 */
	public function getGuiPropByGuiPropPath(GuiPropPath $guiPropPath) {
		$ids = $guiPropPath->toArray();
		$guiDefinition = $this;
		while (null !== ($id = array_shift($ids))) {
			if (empty($ids)) {
				return $guiDefinition->getGuiProp($id);
			}
			
			$guiDefinition = $guiDefinition->getGuiPropFork($id)->getForkedGuiDefinition();
			if ($guiDefinition === null) {
				break;
			}
		}	
		
		throw new GuiException('GuiPropPath could not be resolved: ' . $guiPropPath);
	}
	
	public function containsGuiProp(GuiPropPath $guiPropPath) {
		$eiPropPaths = $guiPropPath->toArray();
		$guiDefinition = $this;
		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
			if (empty($eiPropPaths)) {
				return $guiDefinition->containsEiPropPath($eiPropPath);
			}
			
			$guiDefinition = $guiDefinition->getGuiPropFork($eiPropPath)->getForkedGuiDefinition();
		}
		
		return true;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws NotYetImplementedException
	 * @return \rocket\ei\manage\gui\GuiPropPath|NULL
	 */
	public function eiPropPathToGuiPropPath(EiPropPath $eiPropPath) {
		if ($eiPropPath->hasMultipleIds()) {
			throw new NotYetImplementedException();
		}
		
		$id = $eiPropPath->getFirstId();
		if (isset($this->guiProps[$id])) {
			return new GuiPropPath([$id]);
		}
		
		return null;
	}
	
// 	/**
// 	 * @param GuiPropPath $eiPropPath
// 	 * @return \rocket\ei\EiPropPath|NULL
// 	 */
// 	public function eiPropPathToEiPropPath(GuiPropPath $eiPropPath) {
// 		$ids = $eiPropPath->toArray();
// 		$guiDefinition = $this;
// 		while (null !== ($id = array_shift($ids))) {
// 			if (empty($ids)) {
// 				return $guiDefinition->getLevelEiPropPathById($id);
// 			}
				
// 			$guiDefinition = $guiDefinition->getGuiPropFork($id)->getForkedGuiDefinition();
// 		}
		
// 		return null;
// 	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @param GuiPropPath $guiPropPath
	 * @return \rocket\ei\manage\gui\EiFieldAbstraction|null
	 */
	public function determineEiFieldAbstraction(EiEntry $eiEntry, GuiPropPath $guiPropPath) {
		$ids = $guiPropPath->toArray();
		$id = array_shift($ids);
		if (empty($ids)) {
			return $eiEntry->getEiFieldWrapper(new EiPropPath(array($id)));
		}
		
		$guiPropFork = $this->getGuiPropFork($id);
		$eiFieldWrapper = $guiPropFork->determineEiFieldAbstraction($eiEntry, new GuiPropPath(array($ids)));
		ArgUtils::valTypeReturn($eiFieldWrapper, EiFieldAbstraction::class, $guiPropFork, 'determineEiFieldAbstraction', true);
		return $eiFieldWrapper;
	}
	
	public function getGuiPropForks() {
		return $this->guiPropForks;
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	private function createDefaultIdentityString(EiObject $eiObject, N2nContext $n2nContext, N2nLocale $n2nLocale) {
		$eiType = $eiObject->getEiEntityObj()->getEiType();
		
		$idPatternPart = null;
		$namePatternPart = null;
		
		foreach ($this->getStringRepresentableGuiProps() as $eiPropPathStr => $guiProp) {
			if ($eiPropPathStr == $eiType->getEntityModel()->getIdDef()->getPropertyName()) {
				$idPatternPart = SummarizedStringBuilder::createPlaceholder($eiPropPathStr);
			} else {
				$namePatternPart = SummarizedStringBuilder::createPlaceholder($eiPropPathStr);
			}
			
			if ($namePatternPart !== null) break;
		}
		
		if ($idPatternPart === null) {
			$idPatternPart = $eiObject->getEiEntityObj()->hasId() ?
			$eiType->idToPid($eiObject->getEiEntityObj()->getId()) : 'new';
		}
		
		if ($namePatternPart === null) {
			$namePatternPart = $this->labelLstr->t($n2nLocale);
		}
		
		return $this->createIdentityStringFromPattern($namePatternPart . ' #' . $idPatternPart, $n2nContext, $eiObject, $n2nLocale);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(EiObject $eiObject, N2nContext $n2nContext, N2nLocale $n2nLocale) {
		if ($this->identityStringPattern === null) {
			return $this->createDefaultIdentityString($eiObject, $n2nContext, $n2nLocale);
		}
		
		return $this->createIdentityStringFromPattern($this->identityStringPattern, $n2nContext, $eiObject, $n2nLocale);
	}
	
	/**
	 * @param $entity
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityStringFromPattern(string $identityStringPattern, N2nContext $n2nContext, EiObject $eiObject, N2nLocale $n2nLocale): string {
		$builder = new SummarizedStringBuilder($identityStringPattern, $n2nContext, $n2nLocale);
		$builder->replaceFields(array(), $this, $eiObject);
		return $builder->__toString();
	}
	
	public function getStringRepresentableGuiProps() {
		return $this->filterStringRepresentableGuiProps($this, array());
	}
	
	private function filterStringRepresentableGuiProps(GuiDefinition $guiDefinition, array $baseIds) {
		$guiProps = array();
		
		foreach ($guiDefinition->getGuiProps() as $id => $guiProp) {
			if (!$guiProp->isStringRepresentable()) continue;
			
			$ids = $baseIds;
			$ids[] = EiPropPath::create($id);
			$guiProps[(string) new GuiPropPath($ids)] = $guiProp;
		}
		
		foreach ($guiDefinition->getGuiPropForks() as $id => $guiPropFork) {
			$forkedGuiDefinition = $guiPropFork->getForkedGuiDefinition();
			
			if ($forkedGuiDefinition === null) continue;
			
			$ids = $baseIds;
			$ids[] = EiPropPath::create($id);
			$guiProps = array_merge($guiProps, $this->filterStringRepresentableGuiProps($forkedGuiDefinition, $ids));
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