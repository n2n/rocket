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
namespace rocket\op\spec\setup;

use rocket\op\ei\EiType;
use n2n\persistence\orm\model\EntityModelManager;
use n2n\persistence\orm\OrmException;
use rocket\op\ei\UnknownEiTypeException;
use n2n\reflection\ReflectionContext;
use rocket\attribute\EiPreset;
use n2n\util\ex\err\ConfigurationError;
use n2n\persistence\orm\model\EntityModel;
use rocket\op\ei\component\EiComponentCollection;
use rocket\op\ei\component\EiComponentCollectionListener;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\component\InvalidEiConfigurationException;
use rocket\op\spec\Spec;
use n2n\util\StringUtils;
use rocket\attribute\EiNestedSet;
use n2n\persistence\orm\util\NestedSetStrategy;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\attribute\EiDisplayScheme;
use rocket\ui\si\control\SiIconType;
use rocket\attribute\EiDefaultSort;
use rocket\op\ei\manage\critmod\sort\SortSettingGroup;
use rocket\op\ei\mask\EiMask;
use n2n\core\container\N2nContext;
use rocket\op\ei\EiPropPath;
use rocket\attribute\EiPreview;

class EiTypeFactory {

	private InitListener $initListener;

	public function __construct(private readonly SpecConfigLoader $specConfigLoader,
			private readonly EntityModelManager $entityModelManager) {

		$this->initListener = new InitListener($specConfigLoader->getN2NContext());
	}

	function getEntityModelManager(): EntityModelManager {
		return $this->entityModelManager;
	}

	/**
	 * @param string $id
	 * @param \ReflectionClass $class
	 * @return EiType|null
	 */
	public function build(\ReflectionClass $class, Spec $spec, bool $required): ?EiType {
		$attributeSet = ReflectionContext::getAttributeSet($class);
		$eiTypeAttribute = $attributeSet->getClassAttribute(\rocket\attribute\EiType::class);
		if ($eiTypeAttribute === null) {
			if (!$required) {
				return null;
			}

			throw new UnknownEiTypeException($class->getName() . ' is not annotated with attribute '
					. \rocket\attribute\EiType::class);
		}

		$eiTypeA = $eiTypeAttribute->getInstance();
		$label = $eiTypeA->label ?? StringUtils::pretty($class->getShortName());
		$pluralLabel = $eiTypeA->pluralLabel ?? $label;
		$icon = $eiTypeA->icon ?? SiIconType::ICON_ROCKET;

		return new EiType($this->classNameToId($class->getName()), $this->specConfigLoader->moduleNamespaceOf($class), $class,
				$label, $pluralLabel, $icon, $spec, $eiTypeA->identityStringPattern,
				function () use ($class) {
					return $this->getEntityModel($class);
				},
				function (EiType $eiType) {
					$this->checkForPreview($eiType);
					$this->checkForInheritance($eiType);
					$this->checkForNestedSet($eiType);
					$this->checkForDefaultSort($eiType);
					$this->checkForDisplayScheme($eiType);
					$this->assemble($eiType);
				});
	}

	private function classNameToId(string $className) {
		return str_replace('\\', '-', $className);
	}

	private function idToClassName(string $id) {
		return str_replace('-', '\\', $id);
	}


	private function getEntityModel(\ReflectionClass $class): EntityModel {
		try {
			return $this->entityModelManager->getEntityModelByClass($class);
		} catch (OrmException $e) {
			throw new UnknownEiTypeException('Could not lookup EntityModel of ' . $class->getName()
					. '. Reason: ' . $e->getMessage(), 0, $e);
		}
	}


	private function checkForDefaultSort(EiType $eiType): void {
		$eiDefaultSortAttribute = ReflectionContext::getAttributeSet($eiType->getClass())
				->getClassAttribute(EiDefaultSort::class);
		if ($eiDefaultSortAttribute === null) {
			return;
		}

		$eiDefaultSort = $eiDefaultSortAttribute->getInstance();
		$eiType->getEiMask()->getDef()->setDefaultSortSettingGroup(
				new SortSettingGroup($eiDefaultSort->getSortSettings()));
	}

	private function checkForNestedSet(EiType $eiType): void {
		$nestedSetAttribute = ReflectionContext::getAttributeSet($eiType->getClass())
				->getClassAttribute(EiNestedSet::class);
		if ($nestedSetAttribute === null) {
			return;
		}

		$nestedSet = $nestedSetAttribute->getInstance();
		try {
			$eiType->setNestedSetStrategy(new NestedSetStrategy(CrIt::p($nestedSet->leftProp),
					CrIt::p($nestedSet->rightProp)));
		} catch (\InvalidArgumentException $e) {
			throw new ConfigurationError($e->getMessage(), $nestedSetAttribute->getFile(),
					$nestedSetAttribute->getLine(), previous: $e);
		}
	}

