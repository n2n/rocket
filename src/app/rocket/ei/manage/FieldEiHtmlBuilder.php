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
use rocket\ei\manage\entry\EiFieldValidationResult;
use n2n\impl\web\ui\view\html\HtmlUtils;
use n2n\util\ex\IllegalStateException;
use n2n\web\ui\Raw;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\web\ui\UiComponent;
use rocket\ei\manage\gui\GuiFieldDisplayable;

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
	
	private function pushGuiPropInfo($tagName, EiFieldValidationResult $validationResult, GuiFieldDisplayable $guiField = null, 
			PropertyPath $propertyPath = null) {
		$this->eiPropInfoStack[] = array('tagName' => $tagName, 'displayable' => $guiField,
				'validationResult' => $validationResult, 'propertyPath' => $propertyPath);
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
	
	public function openInputField(string $tagName, $magPropertyPath, EiFieldValidationResult $validationResult, 
			array $attrs = null, bool $mandatory = false) {
		$this->view->out($this->getOpenInputField($tagName, $magPropertyPath, $validationResult, $attrs, $mandatory));
	}
	
	public function getOpenInputField(string $tagName, $magPropertyPath, EiFieldValidationResult $validationResult, 
			array $attrs = null, bool $mandatory = false) {
		$magPropertyPath = $this->formHtml->meta()->createPropertyPath($magPropertyPath);
		
		if ($this->formHtml->meta()->hasErrors($magPropertyPath) || !$validationResult->isValid()) {
			$attrs = HtmlUtils::mergeAttrs((array) $attrs, array('class' => 'rocket-has-error'));
		}
	
		$this->pushGuiPropInfo($tagName, $validationResult, null, $magPropertyPath);
		return $this->formHtml->getMagOpen($tagName, $magPropertyPath, 
				$this->buildContainerAttrs((array) $attrs, false, $mandatory), $this->uiOutfitter);
	}
	
	public function openOutputField($tagName, GuiFieldDisplayable $displayable, EiFieldValidationResult $validationResult, array $attrs = null) {
		$this->view->out($this->getOpenOutputField($tagName, $displayable, $validationResult, $attrs));
	}
	
	public function getOpenOutputField($tagName, GuiFieldDisplayable $displayable, EiFieldValidationResult $validationResult, array $attrs = null) {
		$this->pushGuiPropInfo($tagName, $validationResult, $displayable);
		
		return new Raw('<' . HtmlUtils::hsc($tagName) . HtmlElement::buildAttrsHtml(
				$this->buildContainerAttrs(HtmlUtils::mergeAttrs($displayable->getHtmlContainerAttrs(), $attrs))) . '>');
	}
	
	public function closeField() {
		$this->view->out($this->getCloseField());
	}
	
	public function getCloseField() {
		$eiPropInfo = $this->peakEiPropInfo(true);
		if (isset($eiPropInfo['propertyPath'])) {
			return $this->formHtml->getMagClose();
		}
	
		return new Raw('</' . HtmlUtils::hsc($eiPropInfo['tagName']) . '>');
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
				
		return $this->html->getOut($eiPropInfo['displayable']->createUiComponent($this->view));
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

		$messages = $eiPropInfo['validationResult']->getMessages();
		
		foreach ($messages as $message) {
			if ($message->isProcessed()) continue;
			
			$message->setProcessed(true);
			return new HtmlElement('div', array('class' => 'rocket-message-error'), 
					$message->tByDtc($this->view->getDynamicTextCollection()));
		}

		return null;
	}
}
