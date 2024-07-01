<?php

namespace rocket\op\ei\manage\gui;

use rocket\ui\gui\GuiApiModel;
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

class EiGuiApiModel implements GuiApiModel {

	function __construct(private EiFrame $eiFrame) {

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
			throw new UnknownGuiElementException('Could not determine EiMask of ' . $eiSiMaskId->eiTypePath,
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

		$guiValueBoundary = new GuiValueBoundary($selector->lookupTreeLevel($eiEntry->getEiObject()));

		$eiGuiDefinition = $eiEntry->getEiMask()->getEiEngine()->getEiGuiDefinition($eiSiMaskId->viewMode);
		$guiValueBoundary->putGuiEntry($eiGuiDefinition->createGuiEntry($eiEntry, $eiFrame));


		$factory = new EiGuiDeclarationFactory($this->eiFrame->getN2nContext());

		return $factory->createEiGuiDeclaration($eiSiMaskId->viewMode, true, null)
				->createGuiValueBoundary($this->eiFrame, [$eiEntry], true);
	}

	function createGuiValueBoundary(string $maskId): GuiValueBoundary {
		$eiSiMaskId = $this->parseEiSiMaskId($maskId);

		$factory = new EiObjectFactory($this->eiFrame);

		try {
			$eiEntries = $factory->createPossibleNewEiEntries($eiSiMaskId->eiTypePath);
		} catch (EiException $e) {
			throw new UnknownGuiElementException('Failed to create new EiEntries for Mask: '
					. $eiSiMaskId->eiTypePath);
		}

		$factory = new EiGuiDeclarationFactory($this->eiFrame);

		foreach ($factory->createEiGuiEntries($eiEntries) as $eiGuiEntry) {

		}

	}

}