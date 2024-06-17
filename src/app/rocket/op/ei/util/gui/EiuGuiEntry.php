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
namespace rocket\op\ei\util\gui;

use rocket\ui\gui\GuiEntry;
use n2n\reflection\magic\MagicMethodInvoker;
use rocket\op\ei\manage\DefPropPath;
use rocket\ui\gui\ViewMode;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\op\ei\manage\frame\EiFrameUtil;
use rocket\ui\si\content\SiEntry;
use rocket\ui\gui\EiGuiEntryListener;

class EiuGuiEntry {
//	private $eiuGuiMaskDeclaration;
	
	function __construct(private GuiEntry $eiGuiEntry,
			private ?EiuEntry $eiuEntry, private ?EiuGuiMaskDeclaration $eiuGuiMaskDeclaration,
			private readonly EiuAnalyst $eiuAnalyst) {
	}
	
// 	private function getEiGuiDeclaration() {
// 		return $this->eiuGui->getEiGuiDeclaration() ?? $this->eiuAnalyst->getEiGuiDeclaration(true);
// 	}
	
	/**
	 * @return int
	 */
	function getViewMode(): int {
		return $this->eiGuiEntry->getEiGuiMaskDeclaration()->getViewMode();
	}
	
//	/**
//	 * @return \rocket\op\ei\manage\DefPropPath[]
//	 *@see EiGuiEntry::getGuiIdsPaths()
//	 */
//	function getDefPropPaths() {
//		return $this->eiGuiEntry->getGuiFieldDefPropPaths();
//	}
	
//	/**
//	 * @param DefPropPath $defPropPath
//	 * @return bool
//	 */
//	function containsDefPropPath(DefPropPath $defPropPath) {
//		return $this->eiGuiEntry->containsDisplayable($defPropPath);
//	}
	
//	/**
//	 * @param DefPropPath|string $eiPropPath
//	 * @param bool $required
//	 * @throws GuiException
//	 * @return string|null
//	 */
//	function getFieldLabel($eiPropPath, N2nLocale $n2nLocale = null, bool $required = false) {
//		return $this->guiFrame()->getPropLabel($eiPropPath, $n2nLocale, $required);
//	}
	

	
//	/**
//	 * @return \rocket\op\ei\util\spec\EiuType
//	 * @throws IllegalStateException if $required true and not type is selected.
//	 */
//	function selectedType(bool $required = true) {
//		if (!$this->isTypeSelected()) {
//			if (!$required) return null;
//		}
//
//		return new EiuType($this->eiGuiEntry->getSelectedEiGuiEntry()->getEiMask()->getEiType(), $this->eiuAnalyst);
//	}
	
	/**
	 * @return boolean
	 */
	function isCompact(): bool {
		$viewMode = $this->getViewMode();
		return $viewMode & ViewMode::compact();
	}
	
	/**
	 * @return boolean
	 */
	function isBulky(): bool {
		return (bool) ($this->getViewMode() & ViewMode::bulky());
	}
	
	/**
	 * @return boolean
	 */
	function isReadOnly(): bool {
		return (bool) ($this->getViewMode() & ViewMode::read());
	}
	
	/**
	 * @return GuiEntry
	 */
	function getEiGuiEntry(): GuiEntry {
		return $this->eiGuiEntry;
	}
	
// 	/**
// 	 * @param DefPropPath|string $defPropPath
// 	 * @return SiField[]
// 	 */
// 	function getSiField($defPropPath) {
// 		$defPropPath = DefPropPath::create($defPropPath);
// 		return $this->eiGuiEntry->getGuiFieldByDefPropPath($defPropPath)->getSiField();
// 	}
	
// 	/**
// 	 * @param DefPropPath|string $defPropPath
// 	 * @return SiField[]
// 	 */
// 	function getContextSiFields($defPropPath) {
// 		$defPropPath = DefPropPath::create($defPropPath);
// 		return $this->eiGuiEntry->getGuiFieldByDefPropPath($defPropPath)->getContextSiFields();
// 	}
	
	/**
	 * @return boolean
	 */
	function isReady(): bool {
		return $this->eiGuiEntry->isInitialized();
	}

