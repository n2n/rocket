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
namespace rocket\spec\ei\manage;

use n2n\web\ui\view\impl\html\HtmlView;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\ui\view\impl\html\HtmlUtils;
use rocket\spec\ei\manage\model\EntryModel;
use n2n\util\ex\IllegalStateException;
use n2n\web\ui\view\impl\html\HtmlElement;
use n2n\web\ui\Raw;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\Displayable;
use rocket\spec\ei\manage\model\EntryGuiModel;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\mapping\EiMapping;
use n2n\web\ui\UiComponent;
use rocket\spec\ei\manage\mapping\FieldErrorInfo;
use rocket\spec\ei\manage\util\model\EiStateUtils;

class EntryEiHtmlBuilder {
	private $view;
	private $html;
	private $formHtml;
	private $fieldEiHtml;
	
	private $guiDefinition;
	private $eiState;
	private $entryUtils;
	private $entryGuis;
	private $meta;
	
	public function __construct(HtmlView $view, EiState $eiState, array $entryGuis) {
		$this->view = $view;
		$this->html = $view->getHtmlBuilder();
		$this->formHtml = $view->getFormHtmlBuilder();
		$this->fieldEiHtml = new FieldEiHtmlBuilder($view);
		
		$this->eiState = $eiState;
		$this->entryUtils = new EiStateUtils($this->eiState);
		$this->entryGuis = $entryGuis;
		$this->meta = new EiHtmlBuilderMeta($entryGuis);
	}
	
	public function meta() {
		return $this->meta;
	}
	
	private function pushGuiFieldInfo($tagName, Displayable $displayable, FieldErrorInfo $fieldErrorInfo, 
			PropertyPath $propertyPath = null) {
		$this->eiFieldInfoStack[] = array('tagName' => $tagName, 'displayable' => $displayable, 
				'fieldErrorInfo' => $fieldErrorInfo, 'propertyPath' => $propertyPath);
	}
	
	public function peakEiFieldInfo($pop) {
		if (!sizeof($this->eiFieldInfoStack)) {
			throw new IllegalStateException('No EiField open');
		}

		if ($pop) {
			return array_pop($this->eiFieldInfoStack);
		} else {
			return end($this->eiFieldInfoStack);
		}
	}
	
	public function selector(string $containerTagName, array $containerAttrs = null, $content = '') {
		$this->view->out($this->getSelector($containerTagName, $containerAttrs, $content));
	}
	
	public function getSelector(string $containerTagName, array $containerAttrs = null, $content = ''): UiComponent {
		$eiSelection = $this->meta->getCurrentEiMapping()->getEiSelection();
		$draftId = null;
		if ($eiSelection->isDraft() && !$eiSelection->getDraft()->isNew()) {
			$draftId = $eiSelection->getDraft()->getId();
		}
		
		return new HtmlElement($containerTagName,
				HtmlUtils::mergeAttrs(array('class' => 'rocket-entry-selector', 
						'data-entry-id-rep' => $this->meta()->getCurrentIdRep(),
						'data-draft-id' => ($draftId !== null ? $draftId : ''),
						'data-identity-string' => $this->entryUtils->createIdentityString($this->meta()
								->getCurrentEiMapping()->getEiSelection())), (array) $containerAttrs),
				new HtmlElement('input', array('type' => 'checkbox')));
	}
	
	private function buildAttrs(GuiIdPath $guiIdPath) {
		return array('class' => 'rocket-gui-field-' . implode('-', $guiIdPath->toArray()));
	}
	
	public function openInputField($tagName, $guiIdPath, array $attrs = null) {
		$this->view->out($this->getOpenInputField($tagName, $guiIdPath, $attrs));
	}
	
	public function getOpenInputField($tagName, $guiIdPath, array $attrs = null) {
		$entryGui = $this->meta->getCurrentEntryGui();
		$guiIdPath = GuiIdPath::createFromExpression($guiIdPath);
		
		$entryGuiModel = $entryGui->getEntryGuiModel();
		$eiSelectionGui = $entryGuiModel->getEiSelectionGui();
		$displayable = $eiSelectionGui->getDisplayableByGuiIdPath($guiIdPath);
		$fieldErrorInfo = $entryGuiModel->getEiMapping()->getMappingErrorInfo()->getFieldErrorInfo(
				$eiSelectionGui->getGuiDefinition()->guiIdPathToEiFieldPath($guiIdPath));
		
		if (!$eiSelectionGui->containsEditableInfoGuiIdPath($guiIdPath)) {
			return $this->fieldEiHtml->getOpenOutputField($tagName, $displayable, $fieldErrorInfo, 
					$this->buildAttrs($guiIdPath));
		}
		
		$editableInfo = $eiSelectionGui->getEditableInfoByGuiIdPath($guiIdPath);
		$propertyPath = $entryGui->getEntryPropertyPath()->ext($editableInfo->getMagPropertyPath());
				
		$this->pushGuiFieldInfo($tagName, $displayable, $fieldErrorInfo, $propertyPath);
		return $this->fieldEiHtml->getOpenInputField($tagName, $propertyPath, $fieldErrorInfo, 
				$this->buildAttrs($guiIdPath), $editableInfo->isMandatory());
	}
		
