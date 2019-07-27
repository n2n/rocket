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
use rocket\ei\security\InaccessibleEntryException;
use n2n\web\http\ForbiddenException;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\preview\model\UnavailablePreviewException;
use n2n\web\http\payload\impl\Redirect;
use rocket\ei\manage\entry\UnknownEiObjectException;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\manage\gui\ViewMode;
use n2n\web\http\controller\impl\ControllingUtils;
use rocket\si\structure\impl\EntriesListSiContent;
use rocket\si\SiPayloadFactory;
use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\persistence\orm\util\NestedSetStrategy;
use rocket\ei\util\gui\EiuGui;
use rocket\si\structure\SiCompactDeclaration;
use rocket\si\structure\SiBulkyDeclaration;
use rocket\si\content\SiPartialContent;
use rocket\si\content\SiEntry;
use rocket\si\structure\impl\BulkyEntrySiContent;

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
		} catch (InaccessibleEntryException $e) {
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
		
		$eiuGui = $this->eiuFrame->newGui(ViewMode::COMPACT_READ);
		
		$this->composeEiuGuiForList($eiuGui, $pageSize);
		
		$siCompactDeclaration = new SiCompactDeclaration(
				[$this->eiuFrame->getContextEiType()->getId() => $eiuGui->getEiGui()->getEiGuiSiFactory()->getSiFieldDeclarations()]);
		
		$zone = new EntriesListSiContent($this->eiu->frame()->getApiUrl(), $pageSize, $siCompactDeclaration, 
				new SiPartialContent($this->eiuFrame->countEntries(), $eiuGui->getEiGui()->createSiEntries()));
		
		$this->httpContext->getResponse()->send(SiPayloadFactory::createFromContent($zone));
	}
	
	
	private function composeEiuGuiForList(EiuGui $eiuGui, int $limit) {
		$eiType = $this->eiuFrame->getEiFrame()->getContextEiEngine()->getEiMask()->getEiType();
		
		$criteria = $this->eiuFrame->getEiFrame()->createCriteria(NestedSetUtils::NODE_ALIAS, false);
		$criteria->select(NestedSetUtils::NODE_ALIAS)->limit($limit);
		
		$criteria->limit($limit);
		
		
		if (null !== ($nestedSetStrategy = $eiType->getNestedSetStrategy())) {
			$this->treeLookup($eiuGui, $criteria, $nestedSetStrategy);
		} else {
			$this->simpleLookup($eiuGui, $criteria);
		}
	}
	
	private function simpleLookup(EiuGui $eiuGui, Criteria $criteria) {
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$eiuGui->appendNewEntryGui($entityObj);
		}
	}
	
	private function treeLookup(EiuGui $eiuGui, Criteria $criteria, NestedSetStrategy $nestedSetStrategy) {
		$nestedSetUtils = new NestedSetUtils($this->eiuFrame->em(), $this->eiuFrame->getContextEiType()->getEntityModel()->getClass(), $nestedSetStrategy);
		
		$eiuGui = $this->eiuFrame->newGui(ViewMode::COMPACT_READ)->renderEntryGuiControls();
		
		foreach ($nestedSetUtils->fetch(null, false, $criteria) as $nestedSetItem) {
			$eiuGui->appendNewEntryGui($nestedSetItem->getEntityObj(), $nestedSetItem->getLevel());
		}
	}
	
	function forwardDlZone($eiEntry, bool $editable) {
		if ($this->forwardHtml()) {
			return;
		}

		$eiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($eiEntry, $this->eiuFrame, 'eiEntry', true);
		$eiuEntryGui = $eiuEntry->newEntryGui(true, $editable);
		
		$eiGui = $eiuEntryGui->gui()->getEiGui();
		
		$eiTypeId = $this->eiuFrame->engine()->type()->getId();
		
		$siBulkyDeclaration = new SiBulkyDeclaration([]);
		$siBulkyDeclaration->putFieldStructureDeclarations($eiTypeId, 
				$eiGui->getEiGuiSiFactory()->getSiFieldStructureDeclarations());
		
		$zone = new BulkyEntrySiContent($siBulkyDeclaration,
				$eiuEntryGui->createSiEntry(), $eiGui->createGeneralSiControls());
		
		$this->httpContext->getResponse()->send(SiPayloadFactory::createFromContent($zone));
	}
	
	function forwardNewEntryDlZone(bool $editable = true) {
		$contextEiuType = $this->eiuFrame->engine()->type();
		
		$siEntry = new SiEntry($contextEiuType->supremeType()->getId(), null);
		
		$siBulkyDeclaration = new SiBulkyDeclaration(
				$this->eiuFrame->newGui(ViewMode::determine(true, !$editable, true)->createGeneralSiControls()));
		
		if (!$contextEiuType->isAbstract()) {
			$buildupId = $contextEiuType->getId();
			$eiEntryGui = $this->eiuFrame->newEntry()->newEntryGui()->getEiEntryGui();
			
			$siEntry->putBuildup($buildupId, $eiEntryGui->createSiTypeBuildup());
			$siBulkyDeclaration->putFieldStructureDeclarations(
					$eiEntryGui->getEiEntry()->getEiGuiSiFactory()->getSiFieldStructureDeclaration());
		}
		
		foreach ($contextEiuType->allSubTypes() as $eiuType) {
			if ($eiuType->isAbstract()) {
				continue;
			}
			
			$buildupId = $eiuType->getId();
			$eiuEntryGui = $this->eiuFrame->entry($eiuType->newObject())->newEntryGui(true, $editable);
			$eiEntryGui = $eiuEntryGui->getEiEntryGui();
			
			$siEntry->putBuildup($buildupId, $eiEntryGui->createSiBuildup());
			$siBulkyDeclaration->putFieldStructureDeclarations($buildupId, 
					$eiEntryGui->getEiGui()->getEiGuiSiFactory()->getSiFieldStructureDeclaration());
		}
		
		if (!empty($siEntry->getBuildups())) {
			throw new EiuPerimeterException('Can not create a new EiEntryGui of ' . $contextEiuType->getEiType()
					. ' because this type is abstract and doesn\'t have any sub EiTypes.');
		}
		
		$zone = new BulkyEntrySiContent($this->eiu->frame()->getApiUrl(), $siBulkyDeclaration, $siEntry);
		
		$this->httpContext->getResponse()->send(SiPayloadFactory::createFromContent($zone));
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
