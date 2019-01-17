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
namespace rocket\impl\ei\component\prop\adapter\config;

use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\impl\ei\component\EiConfiguratorAdapter;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\core\container\N2nContext;
use n2n\web\dispatch\mag\MagCollection;
use n2n\l10n\DynamicTextCollection;
use rocket\ei\component\EiSetup;
use n2n\reflection\property\ConstraintsConflictException;
use rocket\ei\component\prop\EiProp;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use rocket\ei\component\prop\indepenent\IncompatiblePropertyException;
use n2n\reflection\property\AccessProxy;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\persistence\meta\structure\Column;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\util\type\attrs\InvalidAttributeException;
use n2n\util\type\attrs\LenientAttributeReader;
use rocket\ei\manage\gui\ViewMode;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\prop\adapter\EditablePropertyEiPropAdapter;
use rocket\impl\ei\component\prop\adapter\PropertyDisplayableEiPropAdapter;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\ei\manage\gui\ui\DisplayItem;

class AdaptableEiPropConfigurator extends EiConfiguratorAdapter implements EiPropConfigurator {
	const ATTR_DISPLAY_IN_OVERVIEW_KEY = 'displayInOverview';
	const ATTR_DISPLAY_IN_DETAIL_VIEW_KEY = 'displayInDetailView';
	const ATTR_DISPLAY_IN_EDIT_VIEW_KEY = 'displayInEditView';
	const ATTR_DISPLAY_IN_ADD_VIEW_KEY = 'displayInAddView';
	const ATTR_HELPTEXT_KEY = 'helpText';
	const ATTR_DISPLAY_ITEM_TYPE = 'containerType';

	const ATTR_CONSTANT_KEY = 'constant';
	const ATTR_READ_ONLY_KEY = 'readOnly';
	const ATTR_MANDATORY_KEY = 'mandatory';
	
	const ATTR_DRAFTABLE_KEY = 'draftable';	
	
	private $propertyAssignation;
	
	private $displayConfig;
	protected $addDisplayItemType = true;
	protected $addHelpText = true;
	
	private $editConfig;
	protected $addConstant = true; 
	protected $addReadOnly = true;
	protected $addMandatory = true;
	protected $autoMandatoryCheck = true;
	
	private $confDraftableEiProp;
	
	private $maxCompatibilityLevel = CompatibilityLevel::COMPATIBLE;
		
