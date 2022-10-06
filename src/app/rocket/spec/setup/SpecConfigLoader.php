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

use rocket\spec\source\ModularConfigSource;
use rocket\spec\extr\SpecConfigSourceDecorator;
use n2n\util\type\ArgUtils;
use n2n\util\type\attrs\DataMap;
use n2n\config\source\ConfigSource;
use n2n\util\magic\MagicContext;
use n2n\util\type\attrs\AttributesException;
use n2n\config\InvalidConfigurationException;
use n2n\util\magic\MagicLookupFailedException;
use n2n\util\StringUtils;

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
					$eiComponentNatureProviders[] = $eiComponentNatureProvider;
					continue;
				}

				throw new InvalidConfigurationException(get_class($eiComponentNatureProvider)
						. ' must implement ' . EiComponentNatureProvider::class . ' if used in '
						. self::ATTR_EI_COMPONENT_NATURE_PROVIDERS);
			}
		} catch (AttributesException|MagicLookupFailedException|InvalidConfigurationException $e) {
			throw new InvalidConfigurationException('Configuration error in data source: ' . $configSource,0, $e);
		}

		return $eiComponentNatureProviders;
	}

	/**
	 * @param \ReflectionClass $class
	 * @return string
	 */
	function moduleNamespaceOf(\ReflectionClass $class) {
		$namespaceName = $class->getNamespaceName();

		foreach ($this->moduleNamespaces as $moduleNamespace) {
			if (StringUtils::startsWith($moduleNamespace, $namespaceName)) {
				return $moduleNamespace;
			}
		}

		return $namespaceName;
	}

	function getEiComponentNatureProviders(): array {
		if ($this->eiComponentNatureProviders === null) {
			$this->reload();
		}

		return $this->eiComponentNatureProviders;
	}

}