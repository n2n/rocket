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

use n2n\persistence\orm\property\EntityProperty;
use rocket\op\ei\util\Eiu;
use rocket\ui\gui\ViewMode;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\manage\generic\UnknownScalarEiPropertyException;
use rocket\op\ei\manage\generic\UnknownGenericEiPropertyException;
use rocket\op\ei\util\spec\EiuEngine;
use n2n\util\type\TypeConstraints;
use rocket\ui\gui\field\impl\string\StringInGuiField;
use n2n\bind\mapper\impl\Mappers;
use rocket\impl\ei\component\prop\string\modificator\PathPartUtil;
use rocket\ui\gui\field\impl\GuiFields;
use rocket\ui\si\content\impl\string\PathPartInSiField;
use rocket\ui\gui\field\impl\string\PathPartInGuiField;

class PathPartEiPropNature extends AlphanumericEiPropNature {

	private ?EiPropPath $baseEiPropPath = null;
	private ?EiPropPath $uniquePerEiPropPath = null;

	private PathPartUtil $pathPartUtil;

	public function __construct(PropertyAccessProxy $propertyAccessProxy, EntityProperty $entityProperty) {
		parent::__construct($propertyAccessProxy->createRestricted(TypeConstraints::string(true)));

		$this->setEntityProperty($entityProperty);

		$this->getDisplayConfig()->setDefaultDisplayedViewModes(ViewMode::bulky());
	}


	function setup(Eiu $eiu): void {
		parent::setup($eiu);

		$this->pathPartUtil = new PathPartUtil($this->requireEntityProperty(),
				$eiu->mask()->getEiMask()->getEiType()->getEntityModel()->getIdDef()->getEntityProperty());

		$eiu->mask()->onEngineReady(function (EiuEngine $eiuEngine) use ($eiu) {
			if ($this->baseEiPropPath !== null) {
				try {
					$this->pathPartUtil->setBaseScalarEiProperty($eiuEngine->getScalarEiProperty($this->baseEiPropPath));
				} catch (UnknownScalarEiPropertyException $e) {
					throw $eiu->prop()->createConfigException(null, $e);
				}
			}

			if ($this->uniquePerEiPropPath !== null) {
				try {
					$this->pathPartUtil->setUniquePerGenericEiProperty($eiuEngine->getGenericEiProperty($this->uniquePerEiPropPath));
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
	
//	function buildInGuiField(Eiu $eiu): ?BackableGuiField {
//		$siField = SiFields::stringIn($eiu->field()->getValue())
//				->setMandatory($this->isMandatory())
//				->setMinlength($this->getMinlength())
//				->setMaxlength($this->getMaxlength())
//				->setPrefixAddons($this->getPrefixSiCrumbGroups())
//				->setSuffixAddons($this->getSuffixSiCrumbGroups())
//				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
//
//		return $eiu->factory()->newGuiField($siField)
//				->setSaver(function () use ($eiu, $siField) {
//					$this->saveSiField($siField, $eiu);
//				});
//	}
//
//	function saveSiField(SiField $siField, Eiu $eiu) {
//		$eiu->field()->setValue($siField->getValue());
//	}

	function buildInGuiField(Eiu $eiu): PathPartInGuiField {
		$guiField = GuiFields::pathPartIn(mandatory: $this->isMandatory(),
				minlength: $this->getMinlength() ?? 3, maxlength: $this->getMaxlength() ?? 150,
				prefixAddons: $this->getPrefixSiCrumbGroups(), suffixAddons: $this->getSuffixSiCrumbGroups());

		if ($this->baseEiPropPath !== null && $eiu->entry()->isNew()) {
			$guiField->getSiField()->setBasedOnPropName($this->baseEiPropPath);
		}

		$guiField->setValue($eiu->field()->getValue());

		$guiField->setModel($eiu->field()->asGuiFieldModel(Mappers::pathPart(
				fn (string $pathPart) => !$this->pathPartUtil->containsPathPart($eiu, $pathPart),
				null, mandatory: $this->isMandatory())));

		return $guiField;
	}
}
