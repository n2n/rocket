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
use rocket\ei\util\Eiu;
use rocket\ei\manage\idname\IdNameProp;
use rocket\ei\manage\idname\IdNamePropFork;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\generic\GenericEiProperty;
use rocket\ei\manage\generic\ScalarEiProperty;
use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\ei\manage\draft\DraftProperty;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\manage\critmod\sort\SortProp;
use rocket\ei\manage\critmod\sort\SortPropFork;
use rocket\ei\manage\security\filter\SecurityFilterProp;
use rocket\ei\manage\frame\EiForkLink;
use rocket\ei\manage\frame\EiFrame;
use n2n\util\ex\UnsupportedOperationException;


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
	 * @see \rocket\ei\component\EiComponentNature::equals()
	 */
	public function equals($obj) {
		return $obj instanceof EiPropNature && $this->getWrapper()->getEiPropPath()->equals(
				$obj->getWrapper()->getEiPropPath());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\EiPropNature::getLabelLstr()
	 */
	public function getLabelLstr(): Lstr {
		return Lstr::create($this->getLabel());
	}

	function getLabel() {
		return $this->label ?? $this->label = StringUtils::pretty((new \ReflectionClass($this))->getShortName());
	}

	function setLabel(string $label) {
		$this->label = $label;
		return $this;
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
	
	public function getPropertyAccessProxy(): ?AccessProxy {
		return null;
	}

	function buildIdNameProp(Eiu $eiu): ?IdNameProp {
		return null;
	}

	public function buildIdNamePropFork(Eiu $eiu): ?IdNamePropFork {
		return null;
	}

	function buildEiField(Eiu $eiu): ?EiField {
		return null;
	}

	function buildGuiProp(Eiu $eiu): ?GuiProp {
		return null;
	}

	public function getGenericEiProperty(): ?GenericEiProperty {
		return null;
	}

	function buildScalarEiProperty(Eiu $eiu): ?ScalarEiProperty {
		return null;
	}

	function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp {
		return null;
	}

	function getDraftProperty(): ?DraftProperty {
		return null;
	}

	function buildFilterProp(Eiu $eiu): ?FilterProp {
		return null;
	}

	function buildSortProp(Eiu $eiu): ?SortProp {
		return null;
	}

	function buildSortPropFork(Eiu $eiu): ?SortPropFork {
		return null;
	}

	function buildSecurityFilterProp(Eiu $eiu): ?SecurityFilterProp {
		return null;
	}

	/**
	 * @param Eiu $eiu
	 * @param EiForkLink $eiForkLink
	 * @return EiFrame
	 */
	public function createForkedEiFrame(Eiu $eiu, EiForkLink $eiForkLink): EiFrame {
		throw new UnsupportedOperationException(get_class($this) . ' can not be forked.');
	}
}