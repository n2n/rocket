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
namespace rocket\spec\ei\component\field\impl\adapter;

use rocket\spec\ei\component\field\indepenent\EiFieldConfigurator;
use rocket\spec\ei\component\field\indepenent\PropertyAssignation;
use rocket\spec\ei\component\impl\EiConfiguratorAdapter;
use n2n\dispatch\mag\impl\model\BoolMag;
use n2n\core\container\N2nContext;
use n2n\dispatch\mag\MagCollection;
use n2n\l10n\DynamicTextCollection;
use rocket\spec\ei\component\EiSetupProcess;
use n2n\reflection\property\ConstraintsConflictException;
use rocket\spec\ei\component\field\EiField;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\component\field\indepenent\CompatibilityLevel;
use rocket\spec\ei\component\field\indepenent\IncompatiblePropertyException;
use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\property\AccessProxy;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use n2n\persistence\meta\structure\Column;
use n2n\dispatch\mag\impl\model\MagForm;
use n2n\dispatch\mag\MagDispatchable;
use n2n\dispatch\mag\impl\model\StringMag;
use n2n\util\config\InvalidAttributeException;
use n2n\util\config\LenientAttributeReader;

class AdaptableEiFieldConfigurator extends EiConfiguratorAdapter implements EiFieldConfigurator {
	const ATTR_DISPLAY_IN_OVERVIEW_KEY = 'displayInOverview';
	const ATTR_DISPLAY_IN_DETAIL_VIEW_KEY = 'displayInDetailView';
	const ATTR_DISPLAY_IN_EDIT_VIEW_KEY = 'displayInEditView';
	const ATTR_DISPLAY_IN_ADD_VIEW_KEY = 'displayInAddView';
	const ATTR_HELPTEXT_KEY = 'helpText';

	const ATTR_CONSTANT_KEY = 'constant';
	const ATTR_READ_ONLY_KEY = 'readOnly';
	const ATTR_MANDATORY_KEY = 'mandatory';
	
	const ATTR_DRAFTABLE_KEY = 'draftable';	
	
	private $displayDefinition;
	
	private $standardEditDefinition;
	protected $addConstant = true; 
	protected $addReadOnly = true;
	protected $addMandatory = true;
	protected $autoMandatoryCheck = true;
	
	private $confDraftableEiField;
	
	private $maxCompatibilityLevel = CompatibilityLevel::COMPATIBLE;
		
	public function initAutoEiFieldAttributes(Column $column = null) {
		if ($this->addMandatory && $this->autoMandatoryCheck && $this->mandatoryRequired()) {
			$this->attributes->set(self::ATTR_MANDATORY_KEY, true);
		}
	}
	
