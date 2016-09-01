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

class GuiDefinition {	
	private $guiFields = array();
	private $eiFieldPaths = array();
	private $guiFieldForks = array();
	private $ids = array();
	
	/**
	 * @param unknown $id
	 * @param GuiField $guiField
	 * @param EiFieldPath $eiFieldPath
	 * @throws GuiException
	 */
	public function putGuiField($id, GuiField $guiField, EiFieldPath $eiFieldPath) {
		if (isset($this->guiFields[$id])) {
			throw new GuiException('GuiField with id \'' . $id . '\' is already registered');
		}
		
		$this->guiFields[$id] = $guiField;
		$this->eiFieldPaths[$id] = $eiFieldPath;
		$this->ids[$id] = $id;
	}
	
	/**
	 * @param unknown $id
	 * @return bool
	 */
	public function containsGuiFieldId($id) {
		return isset($this->guiFields[$id]);
	}
	
	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiField
	 */
	public function getGuiFieldById(string $id) {
		if (!isset($this->guiFields[$id])) {
			throw new GuiException('No GuiField with id \'' . $id . '\' registered');
		}
		
		return $this->guiFields[$id];
	}

	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiField
	 */
	public function getEiFieldPathById($id): EiFieldPath {
		$this->getGuiFieldById($id);
		return $this->eiFieldPaths[$id];
	}
	
	/**
	 * @return GuiField[]
	 */
	public function getGuiFields() {
		return $this->guiFields;
	}
	
	/**
	 * @param string $id
	 * @param GuiFieldFork $guiFieldFork
	 */
	public function putGuiFieldFork($id, GuiFieldFork $guiFieldFork) {
		$this->guiFieldForks[$id] = $guiFieldFork;
		$this->ids[$id] = $id;
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsGuiFieldForkId($id) {
		return isset($this->guiFieldForks[$id]);
	}
	
	/**
	 * @param string $id
	 * @throws GuiException
	 * @return GuiFieldFork
	 */
	public function getGuiFieldForkById($id) {
		if (!isset($this->guiFieldForks[$id])) {
			throw new GuiException('No GuiFieldFork with id \'' . $id . '\' registered.');
		}
		
		return $this->guiFieldForks[$id];
	}
	
	public function filterGuiIdPaths($viewMode = null) {
		return $this->buildGuiIdPaths(array(), $viewMode);
	}
	
	public function getAllGuiIdPaths() {
		return $this->filterGuiIdPaths(DisplayDefinition::ALL_VIEW_MODES);
	}
	
	protected function buildGuiIdPaths(array $baseIds, $viewMode) {
		$guiIdPaths = array();
		
		foreach ($this->ids as $id) {
			if (isset($this->guiFields[$id]) && $this->guiFields[$id]->getDisplayDefinition()
					->isViewModeDefaultDisplayed($viewMode)) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
				$guiIdPaths[] = new GuiIdPath($currentIds);
			}
			
			if (isset($this->guiFieldForks[$id])) {
				$currentIds = $baseIds;
				$currentIds[] = $id;
					
				$guiIdPaths = array_merge($guiIdPaths, $this->guiFieldForks[$id]->getForkedGuiDefinition()
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
				return $guiDefinition->getGuiFieldById($id);
			}
			
			$guiDefinition = $guiDefinition->getGuiFieldForkById($id)->getForkedGuiDefinition();
		}	
	}
	
	public function guiIdPathToEiFieldPath(GuiIdPath $guiIdPath) {
		$ids = $guiIdPath->toArray();
		$guiDefinition = $this;
		while (null !== ($id = array_shift($ids))) {
			if (empty($ids)) {
				return $guiDefinition->getEiFieldPathById($id);
			}
				
			$guiDefinition = $guiDefinition->getGuiFieldForkById($id)->getForkedGuiDefinition();
		}
	}
	
	public function getGuiFieldForks() {
		return $this->guiFieldForks;
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
		
		foreach ($guiDefinition->getGuiFields() as $id => $guiField) {
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
		foreach ($guiDefinition->getGuiFields() as $id => $guiField) {
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
				$forkedMappableSource = $guiFieldFork->determineForkedMappableSource($eiObject);
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
