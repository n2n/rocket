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
namespace rocket\ei\manage;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\util\model\EiuAnalyst;
use n2n\util\col\ArrayUtils;
use n2n\web\ui\Raw;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\GuiIdPath;
use rocket\ei\manage\gui\ui\DisplayItem;
use rocket\ei\manage\gui\EiEntryGui;
use n2n\impl\web\ui\view\html\HtmlUtils;
use rocket\ei\manage\gui\Displayable;
use rocket\ei\manage\mapping\FieldErrorInfo;
use n2n\web\dispatch\map\PropertyPath;
use n2n\l10n\MessageTranslator;
use n2n\reflection\ArgUtils;
use n2n\reflection\CastUtils;
use rocket\ei\manage\control\Control;
use rocket\ei\manage\control\GroupControl;
use rocket\ei\manage\control\ControlButton;
use rocket\ei\manage\control\IconType;
use rocket\ei\manage\gui\GuiFieldAssembly;
use rocket\ei\util\model\Eiu;

class EiHtmlBuilder {
	private $view;
	private $html;
	private $formHtml;
	private $stack = array();
	private $state;
	private $meta;
	private $uiOutfitter;
	
	public function __construct(HtmlView $view) {
		$this->view = $view;
		$this->html = $view->getHtmlBuilder();
		$this->formHtml = $view->getFormHtmlBuilder();
		
		$this->state = $view->getStateObj(self::class);
		if (!($this->state instanceof EiHtmlBuilderState)) {
			$view->setStateObj(self::class, $this->state = new EiHtmlBuilderState());
		}
		
		$this->meta = new EiHtmlBuilderMeta($this->state, $this->view);
		$this->uiOutfitter = new RocketUiOutfitter();
	}
	
	/**
	 * @return \rocket\ei\manage\EiHtmlBuilderMeta
	 */
	public function meta() {
		return $this->meta;
	}
	
	/**
	 * @see self::getLabel();
	 */
	public function label($eiGuiArg, $guiIdPath) {
		$this->html->out($this->getLabel($eiGuiArg, $guiIdPath));
	}
	
	/**
	 * 
	 * @param mixed $eiGuiArg See {@see EiuAnalyst::buildEiGuiFromEiArg()} for allowed argument types.
	 * @param GuiIdPath|string|DisplayItem $guiIdPath
	 * @return \n2n\web\ui\UiComponent|\n2n\web\ui\Raw|string
	 */
	public function getLabel($eiGuiArg, $guiIdPath) {
		$eiGui = EiuAnalyst::buildEiGuiFromEiArg($eiGuiArg);
		if ($guiIdPath instanceof DisplayItem) {
			if (null !== ($label = $guiIdPath->getLabel()) || $guiIdPath->hasDisplayStructure()) {
				return $this->html->getOut($label);
			}
			
			$guiIdPath = $guiIdPath->getGuiIdPath();
		} else {
			$guiIdPath = GuiIdPath::create($guiIdPath);
		}
		
		return $this->html->getOut($eiGui->getGuiDefinition()->getGuiPropByGuiIdPath($guiIdPath)->getDisplayLabel());
	}
	
	private $collectionTagName = null;
	
	public function collectionOpen(string $tagName, $eiTypeArg, array $attrs = null) {
		$this->view->out($this->getCollectionOpen($tagName, $eiTypeArg, $attrs));
	}
	
	public function getCollectionOpen(string $tagName, $eiTypeArg, array $attrs = null) {
		if ($this->collectionTagName !== null) {
			throw new IllegalStateException('Collection already open');
		}
		
		$this->collectionTagName = $tagName;
		$eiType = EiuAnalyst::buildEiTypeFromEiArg($eiTypeArg);
		$supremeEiType = $eiType->getSupremeEiType();
		
		$colAttrs = array(
				'class' => 'rocket-collection',
				'data-rocket-ei-type-id' => $eiType->getId(),
				'data-rocket-supreme-ei-type-id' => $supremeEiType->getId());
		
		return new Raw('<' . HtmlUtils::hsc($tagName) . HtmlElement::buildAttrsHtml(
				HtmlUtils::mergeAttrs($colAttrs, (array) $attrs)) . '>');
	}
	
	public function collectionClose() {
		$this->view->out($this->getCollectionClose());
	}
	