	public function initAutoEiPropAttributes(N2nContext $n2nContext, Column $column = null) {
		if ($this->addMandatory && $this->autoMandatoryCheck && $this->mandatoryRequired()) {
			$this->attributes->set(self::ATTR_MANDATORY_KEY, true);
		}
	}
	
// 	public function getPropertyAssignation(): PropertyAssignation {
// 		return new PropertyAssignation($this->getAssignedEntityProperty(), 
// 				$this->getAssignedObjectPropertyAccessProxy());
// 	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		try {
			$this->assignProperty($propertyAssignation);
			return $this->maxCompatibilityLevel;
		} catch (IncompatiblePropertyException $e) {
			return CompatibilityLevel::NOT_COMPATIBLE;
		}
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::assignProperty()
	 */
	public function assignProperty(PropertyAssignation $propertyAssignation) {
// 		if (!$this->isPropertyAssignable()) {
// 			throw new IncompatiblePropertyException('EiProp can not be assigned to a property.');
// 		}
	
		if ($this->confEntityPropertyEiProp !== null) {
			try {
				$this->confEntityPropertyEiProp->setEntityProperty(
						$propertyAssignation->getEntityProperty(false));
			} catch (\InvalidArgumentException $e) {
				throw $propertyAssignation->createEntityPropertyException(null, $e);
			}
		}
	
		if ($this->confObjectPropertyEiProp !== null) {
			try {
				$this->confObjectPropertyEiProp->setObjectPropertyAccessProxy(
						$propertyAssignation->getObjectPropertyAccessProxy(false));
			} catch (\InvalidArgumentException $e) {
				throw $propertyAssignation->createAccessProxyException(null, $e);
			} catch (ConstraintsConflictException $e) {
				throw $propertyAssignation->createAccessProxyException(null, $e);
			}
		}
		
		$this->propertyAssignation = $propertyAssignation;
	}
	
	public function getTypeName(): string {
		return self::shortenTypeName(parent::getTypeName(), array('Ei', 'Prop'));
	}
	
	public function setMaxCompatibilityLevel(int $maxCompatibilityLevel) {
		$this->maxCompatibilityLevel = $maxCompatibilityLevel;
	}
	
	private $confEntityPropertyEiProp;
	
	public function registerEntityPropertyConfigurable(EntityPropertyConfigurable $entityPropertyEiProp) {
		$this->confEntityPropertyEiProp = $entityPropertyEiProp;
	}
	
	private $confObjectPropertyEiProp;
	
	public function registerObjectPropertyConfigurable(ObjectPropertyConfigurable $confObjectPropertyEiProp) {
		$this->confObjectPropertyEiProp = $confObjectPropertyEiProp;
	}
	
	public function registerDisplayConfig(DisplayConfig $displayConfig) {
		$this->displayConfig = $displayConfig;
	}	
	
	public function registerEditConfig(EditConfig $editConfig, 
			bool $addConstant = true, bool $addReadOnly = true, $addMandatory = true, $autoMandatoryCheck = true) {
		$this->editConfig = $editConfig;
		$this->addConstant = $addConstant;
		$this->addReadOnly = $addReadOnly;
		$this->addMandatory = $addMandatory;
		$this->autoMandatoryCheck = $autoMandatoryCheck;
	}
	
	public function registerDraftConfigurable(DraftConfigurable $confDraftableEiProp) {
		$this->confDraftableEiProp = $confDraftableEiProp;		
	}
	
	public function autoRegister() {
		$eiComponent = $this->eiComponent;
		
		if ($eiComponent instanceof EntityPropertyConfigurable) {
			$this->registerEntityPropertyConfigurable($eiComponent);
		}
		
		if ($eiComponent instanceof ObjectPropertyConfigurable) {
			$this->registerObjectPropertyConfigurable($eiComponent);
		}
		
		if ($eiComponent instanceof PropertyDisplayableEiPropAdapter) {
			$this->registerDisplayConfig($eiComponent->getDisplayConfig());
		}
		
		if ($eiComponent instanceof EditablePropertyEiPropAdapter) {
			$this->registerEditConfig($eiComponent->getEditConfig());
		}
		
		if ($eiComponent instanceof DraftConfigurable) {
			$this->registerDraftConfigurable($eiComponent);
		}
	}
	
	protected function getPropertyAssignation() {
		if ($this->propertyAssignation === null) {
			throw new IllegalStateException('No PropertyAssignation available.');
		}
		
		return $this->propertyAssignation;
	}
	
	/**
	 * @todo remove this everywhere
	 * @deprecated remove this everywhere
	 * @return boolean
	 */
	public function isPropertyAssignable(): bool {
		return $this->confEntityPropertyEiProp !== null
				|| $this->confObjectPropertyEiProp !== null;
	}
	
	protected function isAssignableToEntityProperty(): bool {
		return $this->confEntityPropertyEiProp !== null;
	}
	
	protected function isAssignableToObjectProperty(): bool {
		return $this->confObjectPropertyEiProp != null;
	}

	protected function getAssignedEntityProperty() {
		if ($this->confEntityPropertyEiProp === null) return null;
		
		return $this->confEntityPropertyEiProp->getEntityProperty();
	}
	
// 	protected function getAssignedObjectPropertyAccessProxy() {
// 		if ($this->confObjectPropertyEiProp === null) return null;
		
// 		return $this->confObjectPropertyEiProp->getObjectPropertyAccessProxy();
// 	}
	
// 	protected function requireEntityProperty(): EntityProperty {
// 		if (null !== ($entityProperty = $this->getAssignedEntityProperty())) {
// 			return $entityProperty;
// 		}
	
