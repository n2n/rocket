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
namespace rocket\op\ei\manage\frame;

use n2n\util\ex\IllegalStateException;
use rocket\op\ei\mask\EiMask;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\core\container\N2nContext;
use rocket\op\ei\manage\entry\EiEntry;
use rocket\op\ei\manage\security\EiExecution;
use n2n\web\http\HttpContext;
use n2n\util\uri\Url;
use n2n\util\type\ArgUtils;
use rocket\op\ei\EiCmdPath;
use rocket\op\ei\EiEngine;
use rocket\op\ei\manage\ManageState;
use rocket\op\ei\manage\EiObject;
use rocket\op\ei\manage\security\EiEntryAccess;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\component\command\GenericResult;
use rocket\ui\si\control\SiNavPoint;
use rocket\ui\si\meta\SiFrame;
use rocket\op\ei\manage\api\ApiController;
use rocket\op\ei\component\command\EiCmd;
use rocket\op\ei\manage\EiLaunch;

class EiFrame {
	/**
	 * @var Boundry
	 */
	private $boundry;
	/**
	 * @var Ability
	 */
	private $ability;
	private $baseUrl;
	
	private $eiExecution;
// 	private $eiObject;
// 	private $previewType;
	private $eiRelations = array();

// 	private $filterModel;
// 	private $sortModel;
	
// 	private $eiTypeConstraint;
	
// 	private $breadcrumbs = [];
	
	private $listeners = array();


	public function __construct(private EiEngine $contextEiEngine, private EiLaunch $eiLaunch,
			private ?EiForkLink $eiForkLink = null) {
		$this->boundry = new Boundry();
		$this->ability = new Ability();
	}

// 	/**
// 	 * @return \rocket\op\ei\EiType
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

	public function getEiLaunch(): EiLaunch {
		return $this->eiLaunch;
	}
	
	/**
// 	 * @throws \n2n\util\ex\IllegalStateException
// 	 * @return \n2n\persistence\orm\EntityManager
// 	 */
// 	public function getEntityManager(): EntityManager {
// 		return $this->eiLaunch->getEntityManager();
// 	}
	
	/**
	 * @return N2nContext
	 */
	public function getN2nContext() {
		return $this->eiLaunch->getN2nContext();
	}

	/**
	 * @return EiForkLink|null
	 */
	public function getEiForkLink() {
		return $this->eiForkLink;
	}
	
	/**
	 * @return boolean
	 */
	public function hasBaseUrl() {
		return $this->baseUrl !== null;
	}
	
