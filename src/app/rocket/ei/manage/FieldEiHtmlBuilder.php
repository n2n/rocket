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
use n2n\web\dispatch\map\PropertyPath;
use rocket\ei\manage\mapping\FieldErrorInfo;
use n2n\impl\web\ui\view\html\HtmlUtils;
use rocket\ei\manage\gui\Displayable;
use n2n\util\ex\IllegalStateException;
use n2n\web\ui\Raw;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\web\ui\UiComponent;
use n2n\l10n\MessageTranslator;

class FieldEiHtmlBuilder {
	private $view;
	private $html;
	private $formHtml;
	private $uiOutfitter;
	private $eiPropInfoStrack = array();
	
	public function __construct(HtmlView $view) {
		$this->view = $view;
		$this->html = $view->getHtmlBuilder();
		$this->formHtml = $view->getFormHtmlBuilder();
		$this->uiOutfitter = new RocketUiOutfitter();
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
	
	private function pushGuiPropInfo($tagName, FieldErrorInfo $fieldErrorInfo, Displayable $displayable = null, 
			PropertyPath $propertyPath = null) {
		$this->eiPropInfoStack[] = array('tagName' => $tagName, 'displayable' => $displayable,
				'fieldErrorInfo' => $fieldErrorInfo, 'propertyPath' => $propertyPath);
	}

	public function peakEiPropInfo($pop) {
		if (!sizeof($this->eiPropInfoStack)) {
			throw new IllegalStateException('No EiProp open');
		}
	
		if ($pop) {
			return array_pop($this->eiPropInfoStack);
		} else {
			return end($this->eiPropInfoStack);
		}
	}
	
	public function openInputField(string $tagName, $magPropertyPath, FieldErrorInfo $fieldErrorInfo, 
			array $attrs = null, bool $mandatory = false) {
		$this->view->out($this->getOpenInputField($tagName, $magPropertyPath, $fieldErrorInfo, $attrs, $mandatory));
	}
	
	public function getOpenInputField(string $tagName, $magPropertyPath, FieldErrorInfo $fieldErrorInfo, 
			array $attrs = null, bool $mandatory = false) {
		$magPropertyPath = $this->formHtml->meta()->createPropertyPath($magPropertyPath);
		
		if ($this->formHtml->meta()->hasErrors($magPropertyPath) || !$fieldErrorInfo->isValid()) {
			$attrs = HtmlUtils::mergeAttrs((array) $attrs, array('class' => 'rocket-has-error'));
		}
	
		$this->pushGuiPropInfo($tagName, $fieldErrorInfo, null, $magPropertyPath);
		return $this->formHtml->getMagOpen($tagName, $magPropertyPath, 
				$this->buildContainerAttrs((array) $attrs, false, $mandatory), $this->uiOutfitter);
	}
	
	public function openOutputField($tagName, Displayable $displayable, FieldErrorInfo $fieldErrorInfo, array $attrs = null) {
		$this->view->out($this->getOpenOutputField($tagName, $displayable, $fieldErrorInfo, $attrs));
	}
	
	public function getOpenOutputField($tagName, Displayable $displayable, FieldErrorInfo $fieldErrorInfo, array $attrs = null) {
		$this->pushGuiPropInfo($tagName, $fieldErrorInfo, $displayable);
		
		return new Raw('<' . htmlspecialchars($tagName) . HtmlElement::buildAttrsHtml(
				$this->buildContainerAttrs(HtmlUtils::mergeAttrs($displayable->getOutputHtmlContainerAttrs(), $attrs))) . '>');
	}
	
	public function closeField() {
		$this->view->out($this->getCloseField());
	}
	
	public function getCloseField() {
		$eiPropInfo = $this->peakEiPropInfo(true);
		if (isset($eiPropInfo['propertyPath'])) {
			return $this->formHtml->getMagClose();
		}
	
		return new Raw('</' . htmlspecialchars($eiPropInfo['tagName']) . '>');
	}
	
	public function label(array $attrs = null, $label = null) {
		$this->view->out($this->getLabel($attrs, $label));
	}
	
	public function getLabel(array $attrs = null, $label = null) {
		$eiPropInfo = $this->peakEiPropInfo(false);
	
		if (isset($eiPropInfo['propertyPath'])) {
			return $this->formHtml->getMagLabel($attrs, $label);
		}
	
		return new HtmlElement('label', $attrs, ($label === null ? $eiPropInfo['displayable']->getUiOutputLabel() : $label));
	}
	
	public function field() {
		$this->view->out($this->getField());
	}
	
	public function getField(): UiComponent {
		$eiPropInfo = $this->peakEiPropInfo(false);
	
		if (isset($eiPropInfo['propertyPath'])) {
			return $this->formHtml->getMagField();
		}
				
		return $this->html->getOut($eiPropInfo['displayable']->createOutputUiComponent($this->view));
	}
	
	public function message() {
		$this->view->out($this->getMessage());
	}
	
	public function getMessage() {
		$eiPropInfo = $this->peakEiPropInfo(false);
	
		if (isset($eiPropInfo['propertyPath'])
				&& null !== ($message = $this->formHtml->getMessage($eiPropInfo['propertyPath']))) {
			return new HtmlElement('div', array('class' => 'rocket-message-error'), $message);
		}

		if (null !== ($message = $eiPropInfo['fieldErrorInfo']->processMessage())) {
			$messageTranslator = new MessageTranslator($this->view->getModuleNamespace(),
					$this->view->getN2nLocale());
			
			return new HtmlElement('div', array('class' => 'rocket-message-error'), 
					$messageTranslator->translate($message));
		}

		return null;
	}
}
