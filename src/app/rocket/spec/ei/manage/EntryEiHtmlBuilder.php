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

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlUtils;
use n2n\util\ex\IllegalStateException;
use n2n\impl\web\ui\view\html\HtmlElement;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\Displayable;
use rocket\spec\ei\manage\model\EntryGuiModel;
use n2n\reflection\ArgUtils;
use n2n\web\ui\UiComponent;
use rocket\spec\ei\manage\mapping\FieldErrorInfo;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\web\ui\Raw;
use n2n\web\ui\CouldNotRenderUiComponentException;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\manage\util\model\EiuEntryGui;
use rocket\spec\ei\manage\util\model\EiuFactory;

class EntryEiHtmlBuilder {
	private $view;
	private $html;
	private $formHtml;
	private $fieldEiHtml;
	
	private $guiDefinition;
	private $eiuFrame;
	private $eiEntryGuis;
	private $meta;
	
	public function __construct(HtmlView $view, $eiuFrame, array $eiuEntryGuis = null) {
		$this->view = $view;
		$this->html = $view->getHtmlBuilder();
		$this->formHtml = $view->getFormHtmlBuilder();
		$this->fieldEiHtml = new FieldEiHtmlBuilder($view);
		
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs($eiuFrame, $view->getN2nContext());
		
		$this->eiuFrame = $eiuFactory->getEiuFrame(true);
		if (empty($eiuEntryGuis) && null !== ($eiuEntryGui = $eiuFactory->getEiuEntryGui(false))) {
			$eiuEntryGuis = array($eiuEntryGui);
		}
		
		$this->meta = new EntryEiHtmlBuilderMeta((array) $eiuEntryGuis);
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
	
	private $openEntryTagName = null;
	
	public function entryOpen(string $tagName, array $attrs = null) {
		$this->view->out($this->getEntryOpen($tagName, $attrs));
	}
	
	public function getEntryOpen(string $tagName, array $attrs = null) {
		$this->openEntryTagName = $tagName;
		
		$entryAttrs = array('class' => 'rocket-entry');
		$eiEntry = $this->meta()->getCurrentEiuEntryGui()->getEiuEntry()->getEiEntry();
		if (!$eiEntry->isDraft()) {
			$entryAttrs['data-rocket-live-entry-id-rep'] = $this->meta()->getCurrentIdRep();
		} else {
			$entryAttrs['data-rocket-draft-id'] = $eiEntry->getDraft()->getId();
		}
		
		return new Raw('<' . htmlspecialchars($tagName) 
				. HtmlElement::buildAttrsHtml(HtmlUtils::mergeAttrs($entryAttrs, $attrs)) . '>');
	}
	
	public function entryClose() {
		$this->view->out($this->getEntryClose());
	}
	
	private function ensureEntryOpen() {
		if ($this->openEntryTagName === null) {
			throw new CouldNotRenderUiComponentException('No entry opened.');
		}
	}
	
	public function getEntryClose() {
		$this->ensureEntryOpen();
		
		return new Raw('</' . htmlspecialchars($this->openEntryTagName) . '>');
	}
	
	
	public function selector(string $containerTagName, array $containerAttrs = null, $content = '') {
		$this->view->out($this->getSelector($containerTagName, $containerAttrs, $content));
	}
	
	public function getSelector(string $containerTagName, array $containerAttrs = null, $content = ''): UiComponent {
		$eiEntry = $this->meta->getCurrentEiuEntryGui()->getEiuEntry()->getEiEntry();
		$draftId = null;
		if ($eiEntry->isDraft() && !$eiEntry->getDraft()->isNew()) {
			$draftId = $eiEntry->getDraft()->getId();
		}
		
		return new HtmlElement($containerTagName,
				HtmlUtils::mergeAttrs(array('class' => 'rocket-entry-selector', 
						'data-entry-id-rep' => $this->meta()->getCurrentIdRep(),
						'data-draft-id' => ($draftId !== null ? $draftId : ''),
						'data-identity-string' => $this->eiuFrame->createIdentityString($this->meta()
								->getCurrentEiuEntryGui()->getEiuEntry()->getEiEntry())), (array) $containerAttrs),
				new HtmlElement('input', array('type' => 'checkbox')));
	}
	
	private function buildAttrs(GuiIdPath $guiIdPath) {
		return array('class' => 'rocket-gui-field-' . implode('-', $guiIdPath->toArray()));
	}
	
	public function openInputField($tagName, $guiIdPath, array $attrs = null) {
		$this->view->out($this->getOpenInputField($tagName, $guiIdPath, $attrs));
	}
	
	public function getOpenInputField($tagName, $guiIdPath, array $attrs = null) {
		$eiuEntryGui = $this->meta->getCurrentEiuEntryGui();
		$guiIdPath = GuiIdPath::createFromExpression($guiIdPath);
		
		$eiEntryGui = $eiuEntryGui->getEiEntryGui();
		$displayable = $eiEntryGui->getDisplayableByGuiIdPath($guiIdPath);
		$fieldErrorInfo = $eiuEntryGui->getEiuEntry()->getEiMapping()->getMappingErrorInfo()->getFieldErrorInfo(
				$eiEntryGui->getGuiDefinition()->guiIdPathToEiFieldPath($guiIdPath));
		
		if (!$eiEntryGui->containsEditableWrapperGuiIdPath($guiIdPath)) {
			return $this->fieldEiHtml->getOpenOutputField($tagName, $displayable, $fieldErrorInfo, 
					$this->buildAttrs($guiIdPath));
		}
		
		$editableInfo = $eiEntryGui->getEditableWrapperByGuiIdPath($guiIdPath);
		$propertyPath = $this->meta->getContextPropertyPath()->ext($editableInfo->getMagPropertyPath());
				
		$this->pushGuiFieldInfo($tagName, $displayable, $fieldErrorInfo, $propertyPath);
		return $this->fieldEiHtml->getOpenInputField($tagName, $propertyPath, $fieldErrorInfo, 
				$this->buildAttrs($guiIdPath), $editableInfo->isMandatory());
	}
		
	public function openOutputField($tagName, $guiIdPath, array $attrs = null) {
		$this->view->out($this->getOpenOutputField($tagName, $guiIdPath, $attrs));
	}
	
	public function getOpenOutputField($tagName, $guiIdPath, array $attrs = null) {
		$eiuEntryGui = $this->meta->getCurrentEiuEntryGui();
		$guiIdPath = GuiIdPath::createFromExpression($guiIdPath);
		$displayable = $eiuEntryGui->getEiEntryGui()->getDisplayableByGuiIdPath($guiIdPath);
		$fieldErrorInfo = $eiuEntryGui->getEiuEntry()->getEiMapping()->getMappingErrorInfo()->getFieldErrorInfo(
				$eiuEntryGui->getEiEntryGui()->getGuiDefinition()->guiIdPathToEiFieldPath($guiIdPath));
		
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
		foreach ($this->eiuFrame->getContextEiMask()->createOverallControls($this->eiuFrame, $this->view) as $control) {
			$ul->appendContent(new HtmlElement('li', null, $control->toButton(false)));
		}
	
		return $ul;
	}
	
// 	public function entryGuiControlList($useIcons = false) {
// 		$this->html->out($this->getEntryGuiControlList($useIcons));
// 	}
	
// 	public function getEntryGuiControlList($useIcons = false) {
// 		$entryControls = $this->eiFrame->getContextEiMask()->createEntryControls($this->eiFrame, 
// 				$this->meta->getCurrentEntryGuiModel()->getEiMapping(), $this->view);
	
// 		$ulHtmlElement = new HtmlElement('ul', array('class' => ($useIcons ? 'rocket-simple-controls' : null /* 'rocket-main-controls' */)));
	
// 		foreach ($entryControls as $control) {
// 			$liHtmlElement = new HtmlElement('li', null, $control->toButton($useIcons));
// 			$ulHtmlElement->appendContent($liHtmlElement);
// 		}
	
// 		return $ulHtmlElement;
// 	}
}

class EntryEiHtmlBuilderMeta {
	private $currentEiuEntryGui;
	private $eiuEntryGuis;
	private $entryPropertyPaths;
	
