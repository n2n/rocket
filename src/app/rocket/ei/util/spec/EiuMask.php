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
use rocket\ei\component\prop\EiProp;
use rocket\ei\component\command\EiCommand;
use rocket\ei\component\modificator\EiModificator;
use n2n\util\ex\IllegalStateException;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;
use n2n\l10n\N2nLocale;
use rocket\ei\component\UnknownEiComponentException;

class EiuMask  {
	private $eiMask;
	private $eiuEngine;
	private $eiuAnalyst;
	
	public function __construct(EiMask $eiMask, EiuEngine $eiuEngine = null, EiuAnalyst $eiuAnalyst = null) {
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
	 * @return \rocket\ei\EiType
	 */
	public function getEiType() {
		return $this->eiMask->getEiType();
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
	public function getLabel() {
		return (string) $this->eiMask->getLabelLstr();
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuMask
	 */
	public function supremeMask() {
		if (!$this->eiMask->getEiType()->hasSuperEiType()) {
			return $this;
		}
		
		return new EiuMask($this->eiMask->getEiType()->getSupremeEiType()->getEiMask(),
				null, $this->eiuAnalyst);
	}
	
// 	public function extensionMasks() {
// 		$eiMasks = array();
// 		if (!$this->eiMask->isExtension()) {
// 			$eiMasks = $this->eiMask->getEiType()->getEiTypeExtensionCollection()->toArray();
// 		}
// 	}
	
	/**
	 * @param EiProp $eiProp
	 * @param bool $prepend
	 * @return \rocket\ei\util\spec\EiuEngine
	 */
	public function addEiProp(EiProp $eiProp, bool $prepend = false) {
		$this->eiMask->getEiPropCollection()->add($eiProp, $prepend);
		return $this;
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @param bool $prepend
	 * @return \rocket\ei\util\spec\EiuEngine
	 */
	public function addEiCommand(EiCommand $eiCommand, bool $prepend = false) {
		$this->eiMask->getEiCommandCollection()->add($eiCommand, $prepend);
		return $this;
	}
	
	/**
	 * @param EiModificator $eiModificator
	 * @param bool $prepend
	 * @return \rocket\ei\util\spec\EiuEngine
	 */
	public function addEiModificator(EiModificator $eiModificator, bool $prepend = false) {
		$this->eiMask->getEiModificatorCollection()->add($eiModificator, $prepend);
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
		return $this->eiMask->getEiPropCollection()->getByPath(EiPropPath::create($eiPropPath))->getLabelLstr()
				->t($n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
	
	public function getPropPluralLabel($eiPropPath, N2nLocale $n2nLocale = null) {
		return $this->eiMask->getEiPropCollection()->getByPath(EiPropPath::create($eiPropPath))->getPluralLabelLstr()
				->t($n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
	
	public function containsEiProp($eiPropPath) {
		return $this->eiEngine->getEiMask()->getEiPropCollection()->containsId(EiPropPath::create($eiPropPath));
	}
	
	/**
	 * @param string|EiPropPath|\rocket\ei\component\prop\EiProp $eiPropArg
	 * @param bool $required
	 * @throws UnknownEiComponentException
	 * @return \rocket\ei\util\spec\EiuProp|null
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
			$readyCallback(new Eiu($this->n2nContext, $this));
		}
		
		$that = $this;
		$this->eiMask->onEiEngineSetup(function () use ($readyCallback, $that) {
			$readyCallback($that->engine());
		});
	}
}