// 		throw new InvalidEiComponentConfigurationException('No EntityProperty assigned to EiProp: ' . $this->eiComponent);
// 	}
	
	protected function requireObjectPropertyAccessProxy(): AccessProxy  {
		if (null !== ($accessProxy = $this->getAssignedObjectPropertyAccessProxy())) {
			return $accessProxy;
		}
	
		throw new InvalidEiComponentConfigurationException('No ObjectProperty assigned to EiProp: ' . $this->eiComponent);
	}
	
	protected function requirePropertyName(): string {
		$propertyAssignation = $this->getPropertyAssignation();
		
		if (null !== ($entityProperty = $propertyAssignation->getEntityProperty())) {
			return $entityProperty->getName();
		}
	
		if (null !== ($accessProxy = $propertyAssignation->getObjectPropertyAccessProxy())) {
			return $accessProxy->getPropertyName();
		}
	
		throw new InvalidEiComponentConfigurationException('No property assigned to EiProp: ' . $this->eiComponent);
	}
	
	public function getEntityPropertyName() {
		if ($this->confEntityPropertyEiProp === null) {
			return null;
		}
		
		return $this->confEntityPropertyEiProp->getEntityProperty()->getName();
	}
	
	public function getObjectPropertyName() {
		if ($this->confObjectPropertyEiProp === null) {
			return null;
		}
		
		return $this->confObjectPropertyEiProp->getObjectPropertyAccessProxy()->getPropertyName();
	}
	
	public function setup(EiSetup $eiSetupProcess) {
		try {
			$this->setupDisplayConfig();
		} catch (\InvalidArgumentException $e) {
			throw $eiSetupProcess->createException('Invalid display configuration', $e);
		}

		$this->setupEditConfig();
		$this->setupDraftableAdapter();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\EiConfiguratorAdapter::createMagDispatchable()
	 */
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magCollection = new MagCollection();
		$dtc = new DynamicTextCollection('rocket', $n2nContext->getN2nLocale());
		
		$this->assignDisplayMags($magCollection, $dtc);
		$this->assignEditMags($magCollection, $dtc);
		$this->assignDrafMags($magCollection, $dtc);
		
		return new MagForm($magCollection);
	}

	private function setupDisplayConfig() {
		if ($this->displayConfig === null) return;
	
		if ($this->attributes->contains(self::ATTR_DISPLAY_IN_OVERVIEW_KEY)) {
			$this->displayConfig->changeDefaultDisplayedViewModes(
					ViewMode::compact(), 
					$this->attributes->reqBool(self::ATTR_DISPLAY_IN_OVERVIEW_KEY));
		}
	
		if ($this->attributes->contains(self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY)) {
			$this->displayConfig->changeDefaultDisplayedViewModes(ViewMode::BULKY_READ,
					$this->attributes->reqBool(self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY));
		}
	
		if ($this->attributes->contains(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY)) {
			$this->displayConfig->changeDefaultDisplayedViewModes(ViewMode::BULKY_EDIT,
					$this->attributes->reqBool(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY));
		}
	
		if ($this->attributes->contains(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY)) {
			$this->displayConfig->changeDefaultDisplayedViewModes(ViewMode::BULKY_ADD,
					$this->attributes->reqBool(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY));
		}
		
		if ($this->attributes->contains(self::ATTR_DISPLAY_ITEM_TYPE)) {
			$this->displayConfig->setDisplayItemType(
					$this->attributes->reqEnum(self::ATTR_DISPLAY_ITEM_TYPE, DisplayItem::getTypes()));
		}
	
		if ($this->attributes->contains(self::ATTR_HELPTEXT_KEY)) {
			$this->displayConfig->setHelpText(
					$this->attributes->optString(self::ATTR_HELPTEXT_KEY));
		}
	}
	
	private function assignDisplayMags(MagCollection $magCollection, DynamicTextCollection $dtc) {
		if ($this->displayConfig === null) return;
				
		$lar = new LenientAttributeReader($this->attributes);
		
		if ($this->displayConfig->isCompactViewCompatible()) {
			$magCollection->addMag(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, new BoolMag(
					$dtc->translate('ei_impl_display_in_overview_label'),
					$lar->getBool(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, 
							$this->displayConfig->isViewModeDefaultDisplayed(ViewMode::BULKY_READ))));
		}
	
		if ($this->displayConfig->isViewModeCompatible(ViewMode::BULKY_READ)) {
			$magCollection->addMag(self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY, new BoolMag(
					$dtc->translate('ei_impl_display_in_detail_view_label'),
					$lar->getBool(self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY,
							$this->displayConfig->isViewModeDefaultDisplayed(ViewMode::BULKY_READ))));
		}
	
		if ($this->displayConfig->isViewModeCompatible(ViewMode::BULKY_EDIT)) {
			$magCollection->addMag(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY, new BoolMag(
					$dtc->translate('ei_impl_display_in_edit_view_label'),
					$lar->getBool(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY, 
							$this->displayConfig->isViewModeDefaultDisplayed(ViewMode::BULKY_EDIT))));
		}
	
		if ($this->displayConfig->isViewModeCompatible(ViewMode::BULKY_ADD)) {
			$magCollection->addMag(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY, new BoolMag(
					$dtc->translate('ei_impl_display_in_add_view_label'),
					$lar->getBool(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY, 
							$this->displayConfig->isViewModeDefaultDisplayed(ViewMode::BULKY_ADD))));
		}
		
		if ($this->addDisplayItemType) {
			$types = DisplayItem::getTypes();
			$magCollection->addMag(self::ATTR_DISPLAY_ITEM_TYPE, new EnumMag('Container type', 
					array_combine($types, $types), $this->displayConfig->getDisplayItemType(), true));
		}
		
		if ($this->addHelpText) {
			$magCollection->addMag(self::ATTR_HELPTEXT_KEY, new StringMag('Helptext', 
					$lar->getString(self::ATTR_HELPTEXT_KEY, $this->displayConfig->getHelpText())));
		}
	}
	
	protected function mandatoryRequired() {
		$accessProxy = $this->getPropertyAssignation()->getObjectPropertyAccessProxy(false);
		if (null === $accessProxy) return false;
		return !$accessProxy->getConstraint()->allowsNull() && !$accessProxy->getConstraint()->isArrayLike();
	}
	
	private function setupEditConfig() {
		if ($this->editConfig === null) return;
		
		if ($this->addConstant && $this->attributes->contains(self::ATTR_CONSTANT_KEY)) {
			$this->editConfig->setConstant($this->attributes->getBool(self::ATTR_CONSTANT_KEY));
		}
			
		if ($this->addReadOnly && $this->attributes->contains(self::ATTR_READ_ONLY_KEY)) {
			$this->editConfig->setReadOnly($this->attributes->getBool(self::ATTR_READ_ONLY_KEY));
		}
		
		if ($this->addMandatory) {
			if ($this->attributes->contains(self::ATTR_MANDATORY_KEY)) {
				$mandatory = $this->attributes->getBool(self::ATTR_MANDATORY_KEY);
				$this->editConfig->setMandatory($mandatory);
			}
			
			if (!$this->editConfig->isMandatory() && $this->addMandatory && $this->autoMandatoryCheck 
					&& $this->mandatoryRequired()) {
				throw new InvalidAttributeException(self::ATTR_MANDATORY_KEY . ' must be true because '
						. $this->getPropertyAssignation()->getObjectPropertyAccessProxy(true) 
						. ' does not allow null value.');
			}
		}
	}
	
	private function assignEditMags(MagCollection $magCollection) {
		if ($this->editConfig === null) return;

		$lar = new LenientAttributeReader($this->attributes);
		
		if ($this->addConstant) {
			$magCollection->addMag(self::ATTR_CONSTANT_KEY, new BoolMag('Constant',
					$lar->getBool(self::ATTR_CONSTANT_KEY, $this->editConfig->isConstant())));
		}
			
		if ($this->addReadOnly) {
			$magCollection->addMag(self::ATTR_READ_ONLY_KEY, new BoolMag('Read only',
					$lar->getBool(self::ATTR_READ_ONLY_KEY, $this->editConfig->isReadOnly())));
		}
			
		if ($this->addMandatory) {
			$magCollection->addMag(self::ATTR_MANDATORY_KEY, new BoolMag('Mandatory',
					$lar->getBool(self::ATTR_MANDATORY_KEY, $this->editConfig->isMandatory())));
		}
	}

	private function setupDraftableAdapter() {
		if ($this->confDraftableEiProp === null) return;
		
		$this->confDraftableEiProp->setDraftable(
				$this->attributes->getBool(self::ATTR_DRAFTABLE_KEY, false, false));
	}
	
	private function assignDrafMags(MagCollection $magCollection, DynamicTextCollection $dtc) {
		if ($this->confDraftableEiProp === null) return;

		$lar = new LenientAttributeReader($this->attributes);
		
		$magCollection->addMag(self::ATTR_DRAFTABLE_KEY, new BoolMag($dtc->translate('ei_impl_draftable_label'),
				$lar->getBool(self::ATTR_DRAFTABLE_KEY, $this->confDraftableEiProp->isDraftable())));	
	}

	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
	
		$magCollection = $magDispatchable->getMagCollection();
		$this->saveDisplayMags($magCollection);
		$this->saveStandardEditMags($magCollection);
		$this->saveDraftMags($magCollection);
	}
	
	private function saveDisplayMags(MagCollection $magCollection) {
		if ($this->displayConfig === null) return;
		
		$this->attributes->appendAll($magCollection->readValues(array(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, 
				self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY, self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY, 
				self::ATTR_DISPLAY_IN_ADD_VIEW_KEY, self::ATTR_HELPTEXT_KEY), true), true);
	}
	
	private function saveStandardEditMags(MagCollection $magCollection) {
		if ($this->editConfig === null) return;
	
		$this->attributes->appendAll($magCollection->readValues(array(self::ATTR_CONSTANT_KEY,
				self::ATTR_READ_ONLY_KEY, self::ATTR_MANDATORY_KEY,
				self::ATTR_DISPLAY_IN_ADD_VIEW_KEY, self::ATTR_HELPTEXT_KEY), true), true);
	}
	
	private function saveDraftMags(MagCollection $magCollection) {
		if ($this->confDraftableEiProp === null) return;
	
		$this->attributes->set(self::ATTR_DRAFTABLE_KEY,
				$magCollection->getMagWrapperByPropertyName(self::ATTR_DRAFTABLE_KEY)->getMag()->getValue());
	}
	
	public static function createFromField(EiProp $eiProp) {
		$configurator = new AdaptableEiPropConfigurator($eiProp);
		$configurator->autoRegister($eiProp);
		return $configurator;
	}
}
