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
namespace rocket\ei\util\gui;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\util\EiuAnalyst;
use n2n\web\ui\Raw;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\GuiFieldPath;
use rocket\ei\manage\gui\ui\DisplayItem;
use rocket\ei\manage\gui\EiEntryGui;
use n2n\impl\web\ui\view\html\HtmlUtils;
use n2n\util\type\ArgUtils;
use n2n\util\type\CastUtils;
use rocket\ei\manage\control\Control;
use rocket\ei\util\Eiu;
use rocket\ei\manage\RocketUiOutfitter;
use n2n\impl\web\ui\view\html\HtmlSnippet;
use rocket\ei\manage\entry\ValidationResult;
use rocket\ei\manage\entry\UnknownEiFieldExcpetion;
use n2n\l10n\Message;
use rocket\ei\manage\gui\ui\DisplayStructure;
use rocket\ei\manage\gui\MagAssembly;
use n2n\web\dispatch\map\PropertyPath;
use rocket\ei\manage\gui\GuiFieldDisplayable;

class EiuHtmlBuilder {
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
		if (!($this->state instanceof EiuHtmlBuilderState)) {
			$view->setStateObj(self::class, $this->state = new EiuHtmlBuilderState());
		}
		
		$this->meta = new EiuHtmlBuilderMeta($this->state, $this->view);
		$this->uiOutfitter = new RocketUiOutfitter();
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuHtmlBuilderMeta
	 */
	public function meta() {
		return $this->meta;
	}
	
// 	public function getMessageList($eiEntryArg) {
// 		$this->meta->getUnboundMessages($eiGuiArg->)
// 	}
	
	/**
	 * @see self::getLabel();
	 */
	public function label($eiGuiArg, $eiPropPath) {
		$this->html->out($this->getLabel($eiGuiArg, $eiPropPath));
	}
	
