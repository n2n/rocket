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
use n2n\web\http\controller\ControllerContext;
use rocket\ei\mask\EiMask;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\core\container\N2nContext;
use rocket\ei\manage\mapping\EiEntry;
use rocket\ei\manage\control\EntryNavPoint;
use rocket\ei\manage\security\EiExecution;
use n2n\web\http\HttpContext;
use n2n\util\uri\Url;
use n2n\reflection\ArgUtils;
use rocket\ei\EiEngine;
use rocket\ei\EiTypeExtension;
use rocket\ei\EiType;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\EiObject;

class EiFrame {
	private $contextEiEngine;
	private $manageState;
	private $boundry;
	private $parent;
	private $controllerContext;
	private $subEiTypeExtensions = array();
	
	private $eiExecution;
// 	private $eiObject;
// 	private $previewType;
	private $scriptRelations = array();

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
	 * @param ManageState $controllerContext
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
	public function getManageState(): ManageState {
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
	 * @param EiFrame $parent
	 */
	public function setParent(EiFrame $parent = null) {
		$this->parent = $parent;
	}
	
	/**
	 * @return EiFrame
	 */
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * @param ControllerContext $controllerContext
	 */
	public function setControllerContext(ControllerContext $controllerContext) {
		$this->controllerContext = $controllerContext;
	}
	
	/**
	 * @return ControllerContext
	 */
	public function getControllerContext(): ControllerContext {
		if (null === $this->controllerContext) {
			throw new IllegalStateException('EiFrame has no ControllerContext available');
		}
		
		return $this->controllerContext;
	}
	
	/**
	 * @param EiTypeExtension[] $subEiTypeExtensions
	 */
	public function setSubEiTypeExtensions(array $subEiTypeExtensions) {
		ArgUtils::valArray($subEiTypeExtensions, EiTypeExtension::class);
		$this->subEiTypeExtensions = $subEiTypeExtensions;
	}
	
	/**
	 * @param EiType $eiType
	 * @throws \InvalidArgumentException
	 * @return \rocket\ei\mask\EiMask
	 */
	public function determineEiMask(EiType $eiType) {
		$contextEiMask = $this->contextEiEngine->getEiMask();
		$contextEiType = $contextEiMask->getEiType();
		if ($eiType->equals($contextEiType)) {
			return $contextEiMask;
		}
		
		if (!$contextEiType->containsSubEiTypeId($eiType->getId(), true)) {
			throw new \InvalidArgumentException('Passed EiType ' . $eiType->getId() 
					. ' is not compatible with EiFrame with context EiType ' . $contextEiType->getId() . '.');
		}
		
		if (isset($this->subEiTypeExtensions[$eiType->getId()])) {
			return $this->subEiTypeExtensions[$eiType->getId()]->getEiMask();
		}
		
		return $eiType->getEiMask();
	}
	
	public function setEiRelation($scriptId, EiRelation $scriptRelation) {
		$this->scriptRelations[$scriptId] = $scriptRelation;
	}
	
	public function hasEiRelation($scriptId) {
		return isset($this->scriptRelations[$scriptId]);
	}
	
	public function getEiRelation($scriptId) {
		if (isset($this->scriptRelations[$scriptId])) {
			return $this->scriptRelations[$scriptId];
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
	public function createEiEntry(EiObject $eiObject, int $ignoreConstraintTypes = 0) {
		$eiEntry = $this->determineEiMask($eiObject->getEiEntityObj()->getEiType())->getEiEngine()
				->createFramedEiEntry($this, $eiObject, null, $this->boundry->filterEiEntryConstraints($ignoreConstraintTypes));
		
		foreach ($this->listeners as $listener) {
			$listener->onNewEiEntry($eiEntry);
		}
		
		return $eiEntry;
	}
	
	/**
	 * @param EiExecution $eiExecution
	 */
	public function setEiExecution(EiExecution $eiExecution) {
		$this->eiExecution = $eiExecution;
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
	
	public function getOverviewUrl(HttpContext $httpContext, bool $required = true) {
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
		
		return $this->getContextEiEngine()->getEiMask()->createIdentityString($eiObject, $this->getN2nContext()->getN2nLocale());
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

interface EiFrameListener {
	
	public function onNewEiEntry(EiEntry $eiEntry);
}
