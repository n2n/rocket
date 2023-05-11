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
namespace rocket\op\ei\component;

use n2n\util\ex\IllegalStateException;
use rocket\op\ei\IdPath;
use rocket\op\ei\mask\EiMask;
use n2n\util\type\TypeUtils;
use n2n\util\magic\MagicContext;
use rocket\op\ei\util\Eiu;
use n2n\core\container\N2nContext;
use rocket\op\ei\EiException;
use n2n\util\col\ArrayUtils;

abstract class EiComponentCollection implements \IteratorAggregate, \Countable {
	private $elementName;
//	private $genericType;

	protected EiMask $eiMask;
	private $idPaths = array();
	private $eiComponents = array();
	private $rootElements = array();
	private $forkedElements = array();
	private ?EiComponentCollection $inheritedCollection = null;
	private $disabledInheritIds = array();

	/**
	 * @var EiComponent[]
	 */
	private $uninitializedEiComponents = [];

	public function __construct($elementName/*, $genericType*/) {
		$this->elementName = $elementName;
//		$this->genericType = $genericType;
	}

	protected function setEiMask(EiMask $eiMask) {
		$this->eiMask = $eiMask;
	}

	/**
	 * @throws IllegalStateException
	 * @return \rocket\op\ei\mask\EiMask
	 */
	public function getEiMask() {
		if ($this->eiMask !== null) {
			return $this->eiMask;
		}

		throw new IllegalStateException('No EiMask assigend to EiComponentCollection: ' . $this->elementName);
	}

	/**
	 * @param EiComponentCollection $inheritedCollection
	 */
	public function setInheritedCollection(EiComponentCollection $inheritedCollection = null) {
		$this->ensureNoEiEngine();

		$this->inheritedCollection = $inheritedCollection;
	}

	/**
	 * @return EiComponentCollection
	 */
	public function getInheritedCollection() {
		return $this->inheritedCollection;
	}

	private function ensureNoEiEngine() {
		if (!$this->eiMask->hasEiEngine()) return;

		throw new IllegalStateException('Can not add EiComponent because EiEngine for EiMask ' . $this->eiMask
				. ' is already initialized.');
	}

	/**
	 * @param string $id
	 * @param EiComponentNature $eiComponent
	 * @return string
	 * @throws InvalidEiConfigurationException
	 */
	protected function makeId(?string $id, EiComponentNature $eiComponent, array $forkIds = []): ?string {
		if ($id === null) {
			$baseId = $id = TypeUtils::buildTypeAcronym(get_class($eiComponent));
			$i = 0;
			do {
				$id = $baseId . '-' . $i++;
				$idPathStr = IdPath::implodeIds([...$forkIds, $id]);
			} while (isset($this->eiComponents[$idPathStr]) && $this->eiComponents[$idPathStr] !== $eiComponent);
		}

		if ($id === '' || IdPath::constainsSpecialIdChars($id)) {
			throw new \InvalidArgumentException('Invalid EiComponent id: ' . $id);
		}

		return $id;
	}

//	private ?MagicContext $initMagicContext = null;

	function setup(N2nContext $n2nContext) {
//		IllegalStateException::assertTrue($this->initMagicContext === null, 'Already initialized.');
//		$this->initMagicContext = $magicContext;

		while (null !== ($eiComponent = array_pop($this->uninitializedEiComponents))) {
			try {
				$eiComponent->getNature()->setup(new Eiu($eiComponent, $n2nContext));
			} catch (\RuntimeException $e) {
				throw new EiException('Setup of ' . $this->elementName . ' ' . $eiComponent
						. ' of EiType ' . $this->getEiMask()->getEiType() . ' failed. Reason: '
						. $e->getMessage(),
						0, $e);
			}
		}
	}

	protected function addEiComponent(IdPath $idPath, EiComponent $eiComponent): void {
		$idPathStr = (string) $idPath;

		if (isset($this->eiComponents[$idPathStr])) {
			if ($this->eiComponents[$idPathStr] === $eiComponent) {
				return;
			}

			throw new InvalidEiConfigurationException($this->elementName . ' with id "' . $idPathStr
					. '" already exists.');
		}

		$this->idPaths[$idPathStr] = $idPath;
		$this->eiComponents[$idPathStr] = $eiComponent;

		if (!$idPath->hasMultipleIds()) {
			$this->rootElements[$idPathStr] = $eiComponent;
		}

		$ids = $idPath->toArray();
		$lastId = array_pop($ids);
		$forkIdPathStr = IdPath::implodeIds($ids);

		if (!isset($this->forkedElements[$forkIdPathStr])) {
			$this->forkedElements[$forkIdPathStr] = array();
		}

		$this->forkedElements[$forkIdPathStr][$lastId] = $eiComponent;

		$this->uninitializedEiComponents[] = $eiComponent;

		$this->triggerChanged();
	}

