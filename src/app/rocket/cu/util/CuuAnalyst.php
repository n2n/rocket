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
namespace rocket\cu\util;

use rocket\ei\manage\frame\EiFrame;
use n2n\core\container\N2nContext;
use rocket\ei\manage\ManageException;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\EiEntityObj;
use rocket\ei\manage\LiveEiObject;
use rocket\ei\manage\draft\Draft;
use rocket\ei\manage\DraftEiObject;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\EiPropPath;
use rocket\ei\EiType;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\manage\gui\EiEntryGuiAssembler;
use rocket\ei\EiEngine;
use rocket\spec\Spec;
use rocket\ei\EiTypeExtension;
use rocket\core\model\Rocket;
use rocket\ei\EiCmdPath;
use rocket\ei\util\spec\EiuContext;
use rocket\ei\util\spec\EiuEngine;
use rocket\ei\util\spec\EiuMask;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\util\gui\EiuEntryGui;
use rocket\ei\util\gui\EiuGuiFrame;
use rocket\ei\util\entry\EiuField;
use rocket\ei\util\spec\EiuCmd;
use rocket\ei\util\spec\EiuProp;
use rocket\ei\manage\entry\EiFieldMap;
use rocket\ei\util\entry\EiuFieldMap;
use rocket\ei\util\entry\EiuObject;
use n2n\util\type\TypeUtils;
use rocket\ei\manage\DefPropPath;
use rocket\spec\UnknownTypeException;
use n2n\util\ex\IllegalStateException;
use rocket\ei\util\gui\EiuGuiField;
use rocket\ei\util\gui\EiuGui;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\EiEntryGuiTypeDef;
use rocket\ei\util\gui\EiuEntryGuiTypeDef;
use rocket\ei\manage\gui\EiGuiModel;
use rocket\ei\util\gui\EiuGuiModel;
use rocket\ei\component\prop\EiProp;
use rocket\ei\component\command\EiCmd;
use rocket\ei\component\modificator\EiMod;
use rocket\ei\component\EiComponent;
use rocket\ei\component\UnknownEiComponentException;
use rocket\ei\manage\EiLaunch;
use rocket\ei\util\EiuPerimeterException;
use rocket\ei\util\Eiu;
use rocket\ei\util\EiuCtrl;
use rocket\cu\gui\CuGuiEntry;
use rocket\cu\gui\CuGui;
use rocket\cu\util\gui\CuuGuiEntry;
use InvalidArgumentException;
use n2n\reflection\ReflectionUtils;

class CuuAnalyst {

	
	protected ?N2nContext $n2nContext = null;
	protected ?Eiu $eiu = null;
	protected ?CuGuiEntry $cuGuiEntry = null;
	protected ?CuuGuiEntry $cuuGuiEntry = null;

	private ?array $unappliedEiArgs = null;
	
	public function applyEiArgs(...$eiArgs): void {
		$this->unappliedEiArgs = $eiArgs;
	}

	protected function ensureAppied(): void {
		if ($this->unappliedEiArgs === null) {
			return;
		}

		$eiArgs = $this->unappliedEiArgs;
		$this->unappliedEiArgs = null;

		
		foreach ($eiArgs as $eiArg) {
			if ($eiArg === null) {
				continue;
			}
			
			if ($eiArg instanceof N2nContext) {
				$this->n2nContext = $eiArg;
				continue;
			}

			if ($eiArg instanceof Eiu) {
				$this->eiu = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof CuGuiEntry) {
				$this->assignCuGuiEntry($eiArg);
				continue;
			}
			
			if ($eiArg instanceof CuuGuiEntry) {
				$this->assignCuuGuiEntry($eiArg);
				continue;
			}
			
			if ($eiArg instanceof Cuu) {
				$cuuAnalyst = $eiArg->getCuuAnalyst();
				$cuuAnalyst->ensureAppied();
				
				if ($cuuAnalyst->n2nContext !== null) {
					$this->n2nContext = $cuuAnalyst->n2nContext;
				}
				if ($cuuAnalyst->cuGuiEntry !== null) {
					$this->cuGuiEntry = $cuuAnalyst->cuGuiEntry;
				}
				if ($cuuAnalyst->cuuGuiEntry !== null) {
					$this->cuuGuiEntry = $cuuAnalyst->cuuGuiEntry;
				}
				if ($cuuAnalyst->eiu !== null) {
					$this->eiu = $cuuAnalyst->eiu;
				}
				
				continue;
			}
			
			throw new InvalidArgumentException('Invalid Cuu arg type: ' . TypeUtils::getTypeInfo($eiArg));
		}
	}

	private function assignCuuGuiEntry(CuuGuiEntry $cuuGuiEntry): void {
		if ($this->cuuGuiEntry === $cuuGuiEntry) {
			return;
		}
		
		$this->assignCuGuiEntry($cuuGuiEntry->getCuGuiEntry());
		$this->cuuGuiEntry = $cuuGuiEntry;
	}

	private function assignCuGuiEntry(CuGuiEntry $cuGuiEntry): void {
		if ($this->cuGuiEntry === $cuGuiEntry) {
			return;
		}
		
		ArgUtils::assertTrue($this->cuuGuiEntry === null, 'CuGuiEntry is not compatible.');
		
		$this->cuGuiEntry = $cuGuiEntry;
	}

	public function getN2nContext(bool $required): ?N2nContext {
		$this->ensureAppied();

		if (!$required || $this->n2nContext !== null) {
			return $this->n2nContext;
		}

		throw new EiuPerimeterException('Could not determine N2nContext.');
	}

	function getEiu(bool $required): ?Eiu {
		$this->ensureAppied();

		if (!$required || $this->eiu !== null) {
			return $this->eiu;
		}

		throw new CuuPerimeterException('Could not determine Eiu.');
	}

	public function getCuGuiEntry(bool $required): ?CuGuiEntry {
		$this->ensureAppied();

		if (!$required || $this->cuGuiEntry !== null) {
			return $this->cuGuiEntry;
		}
		
		throw new CuuPerimeterException('Could not determine CuGuiEntry.');
	}

	public function getCuuGuiEntry(bool $required): ?CuuGuiEntry {
		$this->ensureAppied();

		if ($this->cuuGuiEntry !== null) {
			return $this->cuuGuiEntry;
		}
		
		if ($this->cuGuiEntry !== null) {
			return $this->cuuGuiEntry = new CuuGuiEntry($this->cuGuiEntry, $this);
		}

		
		if (!$required) return null;
		
		throw new CuuPerimeterException('No CuuGuiEntry available.');
	}

}