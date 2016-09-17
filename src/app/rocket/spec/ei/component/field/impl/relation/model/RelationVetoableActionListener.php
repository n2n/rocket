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
namespace rocket\spec\ei\component\field\impl\relation\model;

use rocket\spec\ei\component\field\impl\relation\RelationEiField;
use rocket\spec\ei\manage\veto\VetoableActionListener;
use n2n\reflection\CastUtils;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\l10n\MessageCode;
use rocket\core\model\Rocket;
use rocket\spec\ei\manage\LiveEiSelection;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use rocket\spec\ei\manage\veto\VetoableRemoveAction;
use rocket\spec\ei\manage\LiveEntry;
use rocket\spec\ei\manage\ManageState;
use n2n\core\container\N2nContext;

class RelationVetoableActionListener implements VetoableActionListener {
	const STRATEGY_PREVENT = 'prevent';
	const STRATEGY_UNSET = 'unset';
	const STRATEGY_SELF_REMOVE = 'selfRemove';
	
	private $relationEiField;
	private $strategy = true;
	
	public function __construct(RelationEiField $relationEiField, string $strategy) {
		$this->relationEiField = $relationEiField;
		$this->strategy = $strategy;
	}
	
	public function onRemove(VetoableRemoveAction $vetoableRemoveAction,
			N2nContext $n2nContext) {
		$vetoCheck = new VetoCheck($this->relationEiField, $targetLiveEntry, $vetoableRemoveAction, $n2nContext);
		
		switch ($this->strategy) {
			case self::STRATEGY_PREVENT:
				$vetoCheck->prevent();
				break;
			case self::STRATEGY_UNSET:
				$vetoCheck->release();
		}
	}
	
	public static function getStrategies(): array {
		return array(self::STRATEGY_PREVENT, self::STRATEGY_UNSET);
	}
}

class VetoCheck {
	private $relationEiField;
	private $targetLiveEntry;
	private $vetoableRemoveAction;
	
	public function __construct(RelationEiField $relationEiField, LiveEntry $targetLiveEntry, 
			VetoableRemoveAction $vetoableRemoveAction, N2nContext $n2nContext) {
		$this->relationEiField = $relationEiField;
		$this->targetLiveEntry = $targetLiveEntry;
		$this->vetoableRemoveAction = $vetoableRemoveAction;
		$this->n2nContext = $n2nContext;
	}
	
	public function prevent() {
		$num = 0;
		$entityObj = null;
		foreach ($this->findAll() as $entityObj) {
			if (!$this->vetoableRemoveAction->containsEntityObj($entityObj)) $num++;
		}
		
		if ($num === 0) return;
		
		$attrs = array('entry' => $this->createIdentityString($entityObj),
				'generic_label' => $this->getGenericLabel(), 
				'field' => $this->relationEiField->getLabelLstr()->t($this->n2nContext->getN2nLocale()),
				'target_entry' => $this->createTargetIdentityString(),
				'target_generic_label' => $this->getTargetGenericLabel());

		if ($num === 1) {
			$this->vetoableRemoveAction->registerVeto(new MessageCode('ei_impl_relation_remove_veto_err', $attrs));
		} else {
			$attrs['num_more'] = ($num - 1);
			$this->vetoableRemoveAction->registerVeto(new MessageCode('ei_impl_relation_remove_veto_one_and_more_err', 
					$attrs));
		}
	}
	
	public function release() {
		foreach ($this->findAll() as $entityObj) {
			if ($this->vetoableRemoveAction->containsEntityObj($entityObj)) continue;
			
			$that = $this;
			$this->vetoableRemoveAction->executeWhenApproved(function () use ($that) {
				$that->releaseEntityObj($entityObj);
			});
		}	
	}

	private function findAll() {
		$criteria = $this->createCriteria()->select('eo');
		return $criteria->toQuery()->fetchArray();
	}
	
	private function getRelationEntityProperty(): RelationEntityProperty {
		$entityProperty = $this->relationEiField->getEntityProperty();
		CastUtils::assertTrue($entityProperty instanceof RelationEntityProperty);
		return $entityProperty;
	}
	
	
	private function createCriteria() {
		$entityProperty = $this->getRelationEntityProperty();
		$manageState = $this->n2nContext->lookup(ManageState::class);
		CastUtils::assertTrue($manageState instanceof ManageState);
		$criteria = $manageState->getEntityManager()->createCriteria();
		
		$operator = ($this->isToOne() ? CriteriaComparator::OPERATOR_EQUAL : CriteriaComparator::OPERATOR_CONTAINS);
		
		$criteria
				->from($entityProperty->getEntityModel()->getClass(), 'eo')
				->where()->match(CrIt::p('eo', $entityProperty), $operator, 
						CrIt::c($this->targetLiveEntry->getEntityObj()));
		return $criteria;
	}
		
	private function isToOne(): bool {
		$entityProperty = $this->getRelationEntityProperty();
		return $entityProperty->getType() == RelationEntityProperty::TYPE_MANY_TO_ONE 
				|| $entityProperty->getType() == RelationEntityProperty::TYPE_ONE_TO_ONE;
	}
	
	private function releaseEntityObj($entityObj) {
		$objectPropertyAccessProxy = $this->relationEiField->getObjectPropertyAccessProxy();
		
		if ($this->isToOne()) {
			$objectPropertyAccessProxy->setValue($entityObj, null);
			return;
		}
		
		$currentTargetEntityObjs = $objectPropertyAccessProxy->getValue($entityObj);
		if ($currentTargetEntityObjs === null) {
			$currentTargetEntityObjs = new \ArrayObject();
		}
		
		IllegalStateException::assertTrue($currentTargetEntityObjs instanceof \ArrayObject);
		
		$targetEntityObj = $this->targetLiveEntry->getLiveObject();
		foreach ($currentTargetEntityObjs as $key => $currentTargetEntityObj) {
			if ($currentTargetEntityObj === $targetEntityObj) {
				$currentTargetEntityObjs->offsetUnset($key);
			}
		}
	}
	
	private function getGenericLabel(): string {
		return $this->relationEiField->getEiEngine()->getEiSpec()->getEiMaskCollection()->getOrCreateDefault()
				->getLabelLstr()->t($this->n2nContext->getN2nLocale());
	}
	
	private function createIdentityString($entityObj): string {
		$eiSpec = $this->relationEiField->getEiEngine()->getEiSpec();
		return $eiSpec->getEiMaskCollection()->getOrCreateDefault()->createIdentityString(
				LiveEiSelection::create($eiSpec, $entityObj), $this->n2nContext->getN2nLocale());
	}
	
	private function getTargetGenericLabel(): string {
		return $this->relationEiField->getEiFieldRelation()->getTargetEiMask()->getLabelLstr()
				->t($this->n2nContext->getN2nLocale());
	}
	
	private function createTargetIdentityString() {
		return $this->relationEiField->getEiFieldRelation()->getTargetEiMask()
				->createIdentityString(new LiveEiSelection($this->targetLiveEntry), $this->n2nContext->getN2nLocale());
	}
}
