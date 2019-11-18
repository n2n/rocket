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

use rocket\ei\manage\gui\EiEntryGui;
use n2n\reflection\magic\MagicMethodInvoker;
use rocket\ei\manage\gui\EiEntryGuiListener;
use rocket\ei\manage\gui\field\GuiPropPath;
use rocket\ei\manage\gui\GuiException;
use n2n\web\dispatch\mag\MagWrapper;
use rocket\ei\manage\gui\EiFieldAbstraction;
use n2n\web\dispatch\map\PropertyPath;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\util\Eiu;
use rocket\ei\util\EiuPerimeterException;
use rocket\ei\util\EiuAnalyst;
use n2n\l10n\N2nLocale;
use rocket\si\input\SiEntryInput;

class EiuEntryGui {
	private $eiEntryGui;
	private $eiuGuiFrame;
	private $eiuEntry;
	private $eiuAnalyst;
	
	function __construct(EiEntryGui $eiEntryGui, ?EiuGuiFrame $eiuGuiFrame, EiuAnalyst $eiuAnalyst) {
		$this->eiEntryGui = $eiEntryGui;
		$this->eiuGuiFrame = $eiuGuiFrame;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return EiuGuiFrame 
	 */
	function guiFrame() {
		if ($this->eiuGuiFrame !== null) {
			return $this->eiuGuiFrame;
		}
		
		return $this->eiuGuiFrame = new EiuGuiFrame($this->eiEntryGui->getEiGuiFrame(), null, $this->eiuAnalyst);
	}
	
	/**
	 * @return int
	 */
	function getViewMode() {
		return $this->eiEntryGui->getEiGuiFrame()->getViewMode();
	}
	
	/**
	 * @see EiEntryGui::getGuiIdsPaths()
	 * @return \rocket\ei\manage\gui\field\GuiPropPath[]
	 */
	function getGuiPropPaths() {
		return $this->eiEntryGui->getGuiFieldGuiPropPaths();	
	}
	
	/**
	 * @param GuiPropPath $guiPropPath
	 * @return bool
	 */
	function containsGuiPropPath(GuiPropPath $guiPropPath) {
		return $this->eiEntryGui->containsDisplayable($guiPropPath);
	}
	
	/**
	 * @param GuiPropPath|string $eiPropPath
	 * @param bool $required
	 * @throws GuiException
	 * @return string|null
	 */
	function getFieldLabel($eiPropPath, N2nLocale $n2nLocale = null, bool $required = false) {
		return $this->guiFrame()->getPropLabel($eiPropPath, $n2nLocale, $required);
	}
	
	/**
	 * @return \rocket\si\content\SiEntry
	 */
	function createSiEntry() {
		return $this->eiEntryGui->createSiEntry();
	}
	
	/**
	 * @param bool $generalSiControlsIncluded
	 * @param bool $entrySiControlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiComp
	 */
	function createCompactEntrySiComp(bool $generalSiControlsIncluded = true,
			bool $entrySiControlsIncluded = true) {
		return $this->eiEntryGui->createCompactEntrySiComp($generalSiControlsIncluded, $entrySiControlsIncluded);
	}
	
	/**
	 * @param bool $generalSiControlsIncluded
	 * @param bool $entrySiControlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiComp
	 */
	function createBulkyEntrySiComp(bool $generalSiControlsIncluded = true,
			bool $entrySiControlsIncluded = true) {
		return $this->eiEntryGui->createBulkyEntrySiComp($generalSiControlsIncluded, $entrySiControlsIncluded);
	}
	
	/**
	 * @return boolean
	 */
	function isCompact() {
		$viewMode = $this->getViewMode();
		return $viewMode & ViewMode::compact();
	}
	
	/**
	 * @return boolean
	 */
	function isBulky() {
		return (bool) ($this->getViewMode() & ViewMode::bulky());
	}
	
	/**
	 * @return boolean
	 */
	function isReadOnly() {
		return (bool) ($this->getViewMode() & ViewMode::read());
	}
	
	/**
	 * @return EiEntryGui 
	 */
	function getEiEntryGui() {
		return $this->eiEntryGui;
	}
	
	/**
	 * @return \n2n\web\dispatch\Dispatchable|null
	 */
	function getDispatchable() {
		return $this->eiEntryGui->getDispatchable();
	}
	
	/**
	 * @return boolean
	 */
	function isReady() {
		return $this->eiEntryGui->isInitialized();
	}
	
	/**
	 * @param \Closure $closure
	 */
	function whenReady(\Closure $closure) {
		$listener = new ClosureGuiListener(new Eiu($this), $closure);
		
		if ($this->isReady()) {
			$listener->finalized($this->eiEntryGui);
		} else {
			$this->eiEntryGui->registerEiEntryGuiListener($listener);
		}
	}
	
	/**
	 * @param \Closure $closure
	 */
	function onSave(\Closure $closure) {
		$this->eiEntryGui->registerEiEntryGuiListener(new ClosureGuiListener(new Eiu($this), null, $closure));
	}
	
	/**
	 * @param \Closure $closure
	 */
	function onSaved(\Closure $closure) {
		$this->eiEntryGui->registerEiEntryGuiListener(new ClosureGuiListener(new Eiu($this), null, null, $closure));
	}
	
	/**
	 * @return boolean
	 */
	function hasForkMags() {
		foreach ($this->eiEntryGui->getGuiFieldForkAssemblies() as $guiFieldForkAssembly) {
			if (!empty($guiFieldForkAssembly->getMagAssemblies())) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @param GuiPropPath|string $eiPropPath
	 * @param bool $required
	 * @throws GuiException
	 * @return MagWrapper
	 */
	function getMagWrapper($guiPropPath, bool $required = false) {
		$magWrapper = null;
		
		try {
			$magAssembly = $this->eiEntryGui->getGuiFieldAssembly(GuiPropPath::create($guiPropPath))->getMagAssembly();
			if ($magAssembly !== null) {
				return $magAssembly->getMagWrapper();
			}
			
			throw new GuiException('No GuiField with GuiPropPath \'' . $guiPropPath . '\' is not editable.');
		} catch (GuiException $e) {
			if ($required) throw $e;
			return null;
		}
	}
	
	function getSubMagWrappers($prefixGuiPropPath, bool $checkOnEiPropPathLevel = true) {
		$prefixGuiPropPath = GuiPropPath::create($prefixGuiPropPath);
		
		$magWrappers = [];
		foreach ($this->eiEntryGui->filterGuiFieldAssemblies($prefixGuiPropPath, $checkOnEiPropPathLevel)
				as $key => $guiFieldAssembly) {
			if (null !== ($magAssembly = $guiFieldAssembly->getMagAssembly())) {
				$magWrappers[$key] = $magAssembly->getMagWrapper();	
			}
		}
		
		return $magWrappers;
	}
	
	/**
	 * @param GuiPropPath|string $eiPropPath
	 * @param bool $required
	 * @throws GuiException
	 * @return EiFieldAbstraction
	 */
	function getEiFieldAbstraction($guiPropPath, bool $required = false) {
		return $this->eiuEntry->getEiFieldAbstraction($guiPropPath, $required);
	}
	
// 	/**
// 	 * 
// 	 */
// 	protected function triggerWhenReady() {
// 		if (empty($this->whenReadyClosures)) return;
		
// 		$n2nContext = null;
// 		if ($this->eiuEntry !== null && null !== ($eiuFrame = $this->eiuEntry->getEiuFrame(false))) {
// 			$n2nContext = $eiuFrame->getN2nContext();
// 		}
// 		$invoker = new MagicMethodInvoker($n2nContext);
// 		$invoker->setClassParamObject(EiuEntryGui::class, $this);
// 		while (null !== ($closure = array_shift($this->whenReadyClosures))) {
// 			$invoker->invoke(null, $closure);
// 		}
// 	}

	/**
	 * @param PropertyPath|null $propertyPath
	 */
	function setContextPropertyPath(PropertyPath $propertyPath = null) {
		$this->eiEntryGui->setContextPropertyPath($propertyPath);
	}
	
	/**
	 * @return \n2n\web\dispatch\map\PropertyPath|null
	 */
	function getContextPropertyPath() {
		return $this->eiEntryGui->getContextPropertyPath();
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\entry\EiuEntry
	 */
	function entry() {
		if ($this->eiuEntry === null) {
			$this->eiuEntry = $this->guiFrame()->getEiuFrame()->entry($this->getEiEntryGui()->getEiEntry());
		}
		
		return $this->eiuEntry;
	}
	
	/**
	 * @param GuiPropPath|string $guiPropPath
	 * @return \rocket\ei\util\entry\EiuField
	 */
	function field($guiPropPath) {
		return new EiuGuiFrameField(GuiPropPath::create($guiPropPath), $this, $this->eiuAnalyst);
	}
	
	function handleSiEntryInput(SiEntryInput $siEntryInput) {
		$this->eiEntryGui->handleSiEntryInput($siEntryInput);
	}
	
	/**
	 * 
	 */
	function save() {
		$this->eiEntryGui->save();
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuEntryGuiMulti
	 */
	function toMulti() {
		return new EiuEntryGuiMulti($this->eiEntryGui->toMulti(), $this->eiuAnalyst);
	}
	
// 	function getEiMask() {
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

	/**
	 * @param Eiu $eiu
	 * @param \Closure $whenReadyClosure
	 * @param \Closure $onSaveClosure
	 * @param \Closure $savedClosure
	 */
	function __construct(Eiu $eiu, \Closure $whenReadyClosure = null, \Closure $onSaveClosure = null,
			\Closure $savedClosure = null) {
		$this->eiu = $eiu;
		$this->whenReadyClosure = $whenReadyClosure;
		$this->onSaveClosure = $onSaveClosure;
		$this->savedClosure = $savedClosure;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiEntryGuiListener::finalized()
	 */
	function finalized(EiEntryGui $eiEntryGui) {
		if ($this->whenReadyClosure === null) return;
		
		$this->call($this->whenReadyClosure);
		
		if ($this->onSaveClosure === null || $this->savedClosure === null) {
			$eiEntryGui->unregisterEiEntryGuiListener($this);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiEntryGuiListener::onSave()
	 */
	function onSave(EiEntryGui $eiEntryGui) {
		if ($this->onSaveClosure !== null) {
			$this->call($this->onSaveClosure);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiEntryGuiListener::saved()
	 */
	function saved(EiEntryGui $eiEntryGui) {
		if ($this->savedClosure !== null) {
			$this->call($this->savedClosure);
		}
	}

	/**
	 * @param \Closure $closure
	 */
	private function call($closure) {
		$mmi = new MagicMethodInvoker($this->eiu->frame()->getEiFrame()->getN2nContext());
		$mmi->setClassParamObject(Eiu::class, $this->eiu);
		$mmi->invoke(null, new \ReflectionFunction($closure));
	}
}
