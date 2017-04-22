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
namespace rocket\spec\ei\manage;

use n2n\persistence\orm\EntityManager;
use n2n\util\ex\IllegalStateException;
use rocket\core\model\Breadcrumb;
use n2n\web\http\controller\ControllerContext;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\manage\ManageState;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\control\EntryNavPoint;
use rocket\spec\ei\security\EiExecution;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;
use n2n\web\http\HttpContext;
use n2n\util\uri\Url;
use n2n\reflection\ArgUtils;

class EiFrame {
	private $contextEiMask;
	private $manageState;
	private $criteriaConstraintCollection;
	private $parent;
	private $controllerContext;
	
	private $eiExecution;
// 	private $eiObject;
// 	private $previewType;
	private $scriptRelations = array();

	private $criteriaFactory = null;
	private $criteriaConstraints = array();
	private $filterModel;
	private $sortModel;
	
	private $eiTypeConstraint;
	private $commandExecutionConstraint;
	
	private $overviewDisabled = false;
	private $overviewBreadcrumbLabelOverride;
	private $overviewUrlExt;
	private $detailDisabled = false;
	private $detailBreadcrumbLabelOverride;
	private $detailUrlExt;

	/**
	 * @param  $contextEiMask
	 * @param ControllerContext $controllerContext
	 * @param HardFilter $hardFilter
	 */
	public function __construct(EiMask $contextEiMask, ManageState $manageState) {
		$this->contextEiMask = $contextEiMask;
		$this->manageState = $manageState;
		$this->criteriaConstraintCollection = new CriteriaConstraintCollection();

// 		$this->eiTypeConstraint = $manageState->getSecurityManager()->getConstraintBy($contextEiMask);
	}

// 	/**
// 	 * @return \rocket\spec\ei\EiType
// 	 */
// 	public function getContextEiType(): EiType {
// 		return $this->contextEiMask->getEiEngine()->getEiType();
// 	}
	
