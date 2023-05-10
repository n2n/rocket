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
namespace rocket\op\ei\util;

use rocket\op\ei\util\si\EifBulkyEntrySiGui;
use rocket\op\ei\util\si\EifSiGui;
use rocket\op\util\OpuCtrl;

/**
 * @deprecated
 */
class EiuCtrl extends OpuCtrl {

// 	/**
// 	 * @param EiJhtmlEventInfo $ajahEventInfo
// 	 * @return \rocket\op\ei\util\EiJhtmlEventInfo|\rocket\ajah\JhtmlEventInfo
// 	 */
// 	private function completeEventInfo(EiJhtmlEventInfo $ajahEventInfo = null) {
// 		if ($ajahEventInfo === null) {
// 			$ajahEventInfo = new EiJhtmlEventInfo();
// 		}

// 		$n2nContext = $this->httpContext->getN2nContext();

// 		$ajahEventInfo->introduceMessageContainer($n2nContext->lookup(MessageContainer::class));

// 		$manageState = $n2nContext->lookup(ManageState::class);
// 		CastUtils::assertTrue($manageState instanceof ManageState);

// 		$ajahEventInfo->introduceEiLifecycleMonitor($manageState->getEiLifecycleMonitor());

// 		return $ajahEventInfo;
// 	}

// 	function redirectToReferer(string $fallbackUrl, EiJhtmlEventInfo $ajahEventInfo = null, JhtmlExec $ajahExec = null) {
// 	    $refererUrl = $this->httpContext->getRequest()->getHeader('Referer');
// 	    if ($refererUrl === null) {
// 	        $refererUrl = $fallbackUrl;
// 	    }

// 	    $response = $this->httpContext->getResponse();
// 	    $acceptRange = $this->httpContext->getRequest()->getAcceptRange();
// 	    if ('application/json' != $acceptRange->bestMatch(['text/html', 'application/json'])) {
// 	        $response->send(new Redirect($refererUrl));
// 	        return;
// 	    }

// 	    $response->send(RocketJhtmlResponse::redirectToReferer($refererUrl,
// 	    		$this->completeEventInfo($ajahEventInfo), $ajahExec));
// 	}

// 	function redirectBack(string $fallbackUrl, EiJhtmlEventInfo $ajahEventInfo = null, JhtmlExec $ajahExec = null) {
// 	    $response = $this->httpContext->getResponse();
// 	    $acceptRange = $this->httpContext->getRequest()->getAcceptRange();
// 	    if ('application/json' != $acceptRange->bestMatch(['text/html', 'application/json'])) {
// 	    	$response->send(new Redirect($fallbackUrl));
// 	        return;
// 	    }

// 	    $response->send(RocketJhtmlResponse::redirectBack($fallbackUrl,
// 	    		$this->completeEventInfo($ajahEventInfo), $ajahExec));
// 	}

// 	function redirect(string $url, EiJhtmlEventInfo $ajahEventInfo = null, JhtmlExec $ajahExec = null) {
// 		$response = $this->httpContext->getResponse();
// 		$acceptRange = $this->httpContext->getRequest()->getAcceptRange();
// 		if ('application/json' != $acceptRange->bestMatch(['text/html', 'application/json'])) {
// 			$response->send(new Redirect($url));
// 			return;
// 		}

// 		$response->send(RocketJhtmlResponse::redirect($url,
// 				$this->completeEventInfo($ajahEventInfo), $ajahExec));
// 	}

// 	function forwardView(HtmlView $view, EiJhtmlEventInfo $ajahEventInfo = null) {
// 		$response = $this->httpContext->getResponse();
// 		$acceptRange = $this->httpContext->getRequest()->getAcceptRange();

// 		if ($ajahEventInfo === null || 'application/json' != $acceptRange->bestMatch(['text/html', 'application/json'])) {
// 			$response->send($view);
// 			return;
// 		}

// // 		if ('application/json' == $acceptRange->bestMatch(['text/html', 'application/json'])) {
// // 			$response->send(new AjahResponse($view));
// // 			return;
// // 		}

// 		$response->send(RocketJhtmlResponse::view($view, $this->completeEventInfo($ajahEventInfo)));
// 	}

// 	function buildRedirectUrl($eiEntryArg = null) {
// 		$eiObject = $eiEntryArg === null ? null : EiuAnalyst::buildEiObjectFromEiArg($eiEntryArg);
// 		$eiFrame = $this->eiuFrame->getEiFrame();

// 		if ($eiObject !== null && !$eiObject->isNew()) {
// 			$entryNavPoint = $eiObject->toEntryNavPoint();
// 			if ($eiFrame->isDetailUrlAvailable($entryNavPoint)) {
// 				return $eiFrame->getDetailUrl($this->httpContext, $entryNavPoint);
// 			}
// 		}

// 		return $eiFrame->getOverviewUrl($this->httpContext);
// 	}

// 	function parseRefUrl(ParamQuery $refPath = null) {
// 		if ($refPath === null) return null;

// 		try {
// 			$url = Url::create($refPath);
// 			if ($url->isRelative()) return $url;

// 			throw new BadRequestException('refPath not relative: ' . $refPath);
// 		} catch (\InvalidArgumentException $e) {
// 			throw new BadRequestException('Invalid refPath: ' . $refPath, null, $e);
// 		}
// 	}

// 	function buildRefRedirectUrl(Url $redirectUrl = null, EiObject $eiObject = null) {
// 		if ($redirectUrl !== null) {
// 			return $redirectUrl;
// 		}

// 		return $this->buildRedirectUrl($eiObject);
// 	}

// 	function applyCommonBreadcrumbs($eiObjectObj = null, string $currentBreadcrumbLabel = null) {
// 		$eiFrame = $this->eiuFrame->getEiFrame();
// 		$rocketState = $eiFrame->getN2nContext()->lookup(RocketState::class);
// 		CastUtils::assertTrue($rocketState instanceof RocketState);

// 		if (!$eiFrame->isOverviewDisabled()) {
// 			$rocketState->addBreadcrumb($eiFrame->createOverviewBreadcrumb($this->httpContext));
// 		}

// 		if ($eiObjectObj !== null && !$eiFrame->isDetailDisabled()) {
// 			$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectObj, 'eiObjectObj', $this->eiuFrame->getContextEiType());
// 			$rocketState->addBreadcrumb($eiFrame->createDetailBreadcrumb($this->httpContext, $eiObject));
// 		}

// 		if ($currentBreadcrumbLabel !== null) {
// 			$rocketState->addBreadcrumb(new Breadcrumb($eiFrame->getCurrentUrl($this->httpContext),
// 					$currentBreadcrumbLabel));
// 		}
// 	}

// 	function applyBreadcrumbs(Breadcrumb ...$additionalBreadcrumbs) {
// 		$eiFrame = $this->eiuFrame->getEiFrame();
// 		$rocketState = $eiFrame->getN2nContext()->lookup(RocketState::class);
// 		CastUtils::assertTrue($rocketState instanceof RocketState);

// 		foreach ($additionalBreadcrumbs as $additionalBreadcrumb) {
// 			$rocketState->addBreadcrumb($additionalBreadcrumb);
// 		}
// 	}

// 	/**
// 	 * @param GuiControl[] $guiControls
// 	 */
// 	private function createSiControls($guiControls) {
// 		return array_map(function ($guiControl) use ($url) {
// 			return $guiControl->toSiControl($this->cu->getRequest()->getUrl(), new ZoneApiControlCallId([$guiControl->getId()]));
// 		}, $guiControls);
// 	}
}

