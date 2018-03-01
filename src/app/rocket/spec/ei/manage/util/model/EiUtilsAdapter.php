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

use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\EiType;
use n2n\persistence\orm\model\EntityModel;
use rocket\spec\ei\mask\EiMask;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\mapping\EiEntry;
use rocket\spec\ei\manage\EiEntityObj;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\LiveEiObject;
use n2n\reflection\ReflectionUtils;
use rocket\spec\ei\manage\DraftEiObject;
use rocket\user\model\LoginContext;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\draft\DraftValueMap;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\util\ex\IllegalStateException;
use rocket\spec\ei\manage\gui\GuiIdPath;

abstract class EiUtilsAdapter implements EiUtils {
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::getEiType()
	 */
	public function getEiType(): EiType {
		return $this->getEiMask()->getEiType();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::getNestedSetStrategy()
	 */
	public function getNestedSetStrategy() {
		return $this->getEiType()->getNestedSetStrategy();
	}
	
	/**
	 * @return \n2n\persistence\orm\model\EntityModel
	 */
	public function getEntityModel(): EntityModel {
		return $this->getEiType()->getEntityModel();
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::getClass()
	 */
	public function getClass(): \ReflectionClass {
		return $this->getEntityModel()->getClass();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::idToPid()
	 */
	public function idToPid($id): string {
		return $this->getEiType()->idToPid($id);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::pidToId()
	 */
	public function pidToId(string $pid) {
		return $this->getEiType()->pidToId($pid);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::getGenericLabel()
	 */
	public function getGenericLabel($eiObjectObj = null, N2nLocale $n2nLocale = null): string {
		return $this->determineEiMask($eiObjectObj)->getLabelLstr()->t($n2nLocale ?? $this->getN2nLocale());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::getGenericPluralLabel()
	 */
	public function getGenericPluralLabel($eiObjectObj = null, N2nLocale $n2nLocale = null): string {
		return $this->determineEiMask($eiObjectObj)->getPluralLabelLstr()->t($n2nLocale ?? $this->getN2nLocale());
	}
	
	public function getGenericIconType($eiObjectObj = null) {
		return $this->determineEiMask($eiObjectObj)->getIconType();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::createIdentityString()
	 */
	public function createIdentityString(EiObject $eiObject, bool $determineEiMask = true, 
			N2nLocale $n2nLocale = null): string {
		$eiMask = null;
		if ($determineEiMask) {
			$eiMask = $this->determineEiMask($eiObject);
		} else {
			$eiMask = $this->getEiMask();
		}
				
		return $eiMask->createIdentityString($eiObject, $n2nLocale ?? $this->getN2nLocale());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::determineEiType()
	 */
	public function determineEiType($eiObjectObj): EiType {
		if ($eiObjectObj === null) {
			return $this->getEiType();
		}
		
		ArgUtils::valType($eiObjectObj, array(EiObject::class, EiEntry::class, EiEntityObj::class, EiuEntry::class, 'object'), true);
				
		if ($eiObjectObj instanceof EiEntry) {
			return $eiObjectObj->getEiObject()->getEiEntityObj()->getEiType();
		}
		
		if ($eiObjectObj instanceof EiObject) {
			return $eiObjectObj->getEiEntityObj()->getEiType();
		}
		
		if ($eiObjectObj instanceof EiEntityObj) {
			return $eiObjectObj->getEiType();
		}
		
		if ($eiObjectObj instanceof Draft) {
			return $eiObjectObj->getEiEntityObj()->getEiType();
		}
		
		if ($eiObjectObj instanceof EiuEntry) {
			return $eiObjectObj->getEiEntityObj()->getEiType();
		}
		
		return $this->getEiType()->determineAdequateEiType(new \ReflectionClass($eiObjectObj));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::determineEiMask()
	 */
	public function determineEiMask($eiObjectObj): EiMask {
		if ($eiObjectObj === null) {
			return $this->getEiMask();
		}
	
		return $this->getEiMask()->determineEiMask($this->determineEiType($eiObjectObj));
	}
	
	/**
	 * @param mixed $eiObjectObj
	 * @return \rocket\spec\ei\EiEngine
	 */
	public function determineEiEngine($eiObjectObj) {
		return $this->determineEiMask($eiObjectObj)->getEiEngine();
	}
	
	/**
	 * @param mixed $guiIdPath
	 * @param mixed $eiTypeObj
	 * @throws \InvalidArgumentException
	 * @return boolean
	 */
	public function containsGuiProp($guiIdPath, $eiTypeObj = null) {
		return $this->determineEiEngine($eiTypeObj)->getGuiDefinition()->containsGuiProp(
				GuiIdPath::create($guiIdPath));
	}
	
	public function guiIdPathToEiPropPath($guiIdPath, $eiTypeObj = null) {
		try {
			return $this->determineEiEngine($eiTypeObj)->getGuiDefinition()->guiIdPathToEiPropPath(
					GuiIdPath::create($guiIdPath));
		} catch (\rocket\spec\ei\manage\gui\GuiException $e) {
			return null;
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::lookupEiObjectById()
	 */
	public function lookupEiObjectById($id, int $ignoreConstraintTypes = 0): EiObject {
		return new LiveEiObject($this->lookupEiEntityObj($id, $ignoreConstraintTypes));
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::isDraftingEnabled()
	 */
	public function isDraftingEnabled(): bool {
		return $this->getEiMask()->isDraftingEnabled();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::lookupDraftById()
	 */
	public function lookupDraftById(int $id): Draft {
		$draft = $this->getDraftManager()->find($this->getClass(), $id, 
				$this->getEiMask()->getEiEngine()->getDraftDefinition());
		
		if ($draft !== null) return $draft;
		
		throw new UnknownEntryException('Unknown draft with id: ' . $id);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::lookupEiObjectByDraftId()
	 */
	public function lookupEiObjectByDraftId(int $id): EiObject {
		return new DraftEiObject($this->lookupDraftById($id));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::lookupDraftsByEntityObjId()
	 */
	public function lookupDraftsByEntityObjId($entityObjId, int $limit = null, int $num = null): array {
		return $this->getDraftManager()->findByEntityObjId($this->getClass(), $entityObjId, $limit, $num, 
				$this->getEiMask()->getEiEngine()->getDraftDefinition());
	}
		
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::createEntityObj()
	 */
	public function createEntityObj() {
		return ReflectionUtils::createObject($this->getClass());
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::createEiObjectFromEiEntityObj()
	 */
	public function createEiObjectFromEiEntityObj($eiEntityObj): EiObject {
		if ($eiEntityObj instanceof EiEntityObj) {
			return new LiveEiObject($eiEntityObj);
		}
		
		if ($eiEntityObj !== null) {
			return LiveEiObject::create($this->getEiType(), $eiEntityObj);
		}
		
		return new LiveEiObject(EiEntityObj::createNew($this->getEiType()));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::createEiObjectFromDraft()
	 */
	public function createEiObjectFromDraft(Draft $draft): EiObject {
		return new DraftEiObject($draft);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::createNewEiObject()
	 */
	public function createNewEiObject(bool $draft = false, EiType $eiType = null): EiObject {
		if ($eiType === null) {
			$eiType = $this->getEiType();
		}
		
		if (!$draft) {
			return new LiveEiObject(EiEntityObj::createNew($eiType));
		}
		
		$loginContext = $this->getN2nContext()->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
	
		return new DraftEiObject($this->createNewDraftFromEiEntityObj(EiEntityObj::createNew($eiType)));
	}
	
	public function createNewDraftFromEiEntityObj(EiEntityObj $eiEntityObj) {
		$loginContext = $this->getN2nContext()->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
		
		return new Draft(null, $eiEntityObj, new \DateTime(),
				$loginContext->getCurrentUser()->getId(), new DraftValueMap());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::toEiuEntry()
	 */
	public function toEiuEntry($eiObjectObj): EiuEntry {
		return new EiuEntry($eiObjectObj, $this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::persist()
	 */
	public function persist($eiObjectObj, bool $flush = true) {
		if ($eiObjectObj instanceof Draft) {
			$this->persistDraft($eiObjectObj, $flush);
			return;
		}
		
		if ($eiObjectObj instanceof EiEntityObj) {
			$this->persistEiEntityObj($eiObjectObj, $flush);
			return;
		}
		
		$eiObject = EiuFactory::buildEiObjectFromEiArg($eiObjectObj, 'eiObjectObj', $this->getEiType());
		
		if ($eiObject->isDraft()) {
			$this->persistDraft($eiObject->getDraft(), $flush);
			return;
		}
		
		$this->persistEiEntityObj($eiObject->getEiEntityObj(), $flush);
	}
	
	private function persistDraft(Draft $draft, bool $flush) {
		$draftManager = $this->getDraftManager();
		
		if (!$draft->isNew()) {
			$draftManager->persist($draft);
		} else {
			$draftManager->persist($draft, $this->getEiMask()->determineEiMask(
					$draft->getEiEntityObj()->getEiType())->getEiEngine()->getDraftDefinition());
		}
		
		if ($flush) {
			$draftManager->flush();
		}
	}
	
	private function persistEiEntityObj(EiEntityObj $eiEntityObj, bool $flush) {
		$em = $this->em();
		$nss = $this->getNestedSetStrategy();
		if ($nss === null || $eiEntityObj->isPersistent()) {
			$em->persist($eiEntityObj->getEntityObj());
			if (!$flush) return;
			$em->flush();
		} else {
			if (!$flush) {
				throw new IllegalStateException(
						'Flushing is mandatory because EiEntityObj is new and has a NestedSetStrategy.');
			}
			
			$nsu = new NestedSetUtils($em, $this->getClass(), $nss);
			$nsu->insertRoot($eiEntityObj->getEntityObj());
		}
		
		if (!$eiEntityObj->isPersistent()) {
			$eiEntityObj->refreshId();
			$eiEntityObj->setPersistent(true);
		}
	}
}
