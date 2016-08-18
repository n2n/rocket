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
namespace rocket\spec\ei\manage\control;

use n2n\web\ui\view\impl\html\InputField;

use n2n\web\ui\Raw;

use n2n\web\ui\view\impl\html\HtmlElement;
use n2n\util\uri\Url;
use n2n\web\ui\UiComponent;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\ui\view\impl\html\HtmlUtils;


class ControlButton {
	const TYPE_DEFAULT = null;
	const TYPE_SUCCESS = 'success';
	const TYPE_DANGER = 'danger';
	const TYPE_INFO = 'info';
	const TYPE_WARNING = 'warning';
	
	private $name;
	private $tooltip;
	private $important;
	private $type;
	private $iconType;
	private $attrs = array();
	
	public function __construct(string $name, string $tooltip = null, bool $important = false, 
			string $type = self::TYPE_DEFAULT, string $iconType = null, array $attrs = null) {
		$this->name = $name;
		$this->tooltip = $tooltip;
		$this->important = $important;
		$this->type = $type;
		$this->iconType = $iconType;
		$this->attrs = (array) $attrs;
	}
	
	public function isImportant(): bool {
		return $this->important;
	}
	
	public function setImportant(bool $important) {
		$this->important = $important;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName(string $name = null) {
		$this->name = $name;
	}

	public function getType() {
		return $this->type;
	}
	
	public function setType(string $type = null) {
		$this->type = $type;
	}
	
	public function getIconType() {
		return $this->iconType;
	}
	
	public function setIconType(string $iconType = null) {
		$this->iconType = $iconType;
	}
	
	public function getTooltip() {
		return $this->tooltip;
	}
	
	public function setTooltip(string $tooltip = null) {
		$this->tooltip = $tooltip;
	}
	
	public function getAttrs(): array {
		return $this->attrs;
	}
	
	public function setAttrs(array $attrs) {
		$this->attrs = $attrs;
	}
	
	public function setConfirmMessage($confirmMessage) {
		$this->confirmMessage = $confirmMessage;
	}
	
	public function getConfirmMessage() {
		return $this->confirmMessage;
	}
	
	public function setConfirmOkButtonLabel($confirmOkButtonLabel) {
		$this->confirmOkButtonLabel = $confirmOkButtonLabel;
	}
	
	public function getConfirmOkButtonLabel() {
		return $this->confirmOkButtonLabel;
	}
	
	public function setConfirmCancelButtonLabel($confirmCancelButtonLabel) {
		$this->confirmCancelButtonLabel = $confirmCancelButtonLabel;
	}
	
	public function getConfirmCancelButtonLabel($confirmCancelButtonLabel) {
		return $this->confirmCancelButtonLabel;
	}
	
	private function applyAttrs(array $attrs) {
		
		if ($this->tooltip !== null) {
			$attrs['title'] = $this->tooltip;
		}
		if ($this->type !== null) {
			$attrs['class'] = 'rocket-control-' . $this->type;
		} else {
			$attrs['class'] = 'rocket-control';
		}
		if ($this->important) {
			$attrs['class'] .= ' rocket-important';	
		}
		if (isset($this->confirmMessage)) {
			$attrs['data-rocket-confirm-msg'] = $this->confirmMessage;
		}
		if (isset($this->confirmOkButtonLabel)) {
			$attrs['data-rocket-confirm-ok-label'] = $this->confirmOkButtonLabel;
		}
		if (isset($this->confirmCancelButtonLabel)) {
			$attrs['data-rocket-confirm-cancel-label'] = $this->confirmCancelButtonLabel;
		}
		
		return HtmlUtils::mergeAttrs($attrs, $this->attrs);
	}
	
	public function toButton(string $href = null, bool $iconOnly): UiComponent {
		$label = new Raw(new HtmlElement('i', array('class' => $this->iconType), '') . ' '
				. new HtmlElement('span', null, $this->name));
		
		return new HtmlElement('a', $this->applyAttrs(array('href' => $href)), $label);
	}
	
	public function toSubmitButton(PropertyPath $propertyPath): UiComponent {
		$attrs = $inputField->getAttrs();
		$uiButton = new HtmlElement('button', $this->applyAttrs($attrs));
		$uiButton->appendContent(new HtmlElement('i', array('class' => $this->iconType), ''));
// 		$uiButton->appendNl();
		$uiButton->appendContent(new HtmlElement('span', null, $this->name));
		return $uiButton;
	}
}
