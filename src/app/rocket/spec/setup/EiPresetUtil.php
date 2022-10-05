<?php

namespace rocket\spec;

use rocket\attribute\EiPreset;
use n2n\persistence\orm\model\EntityModel;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\reflection\ReflectionException;
use n2n\reflection\property\AccessProxy;
use n2n\util\ex\err\ConfigurationError;
use n2n\util\type\TypeUtils;
use n2n\util\type\ArgUtils;
use n2n\reflection\attribute\Attribute;

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
	function createUnassignedEiPresetPropsErrror(array $unassignedEiPresetProps) {
		ArgUtils::valArray($unassignedEiPresetProps, EiPresetProp::class);

		return $this->createAttributeError($this->eiPreset, 'No suitable EiProps found for the following properties: '
				. implode(',' array_map(fn (EiPresetProp $p) => $p->getName())));
	}

	/**
	 * @param EiPreset $eiPreset
	 * @param string $propertyName
	 * @param \Throwable|null $previous
	 * @param string|null $message
	 * @return ConfigurationError
	 */
	private function createEiPresetAttributeError(string $propertyName, \Throwable $previous = null,
			string $message = null) {
		$attrPropName = $this->eiPreset->containsEditProp($propertyName) ? 'editProps' : 'readProps';

		return $this->createAttributeError($this->eiPreset, 'Could not use property \'' . $propertyName
				. '\' annotated in '
				. TypeUtils::prettyPropName(EiPreset::class, $attrPropName).
				. ($message === null ? '' : ' Reason: ' . $message), $previous);
	}



	/**
	 * @param string|null $message
	 * @param \Throwable|null $previous
	 * @return ConfigurationError
	 */
	private function createAttributeError(?string $message, \Throwable $previous = null) {
		return new ConfigurationError($message, $this->eiPresetAttribute->getFile(),
				$this->eiPresetAttribute->getLine(), previous: $previous);
	}
}