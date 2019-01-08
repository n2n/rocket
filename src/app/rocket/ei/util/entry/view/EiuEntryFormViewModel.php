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
namespace rocket\ei\util\entry\view;

use n2n\util\ex\IllegalStateException;
use n2n\web\dispatch\map\PropertyPathPart;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\gui\ui\DisplayItem;
use rocket\ei\util\entry\form\EiuEntryForm;
use rocket\ei\util\gui\EiuHtmlBuilder;
use n2n\impl\web\ui\view\html\HtmlSnippet;
use n2n\impl\web\ui\view\html\HtmlElement;
use rocket\ei\util\entry\form\EiuEntryTypeForm;
use n2n\web\ui\UiComponent;
use n2n\web\ui\view\View;
use n2n\impl\web\ui\view\html\HtmlUtils;
use rocket\ei\util\gui\EiuHtmlBuilderMeta;

class EiuEntryFormViewModel {
	private $eiuEntryForm;
	private $eiuEntryFormPropertyPath;
	private $displayContainerType = null;
	private $displayContainerLabel = null;
	private $displayContainerAttrs = null;
	private $grouped = false;
	
	public function __construct(EiuEntryForm $eiuEntryForm, bool $grouped, string $type = null, string $label = null) {
		$this->eiuEntryForm = $eiuEntryForm;
		$this->setGrouped($grouped);
		if ($type !== null && $label !== null) {
			$this->addDisplayContainer($label, $type);
		}
	}
	
	/**
	 * @return boolean
	 */
	public function hasDisplayContainer() {
		return $this->displayContainerType !== null;
	}
	
	/**
	 * @param string $label
	 * @param string $type
	 * @return EiuEntryFormViewModel
	 */
	public function addDisplayContainer(string $label, string $type = DisplayItem::TYPE_SIMPLE_GROUP, array $attrs = null) {
		$this->displayContainerType = $type;
		$this->displayContainerLabel = $label;
		$this->displayContainerAttrs = $attrs;
		return $this;
	}
	
	public function removeDisplayContainer() {
		$this->displayContainerType = null;
		$this->displayContainerLabel = null;
		$this->displayContainerAttrs = null;
		return $this;
	}
	
	public function getDisplayContainerType() {
		return $this->displayContainerType;
	}
	
	public function getDisplayContainerLabel() {
		return $this->displayContainerLabel;
	}
	
	public function getDisplayContainerAttrs() {
		return $this->displayContainerAttrs;
	}
	
	public function isGrouped() {
		return $this->grouped;
	}
	
	public function setGrouped(bool $grouped) {
		$this->grouped = $grouped;
	}
	
// 	public function initFromView(HtmlView $view) {
// 		$mappingResult = $view->getFormHtmlBuilder()->meta()->getMapValue($this->eiuEntryFormPropertyPath);
// 		$view->assert($mappingResult instanceof MappingResult);
		
// 		$eiuEntryForm = $mappingResult->getObject();
// 		$view->assert($eiuEntryForm instanceof EiuEntryForm);
		
// 		$this->eiuEntryForm = $eiuEntryForm;
// 	}
	
	/**
	 * @throws IllegalStateException
	 * @return EiuEntryForm
	 */
	public function getEiuEntryForm(): EiuEntryForm {
		if ($this->eiuEntryForm === null) {
			throw new IllegalStateException('EiuEntryFormViewModel not initialized.');	
		}
		
		return $this->eiuEntryForm;
	}
	
	public function getEiuEntryFormPropertyPath() {
		return $this->eiuEntryForm->getChosenEiuEntryTypeForm()->getEiuEntryGui()->getContextPropertyPath();
	}
	
	public function isTypeChangable() {
		return $this->getEiuEntryForm()->isChoosable();
	}
	
	public function getTypeChoicesMap() {
		return $this->getEiuEntryForm()->getChoicesMap();
	}
	
	public function getIconTypeMap() {
		$iconTypeMap = array();
		
		foreach ($this->eiuEntryForm->getEiuEntryTypeForms() as $eiTypeId => $eiuEntryTypeForm) {
			$iconTypeMap[$eiTypeId] = $eiuEntryTypeForm->getEiuEntryGui()->entry()->getGenericIconType();
		}
		
		return $iconTypeMap;
	}
	
	/**
	 * @return array
	 */
	private function createEntryAttrs(array $attrs = null) {
		$cattrs = (array) $this->displayContainerAttrs;
		
		if ($attrs !== null) {
			$cattrs = HtmlUtils::mergeAttrs($attrs, $cattrs);
		}
		
		return EiuHtmlBuilderMeta::createDisplayItemAttrs(
				$this->displayContainerType ?? DisplayItem::TYPE_SIMPLE_GROUP, 
				$cattrs);
	}
	
