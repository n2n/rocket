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
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\ei\component\EiSetup;
use rocket\ei\UnknownEiTypeExtensionException;
use rocket\ei\component\UnknownEiComponentException;
use rocket\impl\ei\component\prop\relation\SimpleRelationEiPropAdapter;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use rocket\impl\ei\component\prop\relation\ToManyEiPropAdapter;
use rocket\impl\ei\component\prop\relation\EmbeddedOneToOneEiProp;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use rocket\ei\EiException;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\core\model\Rocket;
use n2n\util\type\attrs\LenientAttributeReader;
use rocket\spec\UnknownTypeException;
use rocket\impl\ei\component\prop\relation\model\RelationVetoableActionListener;
use rocket\impl\ei\component\prop\relation\model\relation\SelectEiPropRelation;
use n2n\config\InvalidConfigurationException;
use rocket\impl\ei\component\prop\relation\EmbeddedOneToManyEiProp;
use n2n\util\type\CastUtils;
use rocket\spec\Spec;
use rocket\ei\EiPropPath;
use rocket\ei\manage\generic\ScalarEiProperty;
use rocket\impl\ei\component\prop\relation\model\relation\EmbeddedEiPropRelation;
use rocket\ei\util\spec\EiuEngine;
use rocket\ei\EiType;
use rocket\ei\EiTypeExtension;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\MagCollectionMag;
use n2n\persistence\meta\structure\Column;

class RelationEiPropConfigurator extends AdaptableEiPropConfigurator {
	const ATTR_TARGET_EXTENSIONS_KEY = 'targetExtensions';
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
	private $displayInOverViewDefault = true;
	
	public function __construct(RelationEiProp $relationEiProp) {
		parent::__construct($relationEiProp);
		$this->eiPropRelation = $relationEiProp->getEiPropRelation();
		
		$this->autoRegister();
		
		if ($relationEiProp instanceof SimpleRelationEiPropAdapter) {	
			$this->registerDisplayConfig($relationEiProp->getDisplayConfig());
			$this->registerEditConfig($relationEiProp->getEditConfig());
		}
		
		if ($this->eiComponent instanceof ToManyEiPropAdapter) {
			$this->addMandatory = false;
		}
	}
	
