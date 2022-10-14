<?php

namespace rocket\test;

use rocket\spec\source\ModularConfigSource;
use n2n\config\source\WritableConfigSource;
use n2n\config\source\impl\SimpleConfigSource;
use rocket\spec\setup\SpecConfigLoader;
use rocket\impl\ei\component\provider\RocketEiComponentNatureProvider;
use n2n\util\ex\IllegalStateException;
use n2n\config\source\ConfigSource;

class TestModuleConfigSource implements ModularConfigSource {

	function __construct(private array $moduleNamespaces) {

	}

	public function getOrCreateConfigSourceByModuleNamespace(string $moduleNamespace): ConfigSource {
		if (!$this->containsModuleNamespace($moduleNamespace)) {
			throw new IllegalStateException();
		}

		if ($moduleNamespace !== 'rocket') {
			return new SimpleConfigSource([]);
		}

		return new SimpleConfigSource([
			SpecConfigLoader::ATTR_EI_COMPONENT_NATURE_PROVIDERS => [
				RocketEiComponentNatureProvider::class
			]
		]);
	}

	public function containsModuleNamespace(string $moduleNamespace): bool {
		return in_array($moduleNamespace, $this->moduleNamespaces);
	}

	public function hashCode(): string {
		return 'holeradio';
	}
}