	/**
	 * 
	 * @param mixed $eiGuiArg See {@see EiuAnalyst::buildEiGuiFromEiArg()} for allowed argument types.
	 * @param GuiFieldPath|string|DisplayItem $eiPropPath
	 * @return \n2n\web\ui\UiComponent|\n2n\web\ui\Raw|string
	 */
	public function getLabel($eiGuiArg, $guiFieldPath) {
		$eiGui = EiuAnalyst::buildEiGuiFromEiArg($eiGuiArg);
		if ($guiFieldPath instanceof DisplayItem) {
			if ($guiFieldPath->hasDisplayStructure()) {
				$labelLstr = $guiFieldPath->getLabelLstr();
				return $labelLstr == null ? null : $this->html->getOut($labelLstr->t($this->view->getN2nLocale()));
			}
			
			$guiFieldPath = $guiFieldPath->getGuiFieldPath();
		} else {
			$guiFieldPath = GuiFieldPath::create($guiFieldPath);
		}
		
		return $this->html->getOut($eiGui->getEiGuiViewFactory()->getGuiDefinition()
				->getGuiPropByGuiFieldPath($guiFieldPath)
				->getDisplayLabelLstr()->t($this->view->getN2nLocale()));
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
	
	public function entryOpen(string $tagName, $eiEntryGuiArg, string $displayItemType = null, array $attrs = null) {
		$this->view->out($this->getEntryOpen($tagName, $eiEntryGuiArg, $displayItemType, $attrs));
	}
	
	public function getEntryOpen(string $tagName, $eiEntryGuiArg, string $displayItemType = null, array $attrs = null) {
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
		
		$entryAttrs = EiuHtmlBuilderMeta::createDisplayItemAttrs($displayItemType, $entryAttrs);
		$entryAttrs = HtmlUtils::mergeAttrs($entryAttrs, (array) $attrs);
		
		return new Raw('<' . HtmlUtils::hsc($tagName) . HtmlElement::buildAttrsHtml($entryAttrs) . '>');
	}
	
	public function entryClose() {
		$this->view->out($this->getEntryClose());
	}
	
	public function getEntryClose() {
		$tagName = $this->state->popEntry()['tagName'];
		
		return new Raw('</' . HtmlUtils::hsc($tagName) . '>');
	}
	
	public function entryMessages(array $attrs = null) {
		$this->view->out($this->getEntryMessages($attrs));
	}
	
	public function getEntryMessages(array $attrs = null) {
		$messages = $this->meta->getEntryMessages();
		if (empty($messages)) return null;
		
		$hs = new HtmlSnippet();
		foreach ($messages as $message) {
			$hs->appendLn(new HtmlElement('div', array('class' => 'rocket-message-error alert alert-danger'), 
					$this->html->getL10nText('eiu_entry_gui_err', array('msg' => $message))));
		}
		return $hs;
	}
	
	public function entryForkControls(array $attrs = null) {
		$this->view->out($this->getEntryForkControls($attrs));
	}
	
	public function getEntryForkControls(array $attrs = null) {
		$info = $this->state->peakEntry();
		$eiEntryGui = $info['eiEntryGui'];
		CastUtils::assertTrue($eiEntryGui instanceof EiEntryGui);
		
		$forkMagAssemblies = array();
		foreach ($eiEntryGui->getForkMagAssemblies() as $guiFieldPathStr => $forkMagAssembly) {
			if ($this->state->isForkMagRendered($guiFieldPathStr)) continue;
			
			$this->state->markForkMagAsRendered($guiFieldPathStr);
			$forkMagAssemblies[] = $forkMagAssembly;		
		}
		
		return $this->buildEntryForkControlsUi($eiEntryGui, $forkMagAssemblies, $attrs);
	}
	
	
	public function getEntryForkControlsFor(DisplayStructure $displayStructure, array $attrs = null) {
		$eiEntryGui = $this->state->peakEntry()['eiEntryGui'];
		
		$forkMagAssemblies = array();
		foreach ($eiEntryGui->getGuiFieldForkAssemblies() as $guiFieldPathStr => $guiFieldForkAssembly) {
			if ($this->state->isForkMagRendered($guiFieldPathStr)) continue;
			
			$guiFieldPath = GuiFieldPath::create($guiFieldPathStr);
			
			if (!$displayStructure->containsLevelGuiFieldPathPrefix($guiFieldPath)
					&& $displayStructure->containsSubGuiFieldPathPrefix($guiFieldPath)) {
				continue;			
			}
			
			$this->state->markForkMagAsRendered($guiFieldPathStr);
			
			foreach ($guiFieldForkAssembly->getMagAssemblies() as $magAssembly) {
				$forkMagAssemblies[] = $magAssembly;
			}
		}
		
		return $this->buildEntryForkControlsUi($eiEntryGui, $forkMagAssemblies, $attrs);
	}
	
	/**
	 * @param MagAssembly[] $forkMagAssemblies
	 */
	private function buildEntryForkControlsUi($eiEntryGui, $forkMagAssemblies, $attrs) {
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
	
	private function buildFieldAttrs(GuiFieldPath $guiFieldPath, bool $readOnly = true, bool $mandatory = false, 
			array $attrs) {
		$newAttrs = array('class' => 'rocket-field rocket-gui-field-' . implode('-', $guiFieldPath->toArray()));
				
		if ($mandatory) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-required'), $attrs);
		}
		
		if ($readOnly) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-read-only'), $attrs);
		} else {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-editable'), $attrs);
		}
		
		return HtmlUtils::mergeAttrs($newAttrs, $attrs);
	}
	
	/**
	 * @param string $tagName
	 * @param GuiFieldPath|null $guiFieldPath
	 * @param string|bool|null $displayItemType
	 * @param array|null $attrs
	 */
	public function fieldOpen(string $tagName, $guiFieldPath, $displayItemType = null, array $attrs = null, 
			bool $forceReadOnly = false) {
		$this->view->out($this->getFieldOpen($tagName, $guiFieldPath, $displayItemType, $attrs, $forceReadOnly));
	}
	
