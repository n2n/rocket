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

use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\manage\util\model\UnknownEntryException;
use n2n\web\http\PageNotFoundException;
use rocket\spec\ei\security\InaccessibleEntryException;
use n2n\web\http\ForbiddenException;
use n2n\web\http\BadRequestException;
use n2n\util\uri\Url;
use n2n\web\http\controller\ParamQuery;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\control\EntryNavPoint;
use n2n\web\http\HttpContext;
use rocket\core\model\RocketState;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\ManageState;
use rocket\core\model\Breadcrumb;
use n2n\reflection\ReflectionUtils;
use n2n\context\Lookupable;
use rocket\spec\ei\manage\preview\model\UnavailablePreviewException;
use rocket\spec\ei\manage\util\model\EiuEntry;
use n2n\web\http\Redirect;

class EiuCtrl implements Lookupable {
	private $eiu;
	private $eiuFrame;	
	private $httpContext;
	
	private function _init(ManageState $manageState, HttpContext $httpContext) {
		$this->init($manageState, $httpContext);
	}
	
	protected function init(ManageState $manageState, HttpContext $httpContext, EiFrame $eiFrame = null) {
		if ($eiFrame === null) {
			$eiFrame = $manageState->peakEiFrame();
		}
		$this->eiu = new Eiu($eiFrame);
		$this->eiuFrame = $this->eiu->frame();
		$this->httpContext = $httpContext;
	}
	
	/**
	 * @return Eiu
	 */
	public function eiu() {
		return $this->eiu;
	}
	
	/**
	 * 
	 * @return \rocket\spec\ei\manage\util\model\EiuFrame
	 */
	public function frame() {
		return $this->eiuFrame;
	}
	
	
	public function lookupEiSelection(string $liveIdRep, bool $assignToEiu = false) {
		$eiSelection = null;
		try {
			$eiSelection = $this->eiuFrame->lookupEiSelectionById($this->eiuFrame->idRepToId($liveIdRep));
		} catch (UnknownEntryException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (InaccessibleEntryException $e) {
			throw new ForbiddenException(null, 0, $e);
		}
		
		if ($assignToEiu) {
			$this->eiuFrame->assignEiuEntry($eiSelection);
		}
		
		return $eiSelection;
	}
	
	/**
	 * @param string $liveIdRep
	 * @return \rocket\spec\ei\manage\mapping\EiMapping
	 */
	public function lookupEiMapping(string $liveIdRep, bool $assignToEiu = false) {
		$eiMapping = $this->eiuFrame->createEiMapping($this->lookupEiSelection($liveIdRep, false));
		if ($assignToEiu) {
			$this->eiuFrame->assignEiuEntry($eiMapping);
		}
		return $eiMapping;
	}
	
	public function lookupEiSelectionByDraftId($draftId, bool $assignToEiu = false) {
		if (!is_numeric($draftId)) {
			throw new PageNotFoundException('Draft id must be numeric. ' . ReflectionUtils::getTypeInfo($draftId) 
					. ' given');
		}
		
		$eiSelection = null;
		try {
			$eiSelection = $this->eiuFrame->lookupEiSelectionByDraftId((int) $draftId);
		} catch (UnknownEntryException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (InaccessibleEntryException $e) {
			throw new ForbiddenException(null, 0, $e);
		}
		
		if ($assignToEiu) {
			$this->eiuFrame->assignEiuEntry($eiSelection);	
		}
		
		return $eiSelection;
	}
	
	public function lookupEiMappingByDraftId($draftId, bool $assignToEiu = false) {
		$eiMapping = $this->eiuFrame->createEiMapping($this->lookupEiSelectionByDraftId($draftId, false));
		if ($assignToEiu) {
			$this->eiuFrame->assignEiuEntry($eiMapping);
		}
		return $eiMapping;
	}

	public function buildRedirectUrl(EiSelection $eiSelection = null) { 
		$eiFrame = $this->eiuFrame->getEiFrame();
		
		if ($eiSelection !== null && !$eiSelection->isNew()) {
			$entryNavPoint = $eiSelection->toEntryNavPoint();
			if ($eiFrame->isDetailUrlAvailable($entryNavPoint)) {
				return $eiFrame->getDetailUrl($this->httpContext, $entryNavPoint);
			}
		}
		
		return $eiFrame->getOverviewUrl($this->httpContext);
	}
	
	public function parseRefUrl(ParamQuery $refPath = null) {
		if ($refPath === null) return null;
		
		try {
			$url = Url::create($refPath);
			if ($url->isRelative()) return $url;
		
			throw new BadRequestException('refPath not relative: ' . $refPath);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException('Invalid refPath: ' . $refPath, null, $e);
		}
	}
	
	public function buildRefRedirectUrl(Url $redirectUrl = null, EiSelection $eiSelection = null) {
		if ($redirectUrl !== null) {
			return $redirectUrl;	
		}
		
		return $this->buildRedirectUrl($eiSelection);
	}
	
	public function applyCommonBreadcrumbs(EiSelection $eiSelection = null, string $currentBreadcrumbLabel = null) {
		$eiFrame = $this->eiuFrame->getEiFrame();
		$rocketState = $eiFrame->getN2nContext()->lookup(RocketState::class);
		CastUtils::assertTrue($rocketState instanceof RocketState);
		
		if (!$eiFrame->isOverviewDisabled()) {
			$rocketState->addBreadcrumb($eiFrame->createOverviewBreadcrumb($this->httpContext));
		}
			
		if ($eiSelection !== null && !$eiFrame->isDetailDisabled()) {
			$rocketState->addBreadcrumb($eiFrame->createDetailBreadcrumb($this->httpContext, $eiSelection));
		}
		
		if ($currentBreadcrumbLabel !== null) {
			$rocketState->addBreadcrumb(new Breadcrumb($eiFrame->getCurrentUrl($this->httpContext), 
					$currentBreadcrumbLabel));
		}
	}
	
	public function applyBreandcrumbs(Breadcrumb ...$additionalBreadcrumbs) {
		$eiFrame = $this->eiuFrame->getEiFrame();
		$rocketState = $eiFrame->getN2nContext()->lookup(RocketState::class);
		CastUtils::assertTrue($rocketState instanceof RocketState);
		
		foreach ($additionalBreadcrumbs as $additionalBreadcrumb) {
			$rocketState->addBreadcrumb($additionalBreadcrumb);
		}
	}
	
	public function lookupPreviewController(string $previewType, EiSelection $eiSelection) {
		try {
			return $this->eiuFrame->lookupPreviewController($previewType, $eiSelection);
		} catch (UnavailablePreviewException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
	}
	
	public function redirectToOverview(int $status = null) {
		$this->httpContext->getResponse()->send(
				new Redirect($this->getEiFrame()->getOverviewUrl($this->httpContext), $status));
	}
	
	public static function from(HttpContext $httpContext, EiFrame $eiFrame = null) {
		$manageState = $httpContext->getN2nContext()->lookup(ManageState::class);
		CastUtils::assertTrue($manageState instanceof ManageState);
		
		
		$eiCtrlUtils = new EiuCtrl();
		$eiCtrlUtils->init($manageState, $httpContext, $eiFrame);
		return $eiCtrlUtils;
	}
	
	/**
	 * @param unknown $eiEntryObj
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function toEiuEntry($eiEntryObj) {
		return new EiuEntry($eiEntryObj, $this);
	}
}
