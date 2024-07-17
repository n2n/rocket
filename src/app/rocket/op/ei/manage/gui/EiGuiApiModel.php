<?php

namespace rocket\op\ei\manage\gui;

use rocket\ui\gui\GuiMask;
use rocket\op\ei\manage\frame\EiFrame;
use n2n\util\type\attrs\AttributesException;
use rocket\ui\gui\err\UnknownGuiElementException;
use rocket\op\ei\EiException;
use rocket\ui\gui\GuiValueBoundary;
use rocket\op\ei\manage\frame\EiObjectSelector;
use rocket\op\ei\manage\entry\UnknownEiObjectException;
use rocket\op\spec\TypePath;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\frame\EiObjectFactory;
use rocket\op\util\OpfControlResponse;
use rocket\ui\gui\GuiCallResponse;
use rocket\op\ei\manage\EiObject;
use rocket\ui\gui\api\GuiApiModel;

class EiGuiApiModel implements GuiApiModel {

	private \WeakMap $cachedEiEntriesMap;

	function __construct(private EiFrame $eiFrame) {
		$this->cachedEiEntriesMap = new \WeakMap();
	}

	/**
	 * @throws UnknownGuiElementException
	 */
	private function parseEiSiMaskId(string $maskId): EiSiMaskId {
		try {
			return EiSiMaskId::fromString($maskId);
		} catch (AttributesException $e) {
			throw new UnknownGuiElementException('Could not find EiMask. Corrupted si mask id: ' . $maskId,
					previous: $e);
		}
	}

	/**
	 * @throws UnknownGuiElementException
	 */
	private function determineGuiMaskByEiTypePath(TypePath $eiTypePath): EiMask {
		try {
			return $this->eiFrame->getContextEiEngine()->getEiMask()->determineEiMaskByEiTypePath($eiTypePath);
		} catch (EiException $e) {
			throw new UnknownGuiElementException('Could not determine EiMask of ' . $eiTypePath,
					previous: $e);
		}
	}

	function lookupGuiMask(string $maskId): GuiMask {
		$eiSiMaskId = $this->parseEiSiMaskId($maskId);
		$eiMask = $this->determineGuiMaskByEiTypePath($eiSiMaskId->eiTypePath);

		return $eiMask->getEiEngine()->obtainEiGuiMaskDeclaration($eiSiMaskId->viewMode, null)
				->createGuiMask($this->eiFrame);
	}

	function lookupGuiValueBoundary(string $maskId, string $entryId): GuiValueBoundary {
		$eiSiMaskId = $this->parseEiSiMaskId($maskId);

		$selector = new EiObjectSelector($this->eiFrame);
		try {
			$eiEntry = $selector->lookupEiEntry($selector->pidToId($entryId));
		} catch (UnknownEiObjectException|\InvalidArgumentException $e) {
			throw new UnknownGuiElementException(previous: $e);
		}

		$factory = new EiGuiEntryFactory($this->eiFrame);
		$guiValueBoundary = $factory->createGuiValueBoundary($eiSiMaskId->viewMode, [$eiEntry],
				$selector->lookupTreeLevel($eiEntry->getEiObject()));
		$this->cacheEiEntries($guiValueBoundary, [$eiEntry]);
		return $guiValueBoundary;
	}

	function createGuiValueBoundary(string $maskId): GuiValueBoundary {
		$eiSiMaskId = $this->parseEiSiMaskId($maskId);

		$factory = new EiObjectFactory($this->eiFrame);
		try {
			$eiEntries = $factory->createPossibleNewEiEntries($eiSiMaskId->eiTypePath);
		} catch (EiException $e) {
			throw new UnknownGuiElementException('Failed to create new EiEntries for Mask: '
					. $eiSiMaskId->eiTypePath, previous: $e);
		}

		$factory = new EiGuiValueBoundaryFactory($this->eiFrame);
		$guiValueBoundary =  $factory->create(null, $eiEntries, $eiSiMaskId->viewMode);
		$this->cacheEiEntries($guiValueBoundary, $eiEntries);
		return $guiValueBoundary;
	}

