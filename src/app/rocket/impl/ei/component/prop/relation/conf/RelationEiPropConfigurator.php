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
namespace rocket\impl\ei\component\prop\relation\conf;

use rocket\impl\ei\component\prop\relation\RelationEiProp;
use rocket\impl\ei\component\prop\adapter\AdaptableEiPropConfigurator;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\spec\ei\component\EiSetupProcess;
use rocket\spec\ei\mask\UnknownEiMaskExtensionException;
use rocket\spec\ei\component\UnknownEiComponentException;
use rocket\impl\ei\component\prop\relation\SimpleRelationEiPropAdapter;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use rocket\impl\ei\component\prop\relation\ToManyEiPropAdapter;
use rocket\impl\ei\component\prop\relation\EmbeddedOneToOneEiProp;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use rocket\spec\ei\EiException;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\core\model\Rocket;
use n2n\util\config\LenientAttributeReader;
use rocket\spec\config\UnknownSpecException;
use rocket\impl\ei\component\prop\relation\model\RelationVetoableActionListener;
use rocket\impl\ei\component\prop\relation\model\relation\SelectEiPropRelation;
use n2n\util\config\InvalidConfigurationException;
use rocket\impl\ei\component\prop\relation\EmbeddedOneToManyEiProp;
use n2n\reflection\CastUtils;
use rocket\spec\config\SpecManager;
use rocket\spec\ei\EiPropPath;
use rocket\spec\ei\manage\generic\ScalarEiProperty;
use rocket\impl\ei\component\prop\relation\model\relation\EmbeddedEiPropRelation;

class RelationEiPropConfigurator extends AdaptableEiPropConfigurator {
	const ATTR_TARGET_MASK_KEY = 'targetEiMaskId';
	const ATTR_MIN_KEY = 'min';
	const ATTR_MAX_KEY = 'max';
	const ATTR_REPLACEABLE_KEY = 'replaceable';
	const ATTR_REDUCED_KEY = 'reduced';
	const ATTR_TARGET_REMOVAL_STRATEGY_KEY = 'targetRemovalStrategy';
	const ATTR_TARGET_ORDER_EI_FIELD_PATH_KEY = 'targetOrderField';
	const ATTR_ORPHANS_ALLOWED_KEY = 'orphansAllowed';
	const ATTR_FILTERED_KEY = 'filtered';
	const ATTR_HIDDEN_IF_TARGET_EMPTY_KEY = 'hiddenIfTargetEmpty';
	const ATTR_EMBEDDED_ADD_KEY = 'embeddedAddEnabled';
	
	private $eiPropRelation;
	
