<?php

namespace rocket\spec;

use rocket\spec\source\ModularConfigSource;
use rocket\spec\extr\SpecConfigSourceDecorator;
use n2n\util\type\ArgUtils;
use n2n\util\type\attrs\DataMap;
use n2n\config\source\ConfigSource;
use n2n\util\magic\MagicContext;
use n2n\util\type\attrs\AttributesException;
use n2n\config\InvalidConfigurationException;
use n2n\util\magic\MagicLookupFailedException;

class SpecConfigLoader {

	private ?array $eiComponentNatureProviders = null;

	/**
	 * @param ModularConfigSource $moduleConfigSource
	 * @param string[] $moduleNamespaces Namespaces of all modules which spec configurations shall be loaded.
	 */
	function __construct(private ModularConfigSource $modularConfigSource, private array $moduleNamespaces,
			private MagicContext $magicContext) {
		ArgUtils::valArray($moduleNamespaces, 'string');
	}

	/**
	 * @return MagicContext
	 */
	function getMagicContext() {
		return $this->magicContext;
	}


	function reload(): void {
		$this->eiComponentNatureProviders = [];

		foreach ($this->moduleNamespaces as $moduleNamespace) {
			if (!$this->modularConfigSource->containsModuleNamespace($moduleNamespace)) {
				continue;
			}

			$configSource = $this->modularConfigSource->getOrCreateConfigSourceByModuleNamespace($moduleNamespace);

			array_push($this->eiComponentNatureProviders, ...$this->lookupEiComponentNatureProviders($configSource));
		}
	}

	const ATTR_EI_COMPONENT_NATURE_PROVIDERS = 'eiComponentNatureProviders';

	/**
	 * @param ConfigSource $configSource
	 * @return EiComponentNatureProvider[]
	 */
	private function lookupEiComponentNatureProviders(ConfigSource $configSource) {
		$eiComponentNatureProviders = [];
		$dataMap = new DataMap($configSource->readArray());

		try {
			foreach ($dataMap->optArray(self::ATTR_EI_COMPONENT_NATURE_PROVIDERS, 'string') as $lookupId) {
				$eiComponentNatureProvider = $this->magicContext->lookup($lookupId);;
				if ($eiComponentNatureProvider instanceof EiComponentNatureProvider) {
					throw new InvalidConfigurationException(get_class($eiComponentNatureProvider)
							. ' must implement ' . EiComponentNatureProvider::class . ' if used in '
							. self::ATTR_EI_COMPONENT_NATURE_PROVIDERS);
				}

				$eiComponentNatureProviders[] = $eiComponentNatureProvider;
			}
		} catch (AttributesException|MagicLookupFailedException|InvalidConfigurationException $e) {
			throw new InvalidConfigurationException('Configuration error in data source: ' . $configSource,0, $e);
		}

		return $eiComponentNatureProviders;
	}

	/**
	 * @param \Exception $previous
	 * @throws InvalidConfigurationException
	 */
	private function createDataSourceException(\Exception $previous) {

	}

	function getEiComponentNatureProviders(): array {
		if ($this->eiComponentNatureProviders === null) {
			$this->reload();
		}

		return $this->eiComponentNatureProviders;
	}

}