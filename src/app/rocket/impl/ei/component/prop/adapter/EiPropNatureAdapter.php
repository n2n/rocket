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
namespace rocket\impl\ei\component\prop\adapter;

use rocket\ei\component\prop\EiPropNature;
use n2n\l10n\Lstr;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\EiComponentNatureAdapter;
use rocket\ei\component\prop\EiProp;
use n2n\util\StringUtils;
use n2n\reflection\property\AccessProxy;

abstract class EiPropNatureAdapter extends EiComponentNatureAdapter implements EiPropNature {
	private $wrapper;
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\EiPropNature::isPrivileged()
	 */
	public function isPrivileged(): bool {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\EiPropNature::setWrapper()
	 */
	public function setWrapper(EiProp $wrapper) {
		$this->wrapper = $wrapper;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\EiPropNature::getWrapper()
	 */
	public function getWrapper(): EiProp {
		if ($this->wrapper !== null) {
			return $this->wrapper;
		}
		
		throw new IllegalStateException(get_class($this) . ' is not assigned to a Wrapper.');
	}
	
	/**
	 * @return \rocket\ei\mask\EiMask
	 */
	function getEiMask() {
		return $this->getWrapper()->getEiPropCollection()->getEiMask();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\EiComponentNatureAdapter::getIdBase()
	 */
	public function getIdBase(): ?string {
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponentNature::equals()
	 */
	public function equals($obj) {
		return $obj instanceof EiPropNature && $this->getWrapper()->getEiPropPath()->equals(
				$obj->getWrapper()->getEiPropPath());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponentNature::__toString()
	 */
	public function __toString(): string {
		return (new \ReflectionClass($this))->getShortName()
				. ' (id: ' . ($this->wrapper ? $this->wrapper->getEiPropPath() : 'unknown') . ')';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\EiPropNature::getLabelLstr()
	 */
	public function getLabelLstr(): Lstr {
		return Lstr::create(StringUtils::pretty($this->getWrapper()->getEiPropPath()->getLastId()));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\EiPropNature::getHelpTextLstr()
	 */
	public function getHelpTextLstr(): ?Lstr {
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\EiPropNature::isPropFork()
	 */
	public function isPropFork(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\EiPropNature::getPropForkObject()
	 */
	public function getPropForkObject(object $object): object {
		throw new IllegalStateException($this . ' is not a PropFork.');
	}
	
	public function getObjectPropertyAccessProxy(): ?AccessProxy {
		return null;
	}
}