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
use rocket\ei\manage\gui\GuiFieldAssembly;
use rocket\ei\manage\control\Control;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\control\GroupControl;
use rocket\ei\manage\control\ControlButton;
use rocket\ei\manage\control\IconType;
use rocket\ei\manage\gui\ui\DisplayItem;

class EiuHtmlBuilderMeta {
	private $state;
	private $view;
	
	/**
	 * @param EiuHtmlBuilderState $state
	 */
	public function __construct(EiuHtmlBuilderState $state, HtmlView $view) {
		$this->state = $state;
		$this->view = $view;
	}
	
	
// 	function getExtraGuiMessages($eiEntryGuiArg) {
// 		$eiEntryGui = EiuAnalyst::buildEiEntryGuiFromEiArg($eiEntryGui);
		
// 		$eiEntryGui->getEiEntry()->getMappingErrorInfo()->getEiFieldValidationResults()
// // 		$this->
// 	}
	
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
