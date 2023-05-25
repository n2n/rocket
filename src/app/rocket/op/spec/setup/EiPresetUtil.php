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
use n2n\util\ex\IllegalStateException;
use n2n\util\StringUtils;
use n2n\reflection\property\PropertyAccessProxy;

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

		if ($this->eiPreset->mode?->isReadPropsMode()) {
			foreach ($propertiesAnalyzer->analyzeProperties(true, false) as $accessProxy) {
				$propertyName = $accessProxy->getPropertyName();

				if ((!$accessProxy->isReadable() && !$accessProxy->isWritable())
						|| $this->eiPreset->containsExcludeProp($propertyName)) {
					continue;
				}

				$eiPresetProps[$propertyName] = $this->createEiPresetProp($accessProxy,
						$this->eiPreset->containsEditProp($propertyName),
						$this->eiPreset->getPropLabel($propertyName));
			}
		} elseif ($this->eiPreset->mode?->isEditPropsMode()) {
			foreach ($propertiesAnalyzer->analyzeProperties(true, false) as $accessProxy) {
				$propertyName = $accessProxy->getPropertyName();

				if ((!$accessProxy->isReadable() && !$accessProxy->isWritable())
						|| $this->eiPreset->containsExcludeProp($propertyName)) {
					continue;
				}

				$propertyName = $accessProxy->getPropertyName();
				$eiPresetProps[$propertyName] = $this->createEiPresetProp($accessProxy,
						!$this->eiPreset->containsReadProp($propertyName) &&
								($this->eiPreset->containsEditProp($propertyName) || $accessProxy->isWritable()),
						$this->eiPreset->getPropLabel($propertyName));
			}
		}

		foreach ($this->eiPreset->readProps as $propertyName => $label) {
			if (isset($eiPresetProps[$propertyName])) {
				continue;
			}

			$this->ensureNotExcluded($propertyName);

			try {
				$eiPresetProps[$propertyName] = $this->createEiPresetProp(
						$propertiesAnalyzer->analyzeProperty($propertyName, false), false, $label);
			} catch (ReflectionException $e) {
				throw $this->createEiPresetAttributeError($propertyName, $e);
			}
		}

		foreach ($this->eiPreset->editProps as $propertyName => $label) {
			if ($this->eiPreset->containsReadProp($propertyName)) {
				throw $this->createEiPresetAttributeError($propertyName,
						message: 'Already defined in readProps.');
			}

			$this->ensureNotExcluded($propertyName);

			if (isset($eiPresetProps[$propertyName])) {
				if ($eiPresetProps[$propertyName]->isEditable()) {
					continue;
				}

				throw new IllegalStateException($propertyName);
			}

			try {
				$eiPresetProps[$propertyName] = $this->createEiPresetProp(
						$propertiesAnalyzer->analyzeProperty($propertyName), true, $label);
			} catch (ReflectionException $e) {
				throw $this->createEiPresetAttributeError($propertyName, $e);
			}
		}

		return $eiPresetProps;
	}

	private function ensureNotExcluded(string $propertyName) {
		if ($this->eiPreset->containsExcludeProp($propertyName)) {
			throw $this->createEiPresetAttributeError($propertyName,
					message: 'Also defined in excludedProps.');
		}
	}

	/**
	 * @param PropertyAccessProxy $propertyAccessProxy
	 * @param bool $editable
	 * @return EiPresetProp
	 */
	private function createEiPresetProp(PropertyAccessProxy $propertyAccessProxy, bool $editable, ?string $label) {
		$propertyName = $propertyAccessProxy->getPropertyName();
		$entityProperty = null;
		if ($this->entityModel->containsEntityPropertyName($propertyName)) {
			$entityProperty = $this->entityModel->getLevelEntityPropertyByName($propertyName);
		}

		return new EiPresetProp($propertyName, $propertyAccessProxy, $entityProperty, $editable,
				$label ?? StringUtils::pretty($propertyName));
	}

	/**
	 * @param array $unassignedEiPresetProps
	 * @return ConfigurationError
	 */
	function createUnassignedEiPresetPropsError(array $unassignedEiPresetProps) {
		ArgUtils::valArray($unassignedEiPresetProps, EiPresetProp::class);

		return $this->createAttributeError( 'No suitable EiProps found for the following properties in '
				. $this->entityModel->getClass()->getName() . ': '
				. implode(', ', array_map(fn (EiPresetProp $p) => $p->getName(), $unassignedEiPresetProps)));
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

		return $this->createAttributeError('Could not assign property \'' . $propertyName
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