	/**
	 * @param Url $url
	 */
	public function setBaseUrl(?Url $baseUrl) {
		$this->baseUrl = $baseUrl;
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
	
	/**
	 * @param EiExecution $eiExecution
	 */
	public function exec(EiCmd $eiCmd) {
		if ($this->eiExecution !== null) {
			throw new IllegalStateException('EiFrame already executed.');
		}
		
		$this->eiExecution = $this->eiLaunch->getEiPermissionManager()
				->createEiExecution($this->contextEiEngine->getEiMask(), $eiCmd);
		
		foreach ($this->listeners as $listener) {
			$listener->whenExecuted($this->eiExecution);
		}
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
	
	/**
	 * @return Ability
	 */
	public function getAbility() {
		return $this->ability;
	}
	
	/**
	 * @var \rocket\op\ei\manage\critmod\filter\FilterDefinition
	 */
	private $filterDefinition;
	/**
	 * @var \rocket\op\ei\manage\critmod\sort\SortDefinition
	 */
	private $sortDefinition;
	/**
	 * @var \rocket\op\ei\manage\critmod\quick\QuickSearchDefinition
	 */
	private $quickSearchDefinition;
	
	/**
	 * @return \rocket\op\ei\manage\critmod\filter\FilterDefinition
	 */
	public function getFilterDefinition() {
		if ($this->filterDefinition !== null) {
			return $this->filterDefinition;
		}
		
		return $this->filterDefinition = $this->contextEiEngine
				->createFramedFilterDefinition($this);
	}
	
//	/**
//	 * @return boolean
//	 */
//	public function hasFilterProps() {
//		return !$this->getFilterDefinition()->isEmpty();
//	}
	
	/**
	 * @return \rocket\op\ei\manage\critmod\sort\SortDefinition
	 */
	public function getSortDefinition() {
		if ($this->sortDefinition !== null) {
			return $this->sortDefinition;
		}
		
		return $this->sortDefinition = $this->contextEiEngine
				->createFramedSortDefinition($this);
	}
	
//	/**
//	 * @return boolean
//	 */
//	public function hasSortProps() {
//		return !$this->getSortDefinition()->isEmpty();
//	}
	
	/**
	 * @return \rocket\op\ei\manage\critmod\quick\QuickSearchDefinition
	 */
	public function getQuickSearchDefinition() {
		if ($this->quickSearchDefinition !== null) {
			return $this->quickSearchDefinition;
		}
		
		return $this->quickSearchDefinition = $this->contextEiEngine
				->createFramedQuickSearchDefinition($this);
	}
	
//	/**
//	 * @return boolean
//	 */
//	public function hasQuickSearchProps() {
//		return !$this->getQuickSearchDefinition()->isEmpty();
//	}
	

	/**
	 * @param \n2n\persistence\orm\EntityManager $em
	 * @param string $entityAlias
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function createCriteria(string $entityAlias, int $ignoreConstraintTypes = 0) {
		$em = $this->eiLaunch->getEntityManager();
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
		
		if (!($ignoreConstraintTypes & Boundry::TYPE_SECURITY) 
				&& null !== ($criteriaConstraint = $this->getEiExecution()->getCriteriaConstraint())) {
			$criteriaConstraint->applyToCriteria($criteria, $entityAliasCriteriaProperty);
		}
		
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
	 * @param EiEntry|null $copyFrom
	 * @param int $ignoreConstraintTypes
	 * @return EiEntry
	 */
	public function createEiEntry(EiObject $eiObject, ?EiEntry $copyFrom = null, int $ignoreConstraintTypes = 0): EiEntry {
		$eiEntry = $this->contextEiEngine->getEiMask()->determineEiMask($eiObject->getEiEntityObj()->getEiType())->getEiEngine()
				->createFramedEiEntry($this, $eiObject, $copyFrom, $this->boundry->filterEiEntryConstraints($ignoreConstraintTypes));
		$eiEntry->setEiEntryAccess($this->getEiExecution()->createEiEntryAccess($eiEntry));
		
		foreach ($this->listeners as $listener) {
			$listener->onNewEiEntry($eiEntry);
		}
		
		return $eiEntry;
	}
	
	
	
// 	/**
// 	 * @throws IllegalStateException
// 	 * @return EiEntryAccessFactory
// 	 */
// 	public function getEiEntryAccessFactory() {
// 		return $this->getEiExecution()->getEiEntryAccessFactory()->createEiEntryAccess($eiEntry);
// 	}
	
	/**
	 * @return bool
	 */
	public function hasEiEntryAccessFactory() {
		return $this->eiExecution !== null;
	}
	
	/**
	 * @param EiCmdPath $eiCmdPath
	 * @return bool
	 */
	public function isExecutableBy(EiCmdPath $eiCmdPath): bool {
		return $this->getEiEntryAccessFactory()->isExecutableBy($eiCmdPath);
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @return EiEntryAccess
	 */
	public function createEiEntryAccess(EiEntry $eiEntry): EiEntryAccess {
		return $this->getEiEntryAccessFactory()->createEiEntryAccess($eiEntry);
	}
	
	/**
	 * @return boolean
	 */
	public function isOverviewAvailable() {
		return $this->getContextEiEngine()->getEiMask()->getEiCmdCollection()->hasGenericOverview();
	}
	
	/**
	 * @param bool $required
	 * @return SiNavPoint|null
	 */
	public function getOverviewNavPoint(bool $required = true) {
		$result = $this->getContextEiEngine()->getEiMask()->getEiCmdCollection()
				->determineGenericOverview($required);
				
		return $this->compleNavPoint($result);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return boolean
	 */
	public function isDetailAvailable(EiObject $eiObject) {
		return $this->getContextEiEngine()->getEiMask()->getEiCmdCollection()->hasGenericDetail($eiObject);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $required
	 * @return SiNavPoint|null
	 */
	public function getDetailNavPoint(EiObject $eiObject, bool $required = true) {
		$result = $this->getContextEiEngine()->getEiMask()->getEiCmdCollection()
				->determineGenericDetail($eiObject, $required);
		
		return $this->compleNavPoint($result);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return boolean
	 */
	public function isEditAvailable(EiObject $eiObject) {
		return $this->getContextEiEngine()->getEiMask()->getEiCmdCollection()->hasGenericEdit($eiObject);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $required
	 * @return SiNavPoint|null
	 */
	public function getEditNavPoint(EiObject $eiObject, bool $required = true) {
		$result = $this->getContextEiEngine()->getEiMask()->getEiCmdCollection()
				->determineGenericEdit($eiObject, $required);
		
		return $this->compleNavPoint($result);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return boolean
	 */
	public function isAddAvailable(EiObject $eiObject) {
		return $this->getContextEiEngine()->getEiMask()->getEiCmdCollection()->hasGenericAdd();
	}
	
	/**
	 * @param bool $required
	 * @return SiNavPoint|null
	 */
	public function getAddNavPoint(bool $required = true) {
		$result = $this->getContextEiEngine()->getEiMask()->getEiCmdCollection()
				->determineGenericAdd($required);
				
		return $this->compleNavPoint($result);
	}
	
	/**
	 * @param GenericResult|null $result
	 * @return SiNavPoint|null
	 */
	private function compleNavPoint($result) {
		if ($result === null) {
			return null;
		}
		
		$navPoint = $result->getNavPoint();
		if ($navPoint->isUrlComplete()) {
			return $navPoint;
		}
		
		return $navPoint->complete($this->getBaseUrl()
				->ext(EiFrameController::createCmdUrlExt($result->getEiCmdPath())));
	}

	public function getApiUrl(?EiCmdPath $eiCmdPath) {
		if ($eiCmdPath === null) {
			$eiCmdPath = EiCmdPath::from($this->getEiExecution()->getEiCmd());
		}
		
		return $this->getBaseUrl()->ext([EiFrameController::API_PATH_PART, (string) $eiCmdPath]);
	}
	
	public function getCmdUrl(EiCmdPath $eiCmdPath) {
		return $this->getBaseUrl()->ext([EiFrameController::CMD_PATH_PART, (string) $eiCmdPath]);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param string $mode
	 * @param EiEntry|null $eiEntry
	 * @return \n2n\util\uri\Url
	 */
	public function getForkUrl(?EiCmdPath $eiCmdPath, EiPropPath $eiPropPath, string $mode, ?EiObject $eiObject = null) {
		if ($eiCmdPath === null) {
			$eiCmdPath = EiCmdPath::from($this->getEiExecution()->getEiCmd());
		}
		
		if ($eiObject === null) {
			return $this->getBaseUrl()->ext([EiFrameController::FORK_PATH, (string) $eiCmdPath, (string) $eiPropPath, $mode]);
		}
		
		if ($eiObject->isNew()) {
			return $this->getBaseUrl()->ext([EiFrameController::FORK_NEW_ENTRY_PATH, (string) $eiCmdPath, 
					$eiObject->getEiEntityObj()->getEiType()->getId(), (string) $eiPropPath, $mode]);
		}
		
		return $this->getBaseUrl()->ext([EiFrameController::FORK_ENTRY_PATH, (string) $eiCmdPath, 
				$eiObject->getEiEntityObj()->getPid(), (string) $eiPropPath, $mode]);
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
	
	/**
	 * @return \rocket\ui\si\meta\SiFrame
	 */
	function createSiFrame() {
		return (new SiFrame($this->getApiUrl(null)/*, $this->contextEiEngine->getEiMask()->getEiType()->createSiTypeContext()*/))
				->setSortable($this->ability->getSortAbility() !== null)
				->setTreeMode(null !== $this->contextEiEngine->getEiMask()->getEiType()->getNestedSetStrategy());
	}
}

class EiForkLink {
	/**
	 * View only
	 * @var string
	 */
	const MODE_DISCOVER = 'discover';
	/**
	 * E. g. OneToMany-, OneToOne- or ManyToManySelection
	 * @var string
	 */
	const MODE_SELECT = 'select';
	
	private $parent;
	private $mode;
	private $parentEiObject;
	
	function __construct(EiFrame $parent, string $mode, ?EiObject $parentEiObject = null) {
		$this->parent = $parent;
		ArgUtils::valEnum($mode, self::getModes());
		$this->mode = $mode;
		$this->parentEiObject = $parentEiObject;
		
		if ($parentEiObject !== null) {
			ArgUtils::assertTrue($parentEiObject->getEiEntityObj()->getEiType()
							->isA($parent->getContextEiEngine()->getEiMask()->getEiType()), 
					'EiForkLink EiObject is not compatible with EiFrame');	
		}
	}
	
	/**
	 * @return \rocket\op\ei\manage\frame\EiFrame
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
	 * @return \rocket\op\ei\manage\EiObject|null
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