	public function getPropertyAssignation(): PropertyAssignation {
		return new PropertyAssignation($this->getAssignedEntityProperty(), 
				$this->getAssignedObjectPropertyAccessProxy());
	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): int {
		try {
			$this->assignProperty($propertyAssignation);
			return $this->maxCompatibilityLevel;
		} catch (IncompatiblePropertyException $e) {
			return CompatibilityLevel::NOT_COMPATIBLE;
		}
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\indepenent\EiFieldConfigurator::assignProperty()
	 */
	public function assignProperty(PropertyAssignation $propertyAssignation) {
		if (!$this->isPropertyAssignable()) {
			throw new IncompatiblePropertyException('EiField can not be assigned to a property.');
		}
	
		if ($this->confEntityPropertyEiField !== null) {
			try {
				$this->confEntityPropertyEiField->setEntityProperty(
						$propertyAssignation->getEntityProperty());
			} catch (\InvalidArgumentException $e) {
				throw $propertyAssignation->createEntityPropertyException(null, $e);
			}
		}
	
		if ($this->confObjectPropertyEiField !== null) {
			try {
				$this->confObjectPropertyEiField->setObjectPropertyAccessProxy(
						$propertyAssignation->getObjectPropertyAccessProxy());
			} catch (\InvalidArgumentException $e) {
				throw $propertyAssignation->createAccessProxyException(null, $e);
			} catch (ConstraintsConflictException $e) {
				throw $propertyAssignation->createAccessProxyException(null, $e);
			}
		}
	}
	
	public function getTypeName(): string {
		return self::shortenTypeName(parent::getTypeName(), array('Ei', 'Field'));
	}
	
	public function setMaxCompatibilityLevel(int $maxCompatibilityLevel) {
		$this->maxCompatibilityLevel = $maxCompatibilityLevel;
	}
	
	private $confEntityPropertyEiField;
	
	public function registerConfEntityPropertyEiField(ConfEntityPropertyEiField $entityPropertyEiField) {
		$this->confEntityPropertyEiField = $entityPropertyEiField;
	}
	
	private $confObjectPropertyEiField;
	
	public function registerConfObjectPropertyEiField(ConfObjectPropertyEiField $confObjectPropertyEiField) {
		$this->confObjectPropertyEiField = $confObjectPropertyEiField;
	}
	
	public function registerDisplayDefinition(DisplayDefinition $displayDefinition) {
		$this->displayDefinition = $displayDefinition;
	}	
	
	public function registerStandardEditDefinition(StandardEditDefinition $standardEditDefinition) {
		$this->standardEditDefinition = $standardEditDefinition;
	}
	
	public function registerConfDraftableEiField(ConfDraftableEiField $confDraftableEiField) {
		$this->confDraftableEiField = $confDraftableEiField;		
	}	
	
	public function autoRegister() {
		if ($this->eiComponent instanceof ConfEntityPropertyEiFieldAdapter) {
			$this->registerConfEntityPropertyEiField($this->eiComponent);
		}
		
		if ($this->eiComponent instanceof ConfObjectPropertyEiFieldAdapter) {
			$this->registerConfObjectPropertyEiField($this->eiComponent);
		}
		
		if ($this->eiComponent instanceof DisplayableEiFieldAdapter) {
			$this->registerDisplayDefinition($this->eiComponent->getDisplayDefinition());
		}
		
		if ($this->eiComponent instanceof EditableEiFieldAdapter) {
			$this->registerStandardEditDefinition($this->eiComponent->getStandardEditDefinition());
		}
		
		if ($this->eiComponent instanceof DraftableEiFieldAdapter) {
			$this->registerConfDraftableEiField($this->eiComponent);
		}
	}
	
		
	/**
	 * @todo remove this everywhere
	 * @deprecated remove this everywhere
	 * @return boolean
	 */
	public function isPropertyAssignable(): bool {
		return $this->confEntityPropertyEiField !== null
				|| $this->confObjectPropertyEiField !== null;
	}
	
	protected function isAssignableToEntityProperty(): bool {
		return $this->confEntityPropertyEiField !== null;
	}
	
	protected function isAssignableToObjectProperty(): bool {
		return $this->confObjectPropertyEiField != null;
	}

	protected function getAssignedEntityProperty() {
		return $this->confEntityPropertyEiField->getEntityProperty();
	}
	
	protected function getAssignedObjectPropertyAccessProxy() {
		return $this->confObjectPropertyEiField->getObjectPropertyAccessProxy();
	}
	
	protected function requireEntityProperty(): EntityProperty {
		if (null !== ($entityProperty = $this->getAssignedEntityProperty())) {
			return $entityProperty;
		}
	
		throw new InvalidEiComponentConfigurationException('No EntityProperty assigned to EiField: ' . $this->eiComponent);
	}
	
	protected function requireObjectPropertyAccessProxy(): AccessProxy  {
		if (null !== ($accessProxy = $this->getAssignedObjectPropertyAccessProxy())) {
			return $accessProxy;
		}
	
		throw new InvalidEiComponentConfigurationException('No ObjectProperty assigned to EiField: ' . $this->eiComponent);
	}
	
	protected function requirePropertyName(): string {
		$propertyAssignation = $this->getPropertyAssignation();
		
		if (null !== ($entityProperty = $propertyAssignation->getEntityProperty())) {
			return $entityProperty->getName();
		}
	
		if (null !== ($accessProxy = $this->getObjectPropertyAccessProxy())) {
			return $accessProxy->getPropertyName();
		}
	
		throw new InvalidEiComponentConfigurationException('No property assigned to EiField: ' . $this->eiComponent);
	}
	
	public function getEntityPropertyName() {
		if ($this->confEntityPropertyEiField === null) {
			return null;
		}
		
		return $this->confEntityPropertyEiField->getEntityProperty()->getName();
	}
	
	public function getObjectPropertyName() {
		if ($this->confObjectPropertyEiField === null) {
			return null;
		}
		
		return $this->confObjectPropertyEiField->getObjectPropertyAccessProxy()->getPropertyName();
	}
	
	public function setup(EiSetupProcess $eiSetupProcess) {
		try {
			$this->setupDisplayDefinition();
		} catch (\InvalidArgumentException $e) {
			throw $eiSetupProcess->createException('Invalid display configuration', $e);
		}

		$this->setupEditableAdapter();
		$this->setupDraftableAdapter();
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\EiConfigurator::createMagCollection()
	 */
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magCollection = new MagCollection();
		$dtc = new DynamicTextCollection('rocket', $n2nContext->getN2nLocale());
		
		$this->assignDisplayMags($magCollection, $dtc);
		$this->assignEditMags($magCollection, $dtc);
		$this->assignDrafMags($magCollection, $dtc);
		
		return new MagForm($magCollection);
	}

	private function setupDisplayDefinition() {
		if ($this->displayDefinition === null) return;
	
		if ($this->attributes->contains(self::ATTR_DISPLAY_IN_OVERVIEW_KEY)) {
			$this->displayDefinition->changeDefaultDisplayedViewModes(
					DisplayDefinition::VIEW_MODE_LIST_READ | DisplayDefinition::VIEW_MODE_TREE_READ, 
					$this->attributes->get(self::ATTR_DISPLAY_IN_OVERVIEW_KEY));
		}
	
		if ($this->attributes->contains(self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY)) {
			$this->displayDefinition->changeDefaultDisplayedViewModes(DisplayDefinition::VIEW_MODE_BULKY_READ,
					$this->attributes->get(self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY));
		}
	
		if ($this->attributes->contains(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY)) {
			$this->displayDefinition->changeDefaultDisplayedViewModes(DisplayDefinition::VIEW_MODE_BULKY_EDIT,
					$this->attributes->get(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY));
		}
	
		if ($this->attributes->contains(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY)) {
			$this->displayDefinition->changeDefaultDisplayedViewModes(DisplayDefinition::VIEW_MODE_BULKY_ADD,
					$this->attributes->get(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY));
		}
	
		if ($this->attributes->contains(self::ATTR_HELPTEXT_KEY)) {
			$this->displayDefinition->setHelpText(
					$this->attributes->get(self::ATTR_HELPTEXT_KEY));
		}
	}
	
	private function assignDisplayMags(MagCollection $magCollection, DynamicTextCollection $dtc) {
		if ($this->displayDefinition === null) return;
				
		$lar = new LenientAttributeReader($this->attributes);
		
		if ($this->displayDefinition->isListReadViewCompatible()) {
			$magCollection->addMag(new BoolMag(self::ATTR_DISPLAY_IN_OVERVIEW_KEY,
					$dtc->translate('ei_impl_display_in_overview_label'),
					$lar->getBool(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, 
							$this->displayDefinition->isViewModeDefaultDisplayed(DisplayDefinition::VIEW_MODE_LIST_READ))));
		}
	
		if ($this->displayDefinition->isBulkyReadViewCompatible()) {
			$magCollection->addMag(new BoolMag(self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY,
					$dtc->translate('ei_impl_display_in_detail_view_label'),
					$lar->getBool(self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY,
							$this->displayDefinition->isViewModeDefaultDisplayed(
									DisplayDefinition::VIEW_MODE_BULKY_READ))));
		}
	
		if ($this->displayDefinition->isEditViewCompatible()) {
			$magCollection->addMag(new BoolMag(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY,
					$dtc->translate('ei_impl_display_in_edit_view_label'),
					$lar->getBool(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY, 
							$this->displayDefinition->isViewModeDefaultDisplayed(
									DisplayDefinition::VIEW_MODE_BULKY_EDIT))));
		}
	
		if ($this->displayDefinition->isAddViewCompatible()) {
			$magCollection->addMag(new BoolMag(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY,
					$dtc->translate('ei_impl_display_in_add_view_label'),
					$lar->getBool(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY, 
							$this->displayDefinition->isViewModeDefaultDisplayed(
									DisplayDefinition::VIEW_MODE_BULKY_ADD))));
		}
		
		$magCollection->addMag(new StringMag(self::ATTR_HELPTEXT_KEY, $dtc->translate('ei_impl_help_text_label'), 
				$lar->getString(self::ATTR_HELPTEXT_KEY, $this->displayDefinition->getHelpText())));
	}
	

	
	protected function mandatoryRequired() {
		$accessProxy = $this->getAssignedObjectPropertyAccessProxy();
		if (null === $accessProxy) return false;
		
		return !$accessProxy->getConstraint()->allowsNull() && !$accessProxy->getConstraint()->isArrayLike();
	}
	
	private function setupEditableAdapter() {
		if ($this->standardEditDefinition === null) return;
		
		if ($this->addConstant && $this->attributes->contains(self::ATTR_CONSTANT_KEY)) {
			$this->standardEditDefinition->setConstant($this->attributes->getBool(self::ATTR_CONSTANT_KEY));
		}
			
		if ($this->addReadOnly && $this->attributes->contains(self::ATTR_READ_ONLY_KEY)) {
			$this->standardEditDefinition->setReadOnly($this->attributes->getBool(self::ATTR_READ_ONLY_KEY));
		}
		
		if ($this->addMandatory) {
			if ($this->attributes->contains(self::ATTR_MANDATORY_KEY)) {
				$mandatory = $this->attributes->getBool(self::ATTR_MANDATORY_KEY);
				$this->standardEditDefinition->setMandatory($mandatory);
			}
			
			if (!$this->standardEditDefinition->isMandatory() && $this->autoMandatoryCheck 
					&& $this->mandatoryRequired()) {
				throw new InvalidAttributeException(self::ATTR_MANDATORY_KEY . ' must be true because '
						. $this->getAssignedObjectPropertyAccessProxy() . ' does not allow null value.');
			}
		}
	}
	
	private function assignEditMags(MagCollection $magCollection) {
		if ($this->standardEditDefinition === null) return;

		$lar = new LenientAttributeReader($this->attributes);
		
		if ($this->addConstant) {
			$magCollection->addMag(new BoolMag(self::ATTR_CONSTANT_KEY, 'Constant',
					$lar->getBool(self::ATTR_CONSTANT_KEY, $this->standardEditDefinition->isConstant())));
		}
			
		if ($this->addReadOnly) {
			$magCollection->addMag(new BoolMag(self::ATTR_READ_ONLY_KEY, 'Read only',
					$lar->getBool(self::ATTR_READ_ONLY_KEY, $this->standardEditDefinition->isReadOnly())));
		}
			
		if ($this->addMandatory) {
			$magCollection->addMag(new BoolMag(self::ATTR_MANDATORY_KEY, 'Mandatory',
					$lar->getBool(self::ATTR_MANDATORY_KEY, $this->standardEditDefinition->isMandatory())));
		}
	}

	private function setupDraftableAdapter() {
		if ($this->confDraftableEiField === null) return;
		
		$this->confDraftableEiField->setDraftable(
				$this->attributes->getBool(self::ATTR_DRAFTABLE_KEY, false, false));
	}
	
	private function assignDrafMags(MagCollection $magCollection, DynamicTextCollection $dtc) {
		if ($this->confDraftableEiField === null) return;

		$lar = new LenientAttributeReader($this->attributes);
		
		$magCollection->addMag(new BoolMag(self::ATTR_DRAFTABLE_KEY, $dtc->translate('ei_impl_draftable_label'),
				$lar->getBool(self::ATTR_DRAFTABLE_KEY, $this->confDraftableEiField->isDraftable())));	
	}

	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
	
		$magCollection = $magDispatchable->getMagCollection();
		$this->saveDisplayMags($magCollection);
		$this->saveStandardEditMags($magCollection);
		$this->saveDraftMags($magCollection);
	}
	