	/**
	 * @param string $tagName
	 * @param GuiFieldPath|null $guiFieldPath
	 * @param string|bool|null $displayItemType
	 * @param array|null $attrs
	 */
	public function getFieldOpen(string $tagName, $guiFieldPath, $displayItemType = null, array $attrs = null, 
			bool $forceReadOnly = false) {
		$eiEntryGui = $this->state->peakEntry()['eiEntryGui'];
		CastUtils::assertTrue($eiEntryGui instanceof EiEntryGui);
		
		$guiFieldPath = GuiFieldPath::create($guiFieldPath);
		
		$guiPropAssembly = $eiEntryGui->getEiGui()->getGuiPropAssemblyByGuiFieldPath($guiFieldPath);
		$guiFieldAssembly = $eiEntryGui->getGuiFieldAssembly($guiFieldPath);
		$readOnly = $forceReadOnly || $guiFieldAssembly->isReadOnly();
		
		$attrs = $this->buildFieldAttrs($guiFieldPath, $readOnly, 
				!$readOnly && $guiFieldAssembly->getEditable()->isMandatory(), (array) $attrs);
		
		if ($displayItemType === null || $displayItemType === true) {
			$displayItemType = $guiPropAssembly->getDisplayDefinition()->getDisplayItemType();
		} 
		
		if ($displayItemType !== false) {
			$attrs = EiuHtmlBuilderMeta::createDisplayItemAttrs($displayItemType, $attrs);
		} 
			
		$guiDefinition = $eiEntryGui->getEiGui()->getGuiDefinition();
		$validationResult = null;
		try {
			$validationResult = $guiDefinition->determineEiFieldAbstraction($this->view->getN2nContext(), 
					$eiEntryGui->getEiEntry(), $guiFieldPath)->getValidationResult();
		} catch (UnknownEiFieldExcpetion $e) {}
		if ($readOnly) {
			$this->state->pushField($tagName, $guiFieldPath, $validationResult, $guiFieldAssembly);
			return $this->createOutputFieldOpen($tagName, $guiFieldAssembly->getDisplayable(), $validationResult, $attrs);
		}

		$magAssembly = $guiFieldAssembly->getMagAssembly();
		$propertyPath = $eiEntryGui->getContextPropertyPath()->ext($magAssembly->getMagPropertyPath());
		
		$this->state->pushField($tagName, $guiFieldPath, $validationResult, $guiFieldAssembly, $propertyPath);
		return $this->createInputFieldOpen($tagName, $propertyPath, $validationResult, $attrs);
	}
	
	/**
	 * @param string $tagName
	 * @param PropertyPath $magPropertyPath
	 * @param ValidationResult|null $validationResult
	 * @param array $attrs
	 * @param bool $mandatory
	 * @return \n2n\web\ui\Raw
	 */
	private function createInputFieldOpen($tagName, $magPropertyPath, $validationResult, $attrs) {
		$magPropertyPath = $this->formHtml->meta()->createPropertyPath($magPropertyPath);

		if ($this->formHtml->meta()->hasErrors($magPropertyPath) || ($validationResult !== null 
				&& !$validationResult->isValid(true))) {
			$attrs = HtmlUtils::mergeAttrs((array) $attrs, array('class' => 'rocket-has-error'));
		}

		return $this->formHtml->getMagOpen($tagName, $magPropertyPath, $attrs, $this->uiOutfitter);
	}
	
	private function createOutputFieldOpen($tagName, GuiFieldDisplayable $guiFieldDisplayable, ValidationResult $validationResult = null, array $attrs = null) {
		$attrs = HtmlUtils::mergeAttrs($attrs, $guiFieldDisplayable->getHtmlContainerAttrs(), true);
		if ($validationResult !== null && !$validationResult->isValid(true)) {
			$attrs = HtmlUtils::mergeAttrs((array) $attrs, array('class' => 'rocket-has-error'));
		}
		
		return new Raw('<' . HtmlUtils::hsc($tagName) . HtmlElement::buildAttrsHtml($attrs) . '>');
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
		
		$eiEntryGui = $this->state->peakEntry()['eiEntryGui'];
		return new HtmlElement('label', $attrs, $eiEntryGui->getEiGui()->getEiGuiViewFactory()
				->getGuiDefinition()->getGuiPropByGuiFieldPath($fieldInfo['guiFieldPath'])->getDisplayLabelLstr()
				->t($this->view->getN2nLocale()));
	}
	
	public function fieldContent() {
		$this->html->out($this->getFieldContent());
	}
	
	public function getFieldContent() {
		$fieldInfo = $this->state->peakField(false);
		
		if (isset($fieldInfo['propertyPath'])) {
			return $this->formHtml->getMagField();
		}
		
		return $this->html->getOut($fieldInfo['guiFieldAssembly']->getDisplayable()->createUiComponent($this->view));
	}
	
	public function fieldMessage(bool $recursive = true) {
		$this->html->out($this->getFieldMessage($recursive));
	}
	
	public function getFieldMessage(bool $recursive = true) {
		$fieldInfo = $this->state->peakField(false);
		
		if (isset($fieldInfo['propertyPath'])
				&& null !== ($message = $this->formHtml->getMessage($fieldInfo['propertyPath']))) {
			return new HtmlElement('div', array('class' => 'rocket-message-error'), $message);
		}
		
		if (isset($fieldInfo['validationResult']) 
				&& null !== ($message = $this->processMessage($fieldInfo['validationResult'], $recursive))) {
			return new HtmlElement('div', array('class' => 'rocket-message-error'),
					$message->tByDtc($this->view->getDynamicTextCollection()));
		}

		return null;
	}
	
