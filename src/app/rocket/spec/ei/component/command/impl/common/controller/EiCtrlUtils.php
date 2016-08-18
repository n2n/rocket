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
namespace rocket\spec\ei\component\command\impl\common\controller;

use rocket\spec\ei\manage\util\model\EiStateUtils;
use rocket\spec\ei\manage\util\model\UnknownEntryException;
use n2n\http\PageNotFoundException;
use rocket\spec\ei\security\InaccessibleEntryException;
use n2n\http\ForbiddenException;
use n2n\http\BadRequestException;
use n2n\util\uri\Url;
use n2n\http\controller\ParamQuery;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\control\EntryNavPoint;
use n2n\http\HttpContext;
use rocket\core\model\RocketState;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\ManageState;
use rocket\core\model\Breadcrumb;
use n2n\reflection\ReflectionUtils;
use n2n\model\Lookupable;
use rocket\spec\ei\manage\preview\model\PreviewModel;
use rocket\spec\ei\manage\preview\model\UnavailablePreviewException;
use rocket\spec\ei\manage\util\model\EiEntryUtils;

class EiCtrlUtils implements Lookupable {
	private $eiStateUtils;	
	private $httpContext;
	
	private function _init(ManageState $manageState, HttpContext $httpContext) {
		$this->init($manageState, $httpContext);
	}
	
	protected function init(ManageState $manageState, HttpContext $httpContext) {
		$this->eiStateUtils = new EiStateUtils($manageState->peakEiState());
		$this->httpContext = $httpContext;
	}
	
	/**
	 * @return EiStateUtils
	 */
	public function getEiStateUtils() {
		return $this->eiStateUtils;
	}
	
	public function getEiState() {
		return $this->eiStateUtils->getEiState();
	}
	
	public function lookupEiSelection(string $liveIdRep) {
		try {
			return $this->eiStateUtils->lookupEiSelectionById($this->eiStateUtils->idRepToId($liveIdRep));
		} catch (UnknownEntryException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (InaccessibleEntryException $e) {
			throw new ForbiddenException(null, 0, $e);
		}
	}
	
	public function lookupEiMapping(string $liveIdRep) {
		return $this->eiStateUtils->createEiMapping($this->lookupEiSelection($liveIdRep));
	}
	
	public function lookupEiSelectionByDraftId($draftId) {
		if (!is_numeric($draftId)) {
			throw new PageNotFoundException('Draft id must be numeric. ' . ReflectionUtils::getTypeInfo($draftId) 
					. ' given');
		}
		
		try {
			return $this->eiStateUtils->lookupEiSelectionByDraftId((int) $draftId);
		} catch (UnknownEntryException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (InaccessibleEntryException $e) {
			throw new ForbiddenException(null, 0, $e);
		}
	}
	
	public function lookupEiMappingByDraftId($draftId) {
		return $this->eiStateUtils->createEiMapping($this->lookupEiSelectionByDraftId($draftId));
	}

	public function buildRedirectUrl(EiSelection $eiSelection = null) { 
		$eiState = $this->eiStateUtils->getEiState();
		
		if ($eiSelection !== null && !$eiSelection->isNew()) {
			$entryNavPoint = $eiSelection->toEntryNavPoint();
			if ($eiState->isDetailUrlAvailable($entryNavPoint)) {
				return $eiState->getDetailUrl($this->httpContext, $entryNavPoint);
			}
		}
		
		return $eiState->getOverviewUrl($this->httpContext);
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
	
	public function applyCommonBreadcrumbs(EiSelection $eiSelection = null, $currentBreadcrumbLabel = null) {
		$eiState = $this->eiStateUtils->getEiState();
		$rocketState = $eiState->getN2nContext()->lookup(RocketState::class);
		CastUtils::assertTrue($rocketState instanceof RocketState);
		
		if (!$eiState->isOverviewDisabled()) {
			$rocketState->addBreadcrumb($eiState->createOverviewBreadcrumb($this->httpContext));
		}
			
		if ($eiSelection !== null && !$eiState->isDetailDisabled()) {
			$rocketState->addBreadcrumb($eiState->createDetailBreadcrumb($this->httpContext, $eiSelection));
		}
		
		if ($currentBreadcrumbLabel !== null) {
			$rocketState->addBreadcrumb(new Breadcrumb($eiState->getCurrentUrl($this->httpContext), 
					$currentBreadcrumbLabel));
		}
	}
	
	public function applyBreandcrumbs(Breadcrumb ...$additionalBreadcrumbs) {
		$eiState = $this->eiStateUtils->getEiState();
		$rocketState = $eiState->getN2nContext()->lookup(RocketState::class);
		CastUtils::assertTrue($rocketState instanceof RocketState);
		
		foreach ($additionalBreadcrumbs as $additionalBreadcrumb) {
			$rocketState->addBreadcrumb($additionalBreadcrumb);
		}
	}
	
	public function lookupPreviewController(string $previewType, EiSelection $eiSelection) {
		try {
			return $this->eiStateUtils->lookupPreviewController($previewType, $eiSelection);
		} catch (UnavailablePreviewException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
	}
	
	public static function from(HttpContext $httpContext) {
		$manageState = $httpContext->getN2nContext()->lookup(ManageState::class);
		CastUtils::assertTrue($manageState instanceof ManageState);
		
		
		$eiCtrlUtils = new EiCtrlUtils();
		$eiCtrlUtils->_init($manageState, $httpContext);
		return $eiCtrlUtils;
	}
	
	/**
	 * @param unknown $eiEntryObj
	 * @return \rocket\spec\ei\manage\util\model\EiEntryUtils
	 */
	public function toEiEntryUtils($eiEntryObj) {
		return new EiEntryUtils($eiEntryObj, $this);
	}
}
