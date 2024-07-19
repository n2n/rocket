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
namespace rocket\op\ei\component\prop;

use rocket\op\ei\component\EiComponentNature;
use n2n\l10n\Lstr;
use n2n\util\ex\IllegalStateException;
use n2n\reflection\property\AccessProxy;
use rocket\op\ei\util\Eiu;
use rocket\op\ei\manage\idname\IdNameProp;
use rocket\op\ei\manage\idname\IdNamePropFork;
use rocket\op\ei\manage\gui\EiGuiProp;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\op\ei\manage\generic\GenericEiProperty;
use rocket\op\ei\manage\generic\ScalarEiProperty;
use rocket\op\ei\manage\critmod\quick\QuickSearchProp;
use rocket\op\ei\manage\draft\DraftProperty;
use rocket\op\ei\manage\critmod\filter\FilterProp;
use rocket\op\ei\manage\critmod\sort\SortProp;
use rocket\op\ei\manage\security\filter\SecurityFilterProp;
use rocket\op\ei\manage\frame\EiForkLink;
use rocket\op\ei\manage\frame\EiFrame;


interface EiPropNature extends EiComponentNature {
	
	/**
	 * @return Lstr
	 */
	public function getLabelLstr(): Lstr;
	
	/**
	 * @return Lstr|NULL
	 */
	public function getHelpTextLstr(): ?Lstr;
	
	/**
	 * @return bool
	 */
	public function isPrivileged(): bool;
	
	/**
	 * @return AccessProxy|null
	 */
	public function getNativeAccessProxy(): ?AccessProxy;
	
	/**
	 * @return bool
	 */
	public function isPropFork(): bool;
	
	/**
	 * @param object $object
	 * @return object
	 * @throws IllegalStateException if {@see self::isPropFork()} returns false
	 */
	public function getPropForkObject(object $object): object;

	/**
	 * @param Eiu $eiu
	 * @return IdNameProp|null
	 */
	function buildIdNameProp(Eiu $eiu): ?IdNameProp;

	/**
	 * @param Eiu $eiu
	 * @return IdNamePropFork|null
	 */
	function buildIdNamePropFork(Eiu $eiu): ?IdNamePropFork;

	/**
	 * @param Eiu $eiu {Eiu::frame()} is available.
	 */
	function buildEiField(Eiu $eiu): ?EiFieldNature;

	/**
	 * @return GuiProp|null null if not displayable
	 */
	function buildEiGuiProp(Eiu $eiu): ?EiGuiProp;

	/**
	 * @return GenericEiProperty|null
	 */
	function getGenericEiProperty(): ?GenericEiProperty;

	function buildScalarEiProperty(Eiu $eiu): ?ScalarEiProperty;

	function buildQuickSearchProp(Eiu $eiu): ?QuickSearchProp;

	function getDraftProperty(): ?DraftProperty;

	/**
	 * @param Eiu $eiu EiFrame {@see Eiu::frame()} is not available if the FilteProp is created for a filter to restrict
	 * {@see EiTypeExtension}s.
	 * @return FilterProp|null
	 */
	public function buildFilterProp(Eiu $eiu): ?FilterProp;

	/**
	 * @param Eiu $eiu EiFrame {@see Eiu::frame()} is not available if the FilteProp is created for a filter to restrict
	 * {@see EiTypeExtension}s.
	 * @return SortProp
	 */
	public function buildSortProp(Eiu $eiu): ?SortProp;

	/**
	 * @param Eiu $eiu
	 * @return SecurityFilterProp|null
	 */
	public function buildSecurityFilterProp(Eiu $eiu): ?SecurityFilterProp;

	public function createForkedEiFrame(Eiu $eiu, EiForkLink $eiForkLink): EiFrame;
}
