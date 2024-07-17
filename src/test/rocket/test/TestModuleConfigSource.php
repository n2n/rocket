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

namespace rocket\test;

use rocket\op\spec\source\ModularConfigSource;
use n2n\config\source\WritableConfigSource;
use n2n\config\source\impl\SimpleConfigSource;
use rocket\op\spec\setup\SpecConfigLoader;
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

