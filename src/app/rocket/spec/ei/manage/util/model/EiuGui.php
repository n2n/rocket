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
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\model\EntryGuiModel;
use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\manage\gui\EiSelectionGui;
use n2n\reflection\magic\MagicMethodInvoker;
use rocket\spec\ei\manage\gui\EiSelectionGuiListener;

class EiuGui {
	private $eiuEntry;
	protected $eiSelectionGui;
	
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		
		$this->eiSelectionGui = $eiuFactory->getEiSelectionGui(true);
		$this->eiuEntry = $eiuFactory->getEiuEntry(false);
		
		$this->eiSelectionGui->registerEiSelectionGuiListener(new class($this) implements EiSelectionGuiListener {
			private $eiuGui;
			
			public function __construct(EiuGui $eiuGui) {
				$this->eiuGui = $eiuGui;
			}
			
			public function finalized(EiSelectionGui $eiSelectionGui) {
				$this->eiuGui->triggerWhenReady();
			}
			
			public function onSave(EiSelectionGui $eiSelectionGui) {
			}
			
			public function saved(EiSelectionGui $eiSelectionGui) {
			}
		});
	}
	
	public function getViewMode() {
		return $this->eiSelectionGui->getViewMode();
	}
	
	/**
	 * @return EiSelectionGui 
	 */
	public function getEiSelectionGui() {
		return $this->eiSelectionGui;
	}
	
	public function ready(\Closure $closure) {
		$this->whenReadyClosures[] = $closure;
		
		if ($this->eiSelectionGui->isInitialized()) {
			$this->triggerWhenReady();
		}
	}
	
	private function triggerWhenReady() {
		if (empty($this->whenReadyClosures)) return;
		
		$n2nContext = null;
		if ($this->eiuEntry !== null && null !== ($eiuFrame = $this->eiuEntry->getEiuFrame(false))) {
			$n2nContext = $eiuFrame->getN2nContext();
		}
		$invoker = new MagicMethodInvoker($n2nContext);
		$invoker->setClassParamObject(EiuGui::class, $this);
		while (null !== ($closure = array_shift($this->whenReadyClosures))) {
			$invoker->invoke(null, $closure);
		}
	}

	/**
	 * @return boolean
	 */
	public function isViewModeOverview() {
		$viewMode = $this->eiSelectionGui->getViewMode();
		return $viewMode == DisplayDefinition::VIEW_MODE_LIST_READ
				|| $viewMode == DisplayDefinition::VIEW_MODE_TREE_READ;
	}
	
	public function getEiuEntry(bool $required = true) {
		if (!$required || $this->eiuEntry !== null) {
			return $this->eiuEntry;
		}
		
		throw new EiuPerimeterException('No EiuEntry provided to ' . (new \ReflectionClass($this))->getShortName());
	}
	
// 	public function getEiMask() {
// 		if ($this->eiMask !== null) {
// 			return $this->eiMask;
// 		}
		
// 		throw new IllegalStateException('No EiMask available.');
// 	}
	
	/**
	 * @param EntryGuiModel $entryGuiModel
	 * @param EiState $eiState
	 * @return EiuGui
	 */
	public static function from(EntryGuiModel $entryGuiModel, $eiState) {
		$entryGuiUtils = new EiuGui($entryGuiModel, 
				new EiuEntry($entryGuiModel, $eiState));
		$entryGuiUtils->eiSelectionGui = $entryGuiModel->getEiSelectionGui();
		return $entryGuiUtils;
	}
}
