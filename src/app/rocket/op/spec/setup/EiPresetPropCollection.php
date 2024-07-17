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

use rocket\op\ei\EiPropPath;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\model\EntityPropertyCollection;

class EiPresetPropCollection {

	private array $eiPresetProps = [];
	/**
	 * @var EiPresetPropCollection[]
	 */
	private array $eiPresetPropCollections = [];

	function __construct(private readonly EiPropPath $parentEiPropPath,
			private readonly EntityPropertyCollection $entityPropertyCollection) {
	}

	function getEntityPropertyCollection(): EntityPropertyCollection {
		return $this->entityPropertyCollection;
	}

	function getParentEiPropPath(): EiPropPath {
		return $this->parentEiPropPath;
	}

	function add(EiPresetProp $eiPresetProp): void {
		$this->eiPresetProps[(string) $eiPresetProp->getEiPropPath()] = $eiPresetProp;
	}

	/**
	 * @return EiPresetProp[]
	 */
	function getAll(): array {
		return $this->eiPresetProps;
	}

	function containsEiPropPath(EiPropPath|string $eiPropPath): bool {
		return isset($this->eiPresetProps[(string) $eiPropPath]);
	}

	function addChildren(EiPresetPropCollection $eiPresetPropCollection): void {
		$this->eiPresetPropCollections[(string) $eiPresetPropCollection->getParentEiPropPath()] = $eiPresetPropCollection;
	}

	function getByEiPropPath(EiPropPath|string $eiPropPath): EiPresetProp {
		$eiPropPathStr = (string) $eiPropPath;
		if (isset($this->eiPresetProps[$eiPropPathStr])) {
			return $this->eiPresetProps[$eiPropPathStr];
		}

		throw new \OutOfBoundsException();
	}

	function getEiPropPaths(bool $descendantsIncluded): array {
		$eiPropPaths = [];
		foreach ($this->eiPresetProps as $eiPropPathStr => $eiPresetProp) {
			$eiPropPaths[$eiPropPathStr] = $eiPresetProp->getEiPropPath();

			if ($descendantsIncluded && isset($this->eiPresetPropCollections[$eiPropPathStr])) {
				$eiPropPaths += $this->eiPresetPropCollections[$eiPropPathStr]->getEiPropPaths(true);
			}
		}
		return $eiPropPaths;
	}

	/**
	 * @return EiPresetPropCollection[]
	 */
	function toArray(): array {
		$eiPresetPropCollections = [$this];
		foreach ($this->eiPresetPropCollections as $eiPresetPropCollection) {
			array_push($eiPresetPropCollections, ...$eiPresetPropCollection->toArray());
		}
		return $eiPresetPropCollections;
	}
}