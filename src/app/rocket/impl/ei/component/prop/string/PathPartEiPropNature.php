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
namespace rocket\impl\ei\component\prop\string;

use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use n2n\l10n\DynamicTextCollection;
use n2n\util\type\ArgUtils;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\gui\ViewMode;
use rocket\si\content\SiField;
use rocket\impl\ei\component\prop\string\conf\PathPartConfig;
use rocket\si\content\impl\SiFields;
use rocket\op\ei\util\factory\EifGuiField;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\generic\UnknownScalarEiPropertyException;
use rocket\op\ei\manage\generic\UnknownGenericEiPropertyException;
use rocket\op\ei\util\spec\EiuEngine;
use rocket\op\ei\component\InvalidEiConfigurationException;
use rocket\op\ei\manage\generic\GenericEiProperty;
use rocket\impl\ei\component\prop\string\modificator\PathPartEiModNature;
use test\model\Entity;
use n2n\util\type\TypeConstraints;

class PathPartEiPropNature extends AlphanumericEiPropNature {

	private ?EiPropPath $baseEiPropPath = null;
	private ?EiPropPath $uniquePerEiPropPath = null;

	private bool $allowsNull = true;

	public function __construct(PropertyAccessProxy $propertyAccessProxy, EntityProperty $entityProperty) {
		parent::__construct($propertyAccessProxy->createRestricted(TypeConstraints::string(true)));

		$this->setEntityProperty($entityProperty);

		$this->getDisplayConfig()->setDefaultDisplayedViewModes(ViewMode::BULKY_EDIT | ViewMode::COMPACT_READ);
		parent::setMandatory(false);
	}

	function isMandatory(): bool {
		return false;
	}

	function setMandatory(bool $nullAllowed): static {
		$this->allowsNull = $nullAllowed;
		return $this;
	}

	function setup(Eiu $eiu): void {
		parent::setup($eiu);

		$eiu->mask()->addMod($mod = new PathPartEiModNature($eiu->prop()->getPath(), $eiu->mask(),
				$this->requireEntityProperty(), $this->allowsNull));

		$eiu->mask()->onEngineReady(function (EiuEngine $eiuEngine) use ($eiu, $mod) {
			if ($this->baseEiPropPath !== null) {
				try {
					$mod->setBaseScalarEiProperty($eiuEngine->getScalarEiProperty($this->baseEiPropPath));
				} catch (UnknownScalarEiPropertyException $e) {
					throw $eiu->prop()->createConfigException(null, $e);
				}
			}

			if ($this->uniquePerEiPropPath !== null) {
				try {
					$mod->setUniquePerGenericEiProperty($eiuEngine->getGenericEiProperty($this->uniquePerEiPropPath));
				} catch (UnknownGenericEiPropertyException $e) {
					throw $eiu->prop()->createConfigException(null, $e);
				}
			}
		});
	}

	/**
	 * @return EiPropPath|null
	 */
	public function getBaseEiPropPath(): ?EiPropPath {
		return $this->baseEiPropPath;
	}

	/**
	 * @param EiPropPath|null $baseEiPropPath
	 */
	public function setBaseEiPropPath(?EiPropPath $baseEiPropPath): void {
		$this->baseEiPropPath = $baseEiPropPath;
	}

	/**
	 * @return EiPropPath|null
	 */
	public function getUniquePerEiPropPath(): ?EiPropPath {
		return $this->uniquePerEiPropPath;
	}

	/**
	 * @param EiPropPath|null $uniquePerEiPropPath
	 */
	public function setUniquePerEiPropPath(?EiPropPath $uniquePerEiPropPath): void {
		$this->uniquePerEiPropPath = $uniquePerEiPropPath;
	}

//	private function buildMagInputAttrs(Eiu $eiu): array {
//		$attrs = array('placeholder' => $this->getLabelLstr(), 'class' => 'form-control');
//
//		if ($eiu->entry()->isNew() || $eiu->entry()->isDraft() || !$this->critical) {
//			return $attrs;
//		}
//
//		$attrs['class'] = 'rocket-critical-input';
//
//		if (null !== $this->criticalMessage) {
//			$dtc = new DynamicTextCollection('rocket', $eiu->getN2nLocale());
//			$attrs['data-confirm-message'] = $this->criticalMessage;
//			$attrs['data-edit-label'] =  $dtc->translate('common_edit_label');
//			$attrs['data-cancel-label'] =  $dtc->translate('common_cancel_label');
//		}
//
//		return $attrs;
//	}
	
	function createInEifGuiField(Eiu $eiu): EifGuiField {
		$siField = SiFields::stringIn($eiu->field()->getValue())
				->setMandatory($this->isMandatory())
				->setMinlength($this->getMinlength())
				->setMaxlength($this->getMaxlength())
				->setPrefixAddons($this->getPrefixSiCrumbGroups())
				->setSuffixAddons($this->getSuffixSiCrumbGroups())
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
		
		return $eiu->factory()->newGuiField($siField)
				->setSaver(function () use ($eiu, $siField) {
					$this->saveSiField($siField, $eiu);
				});
	}
	
	function saveSiField(SiField $siField, Eiu $eiu) {
		$eiu->field()->setValue($siField->getValue());
	}
}