	private function saveDisplayMags(MagCollection $magCollection) {
		if ($this->displayDefinition === null) return;
		
		$this->attributes->appendAll($magCollection->readValues(array(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, 
				self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY, self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY, 
				self::ATTR_DISPLAY_IN_ADD_VIEW_KEY, self::ATTR_HELPTEXT_KEY), true), true);
	}
	
	private function saveStandardEditMags(MagCollection $magCollection) {
		if ($this->standardEditDefinition === null) return;
	
		$this->attributes->appendAll($magCollection->readValues(array(self::ATTR_CONSTANT_KEY,
				self::ATTR_READ_ONLY_KEY, self::ATTR_MANDATORY_KEY,
				self::ATTR_DISPLAY_IN_ADD_VIEW_KEY, self::ATTR_HELPTEXT_KEY), true), true);
	}
	
	private function saveDraftMags(MagCollection $magCollection) {
		if ($this->confDraftableEiField === null) return;
	
		$this->attributes->set(self::ATTR_DRAFTABLE_KEY,
				$magCollection->getMagByPropertyName(self::ATTR_DRAFTABLE_KEY)->getValue());
	}
	
	public static function createFromField(EiField $eiField) {
		$configurator = new AdaptableEiFieldConfigurator($eiField);
		$configurator->autoRegister($eiField);
		return $configurator;
	}
}
