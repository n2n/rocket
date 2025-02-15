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

namespace rocket\op\util;

use n2n\web\http\HttpContext;
use rocket\op\OpState;
use n2n\web\http\controller\impl\ControllingUtils;
use rocket\op\ei\manage\ManageState;
use rocket\op\ei\util\Eiu;
use n2n\web\http\PageNotFoundException;
use n2n\web\http\ForbiddenException;
use rocket\op\ei\manage\entry\UnknownEiObjectException;
use rocket\op\ei\manage\preview\model\UnavailablePreviewException;
use rocket\ui\gui\Gui;
use rocket\ui\si\SiPayloadFactory;
use rocket\op\ei\manage\frame\EiObjectSelector;
use rocket\op\ei\util\EiuAnalyst;
use n2n\web\ui\UiComponent;
use rocket\ui\si\content\impl\iframe\IframeSiGui;
use rocket\ui\si\content\impl\iframe\IframeData;
use n2n\util\uri\Url;
use n2n\web\http\Method;
use rocket\ui\si\control\SiNavPoint;
use rocket\ui\si\meta\SiBreadcrumb;
use n2n\l10n\DynamicTextCollection;
use rocket\op\cu\util\Cuu;
use rocket\op\ei\util\frame\EiuFrame;
use rocket\op\ei\util\entry\EiuEntry;
use rocket\op\ei\util\entry\EiuObject;
use rocket\ui\si\err\CorruptedSiDataException;
use rocket\op\cu\gui\CuGui;
use rocket\op\cu\gui\control\CuControlCallId;
use rocket\op\cu\util\gui\CufGui;
use n2n\web\http\BadRequestException;
use rocket\ui\gui\control\GuiControlMap;
use rocket\ui\gui\GuiZone;
use rocket\ui\si\api\request\SiZoneCall;
use n2n\web\http\StatusException;
use rocket\ui\gui\control\GuiControlKey;
use rocket\op\ei\manage\gui\factory\EiGuiFactory;
use rocket\op\ei\manage\ManageException;
use n2n\util\type\ArgUtils;
use rocket\ui\gui\control\GuiControl;
use rocket\ui\gui\impl\IframeGui;

class OpuCtrl {

	private Eiu $eiu;
	private Cuu $cuu;
	private HttpContext $httpContext;
	private OpState $opState;


	/**
	 * Private so future backwards compatible changes can be made.
	 * @param ControllingUtils $cu
	 * @throws ManageException
	 */
	private function __construct(private ControllingUtils $cu) {
		$manageState = $cu->getN2nContext()->lookup(ManageState::class);
		$this->eiu = new Eiu($manageState->peakEiFrame());
		$this->cuu = new Cuu($cu->getN2nContext(), $this->eiu);
		$this->httpContext = $cu->getHttpContext();
		$this->opState = $cu->getN2nContext()->lookup(OpState::class);
	}

	function eiu(): Eiu {
		return $this->eiu;
	}

	function frame(): EiuFrame {
		return $this->eiu->frame();
	}

	/**
	 * @param string $livePid
	 * @return EiuEntry
	 * @throws PageNotFoundException
	 * @throws ForbiddenException
	 */
	function lookupEntry(string $pid, int $ignoreConstraintTypes = 0): EiuEntry {
		return $this->frame()->entry($this->lookupObject($pid, $ignoreConstraintTypes));
	}

	/**
	 * @param string $livePid
	 * @return \rocket\op\ei\util\entry\EiuObject
	 * @throws PageNotFoundException
	 * @throws ForbiddenException
	 */
	function lookupObject(string $pid, int $ignoreConstraintTypes = 0): EiuObject {
		try {
			return $this->frame()->lookupObject($this->frame()->pidToId($pid), $ignoreConstraintTypes, true);
		} catch (UnknownEiObjectException|\InvalidArgumentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} /*catch (InaccessibleEiEntryException $e) {
		throw new ForbiddenException(null, 0, $e);
		}*/
	}


