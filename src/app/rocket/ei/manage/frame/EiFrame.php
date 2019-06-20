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
namespace rocket\ei\manage\frame;

use n2n\util\ex\IllegalStateException;
use rocket\core\model\Breadcrumb;
use rocket\ei\mask\EiMask;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\core\container\N2nContext;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\security\EiExecution;
use n2n\web\http\HttpContext;
use n2n\util\uri\Url;
use n2n\util\type\ArgUtils;
use rocket\ei\EiCommandPath;
use rocket\ei\EiEngine;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\security\EiEntryAccessFactory;
use rocket\ei\manage\security\EiEntryAccess;
use rocket\ei\EiPropPath;
use rocket\ei\component\command\EiCommand;

class EiFrame {
	
	private $contextEiEngine;
	private $manageState;
	private $boundry;
	private $eiForkLink;
	private $baseUrl;
	
	private $eiExecution;
// 	private $eiObject;
// 	private $previewType;
	private $eiRelations = array();

	private $filterModel;
	private $sortModel;
	
	private $eiTypeConstraint;
	
	private $overviewDisabled = false;
	private $overviewBreadcrumbLabelOverride;
	private $overviewUrlExt;
	private $detailDisabled = false;
	private $detailBreadcrumbLabelOverride;
	private $detailUrlExt;
	
	private $listeners = array();

	/**
	 * @param EiMask $contextEiEngine
	 * @param ManageState $manageState
	 */
	public function __construct(EiEngine $contextEiEngine, ManageState $manageState) {
		$this->contextEiEngine = $contextEiEngine;
		$this->manageState = $manageState;
		$this->boundry = new Boundry();

// 		$this->eiTypeConstraint = $manageState->getSecurityManager()->getConstraintBy($contextEiMask);
	}

// 	/**
// 	 * @return \rocket\ei\EiType
// 	 */
// 	public function getContextEiType(): EiType {
// 		return $this->contextEiMask->getEiEngine()->getEiMask()->getEiType();
// 	}
	
	/**
	 * @return EiEngine
	 */
	public function getContextEiEngine() {	
		return $this->contextEiEngine;
	}
	
	/**
	 * @return ManageState
	 */
	public function getManageState() {
		return $this->manageState;
	}
	
	/**
// 	 * @throws \n2n\util\ex\IllegalStateException
// 	 * @return \n2n\persistence\orm\EntityManager
// 	 */
// 	public function getEntityManager(): EntityManager {
// 		return $this->manageState->getEntityManager();
// 	}
	
	/**
	 * @return N2nContext
	 */
	public function getN2nContext() {
		return $this->manageState->getN2nContext();
	}
	
	/**
	 * @param EiForkLink|null $forkLink
	 */
	public function setEiFrokLink(?EiForkLink $forkLink) {
		$this->forkLink = $forkLink;
	}
	
	/**
	 * @return EiFrame|null
	 */
	public function getEiForkLink() {
		return $this->parent;
	}
	
	public function setEiForkLink(EiForkLink $eiForkLink) {
		$this->eiForkLink = $eiForkLink;
	}
	
	public function hasBaseUrl() {
		return $this->baseUrl !== null;
	}
	
	/**
	 * @param Url $url
	 */
	public function setBaseUrl(?Url $url) {
		$this->baseUrl = $url;
	}
	
	/**
	 * @return Url
	 */
	public function getBaseUrl() {
		if (null === $this->baseUrl) {
			throw new IllegalStateException('BaseUrl of EiFrame is unknown.');
		}
		
		return $this->baseUrl;
	}
	
// 	/**
// 	 * @param EiTypeExtension[] $subEiTypeExtensions
// 	 */
// 	public function setSubEiTypeExtensions(array $subEiTypeExtensions) {
// 		ArgUtils::valArray($subEiTypeExtensions, EiTypeExtension::class);
// 		$this->subEiTypeExtensions = $subEiTypeExtensions;
// 	}
	
// 	/**
// 	 * @param EiType $eiType
// 	 * @throws \InvalidArgumentException
// 	 * @return \rocket\ei\mask\EiMask
// 	 */
// 	public function determineEiMask(EiType $eiType) {
// 		$contextEiMask = $this->contextEiEngine->getEiMask();
// 		$contextEiType = $contextEiMask->getEiType();
// 		if ($eiType->equals($contextEiType)) {
// 			return $contextEiMask;
// 		}
		
// 		if (!$contextEiType->containsSubEiTypeId($eiType->getId(), true)) {
// 			throw new \InvalidArgumentException('Passed EiType ' . $eiType->getId() 
// 					. ' is not compatible with EiFrame with context EiType ' . $contextEiType->getId() . '.');
// 		}
		
// 		if (isset($this->subEiTypeExtensions[$eiType->getId()])) {
// 			return $this->subEiTypeExtensions[$eiType->getId()]->getEiMask();
// 		}
		
// 		return $eiType->getEiMask();
// 	}
	
