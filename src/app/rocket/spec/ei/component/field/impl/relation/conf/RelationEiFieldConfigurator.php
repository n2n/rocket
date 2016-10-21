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
namespace rocket\spec\ei\component\field\impl\relation\conf;

use rocket\spec\ei\component\field\impl\relation\RelationEiField;
use rocket\spec\ei\component\field\impl\adapter\AdaptableEiFieldConfigurator;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\spec\ei\component\EiSetupProcess;
use rocket\spec\ei\component\field\impl\relation\model\relation\EiFieldRelation;
use rocket\spec\ei\mask\UnknownEiMaskException;
use rocket\spec\ei\component\UnknownEiComponentException;
use rocket\spec\ei\component\field\impl\relation\SimpleRelationEiFieldAdapter;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use rocket\spec\ei\component\field\impl\relation\ToManyEiFieldAdapter;
use rocket\spec\ei\component\field\impl\relation\EmbeddedOneToOneEiField;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use rocket\spec\ei\component\UnknownException;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\core\model\Rocket;
use n2n\util\config\LenientAttributeReader;
use rocket\spec\config\UnknownSpecException;
use rocket\spec\ei\component\field\impl\relation\model\RelationVetoableActionListener;
use rocket\spec\ei\component\field\impl\relation\model\relation\SelectEiFieldRelation;
use n2n\util\config\InvalidConfigurationException;

class RelationEiFieldConfigurator extends AdaptableEiFieldConfigurator {
	const ATTR_TARGET_MASK_KEY = 'targetEiMaskId';
	const ATTR_MIN_KEY = 'min';
	const ATTR_MAX_KEY = 'max';
	const ATTR_REPLACEABLE_KEY = 'replaceable';
	const ATTR_TARGET_REMOVAL_STRATEGY_KEY = 'targetRemovalStrategy';
	const OPTION_FILTERED_KEY = 'filtered';
	const OPTION_EMBEDDED_ADD_KEY = 'embeddedAddEnabled';
	
	private $eiFieldRelation;
	
	public function __construct(RelationEiField $relationEiField) {
		parent::__construct($relationEiField);
		$this->eiFieldRelation = $relationEiField->getEiFieldRelation();
		
		$this->autoRegister();
		
		if ($relationEiField instanceof SimpleRelationEiFieldAdapter) {	
			$this->registerDisplayDefinition($relationEiField->getDisplayDefinition());
			$this->registerStandardEditDefinition($relationEiField->getStandardEditDefinition());
		}
		
		if ($this->eiComponent instanceof ToManyEiFieldAdapter) {
			$this->addMandatory = false;
		}
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$magCollection = $magDispatchable->getMagCollection();
		
		$this->attributes->appendAll($magCollection->readValues(array(self::ATTR_TARGET_MASK_KEY,
				self::ATTR_MIN_KEY, self::ATTR_MAX_KEY, self::ATTR_REPLACEABLE_KEY, 
				self::ATTR_TARGET_REMOVAL_STRATEGY_KEY), true), true);
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);;
		$magCollection = $magDispatchable->getMagCollection();
		
		$lar = new LenientAttributeReader($this->attributes);
		
		$targetEiMaskOptions = array();
		$relationEntityProperty = $this->eiFieldRelation->getRelationEntityProperty();
		$targetEntityClass = $relationEntityProperty->getRelation()->getTargetEntityModel()->getClass();
		try {
			$specManager = $n2nContext->lookup(Rocket::class)->getSpecManager();
			$targetEiSpec = $specManager->getEiSpecByClass($targetEntityClass);
			foreach ($targetEiSpec->getEiMaskCollection() as $eiMask) {
				$targetEiMaskOptions[$eiMask->getId()] = $eiMask->getEiEngine()->getEiSpec()->getLabelLstr();
			}
		} catch (UnknownEiComponentException $e) {
		} catch (UnknownSpecException $e) {
		} catch (InvalidConfigurationException $e) {
		}
		
		$magCollection->addMag(new EnumMag(self::ATTR_TARGET_MASK_KEY, 'Target Mask', $targetEiMaskOptions,
				$lar->getString(self::ATTR_TARGET_MASK_KEY)));
				
		if ($this->eiComponent instanceof ToManyEiFieldAdapter) {
			$magCollection->addMag(new NumericMag(self::ATTR_MIN_KEY, 'Min',
					$lar->getInt(self::ATTR_MIN_KEY, $this->eiComponent->getMin())));
			$magCollection->addMag(new NumericMag(self::ATTR_MAX_KEY, 'Max',
					$lar->getInt(self::ATTR_MAX_KEY, $this->eiComponent->getMax())));
		}
		
		if ($this->eiComponent instanceof EmbeddedOneToOneEiField) {
			$magCollection->addMag(new BoolMag(self::ATTR_REPLACEABLE_KEY, 'Replaceable',
					$lar->getBool(self::ATTR_REPLACEABLE_KEY, $this->eiComponent->isReplaceable())));
		}
		