	/**
	 * @throws PageNotFoundException
	 */
	function lookupPreviewController(string $previewType, $eiObjectArg) {
		try {
			return $this->frame()->lookupPreviewController($previewType, $eiObjectArg);
		} catch (UnavailablePreviewException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
	}

//	function redirectToOverview(?int $status = null) {
//		$this->httpContext->getResponse()->send(
//				new Redirect($this->frame()->getEiFrame()->getOverviewUrl($this->httpContext), $status));
//	}


	function forwardHtml(): bool {
		if ('text/html' == $this->httpContext->getRequest()->getAcceptRange()
						->bestMatch(['text/html', 'application/json'])) {
			$this->cu->forward('\rocket\core\view\anglTemplate.html');
			return true;
		}

		return false;
	}

	/**
	 * @throws StatusException
	 */
//	function forwardCompactExplorerZone(int $pageSize = 30, ?string $title = null, bool $generalSiControlsIncluded = true,
//			bool $entryGuiControlsIncluded = true, array $zoneGuiControls = []): void {
//		if ($this->forwardHtml()) {
//			return;
//		}
//
//		$eiFrame = $this->frame()->getEiFrame();
//
//		$eiGuiFactory = new EiGuiFactory($eiFrame);
//		$gui = $eiGuiFactory->createCompactExplorerGui($pageSize);
//
//		$zoneGuiControlsMap = new GuiControlMap();
//		foreach ($zoneGuiControls as $controlName => $guiControl) {
//			$zoneGuiControlsMap->putGuiControl(new GuiControlKey($controlName), $guiControl);
//		}
//
//		$this->forwardGui($gui,
//				$title ?? $this->frame()->contextEngine()->mask()->getPluralLabel(),
//				$zoneGuiControlsMap);
//	}


//	private function composeEiuGuiForList($eiGui, $limit) {
//
//		$eiType = $this->frame()->getEiFrame()->getContextEiEngine()->getEiMask()->getEiType();
//
//		$criteria = $this->frame()->getEiFrame()->createCriteria(NestedSetUtils::NODE_ALIAS, false);
//		$criteria->select(NestedSetUtils::NODE_ALIAS)->limit($limit);
//
//		if (null !== ($nestedSetStrategy = $eiType->getNestedSetStrategy())) {
//			$this->treeLookup($eiGui, $criteria, $nestedSetStrategy);
//		} else {
//			$this->simpleLookup($eiGui, $criteria);
//		}
//	}
//
//	private function simpleLookup(EiGui $eiGui, Criteria $criteria) {
//		$eiFrame = $this->frame()->getEiFrame();
//		$eiFrameUtil = new EiFrameUtil($eiFrame);
//		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
//			$eiObject = new LiveEiObject($eiFrameUtil->createEiEntityObj($entityObj));
//			$eiGui->appendEiGuiValueBoundary($eiFrame, [$eiFrame->createEiEntry($eiObject)]);
//		}
//	}
//
//	private function treeLookup(EiGui $eiGui, Criteria $criteria, NestedSetStrategy $nestedSetStrategy) {
//		$nestedSetUtils = new NestedSetUtils($this->frame()->em(), $this->frame()->getContextEiType()->getEntityModel()->getClass(), $nestedSetStrategy);
//
//		$eiFrame = $this->frame()->getEiFrame();
//		$eiFrameUtil = new EiFrameUtil($eiFrame);
//		foreach ($nestedSetUtils->fetch(null, false, $criteria) as $nestedSetItem) {
//			$eiObject = new LiveEiObject($eiFrameUtil->createEiEntityObj($nestedSetItem->getEntityObj()));
//			$eiGui->appendEiGuiValueBoundary($eiFrame, [$eiFrame->createEiEntry($eiObject)], $nestedSetItem->getLevel());
//		}
//	}

	/**
	 * @throws BadRequestException
	 * @throws StatusException
	 */
//	function forwardBulkyEntryZone($eiEntryArg, bool $readOnly, bool $generalSiControlsIncluded,
//			bool $entrySiControlsIncluded = true, array $zoneGuiControls = []): void {
//		if ($this->forwardHtml()) {
//			return;
//		}
//
//		$eiEntry = EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg,'eiEntryArg', true);
//
//		$eiFrame = $this->frame()->getEiFrame();
//
//		$eiGuiFactory = new EiGuiFactory($eiFrame);
//		$gui = $eiGuiFactory->createBulkyGui([$eiEntry], $readOnly);
//
//		$zoneGuiControlsMap = new GuiControlMap();
//		foreach ($zoneGuiControls as $controlName => $guiControl) {
//			$zoneGuiControlsMap->putGuiControl(new GuiControlKey($controlName), $guiControl);
//		}
//
//		$this->forwardGui($gui, current($gui->getGuiValueBoundary()->getGuiEntries())->getSiEntry()->getQualifier()->getIdName(),
//				$zoneGuiControlsMap);
//	}

//	/**
//	 * @throws StatusException
//	 */
//	function forwardNewBulkyEntryZone(array $eiuEntries = [], bool $editable = true, bool $generalSiControlsIncluded = true, bool $entrySiControlsIncluded = true,
//			array $zoneGuiControls = []): void {
//		if ($this->forwardHtml()) {
//			return;
//		}
//
//		$eiEntries = array_map(fn ($eiEntryArg) => EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg,'eiuEntries', true),
//				$eiuEntries);
//
//		$eiFrame = $this->frame()->getEiFrame();
//
//		$eiGuiFactory = new EiGuiFactory($eiFrame);
//		$gui = $eiGuiFactory->createBulkyGui($eiEntries, !$editable);
//
//		$zoneGuiControlsMap = new GuiControlMap();
//		foreach ($zoneGuiControls as $controlName => $guiControl) {
//			$zoneGuiControlsMap->putGuiControl(new GuiControlKey($controlName), $guiControl);
//		}
//
//		$this->forwardGui($gui, $this->eiu->dtc('rocket')->t('common_new_entry_label'),
//				$zoneGuiControlsMap);
//	}

	/**
	 * @throws StatusException
	 */
	function forwardGui(Gui $eiGui, ?string $title = null, array $zoneGuiControls = []): void {
		ArgUtils::valArray($zoneGuiControls, GuiControl::class);

		if ($this->forwardHtml()) {
			return;
		}

		$zoneGuiControlsMap = new GuiControlMap();
		foreach ($zoneGuiControls as $controlName => $guiControl) {
			$zoneGuiControlsMap->putGuiControl(new GuiControlKey($controlName), $guiControl);
		}

		$guiZone = new GuiZone($eiGui, $title, $this->opState->getBreadcrumbs(), $zoneGuiControlsMap);
		$siZone = $guiZone->getSiZone();
		try {
			$siZoneCall = SiZoneCall::fromCu($this->cu);
			if ($siZoneCall !== null && null !== ($siResult = $siZone->handleSiZoneCall($siZoneCall, $this->cu->getN2nContext()))) {
				$this->cu->sendJson($siResult->toJsonStruct($this->cu->getN2nContext()));
				return;
			}
		} catch (CorruptedSiDataException $e) {
			throw new BadRequestException('Could not handle SiCall: ' . $e->getMessage(), previous: $e);
		}

		$this->cu->sendJson($siZone->toJsonStruct($this->eiu->getN2nContext()));
	}

	function forwardIframeZone(UiComponent $uiComponent, bool $useTemplate = true, ?string $title = null): void {
		if ($this->forwardHtml()) {
			return;
		}

		$iframeSiGui = null;
		if ($useTemplate) {
			$iframeSiGui = new IframeSiGui(IframeData::createFromUiComponentWithTemplate($uiComponent, $this->eiu->getN2nContext()));
		} else {
			$iframeSiGui = new IframeSiGui(IframeData::createFromUiComponent($uiComponent));
		}

		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($iframeSiGui,
						$this->opState->getBreadcrumbs(),
						$title ?? 'Iframe'));
	}

