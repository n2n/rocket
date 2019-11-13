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
namespace rocket\ei\util;

use n2n\web\http\PageNotFoundException;
use n2n\web\http\ForbiddenException;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\preview\model\UnavailablePreviewException;
use n2n\web\http\payload\impl\Redirect;
use rocket\ei\manage\entry\UnknownEiObjectException;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\manage\gui\ViewMode;
use n2n\web\http\controller\impl\ControllingUtils;
use rocket\si\content\impl\basic\EntriesListSiComp;
use rocket\si\SiPayloadFactory;
use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\persistence\orm\util\NestedSetStrategy;
use rocket\ei\util\gui\EiuGui;
use rocket\si\meta\SiDeclaration;
use rocket\si\content\SiPartialContent;
use rocket\si\content\SiEntry;
use rocket\si\content\impl\basic\BulkyEntrySiComp;
use rocket\ei\manage\security\InaccessibleEiEntryException;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\ei\manage\LiveEiObject;
use rocket\ei\manage\gui\EiGuiLayout;

class EiuCtrl {
	private $eiu;
	private $eiuFrame;	
	private $httpContext;
	private $cu;
	
	/**
	 * Private so future backwards compatible changes can be made.
	 * @param ControllingUtils $cu
	 */
	private function __construct(ControllingUtils $cu) {
		$this->cu = $cu;
		$manageState = $cu->getN2nContext()->lookup(ManageState::class);
		$this->eiu = new Eiu($manageState->peakEiFrame());
		$this->eiuFrame = $this->eiu->frame();
		$this->httpContext = $manageState->getN2nContext()->getHttpContext();
	}
	
	/**
	 * @return Eiu
	 */
	function eiu() {
		return $this->eiu;
	}
	
	/**
	 * @return \rocket\ei\util\frame\EiuFrame
	 */
	function frame() {
		return $this->eiuFrame;
	}
	
	/**
	 * @param string $livePid
	 * @return \rocket\ei\util\entry\EiuEntry
	 * @throws PageNotFoundException
	 * @throws ForbiddenException
	 */
	function lookupEntry(string $pid, int $ignoreConstraintTypes = 0) {
		return $this->eiuFrame->entry($this->lookupEiObject($pid, $ignoreConstraintTypes));
	}
	
	/**
	 * @param string $livePid
	 * @throws PageNotFoundException
	 * @throws ForbiddenException
	 * @return \rocket\ei\manage\EiObject
	 */
	private function lookupEiObject(string $pid, int $ignoreConstraintTypes = 0) {
		$eiObject = null;
		try {
			$eiObject = $this->eiuFrame->lookupEiObjectById($this->eiuFrame->pidToId($pid), $ignoreConstraintTypes);
		} catch (UnknownEiObjectException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (InaccessibleEiEntryException $e) {
			throw new ForbiddenException(null, 0, $e);
		}
		
		return $eiObject;
	}
	
	
// 	/**
// 	 * @param EiJhtmlEventInfo $ajahEventInfo
// 	 * @return \rocket\ei\util\EiJhtmlEventInfo|\rocket\ajah\JhtmlEventInfo
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
	
	function lookupPreviewController(string $previewType, $eiObjectArg) {
		try {
			return $this->eiuFrame->lookupPreviewController($previewType, $eiObjectArg);
		} catch (UnavailablePreviewException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
	}
	
	function redirectToOverview(int $status = null) {
		$this->httpContext->getResponse()->send(
				new Redirect($this->eiuFrame->getEiFrame()->getOverviewUrl($this->httpContext), $status));
	}
	
	
	private function forwardHtml() {
		if ('text/html' == $this->httpContext->getRequest()->getAcceptRange()
				->bestMatch(['text/html', 'application/json'])) {
			$this->cu->forward('\rocket\core\view\anglTemplate.html');
			return true;
		}
		
		return false;
	}
	
	function forwardListZone(int $pageSize = 30) {
		if ($this->forwardHtml()) {
			return;
		}
		
		$eiuGuiLayout = $this->eiuFrame->newGuiLayout(ViewMode::COMPACT_READ);
		
		$this->composeEiuGuiForList($eiuGuiLayout->getEiGuiLayout(), $pageSize);
		
		$siDeclaration = $eiuGuiLayout->getEiGuiLayout()->createSiDeclaration();
		
		$siComp = new EntriesListSiComp($this->eiu->frame()->getApiUrl(), $pageSize, $siDeclaration, 
				new SiPartialContent($this->eiuFrame->countEntries(), $eiuGuiLayout->getEiGuiLayout()->createSiEntries()));
		
		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($siComp, $eiuGuiLayout->getEiGuiLayout()->getEiGui()->createGeneralSiControls()));
	}
	
	
	private function composeEiuGuiForList($eiGuiLayout, $limit) {
		$eiType = $this->eiuFrame->getEiFrame()->getContextEiEngine()->getEiMask()->getEiType();
		
		$criteria = $this->eiuFrame->getEiFrame()->createCriteria(NestedSetUtils::NODE_ALIAS, false);
		$criteria->select(NestedSetUtils::NODE_ALIAS)->limit($limit);
		
		if (null !== ($nestedSetStrategy = $eiType->getNestedSetStrategy())) {
			$this->treeLookup($eiGuiLayout, $criteria, $nestedSetStrategy);
		} else {
			$this->simpleLookup($eiGuiLayout, $criteria);
		}
	}
	
	private function simpleLookup(EiGuiLayout $eiGuiLayout, Criteria $criteria) {
		$eiGui = $eiGuiLayout->getEiGui();
		$eiFrame = $eiGui->getEiFrame();
		$eiFrameUtil = new EiFrameUtil($eiFrame);
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$eiObject = new LiveEiObject($eiFrameUtil->createEiEntityObj($entityObj));
			$eiGuiLayout->addEiEntryGui($eiGui->createEiEntryGui($eiFrame->createEiEntry($eiObject)));
		}
	}
	
