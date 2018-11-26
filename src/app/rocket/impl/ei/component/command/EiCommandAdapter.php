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
namespace rocket\impl\ei\component\command;

use rocket\ei\component\command\EiCommand;
use rocket\impl\ei\component\EiComponentAdapter;
use rocket\ei\component\command\EiCommandWrapper;
use n2n\util\ex\IllegalStateException;

abstract class EiCommandAdapter extends EiComponentAdapter implements EiCommand {
	private $wrapper;
	
	public function setWrapper(EiCommandWrapper $wrapper) {
		$this->wrapper = $wrapper;
	}
	
	public function getWrapper(): EiCommandWrapper {
		if ($this->wrapper !== null) {
			return $this->wrapper;
		}
		
		throw new IllegalStateException(get_class($this) . ' is not assigned to a Wrapper.');
	}
	
	public function getId() {
		return (string) $this->wrapper->getEiCommandPath();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponent::__toString()
	 */
	public function __toString(): string {
		return (new \ReflectionClass($this))->getShortName()
				. ' (id: ' . ($this->wrapper ? $this->wrapper->getEiCommandPath() : 'unknown') . ')';
	}	
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponent::equals()
	 */
	public function equals($obj) {
		return $obj instanceof EiCommand && parent::equals($obj);
	}
}