	function forwardIframeUrlZone(Url $url, ?string $title = null): void {
		if ($this->forwardHtml()) {
			return;
		}

		$iframeSiGui = new IframeSiGui(IframeData::createFromUrl($url));

		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($iframeSiGui,
						$this->opState->getBreadcrumbs(),
						$title ?? 'Iframe'));
	}


	/**
	 * @throws CorruptedSiDataException
	 * @throws StatusException
	 */
//	private function handleEiSiCall(Gui $gui, ZoneGuiControlsMap $zoneGuiControlsMap): ?SiCallResult {
//		$zoneControlPath = $this->cu->getParamPost('zoneControlPath');
//		if (!($this->cu->getRequest()->getMethod() === Method::POST && null !== $zoneControlPath)) {
//			return null;
//		}
//
//		$zoneControlPath = GuiControlPath::create($zoneControlPath);
//
//		$siInputResult = null;
//		if (null !== ($entryInputMapsParam = $this->cu->getParamPost('entryInputMaps'))) {
//			$siInput = (new SiInputFactory())->create($entryInputMapsParam->parseJson());
//			if (null !== ($siInputError = $gui->handleSiInput($siInput))) {
//				return SiCallResult::fromInputError($siInputError);
//			}
//
//			$siInputResult = new \rocket\ui\si\input\SiInputResult($gui->getInputSiValueBoundaries());
//		}
//
//		return SiCallResult::fromCallResponse(
//				$zoneGuiControlsMap->handleSiCall($zoneControlPath),
//				$siInputResult);
//	}

	function forwardUrlIframeZone(Url $url, ?string $title = null): void {
		if ($this->forwardHtml()) {
			return;
		}

		$iframeGui = new IframeGui(IframeData::createFromUrl($url));

		$this->forwardGui($iframeGui, $title ?? 'Iframe');
	}

	public function pushBreadcrumb(SiNavPoint $navPoint, string $label): static {
		$this->opState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));
		return $this;
	}

	public function pushSirefBreadcrumb(Url $url, string $label): static  {
		$this->opState->addBreadcrumb(new SiBreadcrumb(SiNavPoint::siref($url), $label));
		return $this;
	}

	public function pushOverviewBreadcrumb(?string $label = null, bool $required = false): static {
		$navPoint = $this->frame()->getOverviewNavPoint($required);

		if ($navPoint === null) {
			return $this;
		}

		if ($label === null) {
			$label = $this->frame()->getEiFrame()->getContextEiEngine()->getEiMask()->getPluralLabelLstr()
					->t($this->eiu->getN2nLocale());
		}

		$this->opState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));

		return $this;
	}

	public function pushDetailBreadcrumb($eiObjectArg, ?string $label = null, bool $required = false): static {
		$eiFrame = $this->frame()->getEiFrame();
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, '$eiObjectArg',
				$eiFrame->getContextEiEngine()->getEiMask()->getEiType());

		$navPoint = $eiFrame->getDetailNavPoint($eiObject, $required);

		if ($navPoint === null) {
			return $this;
		}

		if ($label === null) {
			$label = (new EiObjectSelector($eiFrame))->createIdentityString($eiObject);
		}

		$this->opState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));

		return $this;
	}

	public function pushEditBreadcrumb($eiObjectArg, ?string $label = null, bool $required = false): static {
		$eiFrame = $this->frame()->getEiFrame();
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, '$eiObjectArg',
				$eiFrame->getContextEiEngine()->getEiMask()->getEiType());

		$navPoint = $eiFrame->getEditNavPoint($eiObject, $required);

		if ($navPoint === null) {
			return $this;
		}

		if ($label === null) {
			$label = (new DynamicTextCollection('rocket', $this->eiu->getN2nContext()->getN2nLocale()))
					->t('common_edit_label');
		}

		$this->opState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));

		return $this;
	}

	public function pushAddBreadcrumb(?string $label = null, bool $required = false): static {
		$navPoint = $this->frame()->getAddNavPoint($required);

		if ($navPoint === null) {
			return $this;
		}

		if ($label === null) {
			$label = (new DynamicTextCollection('rocket', $this->eiu->getN2nContext()->getN2nLocale()))
					->t('common_add_label');
		}

		$this->opState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));

		return $this;
	}

	/**
	 * @param string $label
	 * @param bool $includeOverview
	 * @param mixed $detailEiEntryArg
	 * @return OpuCtrl
	 */
	function pushCurrentAsSirefBreadcrumb(string $label, bool $includeOverview = false, $detailEiEntryArg = null) : static{
		if ($includeOverview) {
			$this->pushOverviewBreadcrumb();
		}

		if ($detailEiEntryArg !== null) {
			$this->pushDetailBreadcrumb($detailEiEntryArg);
		}

		$this->pushSirefBreadcrumb($this->httpContext->getRequest()->getUrl(), $label);

		return $this;
	}

	function cuu(): Cuu {
		return $this->cuu;
	}

