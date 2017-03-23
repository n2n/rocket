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
namespace rocket\spec\ei\manage\gui;

use rocket\spec\ei\manage\EiFrame;
use n2n\web\dispatch\map\PropertyPath;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\EiFieldPath;
use n2n\reflection\ArgUtils;
use n2n\web\http\Request;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\web\http\HttpContext;

class EntrySourceInfo {
	private $eiMapping;
	private $eiFrame;
	private $viewMode;
// 	private $writable;
	private $eiEntryGuiListeners = array();
	/**
	 * @param EiMapping $eiMapping
	 * @param EiFrame $eiFrame
	 * @param int $viewMode
	 * @param PropertyPath $propertyPath
	 */
	public function __construct(EiMapping $eiMapping, EiFrame $eiFrame, int $viewMode) {
		$this->eiMapping = $eiMapping;
		$this->eiFrame = $eiFrame;
		ArgUtils::valEnum($viewMode, DisplayDefinition::getViewModes(), null, false, 'viewMode');
		$this->viewMode = $viewMode;
	}
	
// 	public function isWritable(): bool{
// 		return $this->writable;
// 	}
	
	/**
	 * @return EiMapping
	 */
	public function getEiMapping(): EiMapping {
		return $this->eiMapping;
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiFrame
	 */
	public function getEiFrame() {
		return $this->eiFrame;
	}
	
	public function getRequest(): Request {
		// @todo update when RequestContext available
		return $this->eiFrame->getN2nContext()->getRequest();
	}
	
	public function getViewMode() {
		return $this->viewMode;
	}
	
	/**
	 * @return boolean
	 */
	public function isViewModeOverview() {
		return $this->viewMode == DisplayDefinition::LIST_VIEW_MODES 
				|| $this->viewMode == DisplayDefinition::TREE_VIEW_MODES;
	}
	
	public function isViewModeBulky() {
		return (bool) ($this->viewMode & DisplayDefinition::BULKY_VIEW_MODES);
	}
	
	/**
	 * @return boolean
	 */
	public function isNew() {
		return $this->getEiMapping()->getEiEntry()->isNew();
	}
	
	/**
	 * @return boolean 
	 */
	public function isDraft() {
		return $this->getEiMapping()->getEiEntry()->isDraft();
	}
	
	/**
	 * @param string $id
	 * @return mixed
	 */
	public function getValue(EiFieldPath $eiFieldPath) {
		return $this->getEiMapping()->getValue($eiFieldPath);
	}
	
	/**
	 * @param string $id
	 * @param mixed $value
	 */
	public function setValue(EiFieldPath $eiFieldPath, $value) {
		return $this->getEiMapping()->setValue($eiFieldPath, $value);
	}
	
	public function getId() {
		return $this->getEiMapping()->getEiEntry()->getId();
	}
	
	public function toFieldSourceInfo(EiFieldPath $eiFieldPath): FieldSourceInfo {
		return new FieldSourceInfo($eiFieldPath, $this);
	}
	
	public function addEiEntryGuiListener(EiEntryGuiListener $eiEntryGuiListener) {
		$this->eiEntryGuiListeners[] = $eiEntryGuiListener;
	}
	
	public function getEiEntryGuiListeners(): array {
		return $this->eiEntryGuiListeners;
	}
	
// 	public function createPropertyPath($propertyName, PropertyPath $basePropertyPath = null) {
// 		if (!$this->editable) {
// 			throw new UnsupportedOperationException();
// 		}
		
// 		CastUtils::assertTrue($this->entryModel instanceof EditEntryModel);
// 		return $this->entryModel->createPropertyPath($propertyName, $basePropertyPath);
// 	}
}

class FieldSourceInfo {
	private $eiFieldPath;
	private $entrySourceInfo;
	
	public function __construct(EiFieldPath $eiFieldPath, EntrySourceInfo $entrySourceInfo) {
		$this->eiFieldPath = $eiFieldPath;
		$this->entrySourceInfo = $entrySourceInfo;
	}
	
	public function getEiFieldPath(): EiFieldPath {
		return $this->eiFieldPath;
	}
	
	/**
	 * @return EiMapping
	 */
	public function getEiMapping(): EiMapping {
		return $this->entrySourceInfo->getEiMapping();
	}
	
	/**
	 * @return \rocket\spec\ei\manage\EiFrame
	 */
	public function getEiFrame(): EiFrame {
		return $this->entrySourceInfo->getEiFrame();
	}
	
	public function getHttpContext(): HttpContext {
		return $this->getEiFrame()->getN2nContext()->getHttpContext();
	}
	
	public function getRequest(): Request {
		return $this->getEiFrame()->getN2nContext()->getHttpContext()->getRequest();
	}
	
	public function getN2nLocale() {
		return $this->getEiFrame()->getN2nContext()->getN2nLocale();
	}
	
	public function getViewMode() {
		return $this->entrySourceInfo->getViewMode();
	}
	
// 	/**
// 	 * @return boolean
// 	 */
// 	public function isViewModeOverview() {
// 		return $this->viewMode == DisplayDefinition::LIST_VIEW_MODES
// 		|| $this->viewMode == DisplayDefinition::TREE_VIEW_MODES;
// 	}
	
	public function isViewModeBulky() {
		return (bool) ($this->getViewMode() & DisplayDefinition::BULKY_VIEW_MODES);
	}
	
	/**
	 * @return boolean
	 */
	public function isNew() {
		return $this->getEiMapping()->getEiEntry()->isNew();
	}
	
	/**
	 * @return boolean
	 */
	public function isDraft() {
		return $this->getEiMapping()->getEiEntry()->isDraft();
	}
	
	/**
	 * @param string $id
	 * @return mixed
	 */
	public function getValue() {
		return $this->getMValue($this->eiFieldPath);
	}
	
	public function getMValue(EiFieldPath $eiFieldPath) {
		return $this->getEiMapping()->getValue($eiFieldPath);
	}
	
	/**
	 * @param string $id
	 * @param mixed $value
	 */
	public function setValue($value) {
		return $this->setMValue($this->eiFieldPath, $value);
	}
	
	public function setMValue(EiFieldPath $eiFieldPath, $value) {
		return $this->getEiMapping()->setValue($eiFieldPath, $value);
	}
	
	public function getEntryId() {
		return $this->getEiMapping()->getId();
	}
	
	public function getEntryIdRep() {
		return $this->getEiMapping()->getIdRep();
	}
	
	public function executeWhenSaved(\Closure $closure) {
		$this->entrySourceInfo->addEiEntryGuiListener(new FieldGuiListener($this, null, $closure));
	}
}

class FieldGuiListener implements EiEntryGuiListener {
	private $fieldSourceInfo;
	private $onSaveClosure;
	private $savedClosure;
	
	public function __construct(FieldSourceInfo $fieldSourceInfo, \Closure $onSaveClosure = null, 
			\Closure $savedClosure = null) {
		$this->eiu = $fieldSourceInfo;
		$this->onSaveClosure = $onSaveClosure;
		$this->savedClosure = $savedClosure;
	}
	
	public function finalized(EiEntryGui $eiEntryGui) {
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
		$mmi->setClassParamObject(FieldSourceInfo::class, $this->eiu);
		$mmi->invoke(null, $closure);
	}
}