	public function __construct(array $eiuEntryGuis) {
		ArgUtils::valArray($eiuEntryGuis, EiuEntryGui::class);
		$this->eiuEntryGuis = $eiuEntryGuis;
// 		$this->currentEntryModel = array_shift($this->entryModels);
	}
	
	public function getCurrentIdRep() {
		return $this->getCurrentEiuEntryGui()->getEiuEntry()->getLiveIdRep();
	}
	
	public function getForkMagPropertyPaths() {
		return $this->getCurrentEiuEntryGui()->getEiEntryGui()->getForkMagPropertyPaths();
	}
	
	public function getContextPropertyPath() {
		if (null !== ($contextPropertyPath = $this->getCurrentEiuEntryGui()->getContextPropertyPath())) {
			return $contextPropertyPath;
		}
		
		return new PropertyPath(array());
	}
	
	/**
	 * @throws IllegalStateException
	 * @return EiuEntryGui
	 */
	public function getCurrentEiuEntryGui() {
		if ($this->currentEiuEntryGui === null) {
			if (empty($this->eiuEntryGuis)) {
				throw new IllegalStateException('No EiEntryGui selected');
			}
			$this->next();
		}
		
		return $this->currentEiuEntryGui;
	}
	
	public function next() {
		$this->currentEiuEntryGui = array_shift($this->eiuEntryGuis);
		return $this->currentEiuEntryGui !== null;
	}
}