//	private function forwardHtml(): bool {
//		if ('text/html' == $this->cu->getRequest()->getAcceptRange()
//						->bestMatch(['text/html', 'application/json'])) {
//			$this->cu->forward('\rocket\core\view\anglTemplate.html');
//			return true;
//		}
//
//		return false;
//	}

	/**
	 * @throws CorruptedSiDataException
	 */
	private function handleCuSiCall(?CuGui $cuGui): ?SiCallResult {
		$apiCallIdParam = $this->cu->getParamPost('apiCallId');
		if (!($this->cu->getRequest()->getMethod() === Method::POST && null !== $apiCallIdParam)) {
			return null;
		}

		$siInputResult = null;
		if (null !== ($entryInputMapsParam = $this->cu->getParamPost('entryInputMaps'))) {
			$siInput = (new SiInputFactory())->create($entryInputMapsParam->parseJson());
			if (null !== ($siInputError = $cuGui->handleSiInput($siInput, $this->cu->getN2nContext()))) {
				return SiCallResult::fromInputError($siInputError);
			}

			$siInputResult = new SiInputResult($cuGui->getInputSiValueBoundaries());
		}

		return SiCallResult::fromCallResponse(
				$cuGui->handleCall(CuControlCallId::parse($apiCallIdParam->parseJson()), $this->cuu),
				$siInputResult);
	}

	function forwardZone(CufGui|CuGui $gui, string $title): void {
		if ($this->forwardHtml()) {
			return;
		}

		if ($gui instanceof CufGui) {
			$gui = $gui->getCuGui();
		}

		try {
			if (null !== ($siResult = $this->handleCuSiCall($gui))) {
				$this->cu->sendJson($siResult);
				return;
			}
		} catch (CorruptedSiDataException $e) {
			throw new BadRequestException('Could not handle SiCall: ' . $e->getMessage(), previous: $e);
		}

		if ($gui instanceof CuGui) {
			$gui = $gui->toSiGui($this->cu->getRequest()->getPath()->toUrl());
		}

		$this->cu->send(SiPayloadFactory::create($gui, $this->opState->getBreadcrumbs(), $title));
	}

	public static function from(ControllingUtils $cu): OpuCtrl {
		return new OpuCtrl($cu);
	}
}