	public function __construct(RelationEiProp $relationEiProp) {
		parent::__construct($relationEiProp);
		$this->eiPropRelation = $relationEiProp->getEiPropRelation();
		
		$this->autoRegister();
		
		if ($relationEiProp instanceof SimpleRelationEiPropAdapter) {	
			$this->registerDisplaySettings($relationEiProp->getDisplaySettings());
			$this->registerStandardEditDefinition($relationEiProp->getStandardEditDefinition());
		}
		
		if ($this->eiComponent instanceof ToManyEiPropAdapter) {
			$this->addMandatory = false;
		}
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$magCollection = $magDispatchable->getMagCollection();
		
		$this->attributes->appendAll($magCollection->readValues(array(self::ATTR_TARGET_MASK_KEY,
				self::ATTR_MIN_KEY, self::ATTR_MAX_KEY, self::ATTR_REPLACEABLE_KEY, 
				self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, self::ATTR_TARGET_ORDER_EI_FIELD_PATH_KEY,
				self::ATTR_ORPHANS_ALLOWED_KEY, self::ATTR_EMBEDDED_ADD_KEY, self::ATTR_FILTERED_KEY, 
				self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY, self::ATTR_REDUCED_KEY), true), true);
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magDispatchable = parent::createMagDispatchable($n2nContext);;
		$magCollection = $magDispatchable->getMagCollection();
		
		$lar = new LenientAttributeReader($this->attributes);
		
		$targetEiMaskOptions = array();
		$relationEntityProperty = $this->eiPropRelation->getRelationEntityProperty();
		$targetEntityClass = $relationEntityProperty->getRelation()->getTargetEntityModel()->getClass();
		$targetOrderFieldPathOptions = array();
		try {
			$specManager = $n2nContext->lookup(Rocket::class)->getSpecManager();
			CastUtils::assertTrue($specManager instanceof SpecManager);
			$targetEiType = $specManager->getEiTypeByClass($targetEntityClass);
			foreach ($targetEiType->getEiMaskCollection()->toArray() as $eiMask) {
				$targetEiMaskOptions[$eiMask->getExtension()->getId()] = $eiMask->getEiEngine()->getEiMask()->getEiType()->getLabelLstr();
			}
			
			$scalarEiProperties = $targetEiType->getEiEngine()->getScalarEiDefinition()->getScalarEiProperties();
			foreach ($scalarEiProperties as $ref => $scalarEiProperty) {
				CastUtils::assertTrue($scalarEiProperty instanceof ScalarEiProperty);
				
				$targetOrderFieldPathOptions[(string) $scalarEiProperties->getKeyByHashCode($ref)]
						= $scalarEiProperty->getLabelLstr();
			}
		} catch (UnknownEiComponentException $e) {
		} catch (UnknownSpecException $e) {
		} catch (InvalidConfigurationException $e) {
		}
		
		$magCollection->addMag(self::ATTR_TARGET_MASK_KEY, new EnumMag('Target Mask', $targetEiMaskOptions,
				$lar->getString(self::ATTR_TARGET_MASK_KEY)));
		
		$eiComponent = $this->eiComponent;
				
		if ($eiComponent instanceof ToManyEiPropAdapter) {
			$magCollection->addMag(self::ATTR_MIN_KEY, new NumericMag('Min',
					$lar->getInt(self::ATTR_MIN_KEY, $eiComponent->getMin())));
			$magCollection->addMag(self::ATTR_MAX_KEY, new NumericMag('Max',
					$lar->getInt(self::ATTR_MAX_KEY, $eiComponent->getMax())));
		}
		
		if ($eiComponent instanceof EmbeddedOneToOneEiProp) {
			$magCollection->addMag(self::ATTR_REPLACEABLE_KEY, new BoolMag('Replaceable',
					$lar->getBool(self::ATTR_REPLACEABLE_KEY, $eiComponent->isReplaceable())));
		}
		
		if ($eiComponent instanceof EmbeddedOneToManyEiProp) {
			$magCollection->addMag(self::ATTR_TARGET_ORDER_EI_FIELD_PATH_KEY, new EnumMag('Target order field', 
					$targetOrderFieldPathOptions, $lar->getScalar(self::ATTR_TARGET_ORDER_EI_FIELD_PATH_KEY)));
		}
		
		if ($eiComponent instanceof EmbeddedOneToOneEiProp || $eiComponent instanceof EmbeddedOneToManyEiProp) {
			$magCollection->addMag(self::ATTR_REDUCED_KEY, new BoolMag('Reduced',
					$lar->getBool(self::ATTR_REDUCED_KEY, $eiComponent->isReduced())));
		}
		
		$eiPropRelation = $this->eiPropRelation;
		
		if ($eiPropRelation instanceof EmbeddedEiPropRelation) {
			$magCollection->addMag(self::ATTR_ORPHANS_ALLOWED_KEY, new BoolMag('Allow orphans',
					$lar->getBool(self::ATTR_ORPHANS_ALLOWED_KEY, $eiPropRelation->getOrphansAllowed())));
		}
		
		if ($eiPropRelation instanceof SelectEiPropRelation) {
			$magCollection->addMag(self::ATTR_FILTERED_KEY, new BoolMag('Filtered',
					$lar->getBool(self::ATTR_FILTERED_KEY, $this->eiPropRelation->isFiltered())));
			$magCollection->addMag(self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY, new BoolMag('Hide if target empty',
					$lar->getBool(self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY, $eiPropRelation->isHiddenIfTargetEmpty())));
			$magCollection->addMag(self::ATTR_EMBEDDED_ADD_KEY, new BoolMag(
					'Embedded Add Enabled', $lar->getBool(self::ATTR_EMBEDDED_ADD_KEY,
							$eiPropRelation->isEmbeddedAddEnabled())));
		}

		if ($eiPropRelation->getRelationEntityProperty()->isMaster()) {
			$magCollection->addMag(self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, new EnumMag('Target removal startegy', 
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
		
		$eiComponent = $this->eiComponent;
		
		if ($this->eiPropRelation instanceof EmbeddedEiPropRelation
				&& $this->attributes->contains(self::ATTR_ORPHANS_ALLOWED_KEY)) {
			$this->eiPropRelation->setOrphansAllowed($this->attributes->getBool(self::ATTR_ORPHANS_ALLOWED_KEY));
		}
		
		$relationEntityProperty = $this->eiPropRelation->getRelationEntityProperty();
		$targetEntityClass = $relationEntityProperty->getRelation()->getTargetEntityModel()->getClass();
		try {
			$target = $eiSetupProcess->eiu()->context()->engine($targetEntityClass)->getEiType();
			
			$targetEiMask = null; 
			if (null !== ($eiMaskId = $this->attributes->getString(self::ATTR_TARGET_MASK_KEY, false, null, true))) {
				$targetEiMask = $target->getEiMaskExtensionCollection()->getById($eiMaskId)->getEiMask();
			} else {
				$targetEiMask = $target->getEiMask();
			}
				
			$targetMasterEiProp = null;

			$this->eiPropRelation->init($target, $targetEiMask);
		} catch (EiException $e) {
			throw $eiSetupProcess->createException(null, $e);
		} catch (UnknownEiMaskExtensionException $e) {
			throw $eiSetupProcess->createException(null, $e);
		} catch (UnknownEiComponentException $e) {
			throw $eiSetupProcess->createException('EiProp for Mapped Property required', $e);
		} catch (InvalidEiComponentConfigurationException $e) {
			throw $eiSetupProcess->createException(null, $e);
		}
		
		if ($eiComponent instanceof ToManyEiPropAdapter) {
			if ($this->attributes->contains(self::ATTR_MIN_KEY)) {
				$eiComponent->setMin($this->attributes->getNumeric(self::ATTR_MIN_KEY, true, null, true));
			}
			
			if ($this->attributes->contains(self::ATTR_MAX_KEY)) {
				$eiComponent->setMax($this->attributes->getNumeric(self::ATTR_MAX_KEY, true, null, true));
			}
		}

		if ($eiComponent instanceof EmbeddedOneToOneEiProp 
				&& $this->attributes->contains(self::ATTR_REPLACEABLE_KEY)) {
			$eiComponent->setReplaceable($this->attributes->getBool(self::ATTR_REPLACEABLE_KEY));
		}
		
		if ($eiComponent instanceof EmbeddedOneToManyEiProp
				&& $this->attributes->contains(self::ATTR_TARGET_ORDER_EI_FIELD_PATH_KEY)) {
			$targetEiPropPath = EiPropPath::create($this->attributes->getScalar(self::ATTR_TARGET_ORDER_EI_FIELD_PATH_KEY));
			$this->eiPropRelation->getTargetEiMask()->getEiEngine()->getScalarEiDefinition()
						->getScalarEiPropertyByFieldPath($targetEiPropPath);
			$eiComponent->setTargetOrderEiPropPath($targetEiPropPath);
		}
		
		if (($eiComponent instanceof EmbeddedOneToOneEiProp || $eiComponent instanceof EmbeddedOneToManyEiProp) 
				&& $this->attributes->contains(self::ATTR_REDUCED_KEY)) {
			$eiComponent->setReduced($this->attributes->getBool(self::ATTR_REDUCED_KEY));
		}
		
		
		$eiPropRelation = $this->eiPropRelation;
		if ($eiPropRelation instanceof SelectEiPropRelation) {
			if ($this->attributes->contains(self::ATTR_FILTERED_KEY)) {
				$eiPropRelation->setFiltered($this->attributes->getBool(self::ATTR_FILTERED_KEY));
			}
			
			if ($this->attributes->contains(self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY)) {
				$eiPropRelation->setHiddenIfTargetEmpty(
						$this->attributes->getBool(self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY));
			}
			
			if ($this->attributes->contains(self::ATTR_EMBEDDED_ADD_KEY)) {
				$eiPropRelation->setEmbeddedAddEnabled($this->attributes->get(self::ATTR_EMBEDDED_ADD_KEY));
			}
			
			if ($eiPropRelation->isEmbeddedAddEnabled() && !$eiPropRelation->isPersistCascaded()) {
				throw $eiSetupProcess->createException('Option ' . self::ATTR_EMBEDDED_ADD_KEY
						. ' requires an EntityProperty which cascades persist.');
			}
		}
		
		if ($eiPropRelation->getRelationEntityProperty()->isMaster()) {
			$strategy = $this->attributes->getEnum(self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, 
					RelationVetoableActionListener::getStrategies(), false, 
					RelationVetoableActionListener::STRATEGY_PREVENT);
			
			$eiPropRelation->getTargetEiType()->registerVetoableActionListener(
					new RelationVetoableActionListener($eiPropRelation->getRelationEiProp(), $strategy));		
		}
	}
}