	private function checkForDisplayScheme(EiType $eiType) {
		$displaySchemeAttribute = ReflectionContext::getAttributeSet($eiType->getClass())
				->getClassAttribute(EiDisplayScheme::class);
		if ($displaySchemeAttribute === null) {
			return;
		}

		$displaySchemeA = $displaySchemeAttribute->getInstance();
		$displayScheme = $eiType->getEiMask()->getDisplayScheme();

		$displayScheme->setOverviewDisplayStructure($displaySchemeA->compactDisplayStructure);
		$displayScheme->setBulkyDisplayStructure($displaySchemeA->bulkyDisplayStructure);
		$displayScheme->setDetailDisplayStructure($displaySchemeA->bulkyDetailDisplayStructure);
		$displayScheme->setEditDisplayStructure($displaySchemeA->bulkyEditDisplayStructure);
		$displayScheme->setAddDisplayStructure($displaySchemeA->bulkyAddDisplayStructure);

	}

	private function checkForPreview(EiType $eiType): void {
		$entityModel = $eiType->getEntityModel();
		$class = $entityModel->getClass();
		$attributeSet = ReflectionContext::getAttributeSet($class);
		$eiPreviewAttribute = $attributeSet->getClassAttribute(EiPreview::class);

		$eiType->getEiMask()->getDef()->setPreviewControllerLookupId(
				$eiPreviewAttribute?->getInstance()->previewControllerLookupId);
	}

	private function checkForInheritance(EiType $eiType) {
		$entityModel = $eiType->getEntityModel();
		$spec = $eiType->getSpec();

		if ($entityModel->hasSuperEntityModel()) {
			$superClass = $eiType->getEntityModel()->getSuperEntityModel()->getClass();

			try {
				$eiType->setSuperEiType($spec->getEiTypeByClass($superClass));
			} catch (UnknownEiTypeException $e) {
				throw new InvalidEiConfigurationException('EiType for ' . $eiType->getClass()->getName()
						. ' requires super EiType for ' . $superClass->getName(), 0, $e);
			}
		}

		foreach ($eiType->getEntityModel()->getSubEntityModels() as $subEntityModel) {
			$class = $subEntityModel->getClass();

			if ($spec->containsEiTypeClass($class)) {
				$spec->getEiTypeByClass($class)->ensureInitialized();
			}
		}
	}

	private function assemble(EiType $eiType): void {
		$entityModel = $eiType->getEntityModel();
		$class = $entityModel->getClass();
		$attributeSet = ReflectionContext::getAttributeSet($class);

		$eiPresetPropCollection = null;
		$eiPresetAttribute = $attributeSet->getClassAttribute(EiPreset::class);
		$eiPresetUtil = null;
		if ($eiPresetAttribute !== null) {
			$eiPresetUtil = new EiPresetPropCompiler(new EnhancedEiPreset($eiPresetAttribute), $entityModel,
					new EiPropPath([]));
			$eiPresetPropCollection = $eiPresetUtil->compile();
		}

		$eiTypeSetup = new EiTypeSetup($eiType, $eiPresetAttribute?->getInstance()->mode, $eiPresetPropCollection);
		foreach (EiSetupPhase::cases() as $eiSetupPhase) {
			foreach ($this->specConfigLoader->getEiComponentNatureProviders() as $eiComponentNatureProvider) {
				$eiComponentNatureProvider->provide($eiTypeSetup, $eiSetupPhase);
			}
		}
		$eiTypeSetup->finalize();

		$uninitializedEiPresetProps = $eiTypeSetup->getUnassignedEiPresetProps();
		if ($eiPresetUtil !== null && !empty($uninitializedEiPresetProps)) {
			throw $eiPresetUtil->createUnassignedEiPresetPropsError($uninitializedEiPresetProps);
		}

		$eiModCollection = $eiType->getEiMask()->getEiModCollection();
		$eiModCollection->setup($this->specConfigLoader->getN2NContext());
		$eiModCollection->registerListener($this->initListener);

		$eiPropCollection = $eiType->getEiMask()->getEiPropCollection();
		$eiPropCollection->setup($this->specConfigLoader->getN2NContext());
		$eiPropCollection->registerListener($this->initListener);

		$eiCmdCollection = $eiType->getEiMask()->getEiCmdCollection();
		$eiCmdCollection->setup($this->specConfigLoader->getN2NContext());
		$eiCmdCollection->registerListener($this->initListener);

		$this->initListener->finalize($eiType->getEiMask());
	}


}


class InitListener implements EiComponentCollectionListener {

	function __construct(private readonly N2nContext $n2nContext) {
	}

	function eiComponentCollectionChanged(EiComponentCollection $collection) {
		$collection->setup($this->n2nContext);
	}

	function finalize(EiMask $eiMask) {
		foreach ($eiMask->setupEiEngine($this->n2nContext) as $callback) {
			$callback(new Eiu($eiMask->getEiEngine()));
		};
	}
}