		if ($this->eiFieldRelation instanceof SelectEiFieldRelation) {
			$magCollection->addMag(new BoolMag(self::OPTION_FILTERED_KEY, 'Filtered',
					$lar->getBool(self::OPTION_FILTERED_KEY, $this->eiFieldRelation->isFiltered())));
			$magCollection->addMag(new BoolMag(self::OPTION_EMBEDDED_ADD_KEY,
					'Embedded Add Enabled', $lar->getBool(self::OPTION_EMBEDDED_ADD_KEY,
							$this->eiFieldRelation->isEmbeddedAddEnabled())));
		}

		if ($this->eiFieldRelation->getRelationEntityProperty()->isMaster()) {
			$magCollection->addMag(new EnumMag(self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, 'Target removal startegy', 
					array(RelationVetoableActionListener::STRATEGY_UNSET => 'Unset target',
							RelationVetoableActionListener::STRATEGY_PREVENT => 'Prevent removal',
							RelationVetoableActionListener::STRATEGY_SELF_REMOVE => 'Self remove'),
					$lar->getEnum(self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, RelationVetoableActionListener::getStrategies(),
							RelationVetoableActionListener::STRATEGY_PREVENT),
					false));
		}
		
		return $magDispatchable;
	}
	
	public function setup(EiSetupProcess $eiSetupProcess) {
		parent::setup($eiSetupProcess);
		
		$relationEntityProperty = $this->eiFieldRelation->getRelationEntityProperty();
		$targetEntityClass = $relationEntityProperty->getRelation()->getTargetEntityModel()->getClass();
		try {
			$target = $eiSetupProcess->getEiSpecByClass($targetEntityClass);
			
			$targetEiMask = null; 
			if (null !== ($eiMaskId = $this->attributes->getString(self::ATTR_TARGET_MASK_KEY, false, null, true))) {
				$targetEiMask = $target->getEiMaskCollection()->getById($eiMaskId);
			} else {
				$targetEiMask = $target->getEiMaskCollection()->getOrCreateDefault();
			}
				
			$targetMasterEiField = null;

			$this->eiFieldRelation->init($target, $targetEiMask);
		} catch (UnknownException $e) {
			throw $eiSetupProcess->createException(null, $e);
		} catch (UnknownEiMaskException $e) {
			throw $eiSetupProcess->createException(null, $e);
		} catch (UnknownEiComponentException $e) {
			throw $eiSetupProcess->createException('EiField for Mapped Property required', $e);
		} catch (InvalidEiComponentConfigurationException $e) {
			throw $eiSetupProcess->createException(null, $e);
		}
		
		
		if ($this->eiComponent instanceof ToManyEiFieldAdapter) {
			if ($this->attributes->contains(self::ATTR_MIN_KEY)) {
				$this->eiComponent->setMin($this->attributes->getNumeric(self::ATTR_MIN_KEY, true, null, true));
			}
			
			if ($this->attributes->contains(self::ATTR_MAX_KEY)) {
				$this->eiComponent->setMax($this->attributes->getNumeric(self::ATTR_MAX_KEY, true, null, true));
			}
		}

		if ($this->eiComponent instanceof EmbeddedOneToOneEiField 
				&& $this->attributes->contains(self::ATTR_REPLACEABLE_KEY)) {
			$this->eiComponent->setReplaceable($this->attributes->getBool(self::ATTR_REPLACEABLE_KEY));
		}
		
		if ($this->eiFieldRelation instanceof SelectEiFieldRelation) {
			if ($this->attributes->contains(self::OPTION_FILTERED_KEY)) {
				$this->eiFieldRelation->setFiltered($this->attributes->getBool(self::OPTION_FILTERED_KEY));
			}
			
			if ($this->attributes->contains(self::OPTION_EMBEDDED_ADD_KEY)) {
				$this->eiFieldRelation->setEmbeddedAddEnabled($this->attributes->get(self::OPTION_EMBEDDED_ADD_KEY));
			}
			
			if ($this->eiFieldRelation->isEmbeddedAddEnabled() && !$this->eiFieldRelation->isPersistCascaded()) {
				throw $eiSetupProcess->createException('Option ' . self::OPTION_EMBEDDED_ADD_KEY
						. ' requires an EntityProperty which cascades persist.');
			}
		}
		
		if ($this->eiFieldRelation->getRelationEntityProperty()->isMaster()) {
			$strategy = $this->attributes->getEnum(self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, 
					RelationVetoableActionListener::getStrategies(), false, 
					RelationVetoableActionListener::STRATEGY_PREVENT);
			
			$this->eiFieldRelation->getTargetEiSpec()->registerVetoableActionListener(
					new RelationVetoableActionListener($this->eiFieldRelation->getRelationEiField(), $strategy));		
		}
	}
}
