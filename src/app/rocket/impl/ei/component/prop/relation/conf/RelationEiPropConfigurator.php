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

use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\ei\component\EiSetup;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\util\type\attrs\LenientAttributeReader;
use rocket\impl\ei\component\prop\relation\model\RelationVetoableActionListener;
use rocket\ei\EiPropPath;
use rocket\ei\util\spec\EiuEngine;
use n2n\persistence\meta\structure\Column;
use rocket\ei\util\Eiu;
use n2n\util\col\ArrayUtils;
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\prop\relation\command\TargetReadEiCommand;
use rocket\ei\EiCommandPath;
use n2n\l10n\Lstr;

class RelationEiPropConfigurator extends AdaptableEiPropConfigurator {
	const ATTR_TARGET_EXTENSION_ID_KEY = 'targetExtension';
	const ATTR_MIN_KEY = 'min';	// tm
	const ATTR_MAX_KEY = 'max'; // tm
	const ATTR_REMOVABLE_KEY = 'replaceable'; // eto
	const ATTR_REDUCED_KEY = 'reduced'; // emb
	const ATTR_TARGET_REMOVAL_STRATEGY_KEY = 'targetRemovalStrategy';
	const ATTR_TARGET_ORDER_EI_PROP_PATH_KEY = 'targetOrderField'; // etm
	const ATTR_ORPHANS_ALLOWED_KEY = 'orphansAllowed';
	const ATTR_FILTERED_KEY = 'filtered';
	const ATTR_HIDDEN_IF_TARGET_EMPTY_KEY = 'hiddenIfTargetEmpty';
	
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	private $displayInOverViewDefault = true;
	
	public function __construct(RelationEiProp $relationEiProp) {
		parent::__construct($relationEiProp);

		$this->autoRegister();
		
// 		if ($relationEiProp instanceof SimpleRelationEiPropAdapter) {	
// 			$this->registerDisplayConfig($relationEiProp->getDisplayConfig());
// 			$this->registerEditConfig($relationEiProp->getEditConfig());
// 		}
		
// 		if ($relationModel->isTargetMany()) {
// 			$this->addMandatory = false;
// 		}
	}
	
	function setRelationModel(RelationModel $relationModel) {
		$this->relationModel = $relationModel;
	}
	
// 	public function setDisplayInOverviewDefault(bool $displayInOverViewDefault) {
// 		$this->displayInOverViewDefault = $displayInOverViewDefault;
// 	}
	
