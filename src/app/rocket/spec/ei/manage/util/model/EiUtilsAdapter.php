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

use rocket\spec\ei\manage\util\model\FrameEiu;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\EiEntry;
use rocket\spec\ei\EiSpec;
use n2n\persistence\orm\model\EntityModel;
use rocket\spec\ei\mask\EiMask;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\LiveEntry;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\LiveEiEntry;
use n2n\reflection\ReflectionUtils;
use rocket\spec\ei\manage\DraftEiEntry;
use rocket\user\model\LoginContext;
use n2n\reflection\CastUtils;
use rocket\spec\ei\manage\draft\DraftValueMap;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\util\ex\IllegalStateException;

abstract class EiUtilsAdapter implements EiUtils {
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::getEiSpec()
	 */
	public function getEiSpec(): EiSpec {
		return $this->getEiMask()->getEiEngine()->getEiSpec();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::getNestedSetStrategy()
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
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::getClass()
	 */
	public function getClass(): \ReflectionClass {
		return $this->getEntityModel()->getClass();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::idToIdRep()
	 */
	public function idToIdRep($id): string {
		return $this->getEiSpec()->idToIdRep($id);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::idRepToId()
	 */
	public function idRepToId(string $idRep) {
		return $this->getEiSpec()->idRepToId($idRep);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::getGenericLabel()
	 */
	public function getGenericLabel($eiEntryObj = null, N2nLocale $n2nLocale = null): string {
		return $this->determineEiMask($eiEntryObj)->getLabelLstr()->t($n2nLocale ?? $this->getN2nLocale());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::getGenericPluralLabel()
	 */
	public function getGenericPluralLabel($eiEntryObj = null, N2nLocale $n2nLocale = null): string {
		return $this->determineEiMask($eiEntryObj)->getPluralLabelLstr()->t($n2nLocale ?? $this->getN2nLocale());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::createIdentityString()
	 */
	public function createIdentityString(EiEntry $eiEntry, bool $determineEiMask = true, 
			N2nLocale $n2nLocale = null): string {
		$eiMask = null;
		if ($determineEiMask) {
			$eiMask = $this->determineEiMask($eiEntry);
		} else {
			$eiMask = $this->getEiMask();
		}
				
		return $eiMask->createIdentityString($eiEntry, $n2nLocale ?? $this->getN2nLocale());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::determineEiSpec()
	 */
	public function determineEiSpec($eiEntryObj): EiSpec {
		if ($eiEntryObj === null) {
			return $this->getEiSpec();
		}
		
		ArgUtils::valType($eiEntryObj, array(EiEntry::class, EiMapping::class, LiveEntry::class, 'object'), true);
		
		if ($eiEntryObj instanceof EiMapping) {
			return $eiEntryObj->getEiEntry()->getLiveEntry()->getEiSpec();
		}
		
		if ($eiEntryObj instanceof EiEntry) {
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
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::determineEiMask()
	 */
	public function determineEiMask($eiEntryObj): EiMask {
		if ($eiEntryObj === null) {
			return $this->getEiMask();
		}
	
		return $this->getEiMask()->determineEiMask($this->determineEiSpec($eiEntryObj));
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::lookupEiEntryById()
	 */
	public function lookupEiEntryById($id, int $ignoreConstraintTypes = 0): EiEntry {
		return new LiveEiEntry($this->lookupLiveEntryById($id, $ignoreConstraintTypes));
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::isDraftingEnabled()
	 */
	public function isDraftingEnabled(): bool {
		return $this->getEiMask()->isDraftingEnabled();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::lookupDraftById()
	 */
	public function lookupDraftById(int $id): Draft {
		$draft = $this->getDraftManager()->find($this->getClass(), $id, 
				$this->getEiMask()->getEiEngine()->getDraftDefinition());
		
		if ($draft !== null) return $draft;
		
		throw new UnknownEntryException('Unknown draft with id: ' . $id);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::lookupEiEntryByDraftId()
	 */
	public function lookupEiEntryByDraftId(int $id): EiEntry {
		return new DraftEiEntry($this->lookupDraftById($id));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::lookupDraftsByEntityObjId()
	 */
	public function lookupDraftsByEntityObjId($entityObjId, int $limit = null, int $num = null): array {
		return $this->getDraftManager()->findByEntityObjId($this->getClass(), $entityObjId, $limit, $num, 
				$this->getEiMask()->getEiEngine()->getDraftDefinition());
	}
		
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::createEntityObj()
	 */
	public function createEntityObj() {
		return ReflectionUtils::createObject($this->getClass());
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::createEiEntryFromLiveEntry()
	 */
	public function createEiEntryFromLiveEntry($liveEntry): EiEntry {
		if ($liveEntry instanceof LiveEntry) {
			return new LiveEiEntry($liveEntry);
		}
		
		if ($liveEntry !== null) {
			return LiveEiEntry::create($this->getEiSpec(), $liveEntry);
		}
		
		return new LiveEiEntry(LiveEntry::createNew($this->getEiSpec()));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::createEiEntryFromDraft()
	 */
	public function createEiEntryFromDraft(Draft $draft): EiEntry {
		return new DraftEiEntry($draft);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::createNewEiEntry()
	 */
	public function createNewEiEntry(bool $draft = false, EiSpec $eiSpec = null): EiEntry {
		if ($eiSpec === null) {
			$eiSpec = $this->getEiSpec();
		}
		
		if (!$draft) {
			return new LiveEiEntry(LiveEntry::createNew($eiSpec));
		}
		
		$loginContext = $this->getN2nContext()->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
	
		return new DraftEiEntry($this->createNewDraftFromLiveEntry(LiveEntry::createNew($eiSpec)));
	}
	
	public function createNewDraftFromLiveEntry(LiveEntry $liveEntry) {
		$loginContext = $this->getN2nContext()->lookup(LoginContext::class);
		CastUtils::assertTrue($loginContext instanceof LoginContext);
		
		return new Draft(null, $liveEntry, new \DateTime(),
				$loginContext->getCurrentUser()->getId(), new DraftValueMap());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\util\model\FrameEiu::toEiuEntry()
	 */
	public function toEiuEntry($eiEntryObj): EiuEntry {
		return new EiuEntry($eiEntryObj, $this);
	}
	
	public function persist($eiEntryObj, bool $flush = true) {
		if ($eiEntryObj instanceof Draft) {
			$this->persistDraft($eiEntryObj, $flush);
			return;
		}
		
		if ($eiEntryObj instanceof LiveEntry) {
			$this->persistLiveEntry($eiEntryObj, $flush);
			return;
		}
		
		$eiEntry = EiuFactory::buildEiEntryFromEiArg($eiEntryObj, 'eiEntryObj', $this->getEiSpec());
		
		if ($eiEntry->isDraft()) {
			$this->persistDraft($eiEntry->getDraft(), $flush);
			return;
		}
		
		$this->persistLiveEntry($eiEntry->getLiveEntry(), $flush);
	}
	
	private function persistDraft(Draft $draft, bool $flush) {
		$draftManager = $this->getDraftManager();
		
		if (!$draft->isNew()) {
			$draftManager->persist($draft);
		} else {
			$draftManager->persist($draft, $this->getEiMask()->determineEiMask(
					$draft->getLiveEntry()->getEiSpec())->getEiEngine()->getDraftDefinition());
		}
		
		if ($flush) {
			$draftManager->flush();
		}
	}
	
	private function persistLiveEntry(LiveEntry $liveEntry, bool $flush) {
		$em = $this->em();
		$nss = $this->getNestedSetStrategy();
		if ($nss === null || $liveEntry->isPersistent()) {
			$em->persist($liveEntry->getEntityObj());
			if (!$flush) return;
			$em->flush();
		} else {
			if (!$flush) {
				throw new IllegalStateException(
						'Flushing is mandatory because LiveEntry is new and has a NestedSetStrategy.');
			}
			
			$nsu = new NestedSetUtils($em, $this->getClass(), $nss);
			$nsu->insertRoot($liveEntry->getEntityObj());
		}
		
		if (!$liveEntry->isPersistent()) {
			$liveEntry->refreshId();
			$liveEntry->setPersistent(true);
		}
	}
}