	public function getCollectionClose() {
		if ($this->collectionTagName === null) {
			throw new IllegalStateException('No collection open');
		}
		
		$raw = new Raw('</' . HtmlUtils::hsc($this->collectionTagName) . '>');
		$this->collectionTagName = null;
		return $raw;
	}
	
	public function entryOpen(string $tagName, $eiEntryGuiArg, array $attrs = null) {
		$this->view->out($this->getEntryOpen($tagName, $eiEntryGuiArg, $attrs));
	}
	
	public function getEntryOpen(string $tagName, $eiEntryGuiArg, array $attrs = null) {
		$eiEntryGui = EiuAnalyst::buildEiEntryGuiFromEiArg($eiEntryGuiArg, 'eiEntryGuiArg');
		$eiObject = $eiEntryGui->getEiEntry()->getEiObject();
		$pid = null;
		if ($eiObject->getEiEntityObj()->isPersistent()) {
			$pid = $eiObject->getEiEntityObj()->getPid();
		}
		$draftId = null;
		if ($eiObject->isDraft() && !$eiObject->getDraft()->isNew()) {
			$draftId = $eiObject->getDraft()->getId();
		}
	
		$this->state->pushEntry($tagName, $eiEntryGui);
	
		$treeLevel = $eiEntryGui->getTreeLevel();
				
		$entryAttrs = array(
				'class' => 'rocket-entry' . ($treeLevel !== null ? ' rocket-tree-level-' . $treeLevel : ''),
				'data-rocket-ei-type-id' => $eiEntryGui->getEiEntry()->getEiMask()->getEiType()->getId(),
				'data-rocket-supreme-ei-type-id' => $eiEntryGui->getEiEntry()->getEiMask()->getEiType()->getSupremeEiType()->getId(),
				'data-rocket-ei-id' => $pid,
				'data-rocket-draft-id' => ($draftId !== null ? $draftId : ''),
				'data-rocket-identity-string' => (new Eiu($eiEntryGui->getEiEntry(), $eiEntryGui->getEiGui()->getEiFrame()))->entry()->createIdentityString());
		
		return new Raw('<' . HtmlUtils::hsc($tagName)
				. HtmlElement::buildAttrsHtml(HtmlUtils::mergeAttrs($entryAttrs, (array) $attrs)) . '>');
	}
	
	public function entryClose() {
		$this->view->out($this->getEntryClose());
	}
	
	public function getEntryClose() {
		$tagName = $this->state->popEntry()['tagName'];
		
		return new Raw('</' . HtmlUtils::hsc($tagName) . '>');
	}
	
	public function entryForkControls(array $attrs = null) {
		$this->view->out($this->getEntryForkControls($attrs));
	}
	
	public function getEntryForkControls(array $attrs = null) {
		$info = $this->state->peakEntry();
		$eiEntryGui = $info['eiEntryGui'];
		CastUtils::assertTrue($eiEntryGui instanceof EiEntryGui);
		
		$forkMagAssemblies = $eiEntryGui->getForkMagAssemblies();
		
		if (empty($forkMagAssemblies)) {
			return null;
		}
		
		$div = new HtmlElement('div', HtmlUtils::mergeAttrs(array('class' => 'rocket-group-controls'), $attrs));
		
		foreach ($forkMagAssemblies as $forkMagAssembly) {
			$propertyPath = $eiEntryGui->getContextPropertyPath()->ext($forkMagAssembly->getMagPropertyPath());
			
			$div->appendLn($this->formHtml->getMagOpen('div', $propertyPath, null, $this->uiOutfitter));
			$div->appendLn($this->formHtml->getMagLabel());
			$div->appendLn(new HtmlElement('div', array('class' => 'rocket-control'), $this->formHtml->getMagField()));
			$div->appendLn($this->formHtml->getMagClose()); 
		}
		
		return $div;
	}
	
	public function entryCommands(bool $iconOnly = false, int $max = null) {
		$this->view->out($this->getEntryCommands($iconOnly, $max));
	}
	
	public function getEntryCommands(bool $iconOnly = false, int $max = null) {
		return $this->getCommands($this->meta->createEntryControls(null, $max), $iconOnly);
	}
	
	public function commands(array $controls, bool $iconOnly = false) {
		$this->view->out($this->getCommands($controls, $iconOnly));
	}
	
