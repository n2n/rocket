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
namespace rocket\op\ei\manage\gui;

use n2n\l10n\N2nLocale;
use rocket\ui\gui\GuiMask;
use n2n\util\ex\IllegalStateException;
use rocket\ui\si\meta\SiProp;
use rocket\ui\gui\GuiProp;
use rocket\ui\gui\field\GuiFieldPath;
use rocket\op\ei\manage\DefPropPath;
use rocket\ui\si\meta\SiMaskIdentifier;
use rocket\ui\si\meta\SiMaskQualifier;
use rocket\ui\gui\GuiStructureDeclaration;
use rocket\ui\si\meta\SiStructureDeclaration;

class EiGuiMaskFactory {

	function __construct(private readonly EiGuiDefinition $eiGuiDefinition) {
	}

	function createSiMaskIdentifier(): SiMaskIdentifier {
		$eiMask = $this->eiGuiDefinition->getEiMask();

		$eiSiMaskId = new EiSiMaskId($eiMask->getEiTypePath(), $this->eiGuiDefinition->getViewMode());
		return new SiMaskIdentifier($eiSiMaskId->__toString(), $eiMask->getEiType()->getSupremeEiType()->getId());
	}

	public function createSiMaskQualifier(N2nLocale $n2nLocale): SiMaskQualifier {
		return new SiMaskQualifier($this->createSiMaskIdentifier(),
				$this->eiGuiDefinition->getEiMask()->getLabelLstr()->t($n2nLocale), $this->eiGuiDefinition->getEiMask()->getIconType());
	}

	function createGuiMask(N2nLocale $n2nLocale): GuiMask {
		$guiMask = new GuiMask(
				$this->createSiMaskQualifier($n2nLocale),
				$this->eiGuiDefinition->getGuiStructureDeclarations());

		$this->applyGuiProps($guiMask, $n2nLocale);

		$this->eiGuiDefinition->createGeneralGuiControlsMap()

		return $guiMask;
	}


	/**
	 * @param N2nLocale $n2nLocale
	 * @return SiProp[]
	 */
	private function applyGuiProps(GuiMask $guiMask, N2nLocale $n2nLocale): void {
		$deter = new ContextGuiFieldDeterminer($this->eiGuiDefinition);

		$siProps = [];
		foreach ($this->eiGuiDefinition->getDefPropPaths() as $defPropPath) {
			$eiProp = $this->eiGuiDefinition->getEiGuiPropWrapperByDefPropPath($defPropPath)->getEiProp();
			$eiPropNature = $eiProp->getNature();
			$label = $eiPropNature->getLabelLstr()->t($n2nLocale);
			$helpText = null;
			if (null !== ($helpTextLstr = $eiPropNature->getHelpTextLstr())) {
				$helpText = $helpTextLstr->t($n2nLocale);
			}

			$guiMask->putGuiProp(new GuiFieldPath(array_map(fn ($p) => (string) $p, $defPropPath->toArray())),
					new GuiProp($label, $helpText));

			$deter->reportDefPropPath($defPropPath);
		}

		/*return array_merge(*/$deter->applyContextGuiProps($guiMask, $n2nLocale)/*, $siProps)*/;
	}



}



class ContextGuiFieldDeterminer {
	/**
	 * @var DefPropPath[]
	 */
	private $defPropPaths = [];
	/**
	 * @var DefPropPath[]
	 */
	private $forkDefPropPaths = [];
	private $forkedDefPropPaths = [];

	function __construct(private readonly EiGuiDefinition $eiGuiDefinition) {

	}

	/**
	 * @param DefPropPath $defPropPath
	 */
	function reportDefPropPath(DefPropPath $defPropPath) {
		$defPropPathStr = (string) $defPropPath;

		$this->defPropPaths[$defPropPathStr] = $defPropPath;
		unset($this->forkDefPropPaths[$defPropPathStr]);
		unset($this->forkedDefPropPaths[$defPropPathStr]);

		$forkDefPropPath = $defPropPath;
		while ($forkDefPropPath->hasMultipleEiPropPaths()) {
			$forkDefPropPath = $forkDefPropPath->getPoped();
			$this->reportFork($forkDefPropPath, $defPropPath);
		}
	}

	/**
	 * @param DefPropPath $forkDefPropPath
	 * @param DefPropPath $defPropPath
	 */
	private function reportFork(DefPropPath $forkDefPropPath, DefPropPath $defPropPath) {
		$forkDefPropPathStr = (string) $forkDefPropPath;

		if (isset($this->defPropPaths[$forkDefPropPathStr])) {
			return;
		}

		if (!isset($this->forkDefPropPaths[$forkDefPropPathStr])) {
			$this->forkDefPropPaths[$forkDefPropPathStr] = [];
		}
		$this->forkedDefPropPaths[$forkDefPropPathStr][] = $defPropPath;
		$this->forkDefPropPaths[$forkDefPropPathStr] = $forkDefPropPath;

		if ($forkDefPropPath->hasMultipleEiPropPaths()) {
			$this->reportFork($forkDefPropPath->getPoped(), $forkDefPropPath);
		}
	}

	function applyContextGuiProps(GuiMask $guiMask, N2nLocale $n2nLocale): void {

		foreach ($this->forkDefPropPaths as $forkDefPropPath) {
			$eiProp = $this->eiGuiDefinition->getEiGuiPropWrapperByDefPropPath($forkDefPropPath)->getEiProp();


			$guiProp = (new GuiProp($eiProp->getNature()->getLabelLstr()->t($n2nLocale)))
					->setDescendantGuiPropNames(array_map(
							function ($defPropPath) { return (string) $defPropPath; },
							$this->forkedDefPropPaths[(string) $forkDefPropPath]));

			if (null !== ($helpTextLstr = $eiProp->getNature()->getHelpTextLstr())) {
				$guiProp->setHelpText($helpTextLstr->t($n2nLocale));
			}

			$guiMask->putGuiControl(new GuiFieldPath($forkDefPropPath->toArray()), $guiProp);
		}
	}
}