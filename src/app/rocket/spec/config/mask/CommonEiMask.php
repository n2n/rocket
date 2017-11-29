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
namespace rocket\spec\config\mask;

use rocket\spec\ei\EiThingPath;
use rocket\spec\ei\manage\EiFrame;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\spec\ei\EiType;
use rocket\util\Identifiable;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\EiDef;
use rocket\spec\ei\manage\preview\model\PreviewModel;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\config\mask\model\DisplayScheme;
use rocket\spec\ei\manage\critmod\CriteriaConstraint;
use rocket\spec\ei\EiEngine;
use rocket\spec\ei\EiThing;
use n2n\persistence\orm\model\EntityModel;
use n2n\l10n\Lstr;
use rocket\spec\ei\manage\preview\controller\PreviewController;
use n2n\util\config\InvalidConfigurationException;
use rocket\spec\ei\manage\preview\model\UnavailablePreviewException;
use rocket\spec\ei\manage\control\UnavailableControlException;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\gui\EiGui;
use rocket\spec\ei\manage\gui\EiEntryGui;
use rocket\spec\ei\manage\control\IconType;

class CommonEiMask implements EiMask, Identifiable {
	private $id;
	private $eiType;
	private $moduleNamespace;
	private $subEiMaskIds;
	
	private $eiDef;
	private $eiEngine;
	private $guiOrder;
	
	private $mappingFactory;
	private $guiFactory;
	private $draftDefinitionFactory;
	private $critmodFactory;
	
	private $guiDefinition;
	private $draftDefinition;
	
	public function __construct(EiType $eiType, string $moduleNamespace, DisplayScheme $guiOrder) {
		$this->eiType = $eiType;
		$this->moduleNamespace = $moduleNamespace;
		
		$this->eiDef = new EiDef();
		$this->eiEngine = new EiEngine($this->eiType, $this);
		$this->guiOrder = $guiOrder;
		
		$eiPropCollection = $this->eiEngine->getEiPropCollection();
		$eiPropCollection->setInheritedCollection($this->eiType->getEiEngine()->getEiPropCollection());
		
		$eiCommandCollection = $this->eiEngine->getEiCommandCollection();
		$eiCommandCollection->setInheritedCollection($this->eiType->getEiEngine()->getEiCommandCollection());
		
		$eiModificatorCollection = $this->eiEngine->getEiModificatorCollection();
		$eiModificatorCollection->setInheritedCollection($this->eiType->getEiEngine()->getEiModificatorCollection());
	}

	/* (non-PHPdoc)
	 * @see \rocket\util\Identifiable::getId()
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @param string $id
	 */
	public function setId(string $id = null) {
		$this->id = $id;
	}

	public function getEiThingPath(): EiThingPath {
		$ids = array();
		$curEiThing = $this;
		do {
			if (null !== ($id = $curEiThing->getId())) {
				$ids[] = $id;
			}
		} while (null !== ($curEiThing = $curEiThing->getMaskedEiThing()));

		return new EiThingPath($ids);
	}
	
	public function getModuleNamespace(): string {
		return $this->moduleNamespace;
	}
	