	function whenReady(\Closure $closure): static {
		$listener = new ClosureGuiListener(new Eiu($this), $closure);
		
		if ($this->isReady()) {
			$listener->finalized($this->eiGuiEntry);
		} else {
			$this->eiGuiEntry->registerEiGuiEntryListener($listener);
		}

		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 */
	function onSave(\Closure $closure): void {
		$this->eiGuiEntry->registerEiGuiEntryListener(new ClosureGuiListener(new Eiu($this), null, $closure));
	}
	
	/**
	 * @param \Closure $closure
	 */
	function onSaved(\Closure $closure): void {
		$this->eiGuiEntry->registerEiGuiEntryListener(new ClosureGuiListener(new Eiu($this), null, null, $closure));
	}


	
//	/**
//	 * @param DefPropPath|string $eiPropPath
//	 * @param bool $required
//	 * @throws GuiException
//	 * @return EiFieldAbstraction
//	 */
//	function getEiFieldAbstraction($defPropPath, bool $required = false) {
//		return $this->eiuEntry->getEiFieldAbstraction($defPropPath, $required);
//	}
	
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
// 		$invoker->setClassParamObject(EiuGuiEntry::class, $this);
// 		while (null !== ($closure = array_shift($this->whenReadyClosures))) {
// 			$invoker->invoke(null, $closure);
// 		}
// 	}

	function entry(): EiuEntry {
		if ($this->eiuEntry === null) {
			$this->eiuEntry = new EiuEntry($this->getEiGuiEntry()->getEiEntry(), null, null, $this->eiuAnalyst);
		}
		
		return $this->eiuEntry;
	}

	function guiMaskDeclaration(): EiuGuiMaskDeclaration {
		return $this->eiuGuiMaskDeclaration ?? $this->eiuGuiMaskDeclaration
				= new EiuGuiMaskDeclaration($this->eiGuiEntry->getEiGuiMaskDeclaration(), $this->eiuAnalyst);
	}
	
	/**
	 * @param string|DefPropPath $defPropPath
	 * @return EiuGuiField
	 */
	function field(DefPropPath|string $defPropPath): EiuGuiField {
		return new EiuGuiField(DefPropPath::create($defPropPath), $this, $this->eiuAnalyst);
	}
	
//	/**
//	 * @param SiEntryInput $siEntryInput
//	 * @throws CorruptedSiInputDataException
//	 * @return EiuGuiEntry
//	 */
//	function handleSiEntryInput(SiEntryInput $siEntryInput): static {
//		$this->eiGuiEntry->handleSiEntryInput($siEntryInput);
//		return $this;
//	}
	
	function save(): void {
		$this->eiGuiEntry->save();
	}

	function copy(bool $bulky = null, bool $readOnly = null, array $defPropPathsArg = null, bool $entryGuiControlsIncluded = null): EiuGuiEntry {
		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);

		$eiGuiMaskDeclaration = $this->eiGuiEntry->getEiGuiMaskDeclaration();
		$newViewMode = ViewMode::determine(
				$bulky ?? ViewMode::isBulky($eiGuiMaskDeclaration->getViewMode()),
				$readOnly ?? ViewMode::isReadOnly($eiGuiMaskDeclaration->getViewMode()),
				ViewMode::isAdd($eiGuiMaskDeclaration->getViewMode()));

		$eiFrameUtil = new EiFrameUtil($this->eiuAnalyst->getEiFrame(true));

		$eiGuiEntry = $eiFrameUtil->copyEiGuiEntry($this->eiGuiEntry, $newViewMode, $defPropPaths, $entryGuiControlsIncluded);

		return new EiuGuiEntry($eiGuiEntry, null, null, $this->eiuAnalyst);
	}

	/**
	 * @param bool $siControlsIncluded
	 * @return SiEntry
	 */
	function createSiEntry(bool $siControlsIncluded): SiEntry {
		return $this->eiGuiEntry->getSiEntry($this->eiuAnalyst->getN2nContext(true)->getN2nLocale(),
				$this->eiGuiEntry, $siControlsIncluded);
	}
	
	
// 	/**
// 	 * @param SiEntryInput $siEntryInput
// 	 * @throws IllegalStateException
// 	 * @throws \InvalidArgumentException
// 	 */
// 	function handleSiEntryInput(SiEntryInput $siEntryInput) {
// 		$this->eiGuiEntry->handleSiEntryInput($siEntryInput);
// 	}
	
// 	function getEiMask() {
// 		if ($this->eiMask !== null) {
// 			return $this->eiMask;
// 		}
		
// 		throw new IllegalStateException('No EiMask available.');
// 	}
	
// 	/**
// 	 * @param EntryGuiModel $entryGuiModel
// 	 * @param EiFrame $eiFrame
// 	 * @return EiuGuiEntry
// 	 */
// 	public static function from(EntryGuiModel $entryGuiModel, $eiFrame) {
// 		$entryGuiUtils = new EiuGuiEntry($entryGuiModel, 
// 				new EiuEntry($entryGuiModel, $eiFrame));
// 		$entryGuiUtils->eiGuiEntry = $entryGuiModel->getEiGuiEntry();
// 		return $entryGuiUtils;
// 	}
}


class ClosureGuiListener implements EiGuiEntryListener {
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
	 * @see \rocket\ui\gui\EiGuiEntryListener::finalized()
	 */
	function finalized(GuiEntry $eiGuiEntry) {
		if ($this->whenReadyClosure === null) return;
		
		$this->call($this->whenReadyClosure);
		
		if ($this->onSaveClosure === null || $this->savedClosure === null) {
			$eiGuiEntry->unregisterEiGuiEntryListener($this);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\gui\EiGuiEntryListener::onSave()
	 */
	function onSave(GuiEntry $eiGuiEntry) {
		if ($this->onSaveClosure !== null) {
			$this->call($this->onSaveClosure);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ui\gui\EiGuiEntryListener::saved()
	 */
	function saved(GuiEntry $eiGuiEntry) {
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
