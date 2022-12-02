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
namespace rocket\ei;

use n2n\core\container\N2nContext;
use rocket\ei\component\GuiFactory;
use rocket\ei\component\EiEntryFactory;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\manage\critmod\sort\SortDefinition;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\mask\EiMask;


use rocket\ei\manage\generic\ScalarEiDefinition;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\generic\GenericEiDefinition;
use rocket\ei\manage\critmod\quick\QuickSearchDefinition;
use rocket\ei\manage\ManageState;
use rocket\ei\component\EiFrameFactory;
use rocket\ei\manage\gui\EiEntryGui;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\frame\EiForkLink;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\idname\IdNameDefinition;
use rocket\ei\manage\gui\control\GuiControl;
use rocket\ei\manage\EiLaunch;

class EiEngine {


	private ?GenericEiDefinition $genericEiDefinition = null;
	private ?ScalarEiDefinition $scalarEiDefinition = null;
	private ?IdNameDefinition $idNameDefinition = null;

	/**
	 * @param EiMask $eiMask
	 * @param N2nContext $n2nContext
	 */
	function __construct(private EiMask $eiMask) {
	}
	
	/**
	 * @return EiMask
	 */
	function getEiMask() {
		return $this->eiMask;
	}
	
	/**
	 * @return EiMask
	 */
	private function getSupremeEiMask() {
		$eiType = $this->eiMask->getEiType();
		if (!$eiType->hasSuperEiType()) {
			return $this->eiMask;
		}
		
		return $eiType->getSupremeEiType()->getEiMask();
	}
	
	function getSupremeEiEngine() {
		return $this->getSupremeEiMask()->getEiEngine();
	}


	function getGenericEiDefinition(): GenericEiDefinition {
		if ($this->genericEiDefinition !== null) {
			return $this->genericEiDefinition;
		}

		return $this->genericEiDefinition = $this->eiMask->getEiPropCollection()->createGenericEiDefinition();
	}

	function getScalarEiDefinition(): ScalarEiDefinition {
		if ($this->scalarEiDefinition !== null) {
			return $this->scalarEiDefinition;
		}

		return $this->scalarEiDefinition = $this->eiMask->getEiPropCollection()->createScalarEiDefinition();
	}


	function getIdNameDefinition(): IdNameDefinition {
		if ($this->idNameDefinition !== null) {
			return $this->idNameDefinition;
		}

		return $this->idNameDefinition = $this->eiMask->getEiPropCollection()->createIdNameDefinition();
	}

	private $eiFrameFactory;
	
	/**
	 * @return \rocket\ei\component\EiFrameFactory
	 */
	private function getEiFrameFactory() {
		if ($this->eiFrameFactory === null) {
			$this->eiFrameFactory = new EiFrameFactory($this);
		}
		
		return $this->eiFrameFactory;
	}
//
//	/**
//	 * @param EiLaunch $eiLaunch
//	 * @return EiFrame
//	 */
//	function createEiFrame(EiLaunch $eiLaunch) {
//		return $this->getEiFrameFactory()->create($eiLaunch);
//	}
	
	/**
	 * @param ManageState $manageState
	 * @return EiFrame
	 */
	function createRootEiFrame(EiLaunch $eiLaunch): EiFrame {
		$eiFrame = $this->getEiFrameFactory()->create($eiLaunch);
		$this->eiMask->getEiModCollection()->setupEiFrame($eiFrame);
		return $eiFrame;
	}


