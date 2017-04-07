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

use n2n\web\ui\Raw;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlUtils;

class ControlButton {
	const TYPE_DEFAULT = 'btn btn-default';
	const TYPE_PRIMARY = 'btn btn-primary';
	const TYPE_SUCCESS = 'btn btn-success';
	const TYPE_DANGER = 'btn btn-danger';
	const TYPE_INFO = 'btn btn-info';
	const TYPE_WARNING = 'btn btn-warning';
	
	private $name;
	private $tooltip;
	private $iconType;
	private $important;
	private $type;
	private $attrs = array();
	
	private $confirmMessage;
	private $confirmOkButtonLabel;
	private $confirmCancelButtonLabel;
	
	public function __construct(string $name, string $tooltip = null, bool $important = false, string $type = null, 
			string $iconType = null, array $attrs = null) {
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
		$attrs['aria-hidden'] = 'true';
		
		if ($this->tooltip !== null) {
			$attrs['title'] = $this->tooltip;
		}
		
		if ($this->type !== null) {
			$attrs['class'] = $this->type;
		} else {
			$attrs['class'] = self::TYPE_DEFAULT;
		}
		
		if ($this->important) {
			$attrs['class'] .= ' rocket-important';	
		}
		
		if ($this->confirmMessage !== null) {
			$attrs['data-rocket-confirm-msg'] = $this->confirmMessage;
		}
		
		if ($this->confirmOkButtonLabel !== null) {
			$attrs['data-rocket-confirm-ok-label'] = $this->confirmOkButtonLabel;
		}
		
		if ($this->confirmCancelButtonLabel !== null) {
			$attrs['data-rocket-confirm-cancel-label'] = $this->confirmCancelButtonLabel;
		}
		
		return HtmlUtils::mergeAttrs($attrs, $this->attrs);
	}
	
	public function toButton(bool $iconOnly, array $attrs): UiComponent {
		$iconType = $this->iconType;
		if ($iconType === null) {
			$iconType = IconType::ICON_ROCKET;
		}
		
		$label = new Raw(new HtmlElement('i', array('class' => $iconType), '') . ' '
				. new HtmlElement('span', null, $this->name));
		
		return new HtmlElement('a', $this->applyAttrs($attrs), $label);
	}
	
// 	public function toSubmitButton(PropertyPath $propertyPath): UiComponent {
// 		$attrs = $inputField->getAttrs();
// 		$uiButton = new HtmlElement('button', $this->applyAttrs($attrs));
// 		$uiButton->appendContent(new HtmlElement('i', array('class' => $this->iconType), ''));
// // 		$uiButton->appendLn();
// 		$uiButton->appendContent(new HtmlElement('span', null, $this->name));
// 		return $uiButton;
// 	}
}
