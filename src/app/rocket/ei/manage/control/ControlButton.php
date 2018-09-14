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
namespace rocket\ei\manage\control;

use n2n\web\ui\Raw;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlUtils;

class ControlButton {
	const TYPE_PRIMARY = 'btn btn-primary';
	const TYPE_SECONDARY = 'btn btn-secondary';
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
	private $iconImportant = false;
	private $labelImportant = false;
	private $static = true;
	
	private $confirmMessage;
	private $confirmOkButtonLabel;
	private $confirmCancelButtonLabel;
	
	public function __construct(string $name, string $tooltip = null, bool $important = false, string $type = null, 
			string $iconType = null, array $attrs = null, bool $iconImportant = false, bool $static = true) {
		$this->name = $name;
		$this->tooltip = $tooltip;
		$this->important = $important;
		$this->type = $type;
		$this->iconType = $iconType;
		$this->attrs = (array) $attrs;
		$this->iconImportant = $iconImportant;
		$this->static = $static;
	}
	
	public function isImportant(): bool {
		return $this->important;
	}
	
	/**
	 * Button will be colored according to the type color.
	 * @param bool $important
	 * @return \rocket\ei\manage\control\ControlButton
	 */
	public function setImportant(bool $important) {
		$this->important = $important;
		return $this;
	}
	
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Button text.
	 * @param string $name
	 * @return \rocket\ei\manage\control\ControlButton
	 */
	public function setName(string $name = null) {
		$this->name = $name;
		return $this;
	}

	public function getType() {
		return $this->type;
	}
	
	/**
	 * @param string $type
	 * @return \rocket\ei\manage\control\ControlButton
	 */
	public function setType(string $type = null) {
		$this->type = $type;
		return $this;
	}
	
	public function getIconType() {
		return $this->iconType;
	}
	
	/**
	 * @param string $iconType
	 * @return \rocket\ei\manage\control\ControlButton
	 */
	public function setIconType(string $iconType = null) {
		$this->iconType = $iconType;
		return $this;
	}
	
	public function getTooltip() {
		return $this->tooltip;
	}
	
	/**
	 * @param string $tooltip
	 * @return \rocket\ei\manage\control\ControlButton
	 */
	public function setTooltip(string $tooltip = null) {
		$this->tooltip = $tooltip;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getAttrs(): array {
		return $this->attrs;
	}
	
	/**
	 * @param array $attrs
	 * @return \rocket\ei\manage\control\ControlButton
	 */
	public function setAttrs(array $attrs) {
		$this->attrs = $attrs;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isStatic() {
		return $this->static;
	}
	
	/**
	 * @param bool $static
	 * @return \rocket\ei\manage\control\ControlButton
	 */
	public function setStatic(bool $static) {
		$this->static = $static;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function isIconImportant() {
		return $this->iconImportant;
	}
	
	/**
	 * Icon will always be displayed.
	 * @param bool $iconImportant
	 * @return \rocket\ei\manage\control\ControlButton
	 */
	public function setIconImportant(bool $iconImportant) {
		$this->iconImportant = $iconImportant;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function isLabelImportant() {
		return $this->labelImportant;
	}
	
	/**
	 * Button text will always be displayed.
	 * @param bool $labelImportant
	 * @return \rocket\ei\manage\control\ControlButton
	 */
	public function setLabelImportant(bool $labelImportant) {
		$this->labelImportant = $labelImportant;
		return $this;
	}
	
	public function setConfirmMessage(?string $confirmMessage) {
		$this->confirmMessage = $confirmMessage;
	}
	
	public function getConfirmMessage() {
		return $this->confirmMessage;
	}
	
	public function setConfirmOkButtonLabel(?string $confirmOkButtonLabel) {
		$this->confirmOkButtonLabel = $confirmOkButtonLabel;
	}
	
	public function getConfirmOkButtonLabel() {
		return $this->confirmOkButtonLabel;
	}
	
	public function setConfirmCancelButtonLabel(?string $confirmCancelButtonLabel) {
		$this->confirmCancelButtonLabel = $confirmCancelButtonLabel;
	}
	
	public function getConfirmCancelButtonLabel($confirmCancelButtonLabel) {
		return $this->confirmCancelButtonLabel;
	}
	
	/**
	 * @param array $attrs
	 * @return array
	 */
	private function applyAttrs(array $attrs) {
// 		$attrs['aria-hidden'] = 'true';
		
		if ($this->tooltip !== null) {
			$attrs['title'] = $this->tooltip;
		}
		
		if (!isset($attrs['class'])) {
			$attrs['class'] = '';
		}
		
		if ($this->type !== null) {
			$attrs['class'] .= ' ' . $this->type;
		} else {
			$attrs['class'] .= ' ' . self::TYPE_SECONDARY;
		}
		
		if ($this->important) {
			$attrs['class'] .= ' rocket-important';
		}
		
		if ($this->static) {
			$attrs['class'] .= ' rocket-static';
		}
		
		if ($this->iconImportant) {
			$attrs['class'] .= ' rocket-icon-important';
		}
		
		if ($this->labelImportant) {
			$attrs['class'] .= ' rocket-label-important';
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
	
	/**
	 * @param array $attrs
	 * @param bool $useA
	 * @return UiComponent
	 */
	public function toButton(array $attrs, bool $useA = true): UiComponent {
		$iconType = $this->iconType;
		if ($iconType === null) {
			$iconType = IconType::ICON_ROCKET;
		}
		
		$label = new Raw(new HtmlElement('i', array('class' => $iconType), '') . ' '
				. new HtmlElement('span', null, $this->name));
		return new HtmlElement(($useA ? 'a' : 'button'), $this->applyAttrs($attrs), $label);
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