	/**
	 * @param HtmlView $contextView
	 * @return UiComponent
	 */
	public function createEditView(HtmlView $contextView) {
		$eiuEntryForm = $this->getEiuEntryForm();
		IllegalStateException::assertTrue(!$eiuEntryForm->isChoosable());
		
		$eiuEntryTypeForm = $eiuEntryForm->getChosenEiuEntryTypeForm();
		
		$eiTypeId = $eiuEntryForm->getChosenId();
		
		if (null !== ($contextPropertyPath = $this->eiuEntryForm->getContextPropertyPath())) {
			$eiuEntryTypeForm->getEiuEntryGui()->setContextPropertyPath($contextPropertyPath
					->ext(new PropertyPathPart('eiuEntryTypeForms', true, $eiTypeId))->ext('dispatchable'));
		}
		
		if ($eiuEntryForm->getEiuFrame()->getContextEiType()->hasSubEiTypes() || $this->displayContainerType !== null 
				|| $this->displayContainerLabel !== null || $this->displayContainerAttrs !== null) {
			$eiuEntryGui = $eiuEntryTypeForm->getEiuEntryGui();
			$eiuHtml = new EiuHtmlBuilder($contextView);
			
			$htmlSnippet = new HtmlSnippet();
			
			$htmlSnippet->appendLn($eiuHtml->getEntryOpen('div', $eiuEntryGui, null, $this->createEntryAttrs()));
			
			$htmlSnippet->appendLn(new HtmlElement('label', null, 
					$this->displayContainerLabel ?? $eiuEntryGui->entry()->getGenericLabel()));
			
			$this->groupTypeForm($eiuEntryTypeForm, $htmlSnippet, $contextView);
			
			$htmlSnippet->appendLn($eiuHtml->getEntryClose());
			
			return $htmlSnippet;
		} 
			
		if ($this->grouped) {
// 			$eiuEntryTypeForm->getEiuEntryGui()->forceRootGroups();
		}
		
		return $eiuEntryTypeForm->getEiuEntryGui()->createView($contextView);
	}
	
	public function createEditViews(HtmlView $contextView) {
		$eiuEntryForm = $this->getEiuEntryForm();
		IllegalStateException::assertTrue($eiuEntryForm->isChoosable());
	
		$eiuHtml = new EiuHtmlBuilder($contextView);
		
		$contextPropertyPath = $this->eiuEntryForm->getContextPropertyPath();
		$htmlSnippet = new HtmlSnippet();
		
		foreach ($eiuEntryForm->getEiuEntryTypeForms() as $eiTypeId => $eiuEntryTypeForm) {
			if ($contextPropertyPath !== null) {
				$eiuEntryTypeForm->getEiuEntryGui()->setContextPropertyPath($contextPropertyPath->ext(
						new PropertyPathPart('eiuEntryTypeForms', true, $eiTypeId))->ext('dispatchable'));
			}
			
			$eiuEntryGui = $eiuEntryTypeForm->getEiuEntryGui();
			
			$htmlSnippet->appendLn($eiuHtml->getEntryOpen('div', $eiuEntryGui, DisplayItem::TYPE_SIMPLE_GROUP,
					$this->createEntryAttrs(['class' => 'rocket-ei-type-entry-form rocket-ei-type-' . $eiTypeId])));
			
			$htmlSnippet->appendLn(new HtmlElement('label', null, $eiuEntryGui->entry()->getGenericLabel()));
			
			$this->groupTypeForm($eiuEntryTypeForm, $htmlSnippet, $contextView);
			
			$htmlSnippet->appendLn($eiuHtml->getEntryClose());
		}
		
		return $htmlSnippet;
	}
	
	
	/**
	 * @param EiuEntryTypeForm $eiuEntryTypeForm
	 * @param HtmlSnippet $htmlSnippet
	 * @param View $contextView
	 * @param string $eiTypeId
	 */
	private function groupTypeForm($eiuEntryTypeForm, $htmlSnippet, $contextView) {
		$eiuHtml = new EiuHtmlBuilder($contextView);
		$eiuEntryGui = $eiuEntryTypeForm->getEiuEntryGui();
		
		$eiuEntryGui->gui()->renderEntryControls(false);
		
		$htmlSnippet->appendLn(new HtmlElement('div', ['class' => 'rocket-control'], $eiuEntryGui->createView($contextView)));
	}
// 	private function buildTypeHtmlClasses(EiType $eiType, array $htmlClasses) {
// 		$htmlClasses[] = 'rocket-script-type-' . $eiType->getId();
// 		foreach ($eiType->getSubEiTypes() as $sub) {
// 			$htmlClasses = $this->buildTypeHtmlClasses($sub, $htmlClasses);
// 		}
// 		return $htmlClasses;
// 	}
	
// 	public function createTypeLevelEditView($eiTypeId) {
// 		$eiuEntryFormParts = $this->eiuEntryForm->getLevelEiuEntryFormParts();
// 		if (!isset($eiuEntryFormParts[$eiTypeId])) {
// 			throw new \InvalidArgumentException();
// 		}
		
// 		return $eiuEntryFormParts[$eiTypeId]->getGuiDefinition()->getEiMask()
// 				->createEditEntryView($eiuEntryFormParts[$eiTypeId], 
// 						$this->basePropertyPath->ext('levelEiuEntryFormParts')->fieldExt($eiTypeId));
// 	}
}
