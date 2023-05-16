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
namespace rocket\op\ei\manage\gui;

use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\DefPropPath;
use rocket\op\ei\EiType;
use rocket\si\input\SiEntryInput;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\attrs\AttributesException;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\gui\control\GuiControl;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\si\content\SiEntry;
use rocket\op\ei\manage\gui\control\GuiControlPath;
use rocket\op\ei\manage\api\ApiController;
use rocket\op\ei\manage\api\ApiControlCallId;

class EiGuiEntry {
	/**
	 * @var EiGuiValueBoundary
	 */
	private $eiGuiValueBoundary;
	/**
	 * @var EiMask
	 */
	private $eiMask;
	/**
	 * @var EiEntry
	 */
	private $eiEntry;

	private ?GuiFieldMap $guiFieldMap = null;

	/**
	 * @var array<GuiControl>
	 */
	private array $guiControls = [];
	/**
	 * @var EiGuiValueBoundaryListener[]
	 */
	private array $eiGuiValueBoundaryListeners = array();
	
	public function __construct(EiGuiValueBoundary $eiGuiValueBoundary, EiMask $eiMask, EiEntry $eiEntry) {
		$this->eiGuiValueBoundary = $eiGuiValueBoundary;
		$this->eiMask = $eiMask;
		$this->eiEntry = $eiEntry;
	}
	
	/**
	 * @return EiMask
	 */
	function getEiMask(): EiMask {
		return $this->eiMask;
	}
	
	/**
	 * @return EiGuiValueBoundary
	 */
	function getEiGuiValueBoundary(): EiGuiValueBoundary {
		return $this->eiGuiValueBoundary;
	}
	
	/**
	 * @return EiEntry
	 */
	function getEiEntry(): EiEntry {
		return $this->eiEntry;
	}
	
	/**
	 * @return GuiFieldMap
	 */
	public function getGuiFieldMap() {
		$this->ensureInitialized();
		
		return $this->guiFieldMap;
	}
	
	function getGuiFieldByDefPropPath(DefPropPath $defPropPath) {
		$guiFieldMap = $this->guiFieldMap;
		
		$eiPropPaths = $defPropPath->toArray();
		
		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
			try {
				$guiField = $guiFieldMap->getGuiField($eiPropPath);
			} catch (GuiException $e) { }
			
			if (empty($eiPropPaths)) {
				return $guiField;
			}
			
			$guiFieldMap = $guiField->getForkGuiFieldMap();
			if ($guiFieldMap === null) {
				break;
			}
		}
		
		throw new GuiException('No GuiField with EiPropPath \'' . $defPropPath . '\' for \'' . $this . '\' registered');
	}
	
	function init(GuiFieldMap $guiFieldMap) {
		$this->ensureNotInitialized();
		
		$this->guiFieldMap = $guiFieldMap;
		
		foreach ($this->eiGuiValueBoundaryListeners as $eiGuiValueBoundaryListener) {
			$eiGuiValueBoundaryListener->finalized($this);
		}
	}
	
	
	/**
	 * @return boolean
	 */
	public function isInitialized(): bool {
		return $this->guiFieldMap !== null;
	}
	
	private function ensureInitialized(): void {
		if ($this->isInitialized()) return;
		
		throw new IllegalStateException('EiGuiValueBoundary not yet initialized.');
	}
	
	private function ensureNotInitialized(): void {
		if (!$this->isInitialized()) return;
		
		throw new IllegalStateException('EiGuiValueBoundary already initialized.');
	}

	function createSiEntry(EiFrame $eiFrame, bool $siControlsIncluded = true): SiEntry {
		$eiEntry = $this->getEiEntry();

		$n2nLocale = $eiFrame->getN2nContext()->getN2nLocale();
		$typeId = $this->getEiMask()->getEiType()->getId();
		$idName = null;
		if (!$eiEntry->isNew()) {
			$deterIdNameDefinition = $eiEntry->getEiMask()->getEiEngine()->getIdNameDefinition();
			$idName = $deterIdNameDefinition->createIdentityString($eiEntry->getEiObject(),
					$eiFrame->getN2nContext(), $n2nLocale);
		}

		$siEntry = new SiEntry($eiEntry->getPid(), $idName);

		foreach ($this->getGuiFieldMap()->getAllGuiFields() as $defPropPathStr => $guiField) {
			if (null !== ($siField = $guiField->getSiField())) {
				$siEntry->putField($defPropPathStr, $siField);
			}

// 			$siValueBoundary->putContextFields($defPropPathStr, $guiField->getContextSiFields());
		}

		if (!$siControlsIncluded) {
			return $siEntry;
		}

		foreach ($this->guiControls as $guiControlPathStr => $entryGuiControl) {
			$guiControlPath = GuiControlPath::create($guiControlPathStr);
			$siEntry->putControl($guiControlPathStr, $entryGuiControl->toSiControl(
					$eiFrame->getApiUrl($guiControlPath->getEiCmdPath(), ApiController::API_CONTROL_SECTION),
					new ApiControlCallId($guiControlPath,
							$this->getEiMask()->getEiTypePath(), $eiEntry->getPid(),
							($eiEntry->isNew() ? $eiEntry->getEiType()->getId() : null))));
		}

		return $siEntry;
	}

	/**
	 * @param SiEntryInput $siEntryInput
	 * @throws IllegalStateException
	 * @throws \InvalidArgumentException
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput) {
		if ($this->eiMask->getEiType()->getId() != $siEntryInput->getMaskId()) {
			throw new \InvalidArgumentException('EiType missmatch.');
		}
		
		if ($this->eiEntry->getPid() !== $siEntryInput->getIdentifier()->getId()) {
			throw new \InvalidArgumentException('EiEntry id missmatch.');
		}
		
		foreach ($this->guiFieldMap->getAllGuiFields() as $defPropPathStr => $guiField) {
			$siField = $guiField->getSiField();
			
			if ($siField == null || $siField->isReadOnly()
					|| !$siEntryInput->containsFieldName($defPropPathStr)) {
				continue;
			}
			
			try {
				$siField->handleInput($siEntryInput->getFieldInput($defPropPathStr)->getData());
			} catch (AttributesException $e) {
				throw new \InvalidArgumentException(null, 0, $e);
			}
		}
	}
	
	public function save(): void {
		$this->ensureInitialized();
		
		foreach ($this->eiGuiValueBoundaryListeners as $eiGuiValueBoundaryListener) {
			$eiGuiValueBoundaryListener->onSave($this);
		}
		
		$this->getGuiFieldMap()->save();
		
		foreach ($this->eiGuiValueBoundaryListeners as $eiGuiValueBoundaryListener) {
			$eiGuiValueBoundaryListener->saved($this);
		}
	}
	
	
	public function registerEiGuiValueBoundaryListener(EiGuiValueBoundaryListener $eiGuiValueBoundaryListener) {
		$this->eiGuiValueBoundaryListeners[spl_object_hash($eiGuiValueBoundaryListener)] = $eiGuiValueBoundaryListener;
	}
	
	public function unregisterEiGuiValueBoundaryListener(EiGuiValueBoundaryListener $eiGuiValueBoundaryListener) {
		unset($this->eiGuiValueBoundaryListeners[spl_object_hash($eiGuiValueBoundaryListener)]);
	}
	
	public function __toString() {
		return 'EiGuiEntry of ' . $this->eiEntry;
	}
}