	public function getCommands(array $controls, bool $iconOnly = false) {
		if (empty($controls)) return null;
		
		ArgUtils::valArray($controls, Control::class);
		
		$divHtmlElement = new HtmlElement('div', array('class' => ($iconOnly ? 'rocket-simple-commands' : null)));
		
		foreach ($controls as $control) {
			$divHtmlElement->appendContent($control->createUiComponent());
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
	
	private function buildAttrs(GuiIdPath $guiIdPath, array $attrs, DisplayItem $displayItem = null) {
		$attrs = HtmlUtils::mergeAttrs($attrs, array('class' => 'rocket-gui-field-' . implode('-', $guiIdPath->toArray())));
// 		$attrs = $this->applyDisplayItemAttr($displayItem !== null ? $displayItem->getType() : DisplayItem::TYPE_ITEM, $attrs);
		return $attrs;
	}
	
	public function fieldOpen(string $tagName, $displayItem, array $attrs = null, bool $readOnly = false,
			bool $addDisplayCl = true) {
		$this->view->out($this->getFieldOpen($tagName, $displayItem, $attrs, $readOnly, $addDisplayCl));
	}
	
	public function getFieldOpen(string $tagName, $displayItem, array $attrs = null, bool $readOnly = false,
			bool $addDisplayCl = false) {
		$eiEntryGui = $this->state->peakEntry()['eiEntryGui'];
		CastUtils::assertTrue($eiEntryGui instanceof EiEntryGui);
		$guiIdPath = null;
		if ($displayItem instanceof DisplayItem) {
			if ($displayItem->hasDisplayStructure()) {
				throw new \InvalidArgumentException('DisplayItem with DisplayStructure is disallowed for field opening.');
			}
			
			$guiIdPath = $displayItem->getGuiIdPath();
			if ($addDisplayCl) {
				$attrs = $this->applyDisplayItemAttr($displayItem->getType(), 
						HtmlUtils::mergeAttrs((array) $displayItem->getAttrs(), (array) $attrs));
			}
		} else {
			$guiIdPath = GuiIdPath::create($displayItem);
			if ($addDisplayCl) {
				$attrs = $this->applyDisplayItemAttr(DisplayItem::TYPE_ITEM, (array) $attrs);
			}
			$displayItem = null;
		}
		
		$fieldErrorInfo = $eiEntryGui->getEiEntry()->getMappingErrorInfo()->getFieldErrorInfo(
				$eiEntryGui->getEiGui()->getEiGuiViewFactory()->getGuiDefinition()->guiIdPathToEiPropPath($guiIdPath));
		if (!$eiEntryGui->containsGuiFieldGuiIdPath($guiIdPath)) {
			$this->state->pushField($tagName, $guiIdPath, $fieldErrorInfo, null, null, $displayItem);
			return $this->createOutputFieldOpen($tagName, null, $fieldErrorInfo,
					$this->buildAttrs($guiIdPath, (array) $attrs, $displayItem));
		}
		
		$guiFieldAssembly = $eiEntryGui->getGuiFieldAssembly($guiIdPath);
		$magAssembly = $guiFieldAssembly->getMagAssembly();
		
		if ($readOnly || $magAssembly === null) {
			$this->state->pushField($tagName, $guiIdPath, $fieldErrorInfo, $guiFieldAssembly, null, $displayItem);
			return $this->createOutputFieldOpen($tagName, $guiFieldAssembly->getDisplayable(), $fieldErrorInfo,
					$this->buildAttrs($guiIdPath, (array) $attrs, $displayItem));
		}
	
		$propertyPath = $eiEntryGui->getContextPropertyPath()->ext($magAssembly->getMagPropertyPath());
		
		$this->state->pushField($tagName, $guiIdPath, $fieldErrorInfo, $guiFieldAssembly, $propertyPath, $displayItem);
		return $this->createInputFieldOpen($tagName, $propertyPath, $fieldErrorInfo,
				$this->buildAttrs($guiIdPath, (array) $attrs, $displayItem), $magAssembly->isMandatory());
	}
	
	private function createInputFieldOpen(string $tagName, $magPropertyPath, FieldErrorInfo $fieldErrorInfo,
			array $attrs = null, bool $mandatory = false) {
		$magPropertyPath = $this->formHtml->meta()->createPropertyPath($magPropertyPath);

		if ($this->formHtml->meta()->hasErrors($magPropertyPath) || !$fieldErrorInfo->isValid()) {
			$attrs = HtmlUtils::mergeAttrs((array) $attrs, array('class' => 'rocket-has-error'));
		}

		return $this->formHtml->getMagOpen($tagName, $magPropertyPath, 
				$this->buildContainerAttrs((array) $attrs, false, $mandatory), 
				$this->uiOutfitter);
	}
	
	
	private function createOutputFieldOpen($tagName, Displayable $displayable = null, FieldErrorInfo $fieldErrorInfo, array $attrs = null) {
		return new Raw('<' . HtmlUtils::hsc($tagName) . HtmlElement::buildAttrsHtml(
				$this->buildContainerAttrs(HtmlUtils::mergeAttrs(($displayable !== null ? $displayable->getOutputHtmlContainerAttrs() : array()), $attrs))) . '>');
	}

	private function applyDisplayItemAttr(string $displayItemType = null, array $attrs) {
		if ($displayItemType === null) {
			return $attrs;
		}
		
		if (in_array($displayItemType, DisplayItem::getGroupTypes())) {
			return HtmlUtils::mergeAttrs(array('class' => 'rocket-group rocket-' . $displayItemType), $attrs);
		}
		
		return HtmlUtils::mergeAttrs(array('class' => 'rocket-' . $displayItemType), $attrs);
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
		
		if ($label !== null) {
			return new HtmlElement('label', $attrs, $label);
		}
		
		if (isset($fieldInfo['displayable'])) {
			return new HtmlElement('label', $attrs, $fieldInfo['displayable']->getUiOutputLabel());
		}
		
		$eiEntryGui = $this->state->peakEntry()['eiEntryGui'];
		return new HtmlElement('label', $attrs, (string) $eiEntryGui->getEiGui()->getEiGuiViewFactory()
				->getGuiDefinition()->getGuiPropByGuiIdPath($fieldInfo['guiIdPath'])->getDisplayLabel());
	}
	
	public function fieldContent() {
		$this->html->out($this->getFieldContent());
	}
	
	public function getFieldContent() {
		$fieldInfo = $this->state->peakField(false);
		
		if (isset($fieldInfo['propertyPath'])) {
			return $this->formHtml->getMagField();
		}
		
		if (isset($fieldInfo['guiFieldAssembly'])) {
			return $this->html->getOut($fieldInfo['guiFieldAssembly']->getDisplayable()->createOutputUiComponent($this->view));
		}
		
		return null;
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
	 * @param DisplayItem|string $displayItem
	 * @param array $attrs
	 */
	public function displayItemOpen(string $tagName, $displayItem, array $attrs = null) {
		$this->view->out($this->getDisplayItemOpen($tagName, $displayItem, $attrs));
	}
	
	/**
	 * 
	 * @param string $tagName
	 * @param DisplayItem|string $displayItem
	 * @param array $attrs
	 * @return \n2n\web\ui\UiComponent
	 */
	public function getDisplayItemOpen(string $tagName, $displayItem, array $attrs = null) {
		if ($displayItem instanceof DisplayItem) {
			$attrs = $this->applyDisplayItemAttr($displayItem->getType(), 
					HtmlUtils::mergeAttrs((array) $displayItem->getAttrs(), (array) $attrs));
		} else if ($displayItem !== null) {
			ArgUtils::valType($displayItem, [DisplayItem::class, 'string'], true, 'displayItem');
			$attrs = $this->applyDisplayItemAttr($displayItem, (array) $attrs);
		}
		
		$this->state->pushGroup($tagName);
		return new Raw('<' . HtmlUtils::hsc($tagName) . HtmlElement::buildAttrsHtml($attrs) . '>');
	}
	
	/**
	 * 
	 */
	public function displayItemClose() {
		$this->view->out($this->getDisplayItemClose());
	}
	
	/**
	 * @return \n2n\web\ui\UiComponent
	 */
	public function getDisplayItemClose() {
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
		$eiGui = EiuAnalyst::buildEiGuiFromEiArg($eiGuiArg, 'eiGuiArg');
	
		$divHtmlElement = new HtmlElement('div', array('class' => ($iconOnly ? 'rocket-simple-commands' : null)));
	
		foreach ($eiGui->createOverallControls($this->view) as $control) {
			$divHtmlElement->appendContent($control->createUiComponent());
		}
	
		return $divHtmlElement;
	}
}

class EiHtmlBuilderMeta {
	private $state;
	private $view;
	
	/**
	 * @param EiHtmlBuilderState $state
	 */
	public function __construct(EiHtmlBuilderState $state, HtmlView $view) {
		$this->state = $state;
		$this->view = $view;
	}
	
	/**
	 * @return boolean
	 */
	public function isEntryOpen($eiEntryGui = null) {
		if (!$this->state->containsEntry()) {
			return false;
		}
		$eiEntryGui = EiuAnalyst::buildEiEntryGuiFromEiArg($eiEntryGui);
		return $eiEntryGui === null || $eiEntryGui === $this->state->peakEntry()['eiEntryGui'];
	}
	
	/**
	 * @return boolean
	 */
	public function isFieldGroup() {
		$guiFieldAssembly = $this->getGuiFieldAssembly();
		if ($guiFieldAssembly === null) return false;
		
		return in_array($this->getFieldDisplayType(), DisplayItem::getGroupTypes());
	}
	
	/**
	 * @return boolean
	 */
	public function isFieldPanel() {
		$guiFieldAssembly = $this->getGuiFieldAssembly();
		if ($guiFieldAssembly === null) return false;
		
		return $this->getFieldDisplayType() == DisplayItem::TYPE_PANEL;
	}
	
	public function getFieldDisplayType() {
		$fieldInfo = $this->state->peakField(false);
		if ($fieldInfo === null) return null;
		
		if (isset($fieldInfo['displayItem'])) {
			return $fieldInfo['displayItem']->getType();
		}
		
		if (isset($fieldInfo['guiFieldAssembly'])) {
			return $fieldInfo['guiFieldAssembly']->getDisplayable()->getDisplayItemType();
		}
		
		return null;
	}
		
	/**
	 * @return GuiFieldAssembly|null
	 */
	public function getGuiFieldAssembly() {
		$fieldInfo = $this->state->peakField(false);
		if ($fieldInfo === null) return null;
		
		return $fieldInfo['guiFieldAssembly'] ?? null;
	}
	
	/**
	 * @param mixed $eiEntryGui
	 * @return Control[]
	 */
	public function createEntryControls($eiEntryGui = null, int $max = null) {
		if ($eiEntryGui === null) {
			$eiEntryGui = $this->state->peakEntry()['eiEntryGui'];
		} else {
			$eiEntryGui = EiuAnalyst::buildEiEntryGuiFromEiArg($eiEntryGui);
		}
		
		$controls = $eiEntryGui->createControls($this->view);
		if ($max === null || count($controls) <= $max) return $controls;
		
		$numStatics = 0;
		$vControls = array();
		$groupedControls = array();
		foreach ($controls as $control) {
			if (!$control->isStatic()) {
				$vControls[] = $control;
				continue;
			}
			
			$numStatics++;
			if ($numStatics < $max) {
				$vControls[] = $control;
				continue;
			}
			
			$groupedControls[] = $control; 
		}
		
		if (empty($groupedControls)) {
			return $vControls;
		}
		
		if (count($groupedControls) == 1) {
			$vControls[] = array_pop($groupedControls);
			return $vControls;
		}
		
		$vControls[] = $groupControl = new GroupControl((new ControlButton('more'))->setIconType(IconType::ICON_ELLIPSIS_V));
		$groupControl->add(...$groupedControls);
		
		return $vControls;
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
	 * @param Displayable $guiFieldAssembly
	 * @param PropertyPath $propertyPath
	 */
	public function pushField(string $tagName, GuiIdPath $guiIdPath, FieldErrorInfo $fieldErrorInfo, GuiFieldAssembly $guiFieldAssembly = null,
			PropertyPath $propertyPath = null, DisplayItem $displayItem = null) {
		$this->stack[] = array('type' => 'field', 'guiIdPath' => $guiIdPath, 'tagName' => $tagName, 'guiFieldAssembly' => $guiFieldAssembly,
				'fieldErrorInfo' => $fieldErrorInfo, 'propertyPath' => $propertyPath, 'displayItem' => $displayItem);
	}
	
	/**
	 *
	 * @param bool $pop
	 * @throws IllegalStateException
	 * @return array
	 */
	public function peakField(bool $pop) {
		$info = ArrayUtils::end($this->stack) ;
	
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