	/**
	 * @param string $maskId
	 * @param int $offset
	 * @param int $num
	 * @param string|null $quickSearchStr
	 * @return GuiValueBoundary[]
	 * @throws UnknownGuiElementException
	 */
	function lookupGuiValueBoundaries(string $maskId, int $offset, int $num, ?string $quickSearchStr): array {
		$eiSiMaskId = $this->parseEiSiMaskId($maskId);

		$selector = new EiObjectSelector($this->eiFrame);
		$eiEntryRecords = $selector->lookupEiEntries($offset, $num, $quickSearchStr);

		$factory = new EiGuiValueBoundaryFactory($this->eiFrame);
		$guiValueBoundaries = [];
		foreach ($eiEntryRecords as $eiEntryRecord) {
			$guiValueBoundaries[] = $factory->create($eiEntryRecord->treeLevel, [$eiEntryRecord->eiEntry],
					$eiSiMaskId->viewMode);
		}
		return $guiValueBoundaries;
	}

	/**
	 * @throws UnknownGuiElementException
	 */
	function countGuiValueBoundaries(string $maskId, ?string $quickSearchStr): int {
		$this->parseEiSiMaskId($maskId);

		$selector = new EiObjectSelector($this->eiFrame);
		return $selector->count($quickSearchStr);
	}

	function copyGuiValueBoundary(GuiValueBoundary $guiValueBoundary, string $maskId): GuiValueBoundary {
		$eiMaskId = $this->parseEiSiMaskId($maskId);

		$eiEntries = $this->getCachedEiEntries($guiValueBoundary);

		$factory = new EiGuiEntryFactory($this->eiFrame);
		$copiedGuiValueBoundary =  $factory->createGuiValueBoundary($eiMaskId->viewMode, $eiEntries, $guiValueBoundary->getTreeLevel());
		if ($guiValueBoundary->isEiGuiEntrySelected()) {
			$copiedGuiValueBoundary->selectGuiEntryByMaskId($guiValueBoundary);
		}
		$this->cacheEiEntries($guiValueBoundary, $eiEntries);
		return $copiedGuiValueBoundary;
	}

	private function cacheEiEntries(GuiValueBoundary $guiValueBoundary, array $eiEntries): void {
		$this->cachedEiEntriesMap->offsetSet($guiValueBoundary, $eiEntries);
	}

	/**
	 * @throws UnknownGuiElementException
	 */
	private function getCachedEiEntries(GuiValueBoundary $guiValueBoundary): array {
		if ($this->cachedEiEntriesMap->offsetExists($guiValueBoundary)) {
			return $this->cachedEiEntriesMap->offsetGet($guiValueBoundary);
		}

		throw new UnknownGuiElementException('Provided GuiValueBoundary is unknown to EiGuiApiModel: '
				. $guiValueBoundary);
	}

	function insertAfter(string $maskId, string $entryIds, string $afterEntryId): GuiCallResponse {
		$this->parseEiSiMaskId($maskId);

		$eiObjects = $this->lookupEiObjects([...$entryIds, $afterEntryId]);
		$afterEiObject = array_pop($eiObjects);
		$this->eiFrame->getAbility()->getSortAbility()->insertAfter($eiObjects, $afterEiObject);

		return new OpfControlResponse($this->eiFrame->getN2nContext());
	}

	function insertBefore(string $maskId, string $entryIds, string $beforeEntryId): GuiCallResponse {
		$this->parseEiSiMaskId($maskId);

		$eiObjects = $this->lookupEiObjects([...$entryIds, $beforeEntryId]);
		$beforeEiObject = array_pop($eiObjects);
		$this->eiFrame->getAbility()->getSortAbility()->insertBefore($eiObjects, $beforeEiObject);

		return new OpfControlResponse($this->eiFrame->getN2nContext());
	}

	function insertAsChildren(string $maskId, string $entryIds, string $parentId): GuiCallResponse {
		$this->parseEiSiMaskId($maskId);

		$eiObjects = $this->lookupEiObjects([...$entryIds, $parentId]);
		$parentEiObject = array_pop($eiObjects);
		$this->eiFrame->getAbility()->getSortAbility()->insertBefore($eiObjects, $parentEiObject);

		return new OpfControlResponse($this->eiFrame->getN2nContext());
	}

	/**
	 * @throws UnknownGuiElementException
	 * @param string[] $entryIds;
	 * @return EiObject[]
	 */
	private function lookupEiObjects(array $entryIds): array {
		try {
			$selector = new EiObjectSelector($this->eiFrame);
			$eiObjects = [];
			foreach ($entryIds as $entryId) {
				$eiObjects[] = $selector->lookupEiObject($entryId);
			}
			return $eiObjects;
		} catch (UnknownEiObjectException $e) {
			throw new UnknownGuiElementException(previous: $e);
		}
	}
}
