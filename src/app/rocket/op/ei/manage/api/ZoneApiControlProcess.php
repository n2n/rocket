<?php
///*
// * Copyright (c) 2012-2016, Hofmänner New Media.
// * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
// *
// * This file is part of the n2n module ROCKET.
// *
// * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
// * GNU Lesser General Public License as published by the Free Software Foundation, either
// * version 2.1 of the License, or (at your option) any later version.
// *
// * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
// * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
// *
// * The following people participated in this project:
// *
// * Andreas von Burg...........:	Architect, Lead Developer, Concept
// * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
// * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
// */
//namespace rocket\op\ei\manage\api;
//
//use rocket\op\ei\IdPath;
//use rocket\op\ei\manage\gui\control\GuiControl;
//use n2n\web\http\BadRequestException;
//use rocket\op\ei\manage\frame\EiFrame;
//use rocket\op\ei\manage\frame\EiFrameUtil;
//use rocket\op\ei\manage\gui\EiGuiValueBoundary;
//use rocket\si\control\SiCallResponse;
//use rocket\si\input\SiInputFactory;
//use n2n\util\type\attrs\AttributesException;
//use rocket\op\ei\mask\EiMask;
//use rocket\op\ei\EiException;
//use rocket\op\ei\manage\entry\UnknownEiObjectException;
//use rocket\op\ei\UnknownEiTypeException;
//use rocket\op\ei\manage\security\InaccessibleEiEntryException;
//use n2n\web\http\ForbiddenException;
//use rocket\si\input\SiInput;
//use n2n\util\type\ArgUtils;
//use rocket\si\input\SiInputError;
//use n2n\util\ex\NotYetImplementedException;
//use rocket\si\input\SiInputResult;
//use rocket\op\ei\manage\gui\EiGui;
//use rocket\op\ei\manage\gui\EiGuiDeclaration;
//use rocket\op\ei\manage\entry\EiEntry;
//
//class ZoneApiControlProcess /*extends IdPath*/ {
//	private $eiFrameUtil;
//	private ?EiGuiValueBoundary $eiGuiValueBoundary = null;
//	private $guiControl;
//
//	function __construct(EiFrame $eiFrame) {
//		$this->eiFrameUtil = new EiFrameUtil($eiFrame);
//	}
//
//	function provideEiGuiValueBoundary(EiGuiValueBoundary $eiGuiValueBoundary) {
//		$this->eiGuiValueBoundary = $eiGuiValueBoundary;
//	}
//
//	/**
//	 * @param ZoneApiControlCallId $zoneControlCallId
//	 * @param GuiControl[] $availableGuiControls
//	 * @throws BadRequestException
//	 * @return \rocket\op\ei\manage\gui\control\GuiControl
//	 */
//	function determineGuiControl(ZoneApiControlCallId $zoneControlCallId, array $availableGuiControls): GuiControl {
//		ArgUtils::valArray($availableGuiControls, GuiControl::class);
//
//		$ids = $zoneControlCallId->toArray();
//
//		$id = array_shift($ids);
//		foreach ($availableGuiControls as $guiControl) {
//			if ($guiControl->getId() !== $id) {
//				continue;
//			}
//
//			while (!empty($ids) && $guiControl !== null) {
//				$id = array_shift($ids);
//				$guiControl = $guiControl->getChildById($id);
//			}
//
//			if ($guiControl !== null) {
//				$this->guiControl = $guiControl;
//				return $guiControl;
//			}
//		}
//
//		throw new BadRequestException('No control found for ZoneControlCalId: ' . $zoneControlCallId);
//	}
//
//
//	private function createEiGuiDeclaration(EiMask $eiMask, int $viewMode) {
//		try {
//			return $eiMask->getEiEngine()->obtainEiGuiDeclaration($viewMode, null);
//		} catch (EiException $e) {
//			throw new BadRequestException(null, 0, $e);
//		}
//	}
//
//	/**
//	 * @param array $data
//	 * @throws BadRequestException
//	 * @return SiCallResponse|null
//	 */
//	function handleInput(array $data): void {
//		if (!$this->guiControl->isInputHandled()) {
//			throw new BadRequestException('No input SiControl executed with input.');
//		}
//
//		$inputFactory = new SiInputFactory();
//
//		try {
//			return $this->applyInput($inputFactory->create($data));
//		} catch (AttributesException|\InvalidArgumentException|UnknownEiObjectException|UnknownEiTypeException $e) {
//			throw new BadRequestException(null, null, $e);
//		} catch (InaccessibleEiEntryException $e) {
//			throw new ForbiddenException(null, null, $e);
//		}
//	}
//
//	/**
//	 * @var EiEntry
//	 */
//	private $inputEiEntries = [];
//	/**
//	 * @var EiGuiDeclaration[]
//	 */
//	private $inputEiGuiDeclarations = [];
//
//	/**
//	 * @param SiInput $siInput
//	 * @return SiInputError|null
//	 * @throws UnknownEiObjectException
//	 * @throws UnknownEiTypeException
//	 * @throws InaccessibleEiEntryException
//	 * @throws \InvalidArgumentException
//	 */
//	private function applyInput($siInput) {
//		$errorEntries = [];
//
//		foreach ($siInput->getEntryInputs() as $key => $entryInput) {
//			$eiGuiDeclaration = null;
//			$eiGuiValueBoundary = null;
//			$inputEiGuiDeclaration = null;
//			$eiEntry = null;
//			if ($this->eiGuiValueBoundary !== null) {
//				$eiGuiValueBoundary = $this->eiGuiValueBoundary;
//				$eiGuiValueBoundary->handleSiEntryInput($entryInput);
//				$eiEntry = $eiGuiValueBoundary->getSelectedEiEntry();
//				$eiGuiDeclaration = $this->eiGuiValueBoundary->getEiGuiDeclaration()->getEiGuiDeclaration();
//				$inputEiGuiDeclaration = $this->createEiGuiDeclaration($eiEntry->getEiMask(), $eiGuiDeclaration->getViewMode());
//			} else {
//				$eiObject = null;
//				if (null !== $entryInput->getIdentifier()->getId()) {
//					$eiObject = $this->eiFrameUtil->lookupEiObject($entryInput->getIdentifier()->getId());
//				} else {
//					$eiObject = $this->eiFrameUtil->createNewEiObject($entryInput->getMaskId());
//				}
//
//				$eiEntry = $this->eiFrameUtil->getEiFrame()->createEiEntry($eiObject);
//				$inputEiGuiDeclaration = $eiGuiDeclaration = $this->createEiGuiDeclaration($eiEntry->getEiMask(), $this->eiGuiDeclaration->getViewMode());
//				$eiGuiValueBoundary = $eiGuiDeclaration->createEiGuiValueBoundary($this->eiFrame, [$eiEntry], $this->eiGui);
//				$eiGuiValueBoundary->handleSiEntryInput($entryInput);
//			}
//
//			$eiGuiValueBoundary->save();
//
//			$this->inputEiEntries[$key] = $eiEntry;
//			$this->inputEiGuiDeclarations[$key] = $inputEiGuiDeclaration;
//
//			if ($eiEntry->validate()) {
//				continue;
//			}
//
//			$errorEntries[$key] = $eiGuiDeclaration->createSiEntry($this->eiFrameUtil->getEiFrame(), $eiGuiValueBoundary, false);
//		}
//
//		if (empty($errorEntries)) {
//			return null;
//		}
//
//		return new SiInputError($errorEntries);
//	}
//
//	/**
//	 * @return \rocket\si\input\SiInputResult
//	 */
//	function createSiInputResult() {
//		$siEntries = [];
//		foreach ($this->inputEiGuiDeclarations as $key => $inputEiGuiDeclaration) {
//			$eiEntry = $this->eiFrameUtil->getEiFrame()->createEiEntry($this->inputEiEntries[$key]->getEiObject());
//			$eiGui = new EiGui($inputEiGuiDeclaration);
//			$eiGui->appendEiGuiValueBoundary($this->eiFrameUtil->getEiFrame(), [$eiEntry]);
//			$siEntries[$key] = $eiGui->createSiEntry($this->eiFrameUtil->getEiFrame());
//		}
//		return new SiInputResult($siEntries);
//	}
//
//	/**
//	 * @return \rocket\op\ei\manage\gui\EiGuiDeclaration
//	 */
//	private function getEiGuiDeclaration() {
//		return $this->eiGuiValueBoundary->getEiGuiDeclaration()->getEiGuiDeclaration();
//	}
//
//	/**
//	 * @return SiCallResponse
//	 */
//	function callGuiControl() {
//		return $this->guiControl->handle($this->eiFrameUtil->getEiFrame(), $this->getEiGuiDeclaration(), $this->inputEiEntries);
//	}
//
//}
