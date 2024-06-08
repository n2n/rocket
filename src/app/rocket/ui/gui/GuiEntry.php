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
namespace rocket\ui\gui;

use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\DefPropPath;
use rocket\ui\si\input\SiEntryInput;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\attrs\AttributesException;
use rocket\ui\si\content\SiEntry;
use rocket\op\ei\manage\gui\control\GuiControlMap;
use n2n\l10n\Message;
use n2n\l10n\N2nLocale;
use rocket\ui\si\input\CorruptedSiInputDataException;
use rocket\op\ei\manage\gui\EiGuiMaskDeclaration;
use rocket\ui\si\content\SiEntryQualifier;
use rocket\ui\si\meta\SiMaskIdentifier;
use rocket\op\ei\manage\gui\EiGuiException;

class GuiEntry {
	private ?GuiFieldMap $guiFieldMap = null;

	private ?GuiControlMap $guiControlMap = null;

	/**
	 * @var EiGuiEntryListener[]
	 */
	private array $eiGuiEntryListeners = array();

	private bool $valid = true;
	
	public function __construct(private readonly SiEntryQualifier $siEntryQualifier) {
	}

	function getSiEntryQualifier(): SiEntryQualifier {
		return $this->siEntryQualifier;
	}

	function getMaskId(): string {
		return $this->maskId;
	}

	function getIdName(): ?string {
		return $this->idName;
	}
	
	/**
	 * @return EiEntry
	 */
	function getEiEntry(): EiEntry {
		return $this->eiEntry;
	}

	public function getGuiFieldMap(): GuiFieldMap {
		$this->ensureInitialized();
		
		return $this->guiFieldMap;
	}

	function getGuiControlMap(): ?GuiControlMap {
		$this->ensureInitialized();

		return $this->guiControlMap;
}
	
	function getGuiFieldByDefPropPath(DefPropPath $defPropPath): field\GuiField {
		$guiFieldMap = $this->guiFieldMap;
		
		$eiPropPaths = $defPropPath->toArray();
		
		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
			try {
				$guiField = $guiFieldMap->getGuiField($eiPropPath);
			} catch (EiGuiException $e) { }
			
			if (empty($eiPropPaths)) {
				return $guiField;
			}
			
			$guiFieldMap = $guiField->getForkGuiFieldMap();
			if ($guiFieldMap === null) {
				break;
			}
		}
		
		throw new EiGuiException('No GuiField with EiPropPath \'' . $defPropPath . '\' for \'' . $this . '\' registered');
	}
	
	function init(GuiFieldMap $guiFieldMap, ?GuiControlMap $guiControlMap): void {
		$this->ensureNotInitialized();
		
		$this->guiFieldMap = $guiFieldMap;
		$this->guiControlMap = $guiControlMap;
		
		foreach ($this->eiGuiEntryListeners as $eiGuiValueBoundaryListener) {
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

	function createSiEntry(N2nLocale $n2nLocale): SiEntry {
		$eiEntry = $this->getEiEntry();

		$siEntry = new SiEntry($eiEntry->getPid(), $this->idName);

		foreach ($this->getGuiFieldMap()->getAllGuiFields() as $defPropPathStr => $guiField) {
			if (null !== ($siField = $guiField->getSiField())) {
				$siEntry->putField($defPropPathStr, $siField);
			}

// 			$siValueBoundary->putContextFields($defPropPathStr, $guiField->getContextSiFields());
		}

		$siEntry->setMessages($this->createGeneralMessageStrs($n2nLocale));

		if ($this->guiControlMap !== null) {
			foreach ($this->guiControlMap->createSiControls() as $guiControlPathStr => $siControl) {
				$siEntry->putControl($guiControlPathStr, $siControl);
			}
		}

		return $siEntry;
	}

	/**
	 * @return string[]
	 */
	function createGeneralMessageStrs(N2nLocale $n2nLocale): array {
		if (!$this->eiEntry->hasValidationResult()) {
			return [];
		}

		$eiPropPaths = $this->guiFieldMap->getEiPropPaths();

		$messageStrs = [];
		foreach ($this->eiEntry->getValidationResult()
						 ->getInvalidEiFieldValidationResults(false, exceptEiPropPaths: $eiPropPaths) as $validationResult) {

			$label = $this->eiEntry->getEiMask()->getEiPropCollection()
					->getByPath($validationResult->getEiPropPath())->getNature()->getLabelLstr()->t($n2nLocale);
			array_push($messageStrs, ...array_map(
					fn (Message $m) => $label . ': ' . $m->t($n2nLocale),
					$validationResult->getMessages(false)));
		}
		return $messageStrs;
	}

	/**
	 * @param SiEntryInput $siEntryInput
	 * @throws IllegalStateException
	 * @throws CorruptedSiInputDataException
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput): bool {
		if ((string) $this->getEiMask()->getEiTypePath() != $siEntryInput->getMaskId()) {
			throw new \InvalidArgumentException('EiType missmatch.');
		}
		
		if ($this->eiEntry->getPid() !== $siEntryInput->getIdentifier()->getId()) {
			throw new \InvalidArgumentException('EiEntry id missmatch.');
		}

		$this->valid = true;
		foreach ($this->guiFieldMap->getAllGuiFields() as $defPropPathStr => $guiField) {
			$siField = $guiField->getSiField();
			
			if ($siField == null || $siField->isReadOnly()
					|| !$siEntryInput->containsFieldName($defPropPathStr)) {
				continue;
			}
			
			try {
				if (!$siField->handleInput($siEntryInput->getFieldInput($defPropPathStr)->getData())) {
					$this->valid = false;
				}
			} catch (AttributesException $e) {
				throw new \InvalidArgumentException($e->getMessage(), previous: $e);
			}
		}

		return $this->valid;
	}

	function isValid(): bool {
		return $this->valid;
	}

	public function save(): void {
		$this->ensureInitialized();

		if (!$this->valid) {
			throw new IllegalStateException('Invalid EiGuiEntry cannot be saved.');
		}
		
		foreach ($this->eiGuiEntryListeners as $eiGuiValueBoundaryListener) {
			$eiGuiValueBoundaryListener->onSave($this);
		}
		
		$this->getGuiFieldMap()->save();
		
		foreach ($this->eiGuiEntryListeners as $eiGuiValueBoundaryListener) {
			$eiGuiValueBoundaryListener->saved($this);
		}
	}

	public function registerEiGuiEntryListener(EiGuiEntryListener $eiGuiEntryListener): void {
		$this->eiGuiEntryListeners[spl_object_hash($eiGuiEntryListener)] = $eiGuiEntryListener;
	}
	
	public function unregisterEiGuiEntryListener(EiGuiEntryListener $eiGuiEntryListener): void {
		unset($this->eiGuiEntryListeners[spl_object_hash($eiGuiEntryListener)]);
	}
	
	public function __toString() {
		return 'EiGuiEntry of ' . $this->eiEntry;
	}
}