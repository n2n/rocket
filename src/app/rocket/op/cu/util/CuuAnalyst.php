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
namespace rocket\op\cu\util;

use n2n\core\container\N2nContext;
use n2n\util\type\ArgUtils;
use n2n\util\type\TypeUtils;
use rocket\op\ei\util\EiuPerimeterException;
use rocket\op\ei\util\Eiu;
use rocket\op\cu\gui\CuGuiEntry;
use rocket\op\cu\util\gui\CuuGuiEntry;
use InvalidArgumentException;

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