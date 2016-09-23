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
namespace rocket\spec\ei\manage\util\model;

use n2n\persistence\orm\model\EntityModel;
use rocket\spec\ei\manage\LiveEntry;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\util\NestedSetStrategy;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\EiSpec;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\draft\DraftManager;
use n2n\core\container\N2nContext;

interface EiUtils {
	
	/**
	 * @return EntityManager
	 */
	public function em(): EntityManager;
	
	/**
	 * @return N2nContext
	 */
	public function getN2nContext(): N2nContext;
	
	/**
	 * @return EiMask
	 */
	public function getEiMask(): EiMask;
	
	/**
	 * @return EiSpec
	 */
	public function getEiSpec(): EiSpec;
	
	/**
	 * @return NestedSetStrategy|null 
	 */
	public function getNestedSetStrategy();

	/**
	 * @return EntityModel
	 */
	public function getEntityModel(): EntityModel;
	
	/**
	 * @return \ReflectionClass
	 */
	public function getClass(): \ReflectionClass;
	
	/**
	 * @param mixed $id
	 * @return scalar
	 * @throws \InvalidArgumentException if null is passed as id.
	 */
	public function idToIdRep($id): string;
	
	/**
	 * @param string $idRep
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function idRepToId(string $idRep);
	
	/**
	 * @param object $eiEntryObj
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericLabel($eiEntryObj = null, N2nLocale $n2nLocale = null): string;
	
	/**
	 * @param object $eiEntryObj
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericPluralLabel($eiEntryObj = null, N2nLocale $n2nLocale = null): string;
	
	/**
	 * @param EiSelection $eiSelection
	 * @param bool $determineEiMask
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(EiSelection $eiSelection, bool $determineEiMask = true, 
			N2nLocale $n2nLocale = null): string;
	
	/**
	 * @param object $eiEntryObj
	 * @return EiSpec
	 */
	public function determineEiSpec($eiEntryObj): EiSpec;
	
	/**
	 * @param object $eiEntryObj
	 * @return EiMask
	 */
	public function determineEiMask($eiEntryObj): EiMask;
	
	/**
	 * @return N2nLocale
	 */
	public function getN2nLocale(): N2nLocale;
	
	/**
	 * @param mixed $id
	 * @param int $ignoreConstraintTypes
	 * @return bool
	 */
	public function containsId($id, int $ignoreConstraintTypes = 0): bool;
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::lookupLiveEntryById($id, $ignoreConstraints)
	 */
	public function lookupLiveEntryById($id, int $ignoreConstraintTypes = 0): LiveEntry;
	
	/**
	 * @param int $id
	 * @throws UnknownEntryException
	 * @return \rocket\spec\ei\manage\EiSelection
	 */
	public function lookupEiSelectionById($id, int $ignoreConstraintTypes = 0): EiSelection;
	
	/**
	 * @return bool
	 */
	public function isDraftingEnabled(): bool;
	
	/**
	 * @return \rocket\spec\ei\manage\draft\DraftManager
	 */
	public function getDraftManager(): DraftManager;
	
	/**
	 * @param int $id
	 * @return Draft
	 */
	public function lookupDraftById(int $id): Draft;
	
	/**
	 * @param int $id
	 * @return EiSelection
	 */
	public function lookupEiSelectionByDraftId(int $id): EiSelection;
	
	/**
	 * @param unknown $entityObjId
	 * @param int $limit
	 * @param int $num
	 * @return Draft[]
	 */
	public function lookupDraftsByEntityObjId($entityObjId, int $limit = null, int $num = null): array;
	
	/**
	 * @return object 
	 */
	public function createEntityObj();
	
	/**
	 * @param object $entity
	 * @return \rocket\spec\ei\manage\EiSelection
	 */
	public function createEiSelectionFromLiveEntry($liveEntry): EiSelection;
	
	/**
	 * @param Draft $draft
	 * @return EiSelection
	 */
	public function createEiSelectionFromDraft(Draft $draft): EiSelection;
	
	/**
	 * @param bool $draft
	 * @param EiSpec $eiSpec
	 * @return EiSelection
	 */
	public function createNewEiSelection(bool $draft = false, EiSpec $eiSpec = null): EiSelection;
	
	/**
	 * @param object $eiEntryObj
	 * @return EiEntryUtils
	 */
	public function toEiEntryUtils($eiEntryObj): EiEntryUtils;
}
