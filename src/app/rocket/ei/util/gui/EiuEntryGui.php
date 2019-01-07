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
use rocket\ei\manage\gui\GuiFieldPath;
use rocket\ei\manage\gui\GuiException;
use n2n\web\dispatch\mag\MagWrapper;
use rocket\ei\manage\gui\EiFieldAbstraction;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\util\Eiu;
use rocket\ei\util\EiuPerimeterException;
use rocket\ei\util\EiuAnalyst;
use n2n\l10n\N2nLocale;

class EiuEntryGui {
	private $eiEntryGui;
	private $eiuGui;
	private $eiuEntry;
	private $eiuAnalyst;
	
	public function __construct(EiEntryGui $eiEntryGui, ?EiuGui $eiuGui, EiuAnalyst $eiuAnalyst) {
		$this->eiEntryGui = $eiEntryGui;
		$this->eiuGui = $eiuGui;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return EiuGui 
	 */
	public function gui() {
		if ($this->eiuGui !== null) {
			return $this->eiuGui;
		}
		
		return $this->eiuGui = new EiuGui($this->eiEntryGui->getEiGui(), null, $this->eiuAnalyst);
	}
	
	/**
	 * @return int
	 */
	public function getViewMode() {
		return $this->eiEntryGui->getEiGui()->getViewMode();
	}
	
	/**
	 * @see EiEntryGui::getGuiIdsPaths()
	 * @return \rocket\ei\manage\gui\GuiFieldPath[]
	 */
	public function getGuiFieldPaths() {
		return $this->eiEntryGui->getGuiFieldGuiFieldPaths();	
	}
	
	/**
	 * @param GuiFieldPath $guiFieldPath
	 * @return bool
	 */
	public function containsGuiFieldPath(GuiFieldPath $guiFieldPath) {
		return $this->eiEntryGui->containsDisplayable($guiFieldPath);
	}
	
	/**
	 * @param GuiFieldPath|string $eiPropPath
	 * @param bool $required
	 * @throws GuiException
	 * @return string|null
	 */
	public function getFieldLabel($eiPropPath, N2nLocale $n2nLocale = null, bool $required = false) {
		return $this->gui()->getPropLabel($eiPropPath, $n2nLocale, $required);
	}
	
	/**
	 * @return boolean
	 */
	public function isCompact() {
		$viewMode = $this->getViewMode();
		return $viewMode & ViewMode::compact();
	}
	
	/**
	 * @return boolean
	 */
	public function isBulky() {
		return (bool) ($this->getViewMode() & ViewMode::bulky());
	}
	
	/**
	 * @return boolean
	 */
	public function isReadOnly() {
		return (bool) ($this->getViewMode() & ViewMode::read());
	}
	
	/**
	 * @return EiEntryGui 
	 */
	public function getEiEntryGui() {
		return $this->eiEntryGui;
	}
	
	/**
	 * @return \n2n\web\dispatch\Dispatchable|null
	 */
	public function getDispatchable() {
		return $this->eiEntryGui->getDispatchable();
	}
	
	/**
	 * @return boolean
	 */
	public function isReady() {
		return $this->eiEntryGui->isInitialized();
	}
	
	/**
	 * @param \Closure $closure
	 */
	public function whenReady(\Closure $closure) {
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
	public function onSave(\Closure $closure) {
		$this->eiEntryGui->registerEiEntryGuiListener(new ClosureGuiListener(new Eiu($this), null, $closure));
	}
	
	/**
	 * @param \Closure $closure
	 */
	public function onSaved(\Closure $closure) {
		$this->eiEntryGui->registerEiEntryGuiListener(new ClosureGuiListener(new Eiu($this), null, null, $closure));
	}
	
	/**
	 * @return boolean
	 */
	public function hasForkMags() {
		foreach ($this->eiEntryGui->getGuiFieldForkAssemblies() as $guiFieldForkAssembly) {
			if (!empty($guiFieldForkAssembly->getMagAssemblies())) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @param GuiFieldPath|string $eiPropPath
	 * @param bool $required
	 * @throws GuiException
	 * @return MagWrapper
	 */
	public function getMagWrapper($guiFieldPath, bool $required = false) {
		$magWrapper = null;
		
		try {
			$magAssembly = $this->eiEntryGui->getGuiFieldAssembly(GuiFieldPath::create($guiFieldPath))->getMagAssembly();
			if ($magAssembly !== null) {
				return $magAssembly->getMagWrapper();
			}
			
			throw new GuiException('No GuiField with GuiFieldPath \'' . $guiFieldPath . '\' is not editable.');
		} catch (GuiException $e) {
			if ($required) throw $e;
			return null;
		}
	}
	
	public function getSubMagWrappers($prefixGuiFieldPath, bool $checkOnEiPropPathLevel = true) {
		$prefixGuiFieldPath = GuiFieldPath::create($prefixGuiFieldPath);
		
		$magWrappers = [];
		foreach ($this->eiEntryGui->filterGuiFieldAssemblies($prefixGuiFieldPath, $checkOnEiPropPathLevel)
				as $key => $guiFieldAssembly) {
			if (null !== ($magAssembly = $guiFieldAssembly->getMagAssembly())) {
				$magWrappers[$key] = $magAssembly->getMagWrapper();	
			}
		}
		
		return $magWrappers;
	}
	
	/**
	 * @param GuiFieldPath|string $eiPropPath
	 * @param bool $required
	 * @throws GuiException
	 * @return EiFieldAbstraction
	 */
	public function getEiFieldAbstraction($guiFieldPath, bool $required = false) {
		return $this->eiuEntry->getEiFieldAbstraction($guiFieldPath, $required);
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
	public function setContextPropertyPath(PropertyPath $propertyPath = null) {
		$this->eiEntryGui->setContextPropertyPath($propertyPath);
	}
	
	/**
	 * @return \n2n\web\dispatch\map\PropertyPath|null
	 */
	public function getContextPropertyPath() {
		return $this->eiEntryGui->getContextPropertyPath();
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\entry\EiuEntry
	 */
	public function entry() {
		if ($this->eiuEntry === null) {
			$this->eiuEntry = $this->gui()->getEiuFrame()->entry($this->getEiEntryGui()->getEiEntry());
		}
		
		return $this->eiuEntry;
	}
	
// 	/**
// 	 * @return \rocket\ei\util\gui\EiuEntryGui
// 	 */
// 	public function addDisplayContainer(string $type, string $label, array $attrs = null) {
// 		$this->gui()->addDisplayContainer($type, $label, $attrs);
// 		return $this;
// 	}
		
// 	/**
// 	 * @return \rocket\ei\util\gui\EiuEntryGui
// 	 */
// 	public function removeGroups() {
// 		$this->gui()->removeSubStructures();
// 		return $this;
// 	}
	
// 	/**
// 	 * @return \rocket\ei\util\gui\EiuEntryGui
// 	 */
// 	public function forceRootGroups() {
// 		$this->gui()->forceRootGroups();
// 		return $this;
// 	}
	
// 	/**
// 	 * @return \rocket\ei\util\gui\EiuEntryGui
// 	 */
// 	public function renderEntryControls(bool $renderEntryControls = true) {
// 		$this->gui()->renderEntryControls($renderEntryControls);
// 		return $this;
// 	}
	
// 	/**
// 	 * @return \rocket\ei\util\gui\EiuEntryGui
// 	 */
// 	public function renderForkControls(bool $renderForkControls = true) {
// 		$this->gui()->renderForkControls($renderForkControls);
// 		return $this;
// 	}
		
	/**
	 * @return \rocket\ei\manage\gui\MagAssembly[]
	 */
	public function getAllForkMagAssemblies() {
		return $this->eiEntryGui->getAllForkMagAssemblies();
	}
	
	/**
	 * 
	 * @return \n2n\impl\web\ui\view\html\HtmlView
	 */
	public function createView(HtmlView $contextView = null) {
		return $this->gui()->createView($contextView);
	}
	
	/**
	 * 
	 */
	public function save() {
		$this->eiEntryGui->save();
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

	/**
	 * @param Eiu $eiu
	 * @param \Closure $whenReadyClosure
	 * @param \Closure $onSaveClosure
	 * @param \Closure $savedClosure
	 */
	public function __construct(Eiu $eiu, \Closure $whenReadyClosure = null, \Closure $onSaveClosure = null,
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
	public function finalized(EiEntryGui $eiEntryGui) {
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
	public function onSave(EiEntryGui $eiEntryGui) {
		if ($this->onSaveClosure !== null) {
			$this->call($this->onSaveClosure);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiEntryGuiListener::saved()
	 */
	public function saved(EiEntryGui $eiEntryGui) {
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