	/**
	 * @return EiMask
	 */
	public function getContextEiMask(): EiMask {	
		return $this->contextEiMask;
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
	
	public function getN2nLocale() {
		return $this->getN2nContext()->getN2nLocale();	
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
	 * @param CriteriaFactory $criteriaFactory
	 */
	public function setCriteriaFactory(CriteriaFactory $criteriaFactory) {
		$this->criteriaFactory = $criteriaFactory;
	}
	
	/**
	 * @return CriteriaConstraintCollection
	 */
	public function getCriteriaConstraintCollection() {
		return $this->criteriaConstraintCollection;
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
	public function createCriteria($entityAlias, $ignoreConstraintTypes = 0) {
		$em = $this->manageState->getEntityManager();
		$criteria = null;
		if ($this->criteriaFactory !== null && !($ignoreConstraintTypes & CriteriaConstraint::TYPE_MANAGE)) {
			$criteria = $this->criteriaFactory->create($em, $entityAlias);
		} else {
			$criteria = $em->createCriteria()->from($this->getContextEiMask()->getEiEngine()->getEiType()->getEntityModel()->getClass(), $entityAlias);
		}

		$entityAliasCriteriaProperty = CrIt::p(array($entityAlias));
		
		foreach ($this->criteriaConstraintCollection->findAll($ignoreConstraintTypes) as $criteriaConstraint) {
			$criteriaConstraint->applyToCriteria($criteria, $entityAliasCriteriaProperty);
		}
		
// 		if ($applyMaskConstraints && null !== ($filterData = $this->getContextEiMask()->getFilterGroupData())) {
// 			$this->getOrCreateFilterModel()->createCriteriaConstraint($filterData)
// 			->applyToCriteria($criteria, CrIt::p(array($entityAlias)));
// 		}
		
// 		if ($applyDefaultSort) {
// 			$defaultSortDirections = $this->getContextEiMask()->getDefaultSortData();
// 			if (!empty($defaultSortDirections)
// 					&& null !== ($constraint = $this->getOrCreateSortModel()
// 							->createCriteriaConstraint($defaultSortDirections))) {
// 								$constraint->applyToCriteria($criteria, CrIt::p(array($entityAlias)));
// 							}
// 		}
		
// 		if ($applySecurityConstraints && null !== ($commandExecutionConstraint = $this->getCommandExecutionConstraint())) {
// 			$commandExecutionConstraint->applyToCriteria($criteria, CrIt::p(array($entityAlias)));
// 		}

		if (!($ignoreConstraintTypes & CriteriaConstraint::TYPE_SECURITY)
				&& null !== ($criteriaConstraint = $this->getEiExecution()->getCriteriaConstraint())) {
			$criteriaConstraint->applyToCriteria($criteria, $entityAliasCriteriaProperty);
		}
		
		return $criteria;
	}
	
	public function setEiExecution(EiExecution $eiExecution) {
		$this->eiExecution = $eiExecution;
	}
	

	public function getEiExecution(): EiExecution {
		if (null === $this->eiExecution) {
			throw new IllegalStateException('EiFrame contains no EiExecution.');
		}
		
		return $this->eiExecution;
	}
	
	public function hasEiExecution(): bool {
		return $this->eiExecution !== null;
	}
	
	/**
	 * @return EiMapping
	 */
	public function restrictEiMapping(EiMapping $eiMapping) {
		if (null !== ($mappingConstraint = $this->getEiExecution()->getEiMappingConstraint())) {
			$eiMapping->getEiMappingConstraintSet()->add($mappingConstraint);
		}
		
		if (null !== ($restrictor = $this->getEiExecution()->buildEiCommandAccessRestrictor($eiMapping))) {
			$eiMapping->getEiCommandAccessRestrictorSet()->add($restrictor);
		}
		
		return $eiMapping;
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
		
		return $this->getContextEiMask()->getPluralLabelLstr();
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
				&& $this->getContextEiMask()->getEiEngine()->getEiCommandCollection()->hasGenericOverview());
	}
	
	public function getOverviewUrl(HttpContext $httpContext, bool $required = true) {
		if ($this->overviewUrlExt !== null) {
			return $httpContext->getRequest()->getContextPath()->toUrl()->ext($this->overviewUrlExt);
		} 
		
		$overviewUrlExt = $this->getContextEiMask()->getEiEngine()->getEiCommandCollection()
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
				$this->getDetailUrl($httpContext, $eiObject->toEntryNavPoint($this->getContextEiMask()->getEiEngine()->getEiType())),
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
		
		return $this->getContextEiMask()->createIdentityString($eiObject, $this->getN2nLocale());
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
				(!$this->detailDisabled && $this->getContextEiMask()->getEiEngine()
						->getEiCommandCollection()->hasGenericDetail($entryNavPoint));
	}
	
	public function getDetailUrl(HttpContext $httpContext, EntryNavPoint $entryNavPoint, bool $required = true) {
		if ($this->detailUrlExt !== null) {
			return $httpContext->getRequest()->getContextPath()->ext($this->detailUrlExt);
		}
		
		$detailUrlExt = $this->getContextEiMask()->getEiEngine()->getEiCommandCollection()
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
}

class CriteriaConstraintCollection implements \IteratorAggregate, \Countable {
	private $types = array();
	private $criteriaConstraints = array();
	
	public function add(int $type, CriteriaConstraint $criteriaConstraint) {
		$objHash = spl_object_hash($criteriaConstraint);
		$this->types[$objHash] = $type;
		$this->criteriaConstraints[$objHash] = $criteriaConstraint;
	}
	
	/**
	 * @param int $types
	 * @return CriteriaConstraint[]
	 */
	public function findAll(int $ignoredTypes) {
		$criteriaConstraints = array();
		foreach ($this->types as $objHash => $type) {
			if ($ignoredTypes == 0 || !($ignoredTypes & $type)) {
				$criteriaConstraints[] = $this->criteriaConstraints[$objHash];
			}
		}
		return $criteriaConstraints;
	}
	
	public function count() {
		return count($this->criteriaConstraints);
	}
	
	public function getIterator() {
		return new \ArrayIterator($this->toArray());
	}
	
	public function toArray() {
		return $this->criteriaConstraints;
	}
}
