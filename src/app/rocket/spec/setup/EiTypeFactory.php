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
namespace rocket\spec\setup;

use n2n\core\TypeNotFoundException;
use n2n\reflection\ReflectionUtils;
use rocket\ei\EiType;
use n2n\util\type\attrs\DataSet;
use n2n\persistence\orm\model\EntityModelManager;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\component\prop\indepenent\IncompatiblePropertyException;
use n2n\persistence\orm\OrmException;
use rocket\ei\UnknownEiTypeException;
use n2n\reflection\attribute\AttributeSet;
use n2n\reflection\ReflectionContext;
use n2n\reflection\property\PropertiesAnalyzer;
use rocket\attribute\EiPreset;
use n2n\web\dispatch\target\build\Prop;
use n2n\reflection\ReflectionException;
use n2n\util\ex\err\ConfigurationError;
use n2n\util\type\TypeUtils;
use n2n\reflection\attribute\Attribute;
use n2n\persistence\orm\model\EntityModel;
use n2n\reflection\property\AccessProxy;
use rocket\ei\component\EiComponentCollection;
use rocket\ei\component\EiComponentCollectionListener;
use n2n\util\magic\MagicContext;
use rocket\ei\util\Eiu;

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
	 * @param \ReflectionClass $class
	 * @throws UnknownEiTypeException
	 * @return EiType
	 */
	public function create(string $id, \ReflectionClass $class, ?string $label, ?string $pluralLabel) {
		$className = $class->getName();
		try {
			$entityModel = $this->entityModelManager->getEntityModelByClass($class);
		} catch (OrmException $e) {
			throw new UnknownEiTypeException('Could not lookup EntityModel of ' . $className
					. '. Reason: ' . $e->getMessage(), 0, $e);
		}

		return new EiType($id, $this->specConfigLoader->moduleNamespaceOf($class), $entityModel, $label, $pluralLabel);
	}

	function assemble(EiType $eiType): void {
		$entityModel = $eiType->getEntityModel();
		$class = $entityModel->getClass();
		$attributeSet = ReflectionContext::getAttributeSet($class);

		$eiPresetProps = [];
		$eiPresetAttribute = $attributeSet->getClassAttribute(EiPreset::class);
		$eiPresetUtil = null;
		if ($eiPresetAttribute !== null) {
			$eiPresetUtil= new EiPresetUtil($eiPresetAttribute, $entityModel);
			$eiPresetProps = $eiPresetUtil->createEiPresetProps();
		}

		$eiTypeSetup = new EiTypeSetup($eiType, $eiPresetAttribute?->getInstance()->mode, $eiPresetProps);
		foreach (EiSetupPhase::cases() as $eiSetupPhase) {
			foreach ($this->specConfigLoader->getEiComponentNatureProviders() as $eiComponentNatureProvider) {
				$eiComponentNatureProvider->provide($eiTypeSetup, $eiSetupPhase);
			}
		}

		$uninitializedEiPresetProps = $eiTypeSetup->getUnassignedEiPresetProps();
		if ($eiPresetUtil !== null && !empty($uninitializedEiPresetProps)) {
			throw $eiPresetUtil->createUnassignedEiPresetPropsError($uninitializedEiPresetProps);
		}

		$eiModCollection = $eiType->getEiMask()->getEiModCollection();
		$eiModCollection->init($this->specConfigLoader->getN2NContext());
		$eiModCollection->registerListener($this->initListener);

		$eiPropCollection = $eiType->getEiMask()->getEiPropCollection();
		$eiPropCollection->init($this->specConfigLoader->getN2NContext());
		$eiPropCollection->registerListener($this->initListener);

		$eiCmdCollection = $eiType->getEiMask()->getEiCmdCollection();
		$eiCmdCollection->init($this->specConfigLoader->getN2NContext());
		$eiCmdCollection->registerListener($this->initListener);

		foreach ($eiType->getEiMask()->setupEiEngine() as $callback) {
			$callback(new Eiu($eiType->getEiMask()->getEiEngine()));
		};
	}


}



class InitListener implements EiComponentCollectionListener {

	function __construct(private MagicContext $magicContext) {
	}

	function eiComponentCollectionChanged(EiComponentCollection $collection) {
		$collection->init($this->magicContext);
	}
}