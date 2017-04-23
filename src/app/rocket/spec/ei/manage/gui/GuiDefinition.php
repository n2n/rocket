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
use rocket\spec\ei\manage\mapping\EiMapping;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\mapping\EiFieldWrapper;

class GuiDefinition {	
	private $levelGuiProps = array();
	private $levelEiPropPaths = array();
	private $levelGuiPropForks = array();
	private $levelIds = array();
	
	/**
	 * @param unknown $id
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
	 * @param unknown $id
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
	 * @return GuiProp
	 */
	public function getLevelEiPropPathById(string $id): EiPropPath {
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
	
	public function filterGuiIdPaths($viewMode = null) {
		return $this->buildGuiIdPaths(array(), $viewMode);
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
		return $this->filterGuiIdPaths(DisplayDefinition::ALL_VIEW_MODES);
	}
	
	protected function buildGuiIdPaths(array $baseIds, $viewMode) {
		$guiIdPaths = array();
		
		foreach ($this->levelIds as $id) {
			if (isset($this->levelGuiProps[$id]) && $this->levelGuiProps[$id]->getDisplayDefinition()
					->isViewModeDefaultDisplayed($viewMode)) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
				$guiIdPaths[] = new GuiIdPath($currentIds);
			}
			
			if (isset($this->levelGuiPropForks[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
					
				$guiIdPaths = array_merge($guiIdPaths, $this->levelGuiPropForks[$id]->getForkedGuiDefinition()
						->buildGuiIdPaths($currentIds, $viewMode));
			}			
		}
		
		return $guiIdPaths;
		
// 		foreach ($this->guiProps as $id => $guiProp) {
// 			if (!$guiProp->getDisplayDefinition()->isViewModeDefaultDisplayed($viewMode)) continue;

// 			$currentIds = $baseIds;
// 			$currentIds[] = $id;
// 			$guiIdPaths[] = new GuiIdPath($currentIds);
// 		}
		
// 		foreach ($this->guiPropForks as $id => $guiPropFork) {
// 			$currentIds = $baseIds;
// 			$currentIds[] = $id;
			
// 			$guiIdPaths = array_merge($guiIdPaths, $guiPropFork->getForkedGuiDefinition()->buildGuiIdPaths($currentIds, $viewMode));
// 		}
		
// 		return $guiIdPaths;
	}
	
	public function getGuiPropByGuiIdPath(GuiIdPath $guiIdPath) {
		$ids = $guiIdPath->toArray();
		$guiDefinition = $this;
		while (null !== ($id = array_shift($ids))) {
			if (empty($ids)) {
				return $guiDefinition->getLevelGuiPropById($id);
			}
			
			$guiDefinition = $guiDefinition->getLevelGuiPropForkById($id)->getForkedGuiDefinition();
		}	
	}
	
	public function guiIdPathToEiPropPath(GuiIdPath $guiIdPath) {
		$ids = $guiIdPath->toArray();
		$guiDefinition = $this;
		while (null !== ($id = array_shift($ids))) {
			if (empty($ids)) {
				return $guiDefinition->getLevelEiPropPathById($id);
			}
				
			$guiDefinition = $guiDefinition->getLevelGuiPropForkById($id)->getForkedGuiDefinition();
		}
	}
	
	public function determineEiFieldWrapper(EiMapping $eiMapping, GuiIdPath $guiIdPath) {
		$ids = $guiIdPath->toArray();
		$id = array_shift($ids);
		if (empty($ids)) {
			return $eiMapping->getEiFieldWrapper(new EiPropPath(array($id)));
		}
		
		$guiPropFork = $guiDefinition->getLevelGuiPropForkById($id);
		$eiFieldWrapper = $guiPropFork->determineEiFieldWrapper($eiMapping, $guiIdPath);
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
	
	public function getSummarizableGuiProps() {
		return $this->filterSummarizableGuiProps($this, array());
	}
	
	private function filterSummarizableGuiProps(GuiDefinition $guiDefinition, array $baseIds) {
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
			$guiProps = array_merge($guiProps, $this->filterSummarizableGuiProps(
					$guiPropFork->getForkGuiDefinition(), $ids));
		}
		
		return $guiProps;
	}
	
	public function createGuiDefinition() {
		
	}
}

class QuickSortDefinitionFactory {
	
}

class SummarizedStringBuilder {
	const KNOWN_STRING_FIELD_OPEN_DELIMITER = '{';
	const KNOWN_STRING_FIELD_CLOSE_DELIMITER = '}';
	
	private $identityStringPattern;
	private $n2nLocale;
	
	private $placeholders = array();
	private $replacements = array();
	
	public function __construct(string $identityStringPattern, N2nLocale $n2nLocale) {
		$this->identityStringPattern = $identityStringPattern;
		$this->n2nLocale = $n2nLocale;
	}
	
	public function replaceFields(array $baseIds, GuiDefinition $guiDefinition, EiObject $eiObject = null) {
		foreach ($guiDefinition->getLevelGuiProps() as $id => $guiProp) {
			if (!$guiProp->isStringRepresentable()) continue;

			$placeholder = $this->buildPlaceholder($baseIds, $id);
			if (false === strpos($this->identityStringPattern, $placeholder)) continue;
			
			$this->placeholders[] = $placeholder;
			if ($eiObject === null) {
				$this->replacements[] = '';
			} else {
				$this->replacements[] = $guiProp->buildIdentityString($eiObject, $this->n2nLocale);
			}
		}
		
		foreach ($guiDefinition->getGuiPropForks() as $id => $guiPropFork) {
			$forkedEiFieldSource = null;
			if ($eiObject !== null) {
				$forkedEiFieldSource = $guiPropFork->determineForkedEiObject($eiObject);
			}
			
			$ids = $baseIds;
			$ids[] = $id;
			$this->replaceFields($ids, $guiPropFork->getForkedGuiDefinition(), $forkedEiFieldSource);
		}
	}
	
	private function buildPlaceholder(array $baseIds, $id) {
		$ids = $baseIds;
		$ids[] = $id;
		return self::KNOWN_STRING_FIELD_OPEN_DELIMITER . new GuiIdPath($ids)
				. self::KNOWN_STRING_FIELD_CLOSE_DELIMITER;
	}	
	
	public function __toString(): string {
		return str_replace($this->placeholders, $this->replacements, $this->identityStringPattern);
	}
}