	public function initAutoEiPropAttributes(N2nContext $n2nContext, Column $column = null) {
		parent::initAutoEiPropAttributes($n2nContext, $column);
		
		$this->attributes->set(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, $this->displayInOverViewDefault);
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$magCollection = $magDispatchable->getMagCollection();
		
		$this->attributes->appendAll($magCollection->readValues(array(self::ATTR_TARGET_EXTENSIONS_KEY,
				self::ATTR_MIN_KEY, self::ATTR_MAX_KEY, self::ATTR_REMOVABLE_KEY, 
				self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, self::ATTR_TARGET_ORDER_EI_PROP_PATH_KEY,
				self::ATTR_ORPHANS_ALLOWED_KEY, self::ATTR_EMBEDDED_ADD_KEY, self::ATTR_FILTERED_KEY, 
				self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY, self::ATTR_REDUCED_KEY), true), true);
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		IllegalStateException::assertTrue($this->relationModel !== null, self::class . ' misses RelationModel.');
		
		$magDispatchable = parent::createMagDispatchable($n2nContext);
		$magCollection = $magDispatchable->getMagCollection();
		
		$eiu = new Eiu($n2nContext);
		
		$lar = new LenientAttributeReader($this->attributes);
		
		$targetClass = $this->relationModel->getRelationEntityProperty()->getTargetEntityModel()->getClass();
		$targetEiuType = $eiu->context()->type($targetClass);
		
		$magCollection->addMag(self::ATTR_TARGET_EXTENSION_ID_KEY, 
				new EnumMag('Target Extension', $targetEiuType->getExtensionMaskOptions(), 
						$lar->getString(self::ATTR_TARGET_EXTENSION_ID_KEY), true));
		
		if ($this->relationModel->isTargetMany()) {
			$magCollection->addMag(self::ATTR_MIN_KEY, new NumericMag('Min', $lar->getInt(self::ATTR_MIN_KEY, null)));
			$magCollection->addMag(self::ATTR_MAX_KEY, new NumericMag('Max', $lar->getInt(self::ATTR_MAX_KEY, null)));
		}
		
		if ($this->relationModel->isEmbedded() && $this->relationModel->isTargetMany()
				&& $targetEiuType->mask()->isEngineReady()) {
			$options = $targetEiuType->mask()->engine()->getScalarEiPropertyOptions();
			
			$magCollection->addMag(self::ATTR_TARGET_ORDER_EI_PROP_PATH_KEY, 
					new EnumMag('Target order field', $options, 
							$lar->getScalar(self::ATTR_TARGET_ORDER_EI_PROP_PATH_KEY)));
		}
		
		if ($this->relationModel->isEmbedded()) {
			$magCollection->addMag(self::ATTR_REDUCED_KEY, 
					new BoolMag('Reduced', $lar->getBool(self::ATTR_REDUCED_KEY, true)));
		
			$magCollection->addMag(self::ATTR_ORPHANS_ALLOWED_KEY, 
					new BoolMag('Allow orphans', $lar->getBool(self::ATTR_ORPHANS_ALLOWED_KEY, false)));
			
			$magCollection->addMag(self::ATTR_REMOVABLE_KEY,
					new BoolMag('Removable', $lar->getBool(self::ATTR_REMOVABLE_KEY, true)));
		}
		
// 		if (!$this->relationModel->isSourceMany() && $this->relationModel->isSelect()) {
// 			$magCollection->addMag(self::ATTR_FILTERED_KEY, new BoolMag('Filtered',
// 					$lar->getBool(self::ATTR_FILTERED_KEY, true)));
// 		}
		
		if (!$this->relationModel->isEmbedded()) {
			$magCollection->addMag(self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY, new BoolMag('Hide if target empty',
					$lar->getBool(self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY, $eiPropRelation->isHiddenIfTargetEmpty())));
		}

		if ($this->relationModel->isMaster()) {
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
	
	public function setup(EiSetup $eiSetupProcess) {
		IllegalStateException::assertTrue($this->relationModel !== null, self::class . ' misses RelationModel for ' 
				. $this->eiComponent . '.');
		
		parent::setup($eiSetupProcess);

		$eiu = $eiSetupProcess->eiu();
		$targetClass = $this->relationModel->getRelationEntityProperty()->getTargetEntityModel()->getClass();
		$targetEiuType = $eiu->context()->type($targetClass);
		
		if (null !== ($teArr = $this->attributes->optScalarArray('targetExtensions', null, true, true))) {
			$this->attributes->set(self::ATTR_TARGET_EXTENSION_ID_KEY, ArrayUtils::current($teArr));
		}
		
		$targetExtensionId = $this->attributes->optString(self::ATTR_TARGET_EXTENSION_ID_KEY);
		$targetEiuMask = null;
		if ($targetExtensionId !== null) {
			$targetEiuMask = $targetEiuType->extensionMask($targetExtensionId, false);
		} 
		if ($targetEiuMask === null) {
			$targetEiuMask = $targetEiuType->mask();
		}
		$targetEiuMask->onEngineReady(function ($eiuEngine) {
			$this->relationModel->finalize($eiuEngine);
		});
			
		$targetReadEiCommand = new TargetReadEiCommand(Lstr::create('Change this name'), 'change this', 'change this');
		$targetEiuMask->addEiCommand($targetReadEiCommand);
		$this->relationModel->setTargetReadEiCommandPath(EiCommandPath::from($targetReadEiCommand));
		
				
		if ($this->relationModel->isTargetMany()) {
			$this->relationModel->setMin($this->attributes->optInt(self::ATTR_MIN_KEY, 
					$this->relationModel->getMin(), true));
			$this->relationModel->setMax($this->attributes->optInt(self::ATTR_MAX_KEY, 
					$this->relationModel->getMax(), true));
		}
		
		if ($this->relationModel->isEmbedded() && $this->relationModel->isTargetMany()) {
			$targetOrderEiPropPath = EiPropPath::build(
					$this->attributes->optString(self::ATTR_TARGET_ORDER_EI_PROP_PATH_KEY));
			
			$targetEiuType->mask()->onEngineReady(function (EiuEngine $eiuEngine) use ($targetOrderEiPropPath) {
				if ($eiuEngine->containsScalarEiProperty($targetOrderEiPropPath)) {
					$this->relationModel->setTragetOrderEiPropPath($targetOrderEiPropPath);
				} else {
					$this->relationModel->setTragetOrderEiPropPath(null);
				}
			});
		}
		
		if ($this->relationModel->isEmbedded()) {
			$this->relationModel->setOrphansAllowed(
					$this->attributes->optBool(self::ATTR_ORPHANS_ALLOWED_KEY, 
							$this->relationModel->isOrphansAllowed()));
			
			$this->relationModel->setReduced(
					$this->attributes->optBool(self::ATTR_REDUCED_KEY, $this->relationModel->isReduced()));
			
			
			$this->relationModel->setRemovable(
					$this->attributes->optBool(self::ATTR_REMOVABLE_KEY, $this->relationModel->isRemovable()));
		}
		
// 		if (!$this->relationModel->isSourceMany() && $this->relationModel->isSelect()) {
// 			$this->relationModel->setFiltered(
// 					$this->attributes->optBool(self::ATTR_FILTERED_KEY, $this->relationModel->isFiltered()));
// 		}
		
		if (!$this->relationModel->isEmbedded()) {
			$this->relationModel->setHiddenIfTargetEmpty(
					$this->attributes->optBool(self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY, 
							$this->relationModel->isHiddenIfTargetEmpty()));
		}
		
		if ($this->relationModel->isMaster()) {
			$strategy = $this->attributes->optEnum(self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, 
					RelationVetoableActionListener::getStrategies(),  
					RelationVetoableActionListener::STRATEGY_PREVENT, false);
			
			$targetEiuType->getEiType()->registerVetoableActionListener(
					new RelationVetoableActionListener($this->eiComponent, $strategy));		
		}
	}
}