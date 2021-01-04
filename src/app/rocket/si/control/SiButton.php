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
namespace rocket\si\control;

class SiButton implements \JsonSerializable {
	const TYPE_PRIMARY = 'btn btn-primary';
	const TYPE_SECONDARY = 'btn btn-secondary';
	const TYPE_SUCCESS = 'btn btn-success';
	const TYPE_DANGER = 'btn btn-danger';
	const TYPE_INFO = 'btn btn-info';
	const TYPE_WARNING = 'btn btn-warning';
	
	private $name;
	private $tooltip;
	private $iconType = SiIconType::ICON_ROCKET;
	private $important;
	private $type;
	private $attrs = array();
	private $iconImportant = false;
	private $labelImportant = false;
// 	private $static = true;
	
	private $confirm;
	
	public function __construct(string $name, string $tooltip = null, bool $important = false, string $type = null, 
			string $iconType = null, array $attrs = null, bool $iconImportant = false/*, bool $static = true*/) {
		$this->name = $name;
		$this->tooltip = $tooltip;
		$this->important = $important;
		$this->type = $type ?? self::TYPE_SECONDARY;
		$this->iconType = $iconType ?? SiIconType::ICON_ROCKET;
		$this->iconImportant = $iconImportant;
// 		$this->static = $static;
	}
	
	public function isImportant(): bool {
		return $this->important;
	}
	
	/**
	 * Button will be colored according to the type color.
	 * @param bool $important
	 * @return \rocket\si\control\SiButton
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
	 * @return \rocket\si\control\SiButton
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
	 * @return \rocket\si\control\SiButton
	 */
	public function setType(string $type) {
		$this->type = $type;
		return $this;
	}
	
	public function getIconType() {
		return $this->iconType;
	}
	
	/**
	 * @param string $iconType
	 * @return \rocket\si\control\SiButton
	 */
	public function setIconType(string $iconType) {
		$this->iconType = $iconType;
		return $this;
	}
	
	public function getTooltip() {
		return $this->tooltip;
	}
	
	/**
	 * @param string $tooltip
	 * @return \rocket\si\control\SiButton
	 */
	public function setTooltip(string $tooltip = null) {
		$this->tooltip = $tooltip;
		return $this;
	}
	
// 	/**
// 	 * @return bool
// 	 */
// 	public function isStatic() {
// 		return $this->static;
// 	}
	
// 	/**
// 	 * @param bool $static
// 	 * @return \rocket\si\control\SiButton
// 	 */
// 	public function setStatic(bool $static) {
// 		$this->static = $static;
// 		return $this;
// 	}
	
	/**
	 * @return boolean
	 */
	public function isIconImportant() {
		return $this->iconImportant;
	}
	
	/**
	 * Icon will always be displayed.
	 * @param bool $iconImportant
	 * @return \rocket\si\control\SiButton
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
	 * @return \rocket\si\control\SiButton
	 */
	public function setLabelImportant(bool $labelImportant) {
		$this->labelImportant = $labelImportant;
		return $this;
	}
	
	/**
	 * @param SiConfirm $confirm
	 * @return \rocket\si\control\SiButton
	 */
	public function setConfirm(?SiConfirm $confirm) {
		$this->confirm = $confirm;
		return $this;
	}
	
	/**
	 * @return \rocket\si\control\SiConfirm
	 */
	public function getConfirm() {
		return $this->confirm;
	}
	
	public function jsonSerialize() {
		return [
			'name' => $this->name,
			'tooltip' => $this->tooltip,
			'iconClass' => $this->iconType,
			'btnClass' => $this->type,
			'important' => $this->important,
			'iconImportant' => $this->iconImportant,
			'labelImportant' => $this->labelImportant,
			'confirm' => $this->confirm
		];
	}
	
	
	/**
	 * @param string $name
	 * @param string $siIconType
	 * @return \rocket\si\control\SiButton
	 */
	static function primary(string $name, string $siIconType) {
		return new SiButton($name, null, false, self::TYPE_PRIMARY, $siIconType);
	}
	
	/**
	 * @param string $name
	 * @param string $siIconType
	 * @return \rocket\si\control\SiButton
	 */
	static function secondary(string $name, string $siIconType) {
		return new SiButton($name, null, false, self::TYPE_SECONDARY, $siIconType);
	}
	
	/**
	 * @param string $name
	 * @param string $siIconType
	 * @return \rocket\si\control\SiButton
	 */
	static function success(string $name, string $siIconType) {
		return new SiButton($name, null, false, self::TYPE_SUCCESS, $siIconType);
	}
	
	/**
	 * @param string $name
	 * @param string $siIconType
	 * @return \rocket\si\control\SiButton
	 */
	static function danger(string $name, string $siIconType) {
		return new SiButton($name, null, false, self::TYPE_DANGER, $siIconType);
	}
	
	/**
	 * @param string $name
	 * @param string $siIconType
	 * @return \rocket\si\control\SiButton
	 */
	static function info(string $name, string $siIconType) {
		return new SiButton($name, null, false, self::TYPE_INFO, $siIconType);
	}
	
	/**
	 * @param string $name
	 * @param string $siIconType
	 * @return \rocket\si\control\SiButton
	 */
	static function warning(string $name, string $siIconType) {
		return new SiButton($name, null, false, self::TYPE_WARNING, $siIconType);
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
