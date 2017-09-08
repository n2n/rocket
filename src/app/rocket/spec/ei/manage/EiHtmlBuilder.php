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
use rocket\spec\ei\manage\util\model\EiuFactory;
use n2n\util\col\ArrayUtils;
use n2n\web\ui\Raw;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\ui\DisplayItem;
use rocket\spec\ei\manage\gui\EiEntryGui;
use n2n\impl\web\ui\view\html\HtmlUtils;
use rocket\spec\ei\manage\util\model\GeneralIdUtils;
use rocket\spec\ei\manage\util\model\EiuEntry;
use rocket\spec\ei\manage\gui\Displayable;
use rocket\spec\ei\manage\mapping\FieldErrorInfo;
use n2n\web\dispatch\map\PropertyPath;
use n2n\l10n\MessageTranslator;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\util\model\EiuEntryGui;

class EiHtmlBuilder {
	private $view;
	private $html;
	private $formHtml;
	private $stack = array();
	private $state;
	private $meta;
	
	public function __construct(HtmlView $view) {
		$this->view = $view;
		$this->html = $view->getHtmlBuilder();
		$this->formHtml = $view->getFormHtmlBuilder();
		
		$this->state = $view->getStateObj(self::class);
		if (!($this->state instanceof EiHtmlBuilderState)) {
			$view->setStateObj(self::class, $this->state = new EiHtmlBuilderState());
		}
		
		$this->meta = new EiHtmlBuilderMeta($this->state);
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiHtmlBuilderMeta
	 */
	public function meta() {
		return $this->meta;
	}
	
	public function entryOpen(string $tagName, $eiEntryGuiArg, array $attrs = null) {
		$this->view->out($this->getEntryOpen($tagName, $eiEntryGuiArg, $attrs));
	}
	
	public function getEntryOpen(string $tagName, $eiEntryGuiArg, array $attrs = null) {
		$eiEntryGui = EiuFactory::buildEiEntryGuiFromEiArg($eiEntryGuiArg, 'eiEntryGuiArg');
		$eiObject = $eiEntryGui->getEiEntry()->getEiObject();
		$idRep = null;
		if ($eiObject->getEiEntityObj()->isPersistent()) {
			$idRep = $eiObject->getEiEntityObj()->getIdRep();
		}
		$draftId = null;
		if ($eiObject->isDraft() && !$eiObject->getDraft()->isNew()) {
			$draftId = $eiObject->getDraft()->getId();
		}
	
		$this->state->pushEntry($tagName, $eiEntryGui);
	
		$treeLevel = $eiEntryGui->getTreeLevel();
				
		$entryAttrs = array(
				'class' => 'rocket-entry' . ($treeLevel !== null ? ' rocket-tree-level-' . $treeLevel : ''),
				'data-rocket-general-id' => GeneralIdUtils::generalIdOf($eiEntryGui->getEiEntry()->getEiObject()),
				'data-rocket-id-rep' => $idRep,
				'data-rocket-draft-id' => ($draftId !== null ? $draftId : ''),
				'data-rocket-identity-string' => (new EiuEntry($eiEntryGui))->createIdentityString());
		
		return new Raw('<' . htmlspecialchars($tagName)
				. HtmlElement::buildAttrsHtml(HtmlUtils::mergeAttrs($entryAttrs, (array) $attrs)) . '>');
	}
	
	public function entryClose() {
		$this->view->out($this->getEntryClose());
	}
	
	public function getEntryClose() {
		$tagName = $this->state->popEntry()['tagName'];
		
		return new Raw('</' . htmlspecialchars($tagName) . '>');
	}
	
	public function entryForkControls(array $attrs = null) {
		$this->view->out($this->getEntryForkControls($attrs));
	}
	
	public function getEntryForkControls(array $attrs = null) {
		$info = $this->state->peakEntry();
		$eiEntryGui = $info['eiEntryGui'];
		
		if (empty($eiEntryGui->getForkMagPropertyPaths())) {
			return null;
		}
		
		$div = new HtmlElement('div', HtmlUtils::mergeAttrs(array('class' => 'rocket-group-controls'), $attrs));
		
		foreach ($eiEntryGui->getForkMagPropertyPaths() as $forkMagPropertyPath) {
			$propertyPath = $eiEntryGui->getContextPropertyPath()->ext($forkMagPropertyPath);
			
			$div->appendLn($this->formHtml->getMagOpen('div', $propertyPath));
			$div->appendLn($this->formHtml->getMagLabel());
			$div->appendLn(new HtmlElement('div', array('class' => 'rocket-control'), $this->formHtml->getMagField()));
			$div->appendLn($this->formHtml->getMagClose()); 
		}
		
		return $div;
	}
	
	public function entryCommands(bool $iconOnly = false) {
		$this->view->out($this->getEntryCommands($iconOnly));
	}
	
	public function getEntryCommands(bool $iconOnly = false) {
		$eiEntryGui = $this->state->peakEntry()['eiEntryGui'];
		
		$divHtmlElement = new HtmlElement('div', array('class' => ($iconOnly ? 'rocket-simple-commands' : null)));
		
		foreach ($eiEntryGui->createControls($this->view) as $control) {
			$divHtmlElement->appendContent($control->createUiComponent($iconOnly));
		}
		
		return $divHtmlElement;
	}
	
	/**
	 * 
	 * @param string $containerTagName
	 * @param array $containerAttrs
	 * @param string $content
	 */
	public function entrySelector(string $containerTagName, array $containerAttrs = null, $content = '') {
		$this->view->out($this->getEntrySelector($containerTagName, $containerAttrs, $content));
	}
	
	/**
	 * 
	 * @param string $containerTagName
	 * @param array $containerAttrs
	 * @param string $content
	 * @return \n2n\impl\web\ui\view\html\HtmlElement
	 */
	public function getEntrySelector(string $containerTagName, array $containerAttrs = null, $content = '') {
		$eiEntryGui = $this->state->peakEntry()['eiEntryGui'];
		
		return new HtmlElement($containerTagName,
				HtmlUtils::mergeAttrs(
						array('class' => 'rocket-entry-selector'), 
						(array) $containerAttrs), ''/*
				new HtmlElement('input', array('type' => 'checkbox'))*/);
	}
	
	private function buildAttrs(GuiIdPath $guiIdPath, array $attrs) {
		return HtmlUtils::mergeAttrs($attrs, array(
				'class' => 'rocket-gui-field-' . implode('-', $guiIdPath->toArray())));
	}
	
	public function fieldOpen(string $tagName, $displayItem, array $attrs = null, bool $readOnly = false) {
		$this->view->out($this->getFieldOpen($tagName, $displayItem, $attrs, $readOnly));
	}
	
	public function getFieldOpen(string $tagName, $displayItem, array $attrs = null, bool $readOnly = false) {
		$eiEntryGui = $this->state->peakEntry()['eiEntryGui'];
		
		$guiIdPath = null;
		if ($displayItem instanceof DisplayItem) {
			if ($displayItem->hasDisplayStructure()) {
				throw new \InvalidArgumentException('DisplayItem with DisplayStructure is disallowed for field opening.');
			}
			
			$guiIdPath = $displayItem->getGuiIdPath();
			$attrs = $this->applyGroupTypeAttr($displayItem->getGroupType(), (array) $attrs);
		} else {
			$guiIdPath = GuiIdPath::createFromExpression($displayItem);
			$displayItem = null;
		}
	
		
		$displayable = $eiEntryGui->getDisplayableByGuiIdPath($guiIdPath);
		$fieldErrorInfo = $eiEntryGui->getEiEntry()->getMappingErrorInfo()->getFieldErrorInfo(
				$eiEntryGui->getEiGui()->getGuiDefinition()->guiIdPathToEiPropPath($guiIdPath));
	
		if ($readOnly || !$eiEntryGui->containsEditableWrapperGuiIdPath($guiIdPath)) {
			$this->state->pushField($tagName, $fieldErrorInfo, $displayable);
			return $this->createOutputFieldOpen($tagName, $displayable, $fieldErrorInfo,
					$this->buildAttrs($guiIdPath, (array) $attrs));
		}
	
		$editableInfo = $eiEntryGui->getEditableWrapperByGuiIdPath($guiIdPath);
		$propertyPath = $eiEntryGui->getContextPropertyPath()->ext($editableInfo->getMagPropertyPath());
	
		$this->state->pushField($tagName, $fieldErrorInfo, $displayable, $propertyPath);
		return $this->createInputFieldOpen($tagName, $propertyPath, $fieldErrorInfo,
				$this->buildAttrs($guiIdPath, $attrs), $editableInfo->isMandatory());
	}

	private function createInputFieldOpen(string $tagName, $magPropertyPath, FieldErrorInfo $fieldErrorInfo,
			array $attrs = null, bool $mandatory = false) {
		$magPropertyPath = $this->formHtml->meta()->createPropertyPath($magPropertyPath);

		if ($this->formHtml->meta()->hasErrors($magPropertyPath) || !$fieldErrorInfo->isValid()) {
			$attrs = HtmlUtils::mergeAttrs((array) $attrs, array('class' => 'rocket-has-error'));
		}

		return $this->formHtml->getMagOpen($tagName, $magPropertyPath, $this->buildContainerAttrs(
				(array) $attrs, false, $mandatory));
	}
	
	
	private function createOutputFieldOpen($tagName, Displayable $displayable, FieldErrorInfo $fieldErrorInfo, array $attrs = null) {
		return new Raw('<' . htmlspecialchars($tagName) . HtmlElement::buildAttrsHtml(
				$this->buildContainerAttrs(HtmlUtils::mergeAttrs($displayable->getOutputHtmlContainerAttrs(), $attrs))) . '>');
	}

	private function applyGroupTypeAttr(string $groupType = null, array $attrs) {
		if (null !== $groupType) {
			return HtmlUtils::mergeAttrs(array('class' => 'rocket-group-' . $groupType), $attrs);
		}
		
		return $attrs;
	}
	
	private function buildContainerAttrs(array $attrs, bool $readOnly = true, bool $mandatory = false) {
		$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-field'), $attrs);
	
		if ($mandatory) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-required'), $attrs);
		}
			
		if ($readOnly) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-read-only'), $attrs);
		} else {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-editable'), $attrs);
		}
	