	function createForkEiFrame(EiForkLink $eiForkLink): EiFrame {
		$eiFrame = $this->getEiFrameFactory()->create($eiForkLink->getParent()->getEiLaunch(), $eiForkLink);
		$this->eiMask->getEiModCollection()->setupEiFrame($eiFrame);
		return $eiFrame;
	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @param EiForkLink $eiForkLink
	 * @return EiFrame
	 */
	function forkEiFrame(EiPropPath $eiPropPath, EiForkLink $eiForkLink): EiFrame {
		return $this->eiMask->getEiPropCollection()->createForkedEiFrame($eiPropPath, $eiForkLink);
	}

	private ?GuiDefinition $guiDefinition = null;

	function getGuiDefinition(): GuiDefinition {
		if ($this->guiDefinition !== null) {
			return $this->guiDefinition;
		}

		$this->guiDefinition = $this->eiMask->getEiPropCollection()->createGuiDefinition();
		$this->eiMask->getEiCmdCollection()->supplyGuiDefinition($this->guiDefinition);
		return $this->guiDefinition;
	}


//	private $critmodFactory;
//
//	private function getCritmodFactory() {
//		if ($this->critmodFactory === null) {
//			$this->critmodFactory = new CritmodFactory($this->eiMask->getEiPropCollection(),
//					$this->eiMask->getEiModCollection());
//		}
//
//		return $this->critmodFactory;
//	}
	
	function createFramedFilterDefinition(EiFrame $eiFrame): FilterDefinition {
		return $this->eiMask->getEiPropCollection()->createFramedFilterDefinition($eiFrame);
	}
	
//	function createFilterDefinition(N2nContext $n2nContext): FilterDefinition {
//		return $this->getCritmodFactory()->createFilterDefinition($n2nContext);
//	}
	
	function createFramedSortDefinition(EiFrame $eiFrame): SortDefinition {
		return $this->eiMask->getEiPropCollection()->createFramedSortDefinition($eiFrame);
	}
	
//	/**
//	 * {@inheritDoc}
//	 * @see \rocket\ei\mask\EiMask::createSortDefinition($n2nContext)
//	 */
//	function createSortDefinition(N2nContext $n2nContext): SortDefinition {
//		return $this->getCritmodFactory()->createSortDefinition($n2nContext);
//	}
	
	function createFramedQuickSearchDefinition(EiFrame $eiFrame): QuickSearchDefinition {
		return $this->eiMask->getEiPropCollection()->createFramedQuickSearchDefinition($eiFrame);
	}
	
//	private $securityFactory;
//
//	private function getSecurityFactory() {
//		if ($this->securityFactory === null) {
//			$this->securityFactory = new SecurityFactory($this->eiMask->getEiPropCollection(),
//					$this->eiMask->getEiCmdCollection(), $this->eiMask->getEiModCollection());
//		}
//
//		return $this->securityFactory;
//	}
//
//	function createSecurityFilterDefinition(N2nContext $n2nContext) {
//		return $this->getSecurityFactory()->createSecurityFilterDefinition($n2nContext);
//	}
//
//	function createPrivilegeDefinition(N2nContext $n2nContext) {
//		$securityFactory = new SecurityFactory($this->eiMask->getEiPropCollection(),
//				$this->eiMask->getEiCmdCollection(), $this->eiMask->getEiModCollection());
//		return $securityFactory->createPrivilegedDefinition($n2nContext);
//	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiObject $eiObject
	 * @param EiEntry $copyFrom
	 * @return EiEntry
	 */
	function createFramedEiEntry(EiFrame $eiFrame, EiObject $eiObject, ?EiEntry $copyFrom, array $eiEntryConstraints) {
		$mappingFactory = new EiEntryFactory($this->eiMask, $this->eiMask->getEiPropCollection(), 
				$this->eiMask->getEiModCollection());
		return $mappingFactory->createEiEntry($eiFrame, $eiObject, $copyFrom, $eiEntryConstraints);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @param EiPropPath $forkEiPropPath
	 * @param object $object
	 * @param EiEntry $copyFrom
	 * @return \rocket\ei\manage\entry\EiFieldMap
	 */
	function createFramedEiFieldMap(EiFrame $eiFrame, EiEntry $eiEntry, EiPropPath $forkEiPropPath, object $object, 
			?EiEntry $copyFrom) {
		$mappingFactory = new EiEntryFactory($this->eiMask, $this->eiMask->getEiPropCollection(),
				$this->eiMask->getEiModCollection());
		
		return $mappingFactory->createEiFieldMap($eiFrame, $eiEntry, $forkEiPropPath, $object, $copyFrom);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $from
	 * @param EiEntry $to
	 * @param array $eiPropPaths
	 */
	function copyValues(EiFrame $eiFrame, EiEntry $from, EiEntry $to, array $eiPropPaths = null) {
		ArgUtils::valArray($eiPropPaths, EiPropPath::class, true, 'eiPropPaths');
		$mappingFactory = new EiEntryFactory($this->eiMask, $this->eiMask->getEiPropCollection(), 
				$this->eiMask->getEiModCollection());
		$mappingFactory->copyValues($eiFrame, $from, $to, $eiPropPaths);
	}
	
// 	function createFramedEiGuiFrame(EiFrame $eiFrame, int $viewMode) {
// 		$guiFactory = new GuiFactory($this->eiMask);
// 		return $guiFactory->createEiGuiFrame($eiFrame, $viewMode);
// 	}
	
// 	function createEiGuiFrame(int $viewMode, DisplayStructure $displayStructure) {
// 		$eiMask = $this->eiMask;
// 		if ($this->eiType === null) {
// 			$eiMask = $this->eiType->getEiTypeExtensionCollection()->getOrCreateDefault();
// 		}
		
// 		$guiFactory = new GuiFactory($this->eiMask);
// 		return $guiFactory->createEiEntryGui($eiMask, $eiuEntry, $viewMode, $eiPropPaths);
// 	}
	
//	function getDraftDefinition(): DraftDefinition {
//		if ($this->draftDefinition !== null) {
//			return $this->draftDefinition;
//		}
//
//		$eiThing = $this->eiMask ?? $this->eiType;
//		do {
//			$id = $eiThing->getId();
//		} while (($id === null || $eiThing->getEiEngine()->getEiMask()->getEiPropCollection()->isEmpty(true))
//				&& null !== ($eiThing = $eiThing->getMaskedEiEngineModel()));
//		return $this->draftDefinition = (new DraftDefinitionFactory($this->eiMask->getEntityModel(),
//						$this->eiPropCollection, $this->eiModificatorCollection))
//				->create(DraftMetaInfo::buildTableName($eiThing));
//	}

	
	/**
	 * @param EiEntryGui $eiEntryGui
	 * @param HtmlView $view
	 * @return GuiControl[]
	 */
	function createEiEntryGuiControls(EiEntryGui $eiEntryGui, HtmlView $view) {
		return (new GuiFactory($this->eiMask))->createEntryGuiControls($eiEntryGui, $view);
	}
	
}