	public function setDisplayInOverviewDefault(bool $displayInOverViewDefault) {
		$this->displayInOverViewDefault = $displayInOverViewDefault;
	}
	
	
	public function initAutoEiPropAttributes(N2nContext $n2nContext, Column $column = null) {
		parent::initAutoEiPropAttributes($n2nContext, $column);
		
		$this->attributes->set(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, $this->displayInOverViewDefault);
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$magCollection = $magDispatchable->getMagCollection();
		
		$this->attributes->appendAll($magCollection->readValues(array(self::ATTR_TARGET_EXTENSIONS_KEY,
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
			$spec = $n2nContext->lookup(Rocket::class)->getSpec();
			CastUtils::assertTrue($spec instanceof Spec);
			$targetEiType = $spec->getEiTypeByClass($targetEntityClass);
			foreach ($targetEiType->getEiTypeExtensionCollection()->toArray() as $eiTypeExtension) {
				$targetEiMaskOptions[$eiTypeExtension->getId()] = $eiTypeExtension->getId();
			}
			
			$scalarEiProperties = $targetEiType->getEiMask()->getEiEngine()->getScalarEiDefinition()->getMap();
			foreach ($scalarEiProperties as $ref => $scalarEiProperty) {
				CastUtils::assertTrue($scalarEiProperty instanceof ScalarEiProperty);
				
				$targetOrderFieldPathOptions[(string) $scalarEiProperties->getKeyByHashCode($ref)]
						= $scalarEiProperty->getLabelLstr();
			}
			
			$targetEiTypeExtensionIds = $lar->getScalarArray(self::ATTR_TARGET_EXTENSIONS_KEY);
			if (null !== ($targetEiExtensionId = $lar->getString('targetEiMaskId'))) {
				$targetEiTypeExtensionIds[$targetEiType->getId()] = $targetEiExtensionId;
			}
			$magCollection->addMag(self::ATTR_TARGET_EXTENSIONS_KEY,
					$this->createTargetExtensionsMag($targetEiType, $targetEiTypeExtensionIds));
		} catch (UnknownEiComponentException $e) {
		} catch (UnknownTypeException $e) {
		} catch (InvalidConfigurationException $e) {
		}
		
		
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
	
	private function createTargetExtensionsMag(EiType $targetEiType, array $attrs) {
		$targetEiTypes = array_merge([$targetEiType], $targetEiType->getAllSubEiTypes());
		
		$extMagCollection = new MagCollection();
		
		foreach ($targetEiTypes as $targetEiType) {
			$targetEiTypeExtensionOptions = array();
			foreach ($targetEiType->getEiTypeExtensionCollection() as $eiTypeExtension) {
				CastUtils::assertTrue($eiTypeExtension instanceof EiTypeExtension);
				
				$targetEiTypeExtensionOptions[$eiTypeExtension->getId()] 
						= (string) $eiTypeExtension->getEiMask()->getLabelLstr();
			}
			
			if (empty($targetEiTypeExtensionOptions)) continue;
			
			$label = 'Used extension for ' . $targetEiType->getEiMask()->getLabelLstr();
			$extMagCollection->addMag($targetEiType->getId(), new EnumMag($label, $targetEiTypeExtensionOptions,
					$attrs[$targetEiType->getId()] ?? null));
		}
		
		return new MagCollectionMag('Target', $extMagCollection);
	}
	
	public function setup(EiSetup $eiSetupProcess) {
		parent::setup($eiSetupProcess);
		
		$eiComponent = $this->eiComponent;
		
		if ($this->eiPropRelation instanceof EmbeddedEiPropRelation
				&& $this->attributes->contains(self::ATTR_ORPHANS_ALLOWED_KEY)) {
			$this->eiPropRelation->setOrphansAllowed($this->attributes->getBool(self::ATTR_ORPHANS_ALLOWED_KEY));
		}
		
		$relationEntityProperty = $this->eiPropRelation->getRelationEntityProperty();
		$targetEntityClass = $relationEntityProperty->getRelation()->getTargetEntityModel()->getClass();
		try {
			$target = $eiSetupProcess->eiu()->context()->getSpec()->getEiTypeByClass($targetEntityClass);
			
			$targetEiMask = $target->getEiMask();
			$targetSubEiTypes = $target->getAllSubEiTypes();
			$targetSubEiTypeExtensions = array();
			foreach ((array) $this->attributes->getScalarArray(self::ATTR_TARGET_EXTENSIONS_KEY, false, array(), true) 
					as $targetEiTypeId => $targetEiTypeExtensionId) {
				if ($targetEiTypeExtensionId === null) continue;
						
				if ($target->getId() == $targetEiTypeId) {
					$targetEiMask = $target->getEiTypeExtensionCollection()->getById($targetEiTypeExtensionId)->getEiMask();
					continue;
				} 
				
				if (isset($targetSubEiTypes[$targetEiTypeId]) 
						&& $targetSubEiTypes[$targetEiTypeId]->getEiTypeExtensionCollection()->containsId($targetEiTypeExtensionId)) {
					$targetSubEiTypeExtensions[$targetEiTypeId] = $targetSubEiTypes[$targetEiTypeId]->getEiTypeExtensionCollection()
							->getById($targetEiTypeExtensionId);
				}
			} 
				
			$targetMasterEiProp = null;

			$this->eiPropRelation->init($eiSetupProcess->eiu(), $target, $targetEiMask, $targetSubEiTypeExtensions);
		} catch (EiException $e) {
			throw $eiSetupProcess->createException(null, $e);
		} catch (UnknownEiTypeExtensionException $e) {
			throw $eiSetupProcess->createException(null, $e);
		} catch (UnknownEiComponentException $e) {
			throw $eiSetupProcess->createException('EiProp for Mapped Property required', $e);
		} catch (InvalidEiComponentConfigurationException $e) {
			throw $eiSetupProcess->createException(null, $e);
		}
		
		if ($eiComponent instanceof ToManyEiPropAdapter) {
			if ($this->attributes->contains(self::ATTR_MIN_KEY)) {
				$eiComponent->setMin($this->attributes->reqNumeric(self::ATTR_MIN_KEY, true));
			}
			
			if ($this->attributes->contains(self::ATTR_MAX_KEY)) {
				$eiComponent->setMax($this->attributes->reqNumeric(self::ATTR_MAX_KEY, true));
			}
		}

		if ($eiComponent instanceof EmbeddedOneToOneEiProp 
				&& $this->attributes->contains(self::ATTR_REPLACEABLE_KEY)) {
			$eiComponent->setReplaceable($this->attributes->getBool(self::ATTR_REPLACEABLE_KEY));
		}
		
		if ($eiComponent instanceof EmbeddedOneToManyEiProp
				&& $this->attributes->contains(self::ATTR_TARGET_ORDER_EI_FIELD_PATH_KEY)) {
			$targetEiPropPath = EiPropPath::create($this->attributes->getScalar(self::ATTR_TARGET_ORDER_EI_FIELD_PATH_KEY));
			
			$that = $this;
			$eiSetupProcess->eiu()->mask()->onEngineReady(function (EiuEngine $eiuEngine) use ($that, $targetEiPropPath, $eiComponent) {
				$that->eiPropRelation->getTargetEiMask()->getEiEngine()->getScalarEiDefinition()
						->getScalarEiPropertyByEiPropPath($targetEiPropPath);
				$eiComponent->setTargetOrderEiPropPath($targetEiPropPath);
			});
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
				$eiPropRelation->setEmbeddedAddEnabled($this->attributes->req(self::ATTR_EMBEDDED_ADD_KEY));
			}
			
			if ($eiPropRelation->isEmbeddedAddEnabled() && !$eiPropRelation->isPersistCascaded()) {
				throw $eiSetupProcess->createException('Option ' . self::ATTR_EMBEDDED_ADD_KEY
						. ' requires an EntityProperty which cascades persist.');
			}
		}
		
		if ($eiPropRelation->getRelationEntityProperty()->isMaster()) {
			$strategy = $this->attributes->optEnum(self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, 
					RelationVetoableActionListener::getStrategies(),  
					RelationVetoableActionListener::STRATEGY_PREVENT, false);
			
			$eiPropRelation->getTargetEiType()->registerVetoableActionListener(
					new RelationVetoableActionListener($eiPropRelation->getRelationEiProp(), $strategy));		
		}
	}
}