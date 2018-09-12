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

use rocket\ei\manage\security\EiExecution;
use rocket\ei\EiCommandPath;
use rocket\ei\component\command\EiCommand;
use n2n\util\ex\IllegalStateException;

class FullyGrantedEiExecution implements EiExecution {
	private $commandPath;
	private $eiCommand;

	public function __construct(EiCommandPath $commandPath, ?EiCommand $eiCommand) {
		$this->commandPath = $commandPath;
		$this->eiCommand = $eiCommand;
	}

	public function isGranted(): bool {
		return true;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiExecution::getEiCommandPath()
	 */
	public function getEiCommandPath(): EiCommandPath {
		return $this->commandPath;
	}


	public function hasEiCommand(): bool {
		return $this->eiCommand !== null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiExecution::getEiCommand()
	 */
	public function getEiCommand(): EiCommand {
		if ($this->eiCommand !== null) {
			return $this->eiCommand;
		}

		throw new IllegalStateException();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiExecution::extEiCommandPath($ext)
	 */
	public function extEiCommandPath(string $ext) {
		$this->commandPath = $this->commandPath->ext($ext);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiExecution::isExecutableBy()
	 */
	public function isExecutableBy(EiCommandPath $eiCommandPath): bool {
		return true;
	}
}
