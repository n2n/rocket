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
namespace rocket\user\model\security;

use rocket\spec\ei\security\EiExecution;
use rocket\spec\ei\EiCommandPath;
use rocket\spec\ei\component\command\EiCommand;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\EiFieldPath;
use rocket\spec\ei\security\EiFieldAccess;
use rocket\spec\ei\manage\mapping\EiMapping;

class FullyGrantedEiExecution implements EiExecution {
	private $commandPath;
	private $eiCommand;

	public function __construct(EiCommandPath $commandPath, EiCommand $eiCommand = null) {
		$this->commandPath = $commandPath;
		$this->eiCommand = $eiCommand;
	}

	public function isGranted(): bool {
		return true;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\security\EiExecution::getEiCommandPath()
	 */
	public function getEiCommandPath(): EiCommandPath {
		return $this->commandPath;
	}


	public function hasEiCommand(): bool {
		return $this->eiCommand !== null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\security\EiExecution::getEiCommand()
	 */
	public function getEiCommand(): EiCommand {
		if ($this->eiCommand !== null) {
			return $this->eiCommand;
		}

		throw new IllegalStateException();
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\security\EiExecution::getEiMappingConstraint()
	 */
	public function getEiMappingConstraint() {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\security\EiExecution::getCriteriaConstraint()
	 */
	public function getCriteriaConstraint() {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\security\EiExecution::extEiCommandPath($ext)
	 */
	public function extEiCommandPath(string $ext) {
		$this->commandPath = $this->commandPath->ext($ext);
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\security\EiExecution::createEiFieldAccess($eiFieldPath)
	 */
	public function createEiFieldAccess(EiFieldPath $eiFieldPath): EiFieldAccess {
		return new FullEiFieldAccess();
	}
	
	public function buildEiCommandAccessRestrictor(EiMapping $eiMapping) {
		return null;
	}

}