	public function setModuleNamespace(string $moduleNamespace) {
		$this->moduleNamespace = $moduleNamespace;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\mask\EiMask::get()
	 */
	public function getEiType(): EiType {
		return $this->eiType;
	}
	
	public function getMaskedEiThing(): EiThing {
		return $this->eiType;
	}
	
	public function getEiDef() {
		return $this->eiDef;
	}
	
	public function getEiEngine(): EiEngine {
		return $this->eiEngine;
	}
	
	public function getEntityModel(): EntityModel {
		return $this->eiType->getEntityModel();
	}
	
	public function setDisplayScheme(DisplayScheme $guiOrder) {
		$this->guiOrder = $guiOrder;
	}
	
	public function getDisplayScheme(): DisplayScheme {
		return $this->guiOrder;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\EiThing::getLabelLstr()
	 */
	public function getLabelLstr(): Lstr {
		if (null !== ($label = $this->eiDef->getLabel())) {
			return new Lstr($label, $this->moduleNamespace);
		}
		
		return new Lstr((string) $this->eiType->getDefaultEiDef()->getLabel(), $this->moduleNamespace);
	}
	
	public function getPluralLabelLstr(): Lstr {
		if (null !== ($pluralLabel = $this->eiDef->getPluralLabel())) {
			return new Lstr($pluralLabel, $this->moduleNamespace);
		}
		
		return new Lstr((string) $this->eiType->getDefaultEiDef()->getPluralLabel(), $this->moduleNamespace);
	}
	
	public function getIconType(): string {
		if (null !== ($iconType = $this->eiDef->getIconType())) {
			return $iconType;
		}
		
		if (null !== ($iconType = $this->eiType->getDefaultEiDef()->getIconType())) {
			return $iconType;
		}
		
		return IconType::ICON_FILE_TEXT;
	}
	
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\mask\EiMask::isDraftDisabled()
	 */
	public function isDraftingEnabled(): bool {
		if (null !== ($draftingAllowed = $this->eiDef->isDraftingAllowed())) {
			if (!$draftingAllowed) return false;
		} else if (null !== ($draftingAllowed = $this->eiType->getDefaultEiDef()->isDraftingAllowed())) {
			if (!$draftingAllowed) return false;
		}
		
		return !$this->eiEngine->getDraftDefinition()->isEmpty();
	}
	
// 	private function createEiEntryGui(EiuEntry $eiuEntry, $viewMode): EiEntryGui {
// 		$guiIdPaths = $this->getDisplayStructureViewMode($viewMode)->getAllGuiIdPaths();
	
// 		return $this->eiEngine->createEiEntryGui($eiuEntry, $viewMode, $guiIdPaths);
// 	}
				
// 	/* (non-PHPdoc)
// 	 * @see \rocket\spec\ei\mask\EiMask::getCommands()
// 	 */
// 	public function getCommands() {
// 		return $this->eiEngine->getEiCommandCollection()->toArray();
// 	}
	
	
	private function createDefaultIdentityString(EiObject $eiObject, N2nLocale $n2nLocale) {
		$id = null;
		$name = null;
		
		foreach ($this->eiEngine->getGuiDefinition()->getSummarizableGuiProps() as $placeholder => $guiProp) {
			if ($placeholder == $this->eiEngine->getEiType()->getEntityModel()->getIdDef()->getPropertyName()) {
				$id = $guiProp->buildIdentityString($eiObject, $n2nLocale);
			} else {
				$name = $guiProp->buildIdentityString($eiObject, $n2nLocale);
			}
			
			if ($name !== null) break;
		}
		
		if ($id === null) {
			$id = $eiObject->getEiEntityObj()->hasId() ? 
					$this->eiType->idToIdRep($eiObject->getEiEntityObj()->getId()) : 'new';
		}
		
		if ($name === null) {
			$name = $this->getLabelLstr()->t($n2nLocale);
		}
		
		return $name . ' #' . $id;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\mask\EiMask::createIdentityString()
	 */
	public function createIdentityString(EiObject $eiObject, N2nLocale $n2nLocale): string {
		$identityStringPattern = $this->eiDef->getIdentityStringPattern();
		
		if ($identityStringPattern === null) {
			$identityStringPattern = $this->eiType->getDefaultEiDef()->getIdentityStringPattern();
		}
		
		if ($identityStringPattern === null) {
			return $this->createDefaultIdentityString($eiObject, $n2nLocale);
		}
		
		return $this->eiEngine->getGuiDefinition()
				->createIdentityString($identityStringPattern, $eiObject, $n2nLocale);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\mask\EiMask::sortOverallControls()
	 */
	public function sortOverallControls(array $controls, EiGui $eiGui, HtmlView $view): array {
// 		$eiu = new Eiu($eiGui);
// 		$eiPermissionManager = $eiu->frame()->getEiFrame()->getManageState()->getEiPermissionManager();
		
// 		$controls = array();
		
// 		foreach ($this->eiEngine->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// 			if (!($eiCommand instanceof OverallControlComponent)
// 					|| !$eiPermissionManager->isEiCommandAccessible($eiCommand)) continue;
				
// 			$controls = $eiCommand->createOverallControls($eiu, $view);
// 			ArgUtils::valArrayReturn($controls, $eiCommand, 'createOverallControls', Control::class);
// 			foreach ($controls as $controlId => $control) {
// 				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
// 			}
// 		}
		
		if (null !== ($overallControlOrder = $this->guiOrder->getOverallControlOrder())) {
			return $overallControlOrder->sort($controls);
		}
	
		return $controls;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\mask\EiMask::createEntryControls()
	 */
	public function sortEntryControls(array $controls, EiEntryGui $eiEntryGui, HtmlView $view): array {
// 		$eiu = new Eiu($eiEntryGui);
		
// 		$controls = array();
// 		foreach ($this->eiEngine->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// 			if (!($eiCommand instanceof EntryControlComponent)
// 					|| !$eiEntryGui->getEiEntry()->isExecutableBy(EiCommandPath::from($eiCommand))) {
// 				continue;
// 			}
			
// 			$entryControls = $eiCommand->createEntryControls($eiu, $view);
// 			ArgUtils::valArrayReturn($entryControls, $eiCommand, 'createEntryControls', Control::class);
// 			foreach ($entryControls as $controlId => $control) {
// 				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
// 			}
// 		}
	
// 		if (null !== ($entryControlOrder = $this->guiOrder->getEntryControlOrder())) {
// 			return $entryControlOrder->sort($controls);
// 		}		
		
		return $controls;
	}
	
// 	public function createPartialControls(EiFrame $eiFrame, HtmlView $view): array {
// 		$controls = array();
// 		foreach ($this->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// 			if (!($eiCommand instanceof PartialControlComponent)
// 					|| !$eiFrame->getManageState()->getEiPermissionManager()->isEiCommandAccessible($eiCommand)) continue;
				
// 			$executionPath = EiCommandPath::from($eiCommand);
// 			$partialControls = $eiCommand->createPartialControls($eiFrame, $view);
// 			ArgUtils::valArrayReturn($partialControls, $eiCommand, 'createPartialControls', PartialControl::class);
// 			foreach ($partialControls as $controlId => $control) {
// 				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
				
// 				if (!$control->hasEiCommandPath()) {
// 					$control->setExecutionPath($executionPath->ext($controlId));
// 				}
// 			}
// 		}
		
// 		if (null !== ($overallControlOrder = $this->guiOrder->getOverallControlOrder())) {
// 			return $overallControlOrder->sortControls($controls);
// 		}
	
// 		return $controls;
// 	}
	
	public function createEiGui(EiFrame $eiFrame, int $allowedViewMods): EiGui {
		if (($allowedViewMods & DisplayDefinition::COMPACT_VIEW_MODES) 
				&& ($allowedViewMods & DisplayDefinition::BULKY_VIEW_MODES)) {
			throw new \InvalidArgumentException('Bulky and compact view mods can not be mixed.');			
		}
		
		if (!(($allowedViewMods & DisplayDefinition::COMPACT_VIEW_MODES)
				|| ($allowedViewMods & DisplayDefinition::BULKY_VIEW_MODES))) {
			throw new \InvalidArgumentException('View mode no recognized.');
		}
		
		return new EiGui($eiFrame, $this->eiEngine->getGuiDefinition(), 
				(bool) ($allowedViewMods & DisplayDefinition::BULKY_VIEW_MODES), 
				new CommonEiGuiViewFactory($this->guiOrder));
	}

// 	public function createListEiEntryGui(EiuEntry $eiuEntry, bool $makeEditable): EiEntryGui {
// 		$viewMode = null;
// 		if (!$makeEditable) {
// 			$viewMode = DisplayDefinition::VIEW_MODE_LIST_READ;
// 		} else if ($eiuEntry->isNew()) {
// 			$viewMode = DisplayDefinition::VIEW_MODE_LIST_ADD;
// 		} else {
// 			$viewMode = DisplayDefinition::VIEW_MODE_LIST_EDIT;
// 		}
		
// 		return $this->createEiEntryGui($eiuEntry, $viewMode);
// 	}
	
// 	public function createListView(EiuFrame $eiuFrame, array $eiuEntryGuis): HtmlView {
// 		ArgUtils::valArray($eiuEntryGuis, EiuEntryGui::class);
		
// 		$viewMode = null;
// 		if (empty($eiuEntryGuis)) {
// 			$viewMode = DisplayDefinition::VIEW_MODE_LIST_READ;
// 		} else {
// 			$viewMode = current($eiuEntryGuis)->getViewMode();
// 		}
// 		$displayStructure = $this->getDisplayStructureViewMode($viewMode);
	
// 		return $eiuFrame->getN2nContext()->lookup(ViewFactory::class)->create(
// 				'rocket\spec\config\mask\view\entryList.html', array('entryListViewModel' => new EntryListViewModel(
// 						$eiuFrame, $eiuEntryGuis, $this->eiEngine->getGuiDefinition(), $displayStructure)));
// 	}

// 	public function createTreeEiEntryGui(EiuEntry $eiuEntry, bool $makeEditable): EiEntryGui {
// 		$viewMode = null;
// 		if (!$makeEditable) {
// 			$viewMode = DisplayDefinition::VIEW_MODE_TREE_READ;
// 		} else if ($eiuEntry->isNew()) {
// 			$viewMode = DisplayDefinition::VIEW_MODE_TREE_ADD;
// 		} else {
// 			$viewMode = DisplayDefinition::VIEW_MODE_TREE_EDIT;
// 		}
				
// 		$eiEntryGui = $this->createEiEntryGui($eiFrame, $eiEntry, $viewMode, $makeEditable);
		
// 		return new CommonEntryGuiModel($this, $eiEntryGui, $eiEntry);
// 	}
	
// 	public function createTreeView(EiuFrame $eiuFrame, EiuEntryGuiTree $entryGuiTree): HtmlView {
// 		$displayStructure = $this->getDisplayStructureViewMode(DisplayDefinition::VIEW_MODE_TREE_READ);
	
// 		return $eiuFrame->getN2nContext()->lookup(ViewFactory::class)->create(
// 				'rocket\spec\config\mask\view\entryList.html', array(
// 						'entryListViewModel' => new EntryListViewModel($eiuFrame, $entryGuiTree->getEntryGuis(), 
// 								$this->eiEngine->getGuiDefinition(), $displayStructure),
// 						'entryGuiTree' => $entryGuiTree));
// 	}
	
// 	public function createBulkyEiEntryGui(EiuEntry $eiuEntry, bool $makeEditable): EiEntryGui {
// 		$viewMode = null;
// 		if (!$makeEditable) {
// 			$viewMode = DisplayDefinition::VIEW_MODE_BULKY_READ;
// 		} else if ($eiuEntry->isNew()) {
// 			$viewMode = DisplayDefinition::VIEW_MODE_BULKY_ADD;
// 		} else {
// 			$viewMode = DisplayDefinition::VIEW_MODE_BULKY_EDIT;
// 		}
		
// 		return $this->createEiEntryGui($eiuEntry, $viewMode);
// 	}
	
// 	public function createBulkyView(EiuEntryGui $eiuEntryGui): HtmlView {
// 		$viewMode = $eiuEntryGui->getViewMode();
		
// 		switch ($viewMode) {
// 			case DisplayDefinition::VIEW_MODE_BULKY_READ:
// 				$viewName = 'rocket\spec\config\mask\view\entryDetail.html';
// 				break;
// 			case DisplayDefinition::VIEW_MODE_BULKY_ADD:
// 			case DisplayDefinition::VIEW_MODE_BULKY_EDIT:
// 				$viewName = 'rocket\spec\config\mask\view\entryEdit.html';
// 				break;
// 			default:
// 				throw new \InvalidArgumentException('No bulky viewMode.');
// 		}
		
// 		$displayStructure = $this->getDisplayStructureViewMode($viewMode);
// 		return $eiuEntryGui->getEiuEntry()->getEiFrame()->getN2nContext()->lookup(ViewFactory::class)
// 				->create($viewName, array('displayStructure' => $displayStructure, 'eiu' => new Eiu($eiuEntryGui)));
// 	}
	
// 	public function createEditView(EiFrame $eiFrame, EntryGuiModel $entryModel, PropertyPath $propertyPath = null): View {
// 		$viewMode = $this->determineEditViewMode($entryModel->getEiEntry());
	
// 		$displayStructure = $this->getDisplayStructureViewMode($viewMode);
		
// 		return $eiFrame->getN2nContext()->lookup(ViewFactory::class)->create(
// 				'rocket\spec\config\mask\view\entryEdit.html',
// 				array('displayStructure' => $displayStructure, 'eiFrame' => $eiFrame, 'entryModel' => $entryModel, 
// 						'propertyPath' => $propertyPath));
// 	}
	
// 	public function createAddView(EiFrame $eiFrame, EntryModel $entryModel, PropertyPath $propertyPath = null) {
// 		$displayStructure = $this->getDisplayStructureViewMode(DisplayDefinition::VIEW_MODE_BULKY_ADD);
	
// 		return $eiFrame->getN2nContext()->lookup(ViewFactory::class)->create(
// 				'rocket\spec\config\mask\view\entryEdit.html',
// 				array('displayStructure' => $displayStructure, 'eiFrame' => $eiFrame, 'entryModel' => $entryModel, 
// 						'propertyPath' => $propertyPath));
// 	}

// 	private function filterDisplayStructure(array $displayStructure, GuiDefinition $guiDefinition) {
// 		foreach ($displayStructure as $key => $fieldId) {
// 			if ($fieldId instanceof GroupedDisplayStructure) {
// 				$group = $fieldId->copy($this->filterDisplayStructure(
// 						$fieldId->getDisplayStructure(), $guiDefinition));
// 				if ($group->size()) {
// 					$displayStructure[$key] = $group;
// 					continue;
// 				}
// 			}
			
// 			if (!$guiDefinition->containsGuiPropId($fieldId)) {
// 				unset($displayStructure[$key]);
// 			}
// 		}
// 		return $displayStructure;
// 	}
	
// 	public function getFilterGroupData() {
// 		if (null !== ($filterData = $this->eiDef->getFilterGroupData())) {
// 			return $filterData;
// 		}
		
// 		return $this->eiType->getDefaultEiDef()->getFilterGroupData();
// 	}
	
// 	public function setFilterGroupData(FilterData $filterData = null) {
// 		$this->filterData = $filterData;
// 	}
	
// 	public function getDefaultSortData() {
// 		if (null !== ($defaultSortDirections = $this->eiDef->getDefaultSortData())) {
// 			return $defaultSortDirections;
// 		}
		
// 		return $this->eiType->getDefaultEiDef()->getDefaultSortData();
// 	}
	
// 	public function isFiltered()  {
// 		return null !== $this->eiDef->getFilterGroupData();
// 	}
	
	public function getSubEiMaskIds() {
		return $this->subEiMaskIds;
	}
	
	public function setSubEiMaskIds(array $subEiMaskIds) {
		$this->subEiMaskIds = $subEiMaskIds;
	}
	
	public function determineEiMask(EiType $eiType): EiMask {
		$eiTypeId = $eiType->getId();
		if ($this->eiType->getId() == $eiTypeId) {
			return $this;
		}
		
		if ($this->eiType->containsSubEiTypeId($eiTypeId)) {
			return $this->getSubEiMaskByEiTypeId($eiTypeId);
		}
				
		foreach ($this->eiType->getSubEiTypes() as $subEiType) {
			if (!$subEiType->containsSubEiTypeId($eiTypeId, true)) continue;
			return $this->getSubEiMaskByEiTypeId($subEiType->getId())
					->determineEiMask($eiType);
		}
		
		throw new \InvalidArgumentException();
	}
	
	public function getSubEiMaskByEiTypeId($eiTypeId): EiMask {
		$subMaskIds = $this->getSubEiMaskIds();
		
		foreach ($this->eiType->getSubEiTypes() as $subEiType) {
			if ($subEiType->getId() != $eiTypeId) continue;
			
			if (isset($subMaskIds[$eiTypeId])) {
				return $subEiType->getEiMaskCollection()->getById($subMaskIds[$eiTypeId]);
			} else {
				return $subEiType->getEiMaskCollection()->getOrCreateDefault();
			}
		}
		
		throw new \InvalidArgumentException('EiType ' . $eiTypeId . ' is no SubEiType of ' 
				. $this->eiType->getId());
	}
	
	public function isPreviewSupported(): bool {
		return null !== $this->eiDef->getPreviewControllerLookupId() 
				|| null !== $this->eiType->getDefaultEiDef()->getPreviewControllerLookupId();
	}
	
	public function lookupPreviewController(EiFrame $eiFrame, PreviewModel $previewModel = null): PreviewController {
		$lookupId = $this->eiDef->getPreviewControllerLookupId();
		if (null === $lookupId) {
			$lookupId = $this->eiType->getDefaultEiDef()->getPreviewControllerLookupId();	
		}
		
		if ($lookupId === null) {
			throw new UnavailablePreviewException('No PreviewController available for EiMask: ' . $this);
		}
		
		$previewController = $eiFrame->getN2nContext()->lookup($lookupId);
		if (!($previewController instanceof PreviewController)) {
			throw new InvalidConfigurationException('PreviewController must implement ' . PreviewController::class 
					. ': ' . get_class($previewController));
		}
		
		if ($previewModel === null) {
			return $previewController;
		}
		
		if (!array_key_exists($previewModel->getPreviewType(), $previewController->getPreviewTypeOptions(new Eiu($eiFrame, $previewModel->getEiObject())))) {
			throw new UnavailableControlException('Unknown preview type \'' . $previewModel->getPreviewType() 
					. '\' for PreviewController: ' . get_class($previewController));
		}
		
		$previewController->setPreviewModel($previewModel);
		return $previewController;
	}
	
	public function __toString(): string {
		if ($this->id !== null) {
			return 'CommonEiMask (id: ' . $this->id . ') of ' . $this->eiType;
		}
		
		return 'Default CommonEiMask of ' . $this->eiType;
	}
	
	/**
	 * @todo move to EiEngine!!
	 * @param EiFrame $eiFrame
	 */
	public function setupEiFrame(EiFrame $eiFrame) {
		if (null !== ($filterGroupData = $this->eiDef->getFilterGroupData())
				|| null !== ($filterGroupData = $this->eiType->getDefaultEiDef()->getFilterGroupData())) {
			$criteriaConstraint = $this->createManagedFilterDefinition($eiFrame)
					->buildCriteriaConstraint($filterGroupData, false);
			if ($criteriaConstraint !== null) {
				$eiFrame->addCriteriaConstraint($criteriaConstraint);
			}
		}

		if (null !== ($defaultSortData = $this->eiDef->getDefaultSortData())
				|| null !== ($defaultSortData = $this->eiType->getDefaultEiDef()->getDefaultSortData())) {
			$criteriaConstraint = $this->eiEngine->createManagedSortDefinition($eiFrame)
					->builCriteriaConstraint($defaultSortData, false);
			if ($criteriaConstraint !== null) {
				$eiFrame->getCriteriaConstraintCollection()->add(CriteriaConstraint::TYPE_HARD_SORT, $criteriaConstraint);
			}
		}

		$eiu = new Eiu($eiFrame);
		foreach ($this->eiEngine->getEiModificatorCollection()->toArray() as $modificator) {
			$modificator->setupEiFrame($eiu);
		}
	}

}