		return $attrs;
	}
		
	public function fieldClose() {
		$this->view->out($this->getFieldClose());
	}
	
	public function getFieldClose() {
		$info = $this->state->peakField(true);
		return new Raw('</' . $info['tagName'] . '>');
	}
	
	public function fieldLabel(array $attrs = null, $label = null) {
		$this->html->out($this->getFieldLabel($attrs, $label));
	}
	
	public function getFieldLabel(array $attrs = null, $label = null) {
		$fieldInfo = $this->state->peakField(false);
		
		if (isset($fieldInfo['propertyPath'])) {
			return $this->formHtml->getMagLabel($attrs, $label);
		}
		
		return new HtmlElement('label', $attrs, ($label === null ? $fieldInfo['displayable']->getUiOutputLabel() : $label));
	}
	
	public function fieldContent() {
		$this->html->out($this->getFieldContent());
	}
	
	public function getFieldContent() {
		$fieldInfo = $this->state->peakField(false);
		
		if (isset($fieldInfo['propertyPath'])) {
			return $this->formHtml->getMagField();
		}
		
		return $this->html->getOut($fieldInfo['displayable']->createOutputUiComponent($this->view));
	}
	
	public function fieldMessage() {
		$this->html->out($this->getFieldMessage());
	}
	
	public function getFieldMessage() {
		$fieldInfo = $this->state->peakField(false);
		
		if (isset($fieldInfo['propertyPath'])
				&& null !== ($message = $this->formHtml->getMessage($fieldInfo['propertyPath']))) {
			return new HtmlElement('div', array('class' => 'rocket-message-error'), $message);
		}

		if (null !== ($message = $fieldInfo['fieldErrorInfo']->processMessage())) {
			$messageTranslator = new MessageTranslator($this->view->getModuleNamespace(),
					$this->view->getN2nLocale());
				
			return new HtmlElement('div', array('class' => 'rocket-message-error'),
					$messageTranslator->translate($message));
		}

		return null;
	}
	
	/**
	 * 
	 * @param string $tagName
	 * @param unknown $displayItem
	 * @param array $attrs
	 */
	public function groupOpen(string $tagName, $displayItem, array $attrs = null) {
		$this->view->out($this->getGroupOpen($tagName, $displayItem, $attrs));
	}
	
	/**
	 * 
	 * @param string $tagName
	 * @param unknown $displayItem
	 * @param array $attrs
	 * @return \n2n\web\ui\UiComponent
	 */
	public function getGroupOpen(string $tagName, $displayItem, array $attrs = null) {
		if ($displayItem instanceof DisplayItem) {
			$attrs = $this->applyGroupTypeAttr($displayItem->getGroupType(), (array) $attrs);
		} else {
			ArgUtils::valType($displayItem, [DisplayItem::class, 'string'], 'displayItem');
			$attrs = $this->applyGroupTypeAttr($displayItem->getGroupType(), (array) $attrs);
		}
		$this->state->pushGroup($tagName);
		return new Raw('<' . htmlspecialchars($tagName) . HtmlElement::buildAttrsHtml($attrs) . '>');
	}
	
	/**
	 * 
	 */
	public function groupClose() {
		$this->view->out($this->getGroupClose());
	}
	
	/**
	 * @return \n2n\web\ui\UiComponent
	 */
	public function getGroupClose() {
		$info = $this->state->peakGroup(true);
		return new Raw('</' . $info['tagName'] . '>');
	}
	
	/**
	 * @param string $containerTagName
	 * @param array $containerAttrs
	 * @param string $content
	 */
	public function generalEntrySelector(string $containerTagName, array $containerAttrs = null, $content = '') {
		$this->view->out($this->getGeneralEntrySelector($containerTagName, $containerAttrs, $content));
	}
	
	/**
	 * @param string $containerTagName
	 * @param array $containerAttrs
	 * @param string $content
	 * @return \n2n\impl\web\ui\view\html\HtmlElement
	 */
	public function getGeneralEntrySelector(string $containerTagName, array $containerAttrs = null, $content = '') {
		return new HtmlElement($containerTagName,
				HtmlUtils::mergeAttrs(array('class' => 'rocket-general-entry-selector'), (array) $containerAttrs),
				$content);
	}
	
	public function frameCommands($eiGuiArg, bool $iconOnly = false) {
		$this->view->out($this->getFrameCommands($eiGuiArg, $iconOnly));
	}
	
	public function getFrameCommands($eiGuiArg, bool $iconOnly = false) {
		$eiGui = EiuFactory::buildEiGuiFromEiArg($eiGuiArg, 'eiGuiArg');
	
		$divHtmlElement = new HtmlElement('div', array('class' => ($iconOnly ? 'rocket-simple-commands' : null)));
	
		foreach ($eiGui->createOverallControls($this->view) as $control) {
			$divHtmlElement->appendContent($control->createUiComponent($iconOnly));
		}
	
		return $divHtmlElement;
	}
}

