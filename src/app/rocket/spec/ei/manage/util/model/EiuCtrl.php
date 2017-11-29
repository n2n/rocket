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

use n2n\web\http\PageNotFoundException;
use rocket\spec\ei\security\InaccessibleEntryException;
use n2n\web\http\ForbiddenException;
use n2n\web\http\BadRequestException;
use n2n\util\uri\Url;
use n2n\web\http\controller\ParamQuery;
use rocket\spec\ei\manage\EiObject;
use n2n\web\http\HttpContext;
use rocket\core\model\RocketState;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\ManageState;
use rocket\core\model\Breadcrumb;
use n2n\context\Lookupable;
use rocket\spec\ei\manage\preview\model\UnavailablePreviewException;
use n2n\web\http\payload\impl\Redirect;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ajah\RocketJhtmlResponse;
use rocket\spec\ei\manage\EiFrame;
use n2n\impl\web\ui\view\jhtml\JhtmlExec;

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
	
	/**
	 * @param string $liveIdRep
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function lookupEntry(string $liveIdRep) {
		return $this->eiuFrame->entry($this->lookupEiObject($liveIdRep));
	}
	
	/**
	 * @param string $liveIdRep
	 * @return \rocket\spec\ei\manage\mapping\EiEntry
	 * @deprecated use {@see self::lookupEntry()}
	 */
	public function lookupEiEntry(string $liveIdRep) {
		return $this->eiuFrame->createEiEntry($this->lookupEiObject($liveIdRep));
	}
	
	/**
	 * @param string $liveIdRep
	 * @throws PageNotFoundException
	 * @throws ForbiddenException
	 * @return \rocket\spec\ei\manage\EiObject
	 * @deprecated use {@see self::lookupEntry()}
	 */
	public function lookupEiObject(string $liveIdRep) {
		$eiObject = null;
		try {
			$eiObject = $this->eiuFrame->lookupEiObjectById($this->eiuFrame->idRepToId($liveIdRep));
		} catch (UnknownEntryException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (InaccessibleEntryException $e) {
			throw new ForbiddenException(null, 0, $e);
		}
		
		return $eiObject;
	}
	
	/**
	 * @param string $liveIdRep
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function lookupEntryByDraftId(int $draftId) {
		return $this->eiuFrame->entry($this->lookupEiObjectByDraftId($draftId));
	}
	
	/**
	 * @param int $draftId
	 * @return \rocket\spec\ei\manage\mapping\EiEntry
	 * @deprecated
	 */
	private function lookupEiEntryByDraftId(int $draftId) {
		return $this->eiuFrame->createEiEntry($this->lookupEiObjectByDraftId($draftId));
	}
	
	/**
	 * @param int $draftId
	 * @throws PageNotFoundException
	 * @throws ForbiddenException
	 * @return \rocket\spec\ei\manage\EiObject
	 * @deprecated
	 */
	public function lookupEiObjectByDraftId(int $draftId) {
		$eiObject = null;
		try {
			$eiObject = $this->eiuFrame->lookupEiObjectByDraftId((int) $draftId);
		} catch (UnknownEntryException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (InaccessibleEntryException $e) {
			throw new ForbiddenException(null, 0, $e);
		}
		
		return $eiObject;
	}
	
	public function redirectToReferer(string $fallbackUrl, EiJhtmlEventInfo $ajahEventInfo = null, JhtmlExec $ajahExec = null) {
	    $refererUrl = $this->httpContext->getRequest()->getHeader('Referer');
	    if ($refererUrl === null) {
	        $refererUrl = $fallbackUrl;
	    }
	    
	    $response = $this->httpContext->getResponse();
	    $acceptRange = $this->httpContext->getRequest()->getAcceptRange();
	    if ('application/json' != $acceptRange->bestMatch(['text/html', 'application/json'])) {
	        $response->send(new Redirect($refererUrl));
	        return;
	    }
	    
	    $response->send(RocketJhtmlResponse::redirectToReferer($refererUrl, $ajahEventInfo, $ajahExec));
	}
	
	public function redirectBack(string $fallbackUrl, EiJhtmlEventInfo $ajahEventInfo = null, JhtmlExec $ajahExec = null) {
	    $response = $this->httpContext->getResponse();
	    $acceptRange = $this->httpContext->getRequest()->getAcceptRange();
	    if ('application/json' != $acceptRange->bestMatch(['text/html', 'application/json'])) {
	        $response->send(new Redirect($refererUrl));
	        return;
	    }
	    
	    $response->send(RocketJhtmlResponse::redirectBack($fallbackUrl, $ajahEventInfo, $ajahExec));
	}
	
	public function redirect(string $url, EiJhtmlEventInfo $ajahEventInfo = null, JhtmlExec $ajahExec = null) {
		$response = $this->httpContext->getResponse();
		$acceptRange = $this->httpContext->getRequest()->getAcceptRange();
		if ('application/json' != $acceptRange->bestMatch(['text/html', 'application/json'])) {
			$response->send(new Redirect($url));
			return;
		}
		
		$response->send(RocketJhtmlResponse::redirect($url, $ajahEventInfo, $ajahExec));
	}
	
	public function forwardView(HtmlView $view) {
		$response = $this->httpContext->getResponse();
		$acceptRange = $this->httpContext->getRequest()->getAcceptRange();
// 		if ('application/json' == $acceptRange->bestMatch(['text/html', 'application/json'])) {
// 			$response->send(new AjahResponse($view));
// 			return;
// 		}
		
		$response->send($view);
	}

	public function buildRedirectUrl($eiEntryArg = null) { 
		$eiObject = $eiEntryArg === null ? null : EiuFactory::buildEiObjectFromEiArg($eiEntryArg);
		$eiFrame = $this->eiuFrame->getEiFrame(); 
		
		if ($eiObject !== null && !$eiObject->isNew()) {
			$entryNavPoint = $eiObject->toEntryNavPoint();
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
	
	public function buildRefRedirectUrl(Url $redirectUrl = null, EiObject $eiObject = null) {
		if ($redirectUrl !== null) {
			return $redirectUrl;	
		}
		
		return $this->buildRedirectUrl($eiObject);
	}
	
	public function applyCommonBreadcrumbs($eiObjectObj = null, string $currentBreadcrumbLabel = null) {
		$eiFrame = $this->eiuFrame->getEiFrame();
		$rocketState = $eiFrame->getN2nContext()->lookup(RocketState::class);
		CastUtils::assertTrue($rocketState instanceof RocketState);
		
		if (!$eiFrame->isOverviewDisabled()) {
			$rocketState->addBreadcrumb($eiFrame->createOverviewBreadcrumb($this->httpContext));
		}
			
		if ($eiObjectObj !== null && !$eiFrame->isDetailDisabled()) {
			$eiObject = EiuFactory::buildEiObjectFromEiArg($eiObjectObj, 'eiObjectObj', $this->eiuFrame->getEiType());
			$rocketState->addBreadcrumb($eiFrame->createDetailBreadcrumb($this->httpContext, $eiObject));
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
	
	public function lookupPreviewController(string $previewType, $eiObjectArg) {
		try {
			return $this->eiuFrame->lookupPreviewController($previewType, $eiObjectArg);
		} catch (UnavailablePreviewException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
	}
	
	public function redirectToOverview(int $status = null) {
		$this->httpContext->getResponse()->send(
				new Redirect($this->eiuFrame->getEiFrame()->getOverviewUrl($this->httpContext), $status));
	}
	
	public static function from(HttpContext $httpContext, EiFrame $eiFrame = null) {
		$manageState = $httpContext->getN2nContext()->lookup(ManageState::class);
		CastUtils::assertTrue($manageState instanceof ManageState);
		
		
		$eiCtrlUtils = new EiuCtrl();
		$eiCtrlUtils->init($manageState, $httpContext, $eiFrame);
		return $eiCtrlUtils;
	}
	
	/**
	 * @param object $eiObjectObj
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function toEiuEntry($eiObjectObj) {
		return new EiuEntry($eiObjectObj, $this);
	}
}
