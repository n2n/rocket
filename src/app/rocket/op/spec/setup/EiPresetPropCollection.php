<?php

namespace rocket\op\spec\setup;

use rocket\op\ei\EiPropPath;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\model\EntityPropertyCollection;

class EiPresetPropCollection {

	private array $eiPresetProps = [];
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
				$eiPropPaths += $this->eiPresetPropCollections[$eiPropPathStr]->getAllEiPropPaths();
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