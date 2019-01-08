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
use rocket\ei\util\Eiu;
use n2n\util\ex\NotYetImplementedException;
use n2n\l10n\Lstr;
use rocket\core\model\Rocket;
use n2n\core\container\N2nContext;
use rocket\ei\manage\entry\UnknownEiFieldExcpetion;
use n2n\util\type\ArgUtils;

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
	 * @param EiPropPath $guiFieldPath
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
	 * @param GuiFieldPath $guiFieldPath
	 */
	public function removeGuiPropByPath(GuiFieldPath $guiFieldPath) {
		$guiDefinition = $this;
		$eiPropPaths = $guiFieldPath->toArray();
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
	
	protected function buildGuiProps(array $baseEiPropPaths) {
		$guiProps = array();
		
		foreach ($this->eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			if (isset($this->guiProps[$eiPropPathStr])) {
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				$guiProps[(string) new GuiFieldPath($currentEiPropPaths)] = $this->guiProps[$eiPropPathStr];
			}
				
			if (isset($this->guiPropForks[$eiPropPath])) {
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
					
				$guiProps = array_merge($guiProps, $this->guiPropForks[$eiPropPathStr]->getForkedGuiDefinition()
						->buildGuiProps($currentEiPropPaths));
			}
		}
		
		return $guiProps;
	}
	
	/**
	 * @deprecated use {@see GuiDefinition::getGuiFieldPaths()}
	 * @return \rocket\ei\manage\gui\GuiFieldPath[]
	 */
	public function getAllGuiFieldPaths() {
		return $this->getGuiFieldPaths();
	}
	
	/**
	 * @param GuiFieldPath[] $guiFieldPaths
	 * @return GuiFieldPath[]
	 */
	public function filterGuiFieldPaths(array $guiFieldPaths) {
		return array_filter($guiFieldPaths, function (GuiFieldPath $guiFieldPath) {
			return $this->containsGuiProp($guiFieldPath);
		});
	}
	
	/**
	 * @return \rocket\ei\manage\gui\GuiFieldPath[]
	 */
	public function getGuiFieldPaths() {
		return $this->buildGuiFieldPaths(array());
	}
	
	protected function buildGuiFieldPaths(array $baseEiPropPaths) {
		$guiFieldPaths = array();
		
		foreach ($this->eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			
			if (isset($this->guiProps[$eiPropPathStr])) {
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				$guiFieldPaths[] = new GuiFieldPath($currentEiPropPaths);
			}
			
			if (isset($this->guiPropForks[$eiPropPathStr])) {
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				
				$guiFieldPaths = array_merge($guiFieldPaths, $this->guiPropForks[$eiPropPathStr]->getForkedGuiDefinition()
						->buildGuiFieldPaths($currentEiPropPaths));
			}
		}
		
		return $guiFieldPaths;
	}
	
	public function assembleDefaultGuiProps(EiGui $eiGui) {
		$guiPropAssemblies = [];
		$this->composeGuiPropAssemblies($guiPropAssemblies, [], new Eiu($eiGui));
		return $guiPropAssemblies;
	}
	
	public function assembleGuiProps(EiGui $eiGui, array $guiFieldPaths) {
		ArgUtils::valArray($guiFieldPaths, GuiFieldPath::class);
		
		$eiu = new Eiu($eiGui);
		
		$guiPropAssemblies = [];
		
		foreach ($guiFieldPaths as $guiFieldPath) {
			$guiProp = $this->getGuiPropByGuiFieldPath($guiFieldPath);
			
			$displayDefinition = $guiProp->buildDisplayDefinition($eiu);
			if ($displayDefinition === null) {
				continue;
			} 
			
			$guiPropAssemblies[(string) $guiFieldPath] = new GuiPropAssembly($guiProp, $guiFieldPath, 
					$displayDefinition);
		}
		
		return $guiPropAssemblies;
	}
	
	
	/**
	 * @param array $baseEiPropPaths
	 * @param Eiu $eiu
	 * @param int $minTestLevel
	 */
	protected function composeGuiPropAssemblies(array &$guiPropAssemblies, array $baseEiPropPaths, Eiu $eiu) {
		foreach ($this->eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			
			$displayDefinition = null;
			if (isset($this->guiProps[$eiPropPathStr])
					&& null !== ($displayDefinition = $this->guiProps[$eiPropPathStr]->buildDisplayDefinition($eiu))
					&& $displayDefinition->isDefaultDisplayed()) {
						
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				
				$guiFieldPath = new GuiFieldPath($currentEiPropPaths);
				$guiPropAssemblies[(string) $guiFieldPath] = new GuiPropAssembly($this->guiProps[$eiPropPathStr], 
						$guiFieldPath, $displayDefinition);
			}
			
			if (isset($this->guiPropForks[$eiPropPathStr])
					&& null !== ($forkedGuiDefinition = $this->guiPropForks[$eiPropPathStr]->getForkedGuiDefinition())) {
				$currentEiPropPaths = $baseEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				$forkedGuiDefinition->composeGuiPropAssemblies($guiPropAssemblies, $currentEiPropPaths, $eiu);
			}
		}
	}
	
// 	public function createDefaultDisplayStructure(EiGui $eiGui) {
// 		$displayStructure = new DisplayStructure();
// 		$this->composeDisplayStructure($displayStructure, array(), new Eiu($eiGui));
// 		return $displayStructure;
// 	}
	

	
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return \rocket\ei\manage\gui\GuiProp
	 * @throws GuiException
	 */
	public function getGuiPropByGuiFieldPath(GuiFieldPath $guiFieldPath) {
		$ids = $guiFieldPath->toArray();
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
		
		throw new GuiException('GuiFieldPath could not be resolved: ' . $guiFieldPath);
	}
	
	public function containsGuiProp(GuiFieldPath $guiFieldPath) {
		$eiPropPaths = $guiFieldPath->toArray();
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
	 * @deprecated
	 * @param EiPropPath $eiPropPath
	 * @throws NotYetImplementedException
	 * @return \rocket\ei\manage\gui\GuiFieldPath|NULL
	 */
	public function eiPropPathToGuiFieldPath(EiPropPath $eiPropPath) {
		if ($eiPropPath->hasMultipleIds()) {
			throw new NotYetImplementedException();
		}
		
		$id = $eiPropPath->getFirstId();
		if (isset($this->guiProps[$id])) {
			return new GuiFieldPath([EiPropPath::create($id)]);
		}
		
		return null;
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @param GuiFieldPath $guiFieldPath
	 * @throws UnknownEiFieldExcpetion
	 * @return \rocket\ei\manage\gui\EiFieldAbstraction|null
	 */
	public function determineEiFieldAbstraction(N2nContext $n2nContext, EiEntry $eiEntry, GuiFieldPath $guiFieldPath) {
		$eiFieldPaths = $guiFieldPath->toArray();
		$id = array_shift($eiFieldPaths);
		if (empty($eiFieldPaths)) {
			return $eiEntry->getEiFieldWrapper($id);
		}
		
		$guiPropFork = $this->getGuiPropFork($id);
		return $guiPropFork->determineEiFieldAbstraction(new Eiu($n2nContext, $eiEntry), new GuiFieldPath($eiFieldPaths));
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
			$guiProps[(string) new GuiFieldPath($ids)] = $guiProp;
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
	
	/**
	 * @return GuiDefinitionListener[]
	 */
	public function getGuiDefinitionListeners() {
		return $this->guiDefinitionListeners;
	}
}