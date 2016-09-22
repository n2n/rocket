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

use rocket\spec\ei\manage\util\model\EiUtils;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\EiSpec;
use n2n\persistence\orm\model\EntityModel;
use rocket\spec\ei\mask\EiMask;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\LiveEntry;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\LiveEiSelection;
use n2n\reflection\ReflectionUtils;
use rocket\spec\ei\manage\DraftEiSelection;
use rocket\user\model\LoginContext;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\draft\DraftValueMap;

abstract class EiUtilsAdapter implements EiUtils {
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::getEiSpec()
	 */
	public function getEiSpec(): EiSpec {
		return $this->getEiMask()->getEiEngine()->getEiSpec();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::getNestedSetStrategy()
	 */
	public function getNestedSetStrategy() {
		return $this->getEiSpec()->getNestedSetStrategy();
	}
	
	/**
	 * @return \n2n\persistence\orm\model\EntityModel
	 */
	public function getEntityModel(): EntityModel {
		return $this->getEiSpec()->getEntityModel();
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
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::idToIdRep()
	 */
	public function idToIdRep($id): string {
		return $this->getEiSpec()->idToIdRep($id);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::idRepToId()
	 */
	public function idRepToId(string $idRep) {
		return $this->getEiSpec()->idRepToId($idRep);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::getGenericLabel()
	 */
	public function getGenericLabel($eiEntryObj = null, N2nLocale $n2nLocale = null): string {
		return $this->determineEiMask($eiEntryObj)->getLabelLstr()->t($n2nLocale ?? $this->getN2nLocale());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::getGenericPluralLabel()
	 */
	public function getGenericPluralLabel($eiEntryObj = null, N2nLocale $n2nLocale = null): string {
		return $this->determineEiMask($eiEntryObj)->getPluralLabelLstr()->t($n2nLocale ?? $this->getN2nLocale());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::createIdentityString()
	 */
	public function createIdentityString(EiSelection $eiSelection, bool $determineEiMask = true, 
			N2nLocale $n2nLocale = null): string {
		$eiMask = null;
		if ($determineEiMask) {
			$eiMask = $this->determineEiMask($eiSelection);
		} else {
			$eiMask = $this->getEiMask();
		}
				
		return $eiMask->createIdentityString($eiSelection, $n2nLocale ?? $this->getN2nLocale());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::determineEiSpec()
	 */
	public function determineEiSpec($eiEntryObj): EiSpec {
		if ($eiEntryObj === null) {
			return $this->getEiSpec();
		}
		
		ArgUtils::valType($eiEntryObj, array(EiSelection::class, EiMapping::class, LiveEntry::class, 'object'), true);
		
		if ($eiEntryObj instanceof EiMapping) {
			return $eiEntryObj->getEiSelection()->getLiveEntry()->getEiSpec();
		}
		
		if ($eiEntryObj instanceof EiSelection) {
			return $eiEntryObj->getLiveEntry()->getEiSpec();
		}
		
		if ($eiEntryObj instanceof LiveEntry) {
			return $eiEntryObj->getEiSpec();
		}
		
		if ($eiEntryObj instanceof Draft) {
			return $eiEntryObj->getLiveEntry()->getEiSpec();
		}
		
		return $this->getEiSpec()->determineAdequateEiSpec(new \ReflectionClass($eiEntryObj));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::determineEiMask()
	 */
	public function determineEiMask($eiEntryObj): EiMask {
		if ($eiEntryObj === null) {
			return $this->getEiMask();
		}
	
		return $this->getEiMask()->determineEiMask($this->determineEiSpec($eiEntryObj));
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::lookupEiSelectionById()
	 */
	public function lookupEiSelectionById($id, int $ignoreConstraintTypes = 0): EiSelection {
		return new LiveEiSelection($this->lookupLiveEntryById($id, $ignoreConstraintTypes));
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
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::lookupEiSelectionByDraftId()
	 */
	public function lookupEiSelectionByDraftId(int $id): EiSelection {
		return new DraftEiSelection($this->lookupDraftById($id));
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
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::createEiSelectionFromLiveEntry()
	 */
	public function createEiSelectionFromLiveEntry($liveEntry): EiSelection {
		if ($liveEntry instanceof LiveEntry) {
			return new LiveEiSelection($liveEntry);
		}
		
		if ($liveEntry !== null) {
			return LiveEiSelection::create($this->getEiSpec(), $liveEntry);
		}
		
		return new LiveEiSelection(LiveEntry::createNew($this->getEiSpec()));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::createEiSelectionFromDraft()
	 */
	public function createEiSelectionFromDraft(Draft $draft): EiSelection {
		return new DraftEiSelection($draft);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::createNewEiSelection()
	 */
	public function createNewEiSelection(bool $draft = false, EiSpec $eiSpec = null): EiSelection {
		if ($eiSpec === null) {
			$eiSpec = $this->getEiSpec();
		}
		
		if (!$draft) {
			return new LiveEiSelection(LiveEntry::createNew($eiSpec));
		}
		
		$loginContext = $this->getN2nContext()->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
	
		return new DraftEiSelection(new Draft(null, LiveEntry::createNew($eiSpec), new \DateTime(),
				$loginContext->getCurrentUser()->getId(), new DraftValueMap()));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\EiUtils::toEiEntryUtils()
	 */
	public function toEiEntryUtils($eiEntryObj): EiEntryUtils {
		return new EiEntryUtils($eiEntryObj, $this);
	}
}