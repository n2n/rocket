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
namespace rocket\spec\ei\component\field\impl\adapter;

use n2n\util\ex\IllegalStateException;

use rocket\spec\ei\manage\draft\DraftProperty;
use rocket\spec\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\draft\stmt\PersistDraftStmtBuilder;
use rocket\spec\ei\manage\draft\SimpleDraftValueSelection;
use rocket\spec\ei\manage\draft\DraftManager;
use rocket\spec\ei\manage\draft\DraftValueSelection;
use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\draft\PersistDraftAction;
use rocket\spec\ei\manage\draft\RemoveDraftAction;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\mapping\impl\SimpleEiField;
use rocket\spec\ei\component\field\indepenent\EiPropConfigurator;
use rocket\spec\ei\manage\draft\stmt\RemoveDraftStmtBuilder;

abstract class DraftableEiPropAdapter extends EditableEiPropAdapter implements ConfDraftableEiProp, DraftProperty {
	protected $draftable = false;

	public function isDraftable(): bool {
		return $this->draftable;
	}
	
	/**
	 * @param bool $draftable
	 */
	public function setDraftable(bool $draftable) {
		$this->draftable = $draftable;
	}
	
	public function buildEiField(Eiu $eiu) {
		if (!$eiu->entry()->isDraft()) {
			return parent::buildEiField($eiu);
		}
	
		return new SimpleEiField($eiu->entry()->getEiObject(), 
				$this->getObjectPropertyAccessProxy(true)->getConstraint()->getLenientCopy(), 
				$this, $this, ($this->isReadOnly($eiu) ? null : $this));
	}
	
	public function isReadOnly(Eiu $eiu): bool {
		if (!$eiu->entry()->isDraft() || !$this->isDraftable()) {
			return parent::isReadOnly($eiu);
		}
		
// 		if (!$this->checkForWriteAccess($eiu->entry()->getEiMapping())) return true;
			
		return $this->standardEditDefinition->isReadOnly() || !$this->isDraftable();
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\component\field\impl\EditableEiPropAdapter::createEiConfigurator()
	 */
	public function createEiPropConfigurator(): EiPropConfigurator {
		$eiPropConfigurator = parent::createEiPropConfigurator();
		IllegalStateException::assertTrue($eiPropConfigurator instanceof AdaptableEiPropConfigurator);
		$eiPropConfigurator->registerConfDraftableEiProp($this);
		return $eiPropConfigurator;
	}
	
		
	public function getDraftProperty() {
		if ($this->draftable) {
			return $this;
		}
		
		throw new IllegalStateException('EiProp not draftable.');
	}
	
	public function write(EiObject $eiObject, $value) {
		if (!$this->isDraftable() || !$eiObject->isDraft()) {
			parent::write($eiObject, $value);
			return;
		}
		
		$eiObject->getDraftValueMap()->setValue(EiPropPath::from($this), $value);
	}
	
	public function read(EiObject $eiObject) {
		if (!$this->isDraftable() || !$eiObject->isDraft()) {
			return parent::read($eiObject);
		}
		
		return $eiObject->getDraftValueMap()->getValue(EiPropPath::from($this));
	}
	
	public function createDraftValueSelection(FetchDraftStmtBuilder $selectDraftStmtBuilder, DraftManager $dm, 
			N2nContext $n2nContext): DraftValueSelection {
		return new SimpleDraftValueSelection($selectDraftStmtBuilder->requestColumn(EiPropPath::from($this)));
	}
	
	public function supplyPersistDraftStmtBuilder($value, $oldValue, PersistDraftStmtBuilder $persistDraftStmtBuilder, 
			PersistDraftAction $persistDraftAction) {
		if ($value !== $oldValue) {
			$persistDraftStmtBuilder->registerColumnRawValue(EiPropPath::from($this), $value);
		}
	}
	
	public function supplyRemoveDraftStmtBuilder($value, $oldValue, RemoveDraftStmtBuilder $removeDraftStmtBuilder, 
			RemoveDraftAction $removeDraftAction) {
	}
	
	public function writeDraftValue($object, $value) {
		$this->getPropertyAccessProxy()->setValue($object, $value);
	}
	
// 	public function getDraftColumnName() {
// 		return $this->getEntityProperty()->getReferencedColumnName();
// 	}
	
// 	public function checkDraftMeta(Pdo $dbh) {
// 	}
	
// 	public function draftCopy($value) {
// 		return $value;
// 	}
	
// 	public function publishCopy($value) {
// 		return $value;
// 	}
	
// 	public function mapDraftValue($draftId, MappingJob $mappingJob, \ArrayObject $rawDataMap, \ArrayObject $mappedValues) {
// 		$this->getEntityProperty()->mapValue($mappingJob, $rawDataMap, $mappedValues);
// 	}
	
// 	public function supplyDraftPersistingJob($mappedValue, PersistingJob $persistingJob) {
// 		$this->getEntityProperty()->supplyPersistingJob($mappedValue, $persistingJob);
// 	}
	
// 	public function supplyDraftRemovingJob($mappedValue, RemovingJob $deletingJob) {
// 		$this->getEntityProperty()->supplyRemovingJob($mappedValue, $deletingJob);
// 	}
}