	private function treeLookup(EiGuiLayout $eiGuiLayout, Criteria $criteria, NestedSetStrategy $nestedSetStrategy) {
		$nestedSetUtils = new NestedSetUtils($this->eiuFrame->em(), $this->eiuFrame->getContextEiType()->getEntityModel()->getClass(), $nestedSetStrategy);
		
		$eiGui = $eiGuiLayout->getEiGui();
		$eiFrame = $eiGui->getEiFrame();
		$eiFrameUtil = new EiFrameUtil($eiFrame);
		foreach ($nestedSetUtils->fetch(null, false, $criteria) as $nestedSetItem) {
			$eiObject = new LiveEiObject($eiFrameUtil->createEiEntityObj($nestedSetItem->getEntityObj()));
			$eiGuiLayout->addEiEntryGui($eiGui->createEiEntryGui($eiFrame->createEiEntry($eiObject), $nestedSetItem->getLevel()));
		}
	}
	
	function forwardDlZone($eiEntry, bool $editable) {
		if ($this->forwardHtml()) {
			return;
		}

		$eiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($eiEntry, $this->eiuFrame, 'eiEntry', true);
		$eiuGuiLayout = $eiuEntry->newGuiLayout(true, $editable);
		$eiGuiLayout = $eiuGuiLayout->getEiGuiLayout();
		
		$siDeclaration = $eiGuiLayout->createSiDeclaration();
		
		$comp = new BulkyEntrySiComp($siDeclaration, $eiGuiLayout->createSiEntry());
		
		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($comp, $eiGuiLayout->getEiGui()->createGeneralSiControls()));
	}
	
	function forwardNewEntryDlZone(bool $editable = true) {
		$contextEiuType = $this->eiuFrame->engine()->type();
		
		$siEntry = new SiEntry($contextEiuType->supremeType()->getId(), !$editable, true);
		
		$siDeclaration = new SiDeclaration();
		
		if (!$contextEiuType->isAbstract()) {
			$typeId = $contextEiuType->getId();
			$eiEntryGui = $this->eiuFrame->newEntry()->newEntryGui()->getEiEntryGui();
			
			$siEntry->putBuildup($typeId, $eiEntryGui->createSiEntryBuildup());
			$siDeclaration->putFieldStructureDeclarations(
					$eiEntryGui->getEiEntry()->getEiGuiSiFactory()->getSiStructureDeclaration());
		}
		
		foreach ($contextEiuType->allSubTypes() as $eiuType) {
			if ($eiuType->isAbstract()) {
				continue;
			}
			
			$typeId = $eiuType->getId();
			$eiuEntryGui = $this->eiuFrame->entry($eiuType->newObject())->newEntryGui(true, $editable);
			$eiEntryGui = $eiuEntryGui->getEiEntryGui();
			
			$siEntry->putBuildup($typeId, $eiEntryGui->createSiBuildup());
			$siDeclaration->putFieldStructureDeclarations($typeId, 
					$eiEntryGui->getEiGui()->getEiGuiSiFactory()->getSiStructureDeclaration());
		}
		
		if (!empty($siEntry->getBuildups())) {
			throw new EiuPerimeterException('Can not create a new EiEntryGui of ' . $contextEiuType->getEiType()
					. ' because this type is abstract and doesn\'t have any sub EiTypes.');
		}
		
		$zone = new BulkyEntrySiComp($this->eiu->frame()->getApiUrl(), $siDeclaration, $siEntry);
		
		$contextEiGui = $this->eiuFrame->newGui(ViewMode::determine(true, !$editable, true))->getEiGui();
		$this->httpContext->getResponse()->send(
				SiPayloadFactory::createZoneModel($zone, $contextEiGui->createGeneralSiControls()));
	}
	
	
	
	public static function from(ControllingUtils $cu) {
		return new EiuCtrl($cu);
	}
	
	/**
	 * @param object $eiObjectObj
	 * @return \rocket\ei\util\entry\EiuEntry
	 */
	function toEiuEntry($eiObjectObj) {
		return new EiuEntry($eiObjectObj, $this);
	}
}
