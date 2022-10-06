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

use rocket\attribute\EiPreset;
use n2n\persistence\orm\model\EntityModel;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\reflection\ReflectionException;
use n2n\reflection\property\AccessProxy;
use n2n\util\ex\err\ConfigurationError;
use n2n\util\type\TypeUtils;
use n2n\util\type\ArgUtils;
use n2n\reflection\attribute\Attribute;
use Throwable;

class EiPresetUtil {
	private EiPreset $eiPreset;

	function __construct(private readonly Attribute $eiPresetAttribute,
			private readonly EntityModel $entityModel) {
		$this->eiPreset = $this->eiPresetAttribute->getInstance();
	}

	/**
	 * @return EiPresetProp[]
	 */
	function createEiPresetProps() {
		$propertiesAnalyzer = new PropertiesAnalyzer($this->entityModel->getClass());

		$eiPresetProps = [];

		if ($this->eiPreset->mode->hasReadProps()) {
			foreach ($propertiesAnalyzer->analyzeProperties() as $accessProxy) {
				$propertyName = $accessProxy->getPropertyName();
				$eiPresetProps[$propertyName] = $this->createEiPresetProp($accessProxy,
						$this->eiPreset->containsEditProp($propertyName));
			}
		} elseif ($this->eiPreset->mode->hasEditProps()) {
			foreach ($propertiesAnalyzer->analyzeProperties() as $accessProxy) {
				$propertyName = $accessProxy->getPropertyName();
				$eiPresetProps[$propertyName] = $this->createEiPresetProp($accessProxy,
						!$this->eiPreset->containsReadProp($propertyName));
			}
		}

		foreach ($this->eiPreset->readProps as $propertyName) {
			if (isset($eiPresetProps[$propertyName])) {
				continue;
			}

			try {
				$eiPresetProps[$propertyName] = $this->createEiPresetProp(
						$propertiesAnalyzer->analyzeProperty($propertyName, false), false);
			} catch (ReflectionException $e) {
				throw $this->createEiPresetAttributeError( $propertyName, $e);
			}
		}

		foreach ($this->eiPreset->editProps as $propertyName) {
			if ($this->eiPreset->containsReadProp($propertyName)) {
				throw $this->createEiPresetAttributeError($propertyName,
						message: 'Already defined in readProps.');
			}

			if (isset($eiPresetProps[$propertyName])) {
				if (!$eiPresetProps[$propertyName]->isEditable()) {
					continue;
				}
			}

			try {
				$eiPresetProps[$propertyName] = $this->createEiPresetProp(
						$propertiesAnalyzer->analyzeProperty($propertyName), true);
			} catch (ReflectionException $e) {
				throw $this->createEiPresetAttributeError($this->eiPreset, $propertyName, $e);
			}
		}

		return $eiPresetProps;
	}

	/**
	 * @param AccessProxy $accessProxy
	 * @param bool $editable
	 * @return EiPresetProp
	 */
	private function createEiPresetProp(AccessProxy $accessProxy, bool $editable) {
		$propertyName = $accessProxy->getPropertyName();
		$entityProperty = null;
		if ($this->entityModel->containsEntityPropertyName($propertyName)) {
			$entityProperty = $this->entityModel->getLevelEntityPropertyByName($propertyName);
		}

		return new EiPresetProp($propertyName, $accessProxy, $entityProperty, $editable);
	}

	/**
	 * @param array $unassignedEiPresetProps
	 * @return ConfigurationError
	 */
	function createUnassignedEiPresetPropsError(array $unassignedEiPresetProps) {
		ArgUtils::valArray($unassignedEiPresetProps, EiPresetProp::class);

		return $this->createAttributeError( 'No suitable EiProps found for the following properties: '
				. implode(',', array_map(fn (EiPresetProp $p) => $p->getName(), $unassignedEiPresetProps)));
	}

	/**
	 * @param EiPreset $eiPreset
	 * @param string $propertyName
	 * @param Throwable|null $previous
	 * @param string|null $message
	 * @return ConfigurationError
	 */
	private function createEiPresetAttributeError(string $propertyName, Throwable $previous = null,
			string $message = null) {
		$attrPropName = $this->eiPreset->containsEditProp($propertyName) ? 'editProps' : 'readProps';

		return $this->createAttributeError($this->eiPreset, 'Could not use property \'' . $propertyName
				. '\' annotated in '
				. TypeUtils::prettyPropName(EiPreset::class, $attrPropName)
				. ($message === null ? '' : ' Reason: ' . $message), $previous);
	}



	/**
	 * @param string|null $message
	 * @param Throwable|null $previous
	 * @return ConfigurationError
	 */
	private function createAttributeError(?string $message, Throwable $previous = null) {
		return new ConfigurationError($message, $this->eiPresetAttribute->getFile(),
				$this->eiPresetAttribute->getLine(), previous: $previous);
	}
}