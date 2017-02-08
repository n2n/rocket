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

use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\manage\gui\EiSelectionGui;
use n2n\reflection\magic\MagicMethodInvoker;
use rocket\spec\ei\manage\gui\EiSelectionGuiListener;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\GuiException;
use n2n\web\dispatch\mag\MagWrapper;

class EiuGui {
	private $eiuEntry;
	protected $eiSelectionGui;
	
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		
		$this->eiSelectionGui = $eiuFactory->getEiSelectionGui(true);
		$this->eiuEntry = $eiuFactory->getEiuEntry(false);
	}
	
	public function getViewMode() {
		return $this->eiSelectionGui->getViewMode();
	}
	
	/**
	 * @return boolean
	 */
	public function isViewModeOverview() {
		$viewMode = $this->getViewMode();
		return $viewMode == DisplayDefinition::LIST_VIEW_MODES
				|| $viewMode == DisplayDefinition::TREE_VIEW_MODES;
	}
	
	public function isViewModeBulky() {
		return (bool) ($this->getViewMode() & DisplayDefinition::BULKY_VIEW_MODES);
	}
	
	/**
	 * @return EiSelectionGui 
	 */
	public function getEiSelectionGui() {
		return $this->eiSelectionGui;
	}
	
	public function whenReady(\Closure $closure) {
		$this->eiSelectionGui->registerEiSelectionGuiListener(new ClosureGuiListener(new Eiu($this), $closure));
	}
	
	public function onSave(\Closure $closure) {
		$this->eiSelectionGui->registerEiSelectionGuiListener(new ClosureGuiListener(new Eiu($this), null, $closure));
	}
	
	public function whenSave(\Closure $closure) {
		$this->eiSelectionGui->registerEiSelectionGuiListener(new ClosureGuiListener(new Eiu($this), null, null, $closure));
	}
	
	/**
	 * @param unknown $guiIdPath
	 * @param bool $required
	 * @throws GuiException
	 * @return MagWrapper
	 */
	public function getMagWrapper($guiIdPath, bool $required = false) {
		try {
			return $this->eiSelectionGui->getEditableWrapperByGuiIdPath(
					GuiIdPath::createFromExpression($guiIdPath))->getMagWrapper();
		} catch (GuiException $e) {
			if ($required) throw $e;
			return null;
		}
	}
	
	protected function triggerWhenReady() {
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
	
// 	/**
// 	 * @param EntryGuiModel $entryGuiModel
// 	 * @param EiState $eiState
// 	 * @return EiuGui
// 	 */
// 	public static function from(EntryGuiModel $entryGuiModel, $eiState) {
// 		$entryGuiUtils = new EiuGui($entryGuiModel, 
// 				new EiuEntry($entryGuiModel, $eiState));
// 		$entryGuiUtils->eiSelectionGui = $entryGuiModel->getEiSelectionGui();
// 		return $entryGuiUtils;
// 	}
}


class ClosureGuiListener implements EiSelectionGuiListener {
	private $eiu;
	private $whenReadyClosure;
	private $onSaveClosure;
	private $savedClosure;

	public function __construct(Eiu $eiu, \Closure $whenReadyClosure, \Closure $onSaveClosure = null,
			\Closure $savedClosure = null) {
		$this->eiu = $eiu;
		$this->whenReadyClosure = $whenReadyClosure;
		$this->onSaveClosure = $onSaveClosure;
		$this->savedClosure = $savedClosure;
	}

	public function finalized(EiSelectionGui $eiSelectionGui) {
		if ($this->whenReadyClosure !== null) {
			$this->call($this->whenReadyClosure);
		}
	}

	public function onSave(EiSelectionGui $eiSelectionGui) {
		if ($this->onSaveClosure !== null) {
			$this->call($this->onSaveClosure);
		}
	}

	public function saved(EiSelectionGui $eiSelectionGui) {
		if ($this->savedClosure !== null) {
			$this->call($this->savedClosure);
		}
	}

	private function call($closure) {
		$mmi = new MagicMethodInvoker($this->eiu->frame()->getEiState()->getN2nContext());
		$mmi->setClassParamObject(Eiu::class, $this->eiu);
		$mmi->invoke(null, new \ReflectionFunction($closure));
	}
}
