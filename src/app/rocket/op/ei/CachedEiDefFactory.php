<?php
///*
// * Copyright (c) 2012-2016, Hofmänner New Media.
// * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
// *
// * This file is part of the n2n module ROCKET.
// *
// * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
// * GNU Lesser General Public License as published by the Free Software Foundation, either
// * version 2.1 of the License, or (at your option) any later version.
// *
// * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
// * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
// *
// * The following people participated in this project:
// *
// * Andreas von Burg...........:	Architect, Lead Developer, Concept
// * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
// * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
// */
//namespace rocket\op\ei;
//
//use rocket\op\ei\manage\critmod\filter\FilterDefinition;
//use rocket\op\ei\mask\EiMask;
//use rocket\op\ei\manage\critmod\sort\SortDefinition;
//use rocket\op\ei\manage\security\filter\SecurityFilterDefinition;
//use rocket\op\ei\manage\draft\DraftDefinition;
//use rocket\op\ei\manage\idname\IdNameDefinition;
//use rocket\op\ei\manage\gui\EiGuiDefinition;
//use rocket\op\ei\component\EiComponentCollection;
//use rocket\op\ei\manage\critmod\quick\QuickSearchDefinition;
//use rocket\op\ei\component\EiComponentCollectionListener;
//use n2n\context\attribute\ThreadScoped;
//use n2n\core\container\N2nContext;
//use rocket\op\ei\manage\gui\CachedEiGuiDeclarationFactory;
//use rocket\op\ei\manage\gui\EiGuiDeclarationFactory;
//
//#[ThreadScoped]
//class CachedEiDefFactory /*implements EiComponentCollectionListener*/ {
//
//	private CachedEiGuiDeclarationFactory $eiModelCache;
//
//	function __construct(private N2nContext $n2nContext) {
//		$this->eiModelCache = new CachedEiGuiDeclarationFactory(new EiGuiDeclarationFactory($this->n2nContext, $this));
//	}
//
//	/**
//	 * @var QuickSearchDefinition[]
//	 */
//	private $quickSearchDefinitions = array();
//
//	/**
//	 * @var FilterDefinition[]
//	 */
//	private $filterDefinitions = array();
//
//	/**
//	 * @var SortDefinition[]
//	 */
//	private $sortDefinitions = array();
//
//	/**
//	 * @var SecurityFilterDefinition[]
//	 */
//	private $securityFilterDefinitions = array();
//
//	/**
//	 * @var EiGuiDefinition[]
//	 */
//	private $guiDefinitions = array();
//
//	/**
//	 * @var IdNameDefinition[]
//	 */
//	private $idNameDefinitions = array();
//
////	/**
////	 * @param EiMask $eiMask
////	 */
////	private function registerListeners($eiMask) {
////		$eiMask->getEiPropCollection()->registerListener($this);
////		$eiMask->getEiCmdCollection()->registerListener($this);
////		$eiMask->getEiModCollection()->registerListener($this);
////	}
////
////	/**
////	 * @param EiMask $eiMask
////	 */
////	private function unregisterListeners($eiMask) {
////		$eiMask->getEiPropCollection()->unregisterListener($this);
////		$eiMask->getEiCmdCollection()->unregisterListener($this);
////		$eiMask->getEiModCollection()->unregisterListener($this);
////	}
//
////	function eiComponentCollectionChanged(EiComponentCollection $collection) {
////		$eiMask = $collection->getEiMask();
////		$this->unregisterListeners($eiMask);
////
////		$eiTypePathStr = $eiMask->getEiTypePath();
////		unset($this->filterDefinitions[$eiTypePathStr]);
////		unset($this->sortDefinitions[$eiTypePathStr]);
////		unset($this->securityFilterDefinitions[$eiTypePathStr]);
////		unset($this->guiDefinitions[$eiTypePathStr]);
////		unset($this->idNameDefinitions[$eiTypePathStr]);
////	}
//
////	/**
////	 * @param EiMask $eiMask
////	 * @return QuickSearchDefinition
////	 */
////	public function getQuickSearchDefinition(EiMask $eiMask) {
////		$eiTypePathStr = (string) $eiMask->getEiTypePath();
////
////		if (!isset($this->quickSearchDefinitions[$eiTypePathStr])) {
////			$this->quickSearchDefinitions[$eiTypePathStr] = $eiMask->getEiEngine()
////					->createQuickSearchDefinition($this->n2nContext);
////
//////			$this->registerListeners($eiMask);
////		}
////
////		return $this->quickSearchDefinitions[$eiTypePathStr];
////	}
////
////	/**
////	 * @param EiMask $eiMask
////	 * @return FilterDefinition
////	 */
////	public function getFilterDefinition(EiMask $eiMask) {
////		$eiTypePathStr = (string) $eiMask->getEiTypePath();
////
////		if (!isset($this->filterDefinitions[$eiTypePathStr])) {
////			$this->filterDefinitions[$eiTypePathStr] = $eiMask->getEiEngine()
////					->createFilterDefinition($this->n2nContext);
////
//////			$this->registerListeners($eiMask);
////		}
////
////		return $this->filterDefinitions[$eiTypePathStr];
////	}
////
////
////
////	/**
////	 * @param EiMask $eiMask
////	 * @return SortDefinition
////	 */
////	public function getSortDefinition(EiMask $eiMask) {
////		$eiTypePathStr = (string) $eiMask->getEiTypePath();
////
////		if (!isset($this->sortDefinitions[$eiTypePathStr])) {
////			$this->sortDefinitions[$eiTypePathStr] = $eiMask->getEiEngine()
////					->createSortDefinition($this->n2nContext);
////
//////			$this->registerListeners($eiMask);
////		}
////
////		return $this->sortDefinitions[$eiTypePathStr];
////	}
////
////	/**
////	 * @param EiMask $eiMask
////	 * @return SecurityFilterDefinition
////	 */
////	public function getSecurityFilterDefinition(EiMask $eiMask) {
////		$eiTypePathStr = (string) $eiMask->getEiTypePath();
////
////		if (!isset($this->securityFilterDefinitions[$eiTypePathStr])) {
////			$this->securityFilterDefinitions[$eiTypePathStr] = $eiMask->getEiEngine()
////					->createSecurityFilterDefinition($this->n2nContext);
////
//////			$this->registerListeners($eiMask);
////		}
////
////		return $this->securityFilterDefinitions[$eiTypePathStr];
////	}
//
//
//// 	/**
//// 	 * @var PrivilegeDefinition[]
//// 	 */
//// 	private $privilegeDefinitions = array();
//
//// 	/**
//// 	 * @param EiMask $eiMask
//// 	 * @return PrivilegeDefinition
//// 	 */
//// 	public function getPrivilegeDefinition(EiMask $eiMask) {
//// 		$eiTypePathStr = (string) $eiMask->getEiTypePath();
//
//// 		if (!isset($this->privilegeDefinitions[$eiTypePathStr])) {
//// 			$this->privilegeDefinitions[$eiTypePathStr] = $eiMask->getEiEngine()
//// 					->createPrivilegeDefinition($this->manageState->getN2nContext());
//// 		}
//
//// 		return $this->privilegeDefinitions[$eiTypePathStr];
//// 	}
//
//
////	/**
////	 * @param EiMask $eiMask
////	 * @return EiGuiDefinition
////	 */
////	public function getEiGuiDefinition(EiMask $eiMask) {
////		$eiTypePathStr = (string) $eiMask->getEiTypePath();
////
////		if (!isset($this->guiDefinitions[$eiTypePathStr])) {
////			$this->guiDefinitions[$eiTypePathStr] = $eiMask->getEiEngine()
////					->createEiGuiDefinition($this->manageState->getN2nContext());
////
//////			$this->registerListeners($eiMask);
////		}
////
////		return $this->guiDefinitions[$eiTypePathStr];
////	}
////
////
////	/**
////	 * @param EiMask $eiMask
////	 * @return IdNameDefinition
////	 */
////	public function getIdNameDefinition(EiMask $eiMask) {
////		$eiTypePathStr = (string) $eiMask->getEiTypePath();
////
////		if (!isset($this->idNameDefinitions[$eiTypePathStr])) {
////			$this->idNameDefinitions[$eiTypePathStr] = $eiMask->getEiEngine()
////					->createIdNameDefinition($this->manageState->getN2nContext());
////
//////			$this->registerListeners($eiMask);
////		}
////
////		return $this->idNameDefinitions[$eiTypePathStr];
////	}
//
////	/**
////	 * @var DraftDefinition[]
////	 */
////	private $draftDefinitions = array();
////
////	/**
////	 * @param EiMask $eiMask
////	 * @return DraftDefinition
////	 */
////	public function getDraftDefinition(EiMask $eiMask) {
////		$eiTypePathStr = (string) $eiMask->getEiTypePath();
////
////		if (!isset($this->draftDefinitions[$eiTypePathStr])) {
////			$this->draftDefinitions[$eiTypePathStr] = $eiMask->getEiEngine()
////					->createDraftDefinition($this->manageState->getN2nContext());
////		}
////
////		return $this->draftDefinitions[$eiTypePathStr];
////	}
//
//
//}