	public function openOutputField($tagName, $guiIdPath, array $attrs = null) {
		$this->view->out($this->getOpenOutputField($tagName, $guiIdPath, $attrs));
	}
	
	public function getOpenOutputField($tagName, $guiIdPath, array $attrs = null) {
		$entryGuiModel = $this->meta->getCurrentEntryGuiModel();
		$guiIdPath = GuiIdPath::createFromExpression($guiIdPath);
		$displayable = $entryGuiModel->getEiSelectionGui()->getDisplayableByGuiIdPath($guiIdPath);
		$fieldErrorInfo = $entryGuiModel->getEiMapping()->getMappingErrorInfo()->getFieldErrorInfo(
				$entryGuiModel->getEiSelectionGui()->getGuiDefinition()->guiIdPathToEiFieldPath($guiIdPath));
		
		return $this->fieldEiHtml->getOpenOutputField($tagName, $displayable, $fieldErrorInfo, 
				$this->buildAttrs($guiIdPath));
	}
	
	public function closeField() {
		$this->view->out($this->getCloseField());
	}
	
	public function getCloseField() {
		return $this->fieldEiHtml->getCloseField();
	}
	
	public function label(array $attrs = null, $label = null) {
		$this->html->out($this->getLabel($attrs, $label));
	}
	
	public function getLabel(array $attrs = null, $label = null) {
		return $this->fieldEiHtml->getLabel($attrs, $label);
	} 
	
	public function field() {
		$this->html->out($this->getField());
	}
	
	public function getField() {
		return $this->fieldEiHtml->getField();
	}
	
	public function message() {
		$this->html->out($this->getMessage());
	}
	
	public function getMessage() {
		return $this->fieldEiHtml->getMessage();
	}
	
	public function overallControlList() {
		$this->html->out($this->getOverallControlList());
	}
	
	public function getOverallControlList() {
		$ul = new HtmlElement('ul'/*, array('class' => 'rocket-main-controls')*/);
		foreach ($this->eiState->getContextEiMask()->createOverallHrefControls($this->eiState, $this->view) as $control) {
			$ul->appendContent(new HtmlElement('li', null, $control->toButton(false)));
		}
	
		return $ul;
	}
	
// 	public function entryControlList($useIcons = false) {
// 		$this->html->out($this->getEntryControlList($useIcons));
// 	}
	
// 	public function getEntryControlList($useIcons = false) {
// 		$entryControls = $this->eiState->getContextEiMask()->createEntryHrefControls($this->eiState, 
// 				$this->meta->getCurrentEntryGuiModel()->getEiMapping(), $this->view);
	
// 		$ulHtmlElement = new HtmlElement('ul', array('class' => ($useIcons ? 'rocket-simple-controls' : null /* 'rocket-main-controls' */)));
	
// 		foreach ($entryControls as $control) {
// 			$liHtmlElement = new HtmlElement('li', null, $control->toButton($useIcons));
// 			$ulHtmlElement->appendContent($liHtmlElement);
// 		}
	
// 		return $ulHtmlElement;
// 	}
}

class EiHtmlBuilderMeta {
	private $currentEntryGui;
	private $entryGuis;
	private $entryPropertyPaths;
	
	public function __construct(array $entryGuis) {
		ArgUtils::valArray($entryGuis, EntryGui::class);
		$this->entryGuis = $entryGuis;
// 		$this->currentEntryModel = array_shift($this->entryModels);
	}
	
	public function getCurrentIdRep() {
		return $this->getCurrentEntryGuiModel()->getEiMapping()->getIdRep();
	}
	
	public function getForkMagPropertyPaths() {
		return $this->getCurrentEntryGuiModel()->getEiSelectionGui()->getForkMagPropertyPaths();
	}
	
	public function getCurrentEntryGui(): EntryGui {
		if ($this->currentEntryGui === null) {
			if (empty($this->entryGuis)) {
				throw new IllegalStateException('No EntryGui selected');
			}
			$this->next();
		}
		
		return $this->currentEntryGui;
	}
	
	public function getCurrentEiMapping(): EiMapping {
		return $this->getCurrentEntryGuiModel()->getEiMapping();
	}
	
	/**
	 * @throws IllegalStateException
	 * @return EntryModel
	 */
	public function getCurrentEntryGuiModel(): EntryGuiModel {
		return $this->getCurrentEntryGui()->getEntryGuiModel();
	}
	
	public function getCurrentEntryPropertyPath() {
		return $this->getCurrentEntryGui()->getEntryPropertyPath();
	}
	
	public function next() {
		$this->currentEntryGui = array_shift($this->entryGuis);
		return $this->currentEntryGui !== null;
	}
}
