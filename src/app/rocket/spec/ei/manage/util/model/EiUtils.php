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
use rocket\spec\ei\manage\EiEntityObj;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\util\NestedSetStrategy;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\EiType;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\EiObject;
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
	 * @return EiType
	 */
	public function getEiType(): EiType;
	
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
	 * @return string
	 * @throws \InvalidArgumentException if null is passed as id.
	 */
	public function idToPid($id): string;
	
	/**
	 * @param string $pid
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function pidToId(string $pid);
	
	/**
	 * @param object $eiObjectObj
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericLabel($eiObjectObj = null, N2nLocale $n2nLocale = null): string;
	
	/**
	 * @param object $eiObjectObj
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericPluralLabel($eiObjectObj = null, N2nLocale $n2nLocale = null): string;
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $determineEiMask
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(EiObject $eiObject, bool $determineEiMask = true, 
			N2nLocale $n2nLocale = null): string;
	
	/**
	 * @param object $eiObjectObj
	 * @return EiType
	 */
	public function determineEiType($eiObjectObj): EiType;
	
	/**
	 * @param object $eiObjectObj
	 * @return EiMask
	 */
	public function determineEiMask($eiObjectObj): EiMask;
	
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
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::lookupEiEntityObj($id, $ignoreConstraints)
	 */
	public function lookupEiEntityObj($id, int $ignoreConstraintTypes = 0): EiEntityObj;
	
	/**
	 * @param int $id
	 * @throws UnknownEntryException
	 * @return \rocket\spec\ei\manage\EiObject
	 */
	public function lookupEiObjectById($id, int $ignoreConstraintTypes = 0): EiObject;
	
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
	 * @return EiObject
	 */
	public function lookupEiObjectByDraftId(int $id): EiObject;
	
	/**
	 * @param mixed $entityObjId
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
	 * @return \rocket\spec\ei\manage\EiObject
	 */
	public function createEiObjectFromEiEntityObj($eiEntityObj): EiObject;
	
	/**
	 * @param Draft $draft
	 * @return EiObject
	 */
	public function createEiObjectFromDraft(Draft $draft): EiObject;
	
	/**
	 * @param bool $draft
	 * @param EiType $eiType
	 * @return EiObject
	 */
	public function createNewEiObject(bool $draft = false, EiType $eiType = null): EiObject;
	
	/**
	 * @param object $eiObjectObj
	 * @return EiuEntry
	 */
	public function toEiuEntry($eiObjectObj): EiuEntry;
	
	public function persist($eiObjectObj, bool $flush = true);
}