	/**
	 * @param ValidationResult $validationResult
	 * @param bool $recursive
	 * @return Message|null
	 */
	private function processMessage(ValidationResult $validationResult, bool $recursive) {
		foreach ($validationResult->getMessages($recursive) as $message) {
			if ($message->isProcessed()) continue;
			
			$message->setProcessed(true);
			return $message;
		}
		
		return null;
	}
	
	
	public function fieldControls(array $attrs = null) {
		$this->view->out($this->getFieldControls());
	}
	
	public function getFieldControls(array $attrs = null) {
		$fieldInfo = $this->state->peakField(false);
		
		$eiEntryGui = $this->state->peakEntry()['eiEntryGui'];
		$helpTextLstr = $eiEntryGui->getEiGui()->getEiGuiViewFactory()->getGuiDefinition()
				->getGuiPropByGuiFieldPath($fieldInfo['guiFieldPath'])->getDisplayHelpTextLstr();
		$helpText = null;
		if ($helpTextLstr !== null) {
			$helpText = $helpTextLstr->t($this->view->getN2nLocale());
		}
		
		if ($helpText === null) return null;
		
		
		$div = new HtmlElement('div', HtmlUtils::mergeAttrs(array('class' => 'rocket-group-controls'), $attrs));
		
		$div->append(
				new HtmlElement('button', ['type' => 'button', 'class' => 'btn btn-secondary', 'title' => $helpText], 
						new HtmlElement('i', ['class' => 'fa fa-lightbulb-o'], '')));
		
		return $div;
	}
	
	/**
	 * @param bool $showFieldControls
	 * @param bool|DisplayStructure $showForkControls
	 * @param bool|null $showEntryControls
	 * @param int $entryControlMax
	 */
	public function toolbar(bool $showFieldControls, $showForkControls, $showEntryControls, int $entryControlMax = 6) {
		$this->view->out($this->getToolbar($showFieldControls, $showForkControls, $showEntryControls, $entryControlMax));
	}
	
	/**
	 * @param bool $showFieldControls
	 * @param bool|DisplayStructure $showForkControls
	 * @param bool $showEntryControls
	 * @param int $entryControlMax
	 * @return \n2n\impl\web\ui\view\html\HtmlElement
	 */
	public function getToolbar(bool $showFieldControls, $showForkControls, $showEntryControls, int $entryControlMax = 6) {
		$fieldControlsUi = $showFieldControls ? $this->getFieldControls() : null;
		
		$forkControlsUi = null;
		if ($showForkControls === true) {
			$forkControlsUi = $this->getEntryForkControls();
		} else if ($showForkControls instanceof DisplayStructure) {
			$forkControlsUi = $this->getEntryForkControlsFor($showForkControls);
		} else if ($showForkControls !== false) {
			ArgUtils::valType($showForkControls, ['bool', DisplayStructure::class], true, 'showForkControls');
		}
		
		if ($showEntryControls === null) {
			$showEntryControls = $this->state->peakEntry()['eiEntryGui']->getEiGui()->getEiGuiNature()
					->areEntryControlsRendered();
		} else if (!is_bool($showEntryControls)) {
			ArgUtils::valType($showForkControls, 'bool', true, '$showEntryControls');
		}
		
		$entryControlsUi = $showEntryControls ? $this->getEntryCommands(true, $entryControlMax) : null;
		
		if ($fieldControlsUi !== null || $forkControlsUi !== null || $entryControlsUi !== null) {
			return new HtmlElement('div', ['class' => 'rocket-toolbar'], 
					[$fieldControlsUi, $forkControlsUi, $entryControlsUi]);
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
			$attrs = EiuHtmlBuilderMeta::createDisplayItemAttrs($displayItem->getType(), 
					HtmlUtils::mergeAttrs((array) $displayItem->getAttrs(), (array) $attrs));
		} else if ($displayItem !== null) {
			ArgUtils::valType($displayItem, [DisplayItem::class, 'string'], true, 'displayItem');
			$attrs = EiuHtmlBuilderMeta::createDisplayItemAttrs($displayItem, (array) $attrs);
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