	/**
	 * @param string[]|IdPath[] $idPaths
	 * @return void
	 */
	protected function changeEiComponentOrder(array $idPaths): void {
		$this->reorder($this->eiComponents, $idPaths);

//		order rootEiComponents and forkedEiComponents
//		$eiComponents = [];
//		foreach ($idPropPaths as $idPropPath) {
//			if ($idPropPath->)
//			$idPropPathStr = (string) $idPropPath;
//			if (isset([]))
//		}
	}

	private function reorder(array &$eiComponents, array $idPaths) {
		$oldArr = $eiComponents;
		$newArr = [];
		foreach ($idPaths as $idPath) {
			$idPathStr = (string) $idPath;
			if (!isset($oldArr[$idPathStr])) {
				continue;
			}

			$newArr[$idPathStr] = $oldArr[$idPathStr];
			unset($oldArr[$idPathStr]);
		}
		$eiComponents = array_merge($newArr, $oldArr);
	}

	/**
	 * @param string $id
	 * @return mixed
	 * @throws UnknownEiComponentException
	 */
	protected function getElementByIdPath(IdPath $idPath) {
		$idPathStr = (string) $idPath;

		if (isset($this->eiComponents[$idPathStr])) {
			return $this->eiComponents[$idPathStr];
		}

		if ($this->inheritedCollection !== null) {
			return $this->inheritedCollection->getElementByIdPath($idPath);
		}

		throw new UnknownEiComponentException('No ' . $this->elementName . ' with id \'' . $idPathStr
				. '\' found in ' . $this->eiMask . '.');
	}

	/**
	 * @param IdPath $forkIdPath
	 * @return array
	 */
	protected function getElementsByForkIdPath(IdPath $forkIdPath) {
		$elements = null;
		if ($forkIdPath->isEmpty()) {
			$elements = $this->rootElements;
		} else {
			$forkIdPathStr = (string) $forkIdPath;
			$elements = $this->forkedElements[$forkIdPathStr] ?? array();
		}

		if ($this->inheritedCollection !== null) {
			return array_merge($elements, $this->inheritedCollection->getElementsByForkIdPath($forkIdPath));
		}

		return $elements;
	}

	/**
	 * @param bool $checkInherited
	 * @return boolean
	 */
	public function isEmpty(bool $checkInherited = true): bool {
		if (!$checkInherited) {
			return empty($this->eiComponents);
		}

		return empty($this->eiComponents) && ($this->inheritedCollection === null
						|| $this->inheritedCollection->isEmpty());
	}

	/**
	 * @param string $idBase
	 * @return string
	 */
	protected function createUniqueId(string $idBase) {
		$idBase = IdPath::stripSpecialIdChars($idBase);
		if (0 < mb_strlen($idBase) && !$this->containsId($idBase, true, true)) {
			return $idBase;
		}

		for ($ext = 1; true; $ext++) {
			$id = $idBase . $ext;
			if (!$this->containsId($id, true, true)) {
				return $id;
			}
		}
	}

	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsId($id, $checkInherited = true): bool {
		if (isset($this->eiComponents[$id])) return true;

		if ($this->inheritedCollection !== null && $checkInherited &&
				$this->inheritedCollection->containsId($id, true, false)) {
			return true;
		}

		return false;
	}

	/* (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator(): \Traversable {
		return new \ArrayIterator($this->toArray());
	}

	/**
	 * @param string $independentOnly
	 * @return \rocket\op\ei\component\EiComponentNature[]
	 */
	public function toArray(bool $includeInherited = true): array {
		if ($this->inheritedCollection === null || !$includeInherited) return $this->eiComponents;

		$superElements = $this->filterEnableds($this->inheritedCollection->toArray(true));

		return $superElements + $this->eiComponents;
	}

	/**
	 * @todo remove probably
	 * @param array $elements
	 */
	private function filterEnableds(array $elements) {
		return $elements;
	}

	function count(): int {
		$num = count($this->eiComponents);
		if ($this->inheritedCollection !== null) {
			$num += $this->inheritedCollection->count();
		}
		return $num;
	}

	/**
	 * @var EiComponentCollectionListener[]
	 */
	private $listeners = [];

	function registerListener(EiComponentCollectionListener $listener) {
		$this->listeners[spl_object_hash($listener)] = $listener;
	}

	function unregisterListener(EiComponentCollectionListener $listener) {
		unset($this->listeners[spl_object_hash($listener)]);
	}

	private function triggerChanged() {
		foreach ($this->listeners as $listener) {
			$listener->eiComponentCollectionChanged($this);
		}
	}

}
