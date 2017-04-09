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
use rocket\spec\ei\manage\gui\EiEntryGui;
use n2n\reflection\magic\MagicMethodInvoker;
use rocket\spec\ei\manage\gui\EiEntryGuiListener;
use rocket\spec\ei\manage\gui\GuiIdPath;
use rocket\spec\ei\manage\gui\GuiException;
use n2n\web\dispatch\mag\MagWrapper;
use rocket\spec\ei\manage\mapping\MappableWrapper;
use n2n\web\dispatch\map\PropertyPath;

class EiuEntryGui {
	private $eiuEntry;
	protected $eiEntryGui;
	
	public function __construct(...$eiArgs) {
		$eiuFactory = new EiuFactory();
		$eiuFactory->applyEiArgs(...$eiArgs);
		
		$this->eiEntryGui = $eiuFactory->getEiEntryGui(true);
		$this->eiuEntry = $eiuFactory->getEiuEntry(false);
	}
	
	public function getViewMode() {
		return $this->eiEntryGui->getViewMode();
	}
	
	/**
	 * @return boolean
	 */
	public function isOverview() {
		$viewMode = $this->getViewMode();
		return $viewMode & DisplayDefinition::LIST_VIEW_MODES
				|| $viewMode & DisplayDefinition::TREE_VIEW_MODES;
	}
	
	/**
	 * @return boolean
	 */
	public function isBulky() {
		return (bool) ($this->getViewMode() & DisplayDefinition::BULKY_VIEW_MODES);
	}
	
	/**
	 * @return boolean
	 */
	public function isReadOnly() {
		return (bool) ($this->getViewMode() & DisplayDefinition::READ_VIEW_MODES);
	}
	
	/**
	 * @return EiEntryGui 
	 */
	public function getEiEntryGui() {
		return $this->eiEntryGui;
	}
	
	public function whenReady(\Closure $closure) {
		$this->eiEntryGui->registerEiEntryGuiListener(new ClosureGuiListener(new Eiu($this), $closure));
	}
	
	public function onSave(\Closure $closure) {
		$this->eiEntryGui->registerEiEntryGuiListener(new ClosureGuiListener(new Eiu($this), null, $closure));
	}
	
	public function whenSave(\Closure $closure) {
		$this->eiEntryGui->registerEiEntryGuiListener(new ClosureGuiListener(new Eiu($this), null, null, $closure));
	}
	
	/**
	 * @param unknown $guiIdPath
	 * @param bool $required
	 * @throws GuiException
	 * @return MagWrapper
	 */
	public function getMagWrapper($guiIdPath, bool $required = false) {
		try {
			return $this->eiEntryGui->getEditableWrapperByGuiIdPath(
					GuiIdPath::createFromExpression($guiIdPath))->getMagWrapper();
		} catch (GuiException $e) {
			if ($required) throw $e;
			return null;
		}
	}
	
	/**
	 * @param unknown $guiIdPath
	 * @param bool $required
	 * @throws GuiException
	 * @return MappableWrapper
	 */
	public function getMappableWrapper($guiIdPath, bool $required = false) {
		try {
			return $this->eiEntryGui->getMappableWrapperByGuiIdPath(
					GuiIdPath::createFromExpression($guiIdPath));
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
		$invoker->setClassParamObject(EiuEntryGui::class, $this);
		while (null !== ($closure = array_shift($this->whenReadyClosures))) {
			$invoker->invoke(null, $closure);
		}
	}

	public function setContextPropertyPath(PropertyPath $propertyPath) {
		$this->eiEntryGui->setContextPropertyPath($propertyPath);
	}
	
	public function getContextPropertyPath() {
		return $this->eiEntryGui->getContextPropertyPath();
	}
	
	public function getEiuEntry(bool $required = true) {
		if (!$required || $this->eiuEntry !== null) {
			return $this->eiuEntry;
		}
		
		throw new EiuPerimeterException('No EiuEntry provided to ' . (new \ReflectionClass($this))->getShortName());
	}
	
	/**
	 * 
	 * @return \n2n\impl\web\ui\view\html\HtmlView
	 */
	public function createBulkyView() {
		return $this->getEiuEntry()->getEiuFrame()->createBulkyView($this);
	}
		
// 	public function getEiMask() {
// 		if ($this->eiMask !== null) {
// 			return $this->eiMask;
// 		}
		
// 		throw new IllegalStateException('No EiMask available.');
// 	}
	
// 	/**
// 	 * @param EntryGuiModel $entryGuiModel
// 	 * @param EiFrame $eiFrame
// 	 * @return EiuEntryGui
// 	 */
// 	public static function from(EntryGuiModel $entryGuiModel, $eiFrame) {
// 		$entryGuiUtils = new EiuEntryGui($entryGuiModel, 
// 				new EiuEntry($entryGuiModel, $eiFrame));
// 		$entryGuiUtils->eiEntryGui = $entryGuiModel->getEiEntryGui();
// 		return $entryGuiUtils;
// 	}
}


class ClosureGuiListener implements EiEntryGuiListener {
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

	public function finalized(EiEntryGui $eiEntryGui) {
		if ($this->whenReadyClosure !== null) {
			$this->call($this->whenReadyClosure);
		}
	}

	public function onSave(EiEntryGui $eiEntryGui) {
		if ($this->onSaveClosure !== null) {
			$this->call($this->onSaveClosure);
		}
	}

	public function saved(EiEntryGui $eiEntryGui) {
		if ($this->savedClosure !== null) {
			$this->call($this->savedClosure);
		}
	}

	private function call($closure) {
		$mmi = new MagicMethodInvoker($this->eiu->frame()->getEiFrame()->getN2nContext());
		$mmi->setClassParamObject(Eiu::class, $this->eiu);
		$mmi->invoke(null, new \ReflectionFunction($closure));
	}
}