class EiHtmlBuilderMeta {
	private $state;
	
	/**
	 * @param EiHtmlBuilderState $state
	 */
	public function __construct(EiHtmlBuilderState $state) {
		$this->state = $state;
	}
	
	/**
	 * @return boolean
	 */
	public function isEntryOpen($eiEntryGui = null) {
		if (!$this->state->containsEntry()) {
			return false;
		}
		
		return $eiEntryGui === null || $eiEntryGui === $this->state->peakEntry()['eiEntryGui'];
	}
}

class EiHtmlBuilderState {
	private $stack = array();
	
	/**
	 * @return boolean
	 */
	public function containsEntry() {
		foreach ($this->stack as $info) {
			if ($info['type'] == 'entry') return true;
		}
		
		return false;
	}
	
	/**
	 * @param string $tagName
	 * @param EiEntryGui $eiEntryGui
	 */
	public function pushEntry(string $tagName, EiEntryGui $eiEntryGui) {
		$this->stack[] = array(
				'type' => 'entry',
				'eiEntryGui' => $eiEntryGui,
				'tagName' => $tagName);
	}
	
	/**
	 *
	 * @param string $tagName
	 * @throws IllegalStateException
	 * @return array
	 */
	public function peakEntry() {
		for ($i = count($this->stack) - 1; $i >= 0; $i--) {
			if ($this->stack[$i]['type'] == 'entry') {
				return $this->stack[$i];
			}
		}
	
		throw new IllegalStateException('No entry open.');
	}
	
