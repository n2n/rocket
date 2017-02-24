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
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\mapping\EiMapping;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\mapping\MappableWrapper;

class GuiDefinition {	
	private $levelGuiFields = array();
	private $levelEiFieldPaths = array();
	private $levelGuiFieldForks = array();
	private $levelIds = array();
	
	/**
	 * @param unknown $id
	 * @param GuiField $guiField
	 * @param EiFieldPath $eiFieldPath
	 * @throws GuiException
	 */
	public function putLevelGuiField(string $id, GuiField $guiField, EiFieldPath $eiFieldPath) {
		if (isset($this->levelGuiFields[$id])) {
			throw new GuiException('GuiField with id \'' . $id . '\' is already registered');
		}
		
		$this->levelGuiFields[$id] = $guiField;
		$this->levelEiFieldPaths[$id] = $eiFieldPath;
		$this->levelIds[$id] = $id;
	}
	
	/**
	 * @param unknown $id
	 * @return bool
	 */
	public function containsLevelGuiFieldId(string $id) {
		return isset($this->levelGuiFields[$id]);
	}
	
	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiField
	 */
	public function getLevelGuiFieldById(string $id) {
		if (!isset($this->levelGuiFields[$id])) {
			throw new GuiException('No GuiField with id \'' . $id . '\' registered');
		}
		
		return $this->levelGuiFields[$id];
	}

	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiField
	 */
	public function getLevelEiFieldPathById(string $id): EiFieldPath {
		$this->getLevelGuiFieldById($id);
		return $this->levelEiFieldPaths[$id];
	}
	
	/**
	 * @return GuiField[]
	 */
	public function getLevelGuiFields() {
		return $this->levelGuiFields;
	}
	