	public function setEiRelation(EiPropPath $eiPropPath, EiRelation $scriptRelation) {
		$this->eiRelations[(string) $eiPropPath] = $scriptRelation;
	}
	
	public function hasEiRelation(EiPropPath $eiPropPath) {
		return isset($this->eiRelations[(string) $eiPropPath]);
	}
	
	public function getEiRelation(EiPropPath $eiPropPath) {
		if (isset($this->eiRelations[(string) $eiPropPath])) {
			return $this->eiRelations[(string) $eiPropPath];
		}
		
		return null;
	}
	
	/**
	 * @return Boundry
	 */
	public function getBoundry() {
		return $this->boundry;
	}
	
// 	public function getOrCreateFilterModel() {
// 		if ($this->filterModel !== null) {
// 			return $this->filterModel;
// 		}

// 		return $this->filterModel = CritmodFactory::createFilterModelFromEiFrame($this);
// 	}
	
// 	public function getOrCreateSortModel() {
// 		if ($this->sortModel !== null) {
// 			return $this->sortModel;
// 		}
	
// 		return $this->sortModel = CritmodFactory::createSortModelFromEiFrame($this);
// 	}
	/**
	 * @param \n2n\persistence\orm\EntityManager $em
	 * @param string $entityAlias
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function createCriteria(string $entityAlias, int $ignoreConstraintTypes = 0) {
		$em = $this->manageState->getEntityManager();
		$criteria = null;
		$criteriaFactory = $this->boundry->getCriteriaFactory();		
		if ($criteriaFactory !== null && !($ignoreConstraintTypes & Boundry::TYPE_MANAGE)) {
			$criteria = $criteriaFactory->create($em, $entityAlias);
		} else {
			$criteria = $em->createCriteria()->from(
					$this->getContextEiEngine()->getEiMask()->getEiType()->getEntityModel()->getClass(), 
					$entityAlias);
		}

		$entityAliasCriteriaProperty = CrIt::p(array($entityAlias));
		
		foreach ($this->boundry->filterCriteriaConstraints($ignoreConstraintTypes) as $criteriaConstraint) {
			$criteriaConstraint->applyToCriteria($criteria, $entityAliasCriteriaProperty);
		}

// 		if (!($ignoreConstraintTypes & Boundry::TYPE_SECURITY)
// 				&& null !== ($criteriaConstraint = $this->getEiExecution()->getCriteriaConstraint())) {
// 			$criteriaConstraint->applyToCriteria($criteria, $entityAliasCriteriaProperty);
// 		}
		
		return $criteria;
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param int $ignoreConstraintTypes
	 * @return EiEntry
	 */
	public function createEiEntry(EiObject $eiObject, EiEntry $copyFrom = null, int $ignoreConstraintTypes = 0) {
		$eiEntry = $this->contextEiEngine->getEiMask()->determineEiMask($eiObject->getEiEntityObj()->getEiType())->getEiEngine()
				->createFramedEiEntry($this, $eiObject, $copyFrom, $this->boundry->filterEiEntryConstraints($ignoreConstraintTypes));
		
		foreach ($this->listeners as $listener) {
			$listener->onNewEiEntry($eiEntry);
		}
		
		return $eiEntry;
	}
	
	/**
	 * @param EiExecution $eiExecution
	 */
	public function exec(EiCommandPath $eiCommandPath, ?EiCommand $eiCommand) {
		if ($this->eiExecution !== null) {
			throw new IllegalStateException('EiFrame already executed.');
		}
		
		$this->eiExecution = $this->manageState->getEiPermissionManager()
				->createEiExecution($this, $eiCommandPath, $eiCommand);
	}
	
	/**
	 * @throws IllegalStateException
	 * @return EiExecution
	 */
	public function getEiExecution() {
		if (null === $this->eiExecution) {
			throw new IllegalStateException('EiFrame contains no EiExecution.');
		}
		
		return $this->eiExecution;
	}
	
	/**
	 * @return bool
	 */
	public function hasEiExecution() {
		return $this->eiExecution !== null;
	}
	
	
	
	/**
	 * @throws IllegalStateException
	 * @return EiEntryAccessFactory
	 */
	public function getEiEntryAccessFactory() {
		return $this->getEiExecution()->getEiEntryAccessFactory();
	}
	
