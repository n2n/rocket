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
namespace rocket\ei\util\spec;

use rocket\ei\mask\EiMask;
use rocket\core\model\Rocket;
use rocket\ei\component\prop\EiPropNature;
use rocket\ei\component\command\EiCmdNature;
use rocket\ei\component\modificator\EiModNature;
use n2n\util\ex\IllegalStateException;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;
use n2n\l10n\N2nLocale;
use rocket\ei\component\UnknownEiComponentException;
use rocket\si\meta\SiMaskQualifier;
use rocket\ei\EiType;

class EiuMask  {
	private $eiMask;
	private $eiuType;
	private $eiuEngine;
	private $eiuAnalyst;
	
	public function __construct(EiMask $eiMask, ?EiuEngine $eiuEngine, EiuAnalyst $eiuAnalyst) {
		$this->eiMask = $eiMask;
		$this->eiuEngine = $eiuEngine;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\ei\mask\EiMask
	 */
	public function getEiMask() {
		return $this->eiMask;
	}
	
	/**
	 * @return \rocket\spec\TypePath
	 */
	function getEiTypePath() {
		return $this->eiMask->getEiTypePath();
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuType
	 */
	function type() {
		if ($this->eiuType === null) {
			$this->eiuType = new EiuType($this->eiMask->getEiType(), $this->eiuAnalyst);
		}
		
		return $this->eiuType;
	}
	
	/**
	 * @return string
	 */
	public function getIconType() {
		return $this->eiMask->getIconType();
	}
	
	/**
	 * @return string
	 */
	public function getLabel(N2nLocale $n2nLocale = null) {
		return $this->eiMask->getLabelLstr()->t($n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
	
	public function getPluralLabel(N2nLocale $n2nLocale = null) {
		return $this->eiMask->getPluralLabelLstr()
				->t($n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuMask
	 */
	public function supremeMask() {
		if (!$this->eiMask->getEiType()->hasSuperEiType()) {
			return $this;
		}
		
		return new EiuMask($this->eiMask->determineEiMask($this->eiMask->getEiType()->getSupremeEiType(), true),
				null, $this->eiuAnalyst);
	}
	
	/**
	 * @param string[]|null $allowedSubEiTypeIds
	 * @param bool $includeAbstractTypes
	 * @return \rocket\ei\util\spec\EiuMask[]
	 */
	public function possibleMasks(array $allowedSubEiTypeIds = null, bool $includeAbstractTypes = false) {
		$eiuMasks = [];
		
		if ($this->eiTypeMatches($this->eiMask->getEiType(), $allowedSubEiTypeIds, $includeAbstractTypes)) {
			$eiuMasks[] = $this;
		}
		
		foreach ($this->eiMask->getEiType()->getAllSubEiTypes() as $subEiType) {
			if ($this->eiTypeMatches($subEiType, $allowedSubEiTypeIds, $includeAbstractTypes)) {
				$eiuMasks[] = new EiuMask($this->eiMask->determineEiMask($subEiType), null, $this->eiuAnalyst);
			}
		}
		
		return $eiuMasks;
	}
	
	/**
	 * @param EiType $eiType
	 * @param string[]|null $allowedSubEiTypeIds
	 * @param bool $includeAbstractTypes
	 * @return boolean
	 */
	private function eiTypeMatches($eiType, $allowedSubEiTypeIds, $includeAbstractTypes) {
		return ($includeAbstractTypes || !$eiType->isAbstract())
				&& ($allowedSubEiTypeIds === null || in_array($eiType->getId(), $allowedSubEiTypeIds));
	}
	
// 	public function extensionMasks() {
// 		$eiMasks = array();
// 		if (!$this->eiMask->isExtension()) {
// 			$eiMasks = $this->eiMask->getEiType()->getEiTypeExtensionCollection()->toArray();
// 		}
// 	}
	
	/**
	 * @param EiPropNature $eiProp
	 * @param bool $prepend
	 * @return EiuProp
	 */
	public function addProp(EiPropNature $eiProp, string $id = null) {
		return new EiuProp($this->eiMask->getEiPropCollection()->add($id, $eiProp)->getEiPropPath(), $this,
				$this->eiuAnalyst);
	}
	
	/**
	 * @param EiCmdNature $eiCmdNature
	 * @param bool $prepend
	 * @return EiuCmd
	 */
	public function addCmd(EiCmdNature $eiCmdNature, string $id = null) {
		return new EiuCmd($this->eiMask->getEiCmdCollection()->add($id, $eiCmdNature)->getEiCmdPath(), $this);
	}
	
	/**
	 * @param EiModNature $eiModificator
	 * @param bool $prepend
	 * @return \rocket\ei\util\spec\EiuEngine
	 */
	public function addMod(EiModNature $eiModificator, string $id = null) {
		$this->eiMask->getEiModCollection()->add($id, $eiModificator);
		return $this;
	}
	
	/**
	 * @param bool $required
	 * @return \rocket\ei\util\spec\EiuEngine|NULL
	 * @throws IllegalStateException
	 */
	public function engine(bool $required = true) {
		if (!$required && !$this->isEngineReady()) {
			return null;
		}
		
		if ($this->eiuEngine !== null) {
			return $this->eiuEngine;
		}
		
		return $this->eiuEngine = new EiuEngine($this->eiMask->getEiEngine(), $this, $this->eiuAnalyst);
	}
	
	public function getPropLabel($eiPropPath, N2nLocale $n2nLocale = null) {
		return $this->eiMask->getEiPropCollection()->getByPath(EiPropPath::create($eiPropPath))->getNature()->getLabelLstr()
				->t($n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
	
	public function getPropPluralLabel($eiPropPath, N2nLocale $n2nLocale = null) {
		return $this->eiMask->getEiPropCollection()->getByPath(EiPropPath::create($eiPropPath))->getNature()->getPluralLabelLstr()
				->t($n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
	
	public function getPropHelpText($eiPropPath, N2nLocale $n2nLocale = null) {
		$helpTextLstr = $this->eiMask->getEiPropCollection()->getByPath(EiPropPath::create($eiPropPath))
				->getHelpTextLstr();
		
		if ($helpTextLstr === null) {
			return null;
		}
				
		return $helpTextLstr->t($n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
	
	public function containsEiProp($eiPropPath) {
		return $this->eiEngine->getEiMask()->getEiPropCollection()->containsId(EiPropPath::create($eiPropPath));
	}
	
	/**
	 * @param string|EiPropPath|\rocket\ei\component\prop\EiPropNature $eiPropArg
	 * @param bool $required
	 * @return \rocket\ei\util\spec\EiuProp|null
	 *@throws UnknownEiComponentException
	 */
	public function prop($eiPropArg, bool $required = true) {
		$eiPropPath = EiPropPath::create($eiPropArg);
		try {
			$this->eiMask->getEiPropCollection()->getByPath($eiPropPath);
		} catch (UnknownEiComponentException $e) {
			if (!$required) return null;
			
			throw $e;
		}
		
		return new EiuProp($eiPropPath, $this, $this->eiuAnalyst);
	}
	
	/**
	 * @return boolean
	 */
	public function isEngineReady() {
		return $this->eiMask->hasEiEngine();
	}
	
	public function onEngineReady(\Closure $readyCallback) {
		if ($this->eiMask->hasEiEngine()) {
			$readyCallback($this->engine());
			return;
		}

		$this->eiMask->onEiEngineSetup(function () use ($readyCallback) {
			$readyCallback($this->engine());
		});
	}
	
	/**
	 * @return SiMaskQualifier
	 */
	public function createSiMaskQualifier(N2nLocale $n2nLocale = null) {
		return $this->eiMask->createSiMaskQualifier($n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
}