	/**
	 * @param string $id
	 * @param GuiFieldFork $guiFieldFork
	 */
	public function putLevelGuiFieldFork(string $id, GuiFieldFork $guiFieldFork) {
		$this->levelGuiFieldForks[$id] = $guiFieldFork;
		$this->levelIds[$id] = $id;
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsLevelGuiFieldForkId(string $id) {
		return isset($this->levelGuiFieldForks[$id]);
	}
	
	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiFieldFork
	 */
	public function getLevelGuiFieldForkById(string $id) {
		if (!isset($this->levelGuiFieldForks[$id])) {
			throw new GuiException('No GuiFieldFork with id \'' . $id . '\' registered.');
		}
		
		return $this->levelGuiFieldForks[$id];
	}
	
	public function filterGuiIdPaths($viewMode = null) {
		return $this->buildGuiIdPaths(array(), $viewMode);
	}
	
	public function getGuiFields() {
		return $this->buildGuiFields(array());
	}
	
	protected function buildGuiFields(array $baseIds) {
		$guiFields = array();
		
		foreach ($this->levelIds as $id) {
			if (isset($this->levelGuiFields[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
				$guiFields[(string) new GuiIdPath($currentIds)] = $this->levelGuiFields[$id];
			}
				
			if (isset($this->levelGuiFieldForks[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
					
				$guiFields = array_merge($guiFields, $this->levelGuiFieldForks[$id]->getForkedGuiDefinition()
						->buildGuiFields($currentIds));
			}
		}
		
		return $guiFields;
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
			if (isset($this->levelGuiFields[$id]) && $this->levelGuiFields[$id]->getDisplayDefinition()
					->isViewModeDefaultDisplayed($viewMode)) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
				$guiIdPaths[] = new GuiIdPath($currentIds);
			}
			
			if (isset($this->levelGuiFieldForks[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
					
				$guiIdPaths = array_merge($guiIdPaths, $this->levelGuiFieldForks[$id]->getForkedGuiDefinition()
						->buildGuiIdPaths($currentIds, $viewMode));
			}			
		}
		
		return $guiIdPaths;
		
// 		foreach ($this->guiFields as $id => $guiField) {
// 			if (!$guiField->getDisplayDefinition()->isViewModeDefaultDisplayed($viewMode)) continue;

// 			$currentIds = $baseIds;
// 			$currentIds[] = $id;
// 			$guiIdPaths[] = new GuiIdPath($currentIds);
// 		}
		
// 		foreach ($this->guiFieldForks as $id => $guiFieldFork) {
// 			$currentIds = $baseIds;
// 			$currentIds[] = $id;
			
// 			$guiIdPaths = array_merge($guiIdPaths, $guiFieldFork->getForkedGuiDefinition()->buildGuiIdPaths($currentIds, $viewMode));
// 		}
		
// 		return $guiIdPaths;
	}
	
	public function getGuiFieldByGuiIdPath(GuiIdPath $guiIdPath) {
		$ids = $guiIdPath->toArray();
		$guiDefinition = $this;
		while (null !== ($id = array_shift($ids))) {
			if (empty($ids)) {
				return $guiDefinition->getLevelGuiFieldById($id);
			}
			
			$guiDefinition = $guiDefinition->getLevelGuiFieldForkById($id)->getForkedGuiDefinition();
		}	
	}
	
	public function guiIdPathToEiFieldPath(GuiIdPath $guiIdPath) {
		$ids = $guiIdPath->toArray();
		$guiDefinition = $this;
		while (null !== ($id = array_shift($ids))) {
			if (empty($ids)) {
				return $guiDefinition->getLevelEiFieldPathById($id);
			}
				
			$guiDefinition = $guiDefinition->getLevelGuiFieldForkById($id)->getForkedGuiDefinition();
		}
	}
	
	public function determineMappableWrapper(EiMapping $eiMapping, GuiIdPath $guiIdPath) {
		$ids = $guiIdPath->toArray();
		$id = array_shift($ids);
		if (empty($ids)) {
			return $eiMapping->getMappableWrapper(new EiFieldPath(array($id)));
		}
		
		$guiFieldFork = $guiDefinition->getLevelGuiFieldForkById($id);
		$mappableWrapper = $guiFieldFork->determineMappableWrapper($eiMapping, $guiIdPath);
		ArgUtils::valTypeReturn($mappableWrapper, MappableWrapper::class, $guiFieldFork, 'determineMappableWrapper', true);
		return $mappableWrapper;
	}
	
	public function getGuiFieldForks() {
		return $this->levelGuiFieldForks;
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
	
	public function getSummarizableGuiFields() {
		return $this->filterSummarizableGuiFields($this, array());
	}
	
	private function filterSummarizableGuiFields(GuiDefinition $guiDefinition, array $baseIds) {
		$guiFields = array();
		
		foreach ($guiDefinition->getLevelGuiFields() as $id => $guiField) {
			if (!$guiField->isStringRepresentable()) continue;
			
			$ids = $baseIds;
			$ids[] = $id;
			$guiFields[(string) new GuiIdPath($ids)] = $guiField;
		}
		
		foreach ($guiDefinition->getGuiFieldForks() as $id => $guiFieldFork) {
			$ids = $baseIds;
			$ids[] = $id;
			$guiFields = array_merge($guiFields, $this->filterSummarizableGuiFields(
					$guiFieldFork->getForkGuiDefinition(), $ids));
		}
		
		return $guiFields;
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
		foreach ($guiDefinition->getLevelGuiFields() as $id => $guiField) {
			if (!$guiField->isStringRepresentable()) continue;

			$placeholder = $this->buildPlaceholder($baseIds, $id);
			if (false === strpos($this->identityStringPattern, $placeholder)) continue;
			
			$this->placeholders[] = $placeholder;
			if ($eiObject === null) {
				$this->replacements[] = '';
			} else {
				$this->replacements[] = $guiField->buildIdentityString($eiObject, $this->n2nLocale);
			}
		}
		
		foreach ($guiDefinition->getGuiFieldForks() as $id => $guiFieldFork) {
			$forkedMappableSource = null;
			if ($eiObject !== null) {
				$forkedMappableSource = $guiFieldFork->determineForkedEiObject($eiObject);
			}
			
			$ids = $baseIds;
			$ids[] = $id;
			$this->replaceFields($ids, $guiFieldFork->getForkedGuiDefinition(), $forkedMappableSource);
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