	/**
	 * @return bool
	 */
	public function hasEiEntryAccessFactory() {
		return $this->eiExecution !== null;
	}
	
	/**
	 * @param EiCommandPath $eiCommandPath
	 * @return bool
	 */
	public function isExecutableBy(EiCommandPath $eiCommandPath): bool {
		return $this->getEiEntryAccessFactory()->isExecutableBy($eiCommandPath);
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @return EiEntryAccess
	 */
	public function createEiEntryAccess(EiEntry $eiEntry): EiEntryAccess {
		return $this->getEiEntryAccessFactory()->createEiEntryAccess($eiEntry);
	}
	
	public function setOverviewDisabled(bool $overviewDisabled) {
		$this->overviewDisabled = $overviewDisabled;
	}
	
	public function isOverviewDisabled() {
		return $this->overviewDisabled;
	}
	
	private function ensureOverviewEnabled() {
		if ($this->overviewDisabled) {
			throw new IllegalStateException('Overview is disabled');
		}
	}
	
	public function setOverviewBreadcrumbLabelOverride(string $overviewBreadcrumbLabel = null) {
		$this->overviewBreadcrumbLabelOverride = $overviewBreadcrumbLabel;
	}
	
	public function getOverviewBreadcrumbLabelOverride() {
		return $this->overviewBreadcrumbLabelOverride;
	}
	
	public function getOverviewBreadcrumbLabel() {
		if (null !== $this->overviewBreadcrumbLabelOverride) {
			return $this->overviewBreadcrumbLabelOverride; 
		}
		
		$this->ensureOverviewEnabled();
		
		return $this->getContextEiEngine()->getEiMask()->getPluralLabelLstr();
	}
	
	public function setOverviewUrlExt(Url $overviewUrlExt = null) {
		ArgUtils::assertTrue($overviewUrlExt->isRelative(), 'Url must be relative.');
		$this->overviewUrlExt = $overviewUrlExt;
	}
	
	public function getOverviewUrlExt() {
		return $this->overviewUrlExt;
	}

	public function isOverviewUrlAvailable() {
		return $this->overviewUrlExt !== null || (!$this->overviewDisabled
				&& $this->getContextEiEngine()->getEiMask()->getEiCommandCollection()->hasGenericOverview());
	}
	
	public function getOverviewUrl(bool $required = true) {
		$httpContext = $this->getN2nContext()->getHttpContext();
		
		if ($this->overviewUrlExt !== null) {
			return $httpContext->getRequest()->getContextPath()->toUrl()->ext($this->overviewUrlExt);
		} 
		
		$overviewUrlExt = $this->getContextEiEngine()->getEiMask()->getEiCommandCollection()
				->getGenericOverviewUrlExt($required);
		
		if ($overviewUrlExt === null) return null;
		
		$this->ensureOverviewEnabled();
		
		return $httpContext->getControllerContextPath($this->getControllerContext())->toUrl()->ext($overviewUrlExt);
	}

	public function createOverviewBreadcrumb(HttpContext $httpContext) {
		return new Breadcrumb($this->getOverviewUrl($httpContext), $this->getOverviewBreadcrumbLabel());
	}
	
	private function ensureDetailEnabled() {
		if ($this->detailDisabled) {
			throw new IllegalStateException('Detail is disabled');
		}
	}
	
	public function createDetailBreadcrumb(HttpContext $httpContext, EiObject $eiObject) {
		return new Breadcrumb(
				$this->getDetailUrl($httpContext, $eiObject->toEntryNavPoint($this->getContextEiEngine()->getEiMask()->getEiType())),
				$this->getDetailBreadcrumbLabel($eiObject));
	}
	
	public function getApiUrl(EiCommandPath $eiCommandPath) {
		return $this->getBaseUrl()->ext([EiFrameController::API_PATH_PART, (string) $eiCommandPath]);
	}
	
	public function getCmdUrl(EiCommandPath $eiCommandPath) {
		return $this->getBaseUrl()->ext([EiFrameController::CMD_PATH_PART, (string) $eiCommandPath]);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param string $mode
	 * @param EiEntry|null $eiEntry
	 * @return \n2n\util\uri\Url
	 */
	public function getForkUrl(EiPropPath $eiPropPath, string $mode, EiObject $eiObject = null) {
		if ($eiObject === null) {
			return $this->getBaseUrl()->ext([EiFrameController::FORK_PATH, (string) $eiPropPath, $mode]);
		}
		 
		return $this->getBaseUrl()->ext([EiFrameController::FORK_ENTRY_PATH, $eiObject->getEiEntityObj()->getPid(), 
				(string) $eiPropPath, $mode]);
	}
	
	public function setDetailDisabled($detailDisabled) {
		$this->detailDisabled = (boolean) $detailDisabled;
	}
	
	public function isDetailDisabled() {
		return $this->detailDisabled;
	}
	
	public function setDetailBreadcrumbLabelOverride(string $detailBreadcrumbLabelOverride = null) {
		$this->detailBreadcrumbLabelOverride = $detailBreadcrumbLabelOverride;
	}
	
	/**
	 * @return string
	 */
	public function getDetailBreadcrumbLabelOverride() {
		return $this->detailBreadcrumbLabelOverride;
	}
		
	/**
	 * @param EiObject $eiObject
	 * @return string
	 */
	public function getDetailBreadcrumbLabel(EiObject $eiObject): string {		
		if ($this->detailBreadcrumbLabelOverride !== null) {
			return $this->detailBreadcrumbLabelOverride;
		}
	
		$this->ensureDetailEnabled();
		
		return $this->manageState->getDef()->getGuiDefinition($this->contextEiEngine->getEiMask())
				->createIdentityString($eiObject, $this->getN2nContext(), $this->getN2nContext()->getN2nLocale());
	}
	
	public function setDetailUrlExt(Url $detailUrlExt) {
		ArgUtils::assertTrue($detailUrlExt->isRelative(), 'Url must be relative.');
		$this->detailUrlExt = $detailUrlExt;
	}
	
	public function getDetailUrlExt() {
		return $this->detailUrlExt;
	}

	public function isDetailUrlAvailable(EntryNavPoint $entryNavPoint) {
		return $this->detailUrlExt !== null || 
				(!$this->detailDisabled && $this->getContextEiEngine()->getEiMask()
						->getEiCommandCollection()->hasGenericDetail($entryNavPoint));
	}
	
	public function getDetailUrl(HttpContext $httpContext, EntryNavPoint $entryNavPoint, bool $required = true) {
		if ($this->detailUrlExt !== null) {
			return $httpContext->getRequest()->getContextPath()->ext($this->detailUrlExt);
		}
		
		$detailUrlExt = $this->getContextEiEngine()->getEiMask()->getEiCommandCollection()
				->getGenericDetailUrlExt($entryNavPoint, $required);
		
		if ($detailUrlExt === null) return null;
		
		$this->ensureDetailEnabled();
		
		return $httpContext->getControllerContextPath($this->getControllerContext())->toUrl()
				->ext($detailUrlExt);
	}
	
	private $currentUrlExt;
	
	public function setCurrentUrlExt(Url $currentUrlExt) {
		ArgUtils::assertTrue($currentUrlExt->isRelative(), 'Url must be relative.');
		$this->currentUrlExt = $currentUrlExt;
	}
	
	public function getCurrentUrlExt() {
		return $this->currentUrlExt;
	}
	
	public function getCurrentUrl(HttpContext $httpContext) {
		if ($this->currentUrlExt !== null) {
			return $httpContext->getRequest()->getContextPath()->toUrl()->ext($this->currentUrlExt);
		}
		
		return $httpContext->getRequest()->getRelativeUrl();
	}
	
	public function registerListener(EiFrameListener $listener) {
		$this->listeners[spl_object_hash($listener)] = $listener;
	}
	
	public function unregisterListener(EiFrameListener $listener) {
		unset($this->listeners[spl_object_hash($listener)]);		
	}
}

class EiForkLink {
	const MODE_DISCOVER = 'discover';
	const MODE_SELECT = 'select';
	
	private $parent;
	private $mode;
	private $parentEiObject;
	
	function __construct(EiFrame $parent, string $mode, EiObject $parentEiObject = null) {
		$this->parent = $parent;
		ArgUtils::valEnum($mode, self::getModes());
		$this->mode = $mode;
		$this->parentEiObject = $parentEiObject;
	}
	
	/**
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	function getParent() {
		return $this->parent;
	}
	
	/**
	 * @return string
	 */
	function getMode() {
		return $this->mode;
	}
	
	/**
	 * @return \rocket\ei\manage\EiObject|null
	 */
	function getParentEiObject() {
		return $this->parentEiObject;
	}
	
	/**
	 * @return string[]
	 */
	static function getModes() {
		return [self::MODE_DISCOVER, self::MODE_SELECT];
	}
}

interface EiFrameListener {
	
	public function onNewEiEntry(EiEntry $eiEntry);
}