	/**
	 *
	 * @throws IllegalStateException
	 * @return array
	 */
	public function popEntry() {
		$info = ArrayUtils::end($this->stack);
	
		if ($info === null) {
			throw new IllegalStateException('No entry open.');
		}
	
		if ($info['type'] != 'entry') {
			throw new IllegalStateException('Field open.');
		}
		
		array_pop($this->stack);
	
		return $info;
	}
	
	/**
	 * @param string $tagName
	 * @param FieldErrorInfo $fieldErrorInfo
	 * @param Displayable $displayable
	 * @param PropertyPath $propertyPath
	 */
	public function pushField(string $tagName, FieldErrorInfo $fieldErrorInfo, Displayable $displayable = null,
			PropertyPath $propertyPath = null) {
		$this->stack[] = array('type' => 'field', 'tagName' => $tagName, 'displayable' => $displayable,
				'fieldErrorInfo' => $fieldErrorInfo, 'propertyPath' => $propertyPath);
	}
	
	/**
	 *
	 * @param bool $pop
	 * @throws IllegalStateException
	 * @return array
	 */
	public function peakField(bool $pop) {
		$info = ArrayUtils::end($this->stack);
	
		if ($info === null || $info['type'] != 'field') {
			throw new IllegalStateException('No field open.');
		}
	
		if ($pop) {
			return array_pop($this->stack);
		} else {
			return end($this->stack);
		}
	}
	

	/**
	 * @param string $tagName
	 */
	public function pushGroup(string $tagName) {
		$this->stack[] = array('type' => 'group', 'tagName' => $tagName);
	}
	
	/**
	 *
	 * @param bool $pop
	 * @throws IllegalStateException
	 * @return array
	 */
	public function peakGroup(bool $pop) {
		$info = ArrayUtils::end($this->stack);
		
		if ($info === null || $info['type'] != 'group') {
			throw new IllegalStateException('No group open.');
		}
	
		if ($pop) {
			return array_pop($this->stack);
		} else {
			return end($this->stack);
		}
	}
}