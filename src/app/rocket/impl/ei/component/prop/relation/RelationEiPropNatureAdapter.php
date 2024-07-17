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
namespace rocket\impl\ei\component\prop\relation;

use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\op\ei\manage\frame\EiForkLink;
use rocket\op\ei\manage\frame\EiFrame;
use rocket\ui\gui\field\GuiField;
use rocket\op\ei\util\Eiu;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\relation\model\Relation;
use rocket\op\ei\manage\idname\IdNameProp;
use rocket\impl\ei\component\prop\relation\command\TargetReadEiCommandNature;
use n2n\l10n\Lstr;
use rocket\impl\ei\component\prop\relation\command\TargetEditEiCommandNature;
use rocket\impl\ei\component\prop\relation\model\RelationVetoableActionListener;
use rocket\impl\ei\component\prop\adapter\EiPropNatureAdapter;
use n2n\reflection\property\PropertyAccessProxy;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\TypeConstraints;
use rocket\impl\ei\component\prop\adapter\trait\DisplayConfigTrait;
use rocket\impl\ei\component\prop\adapter\trait\InGuiPropTrait;

abstract class RelationEiPropNatureAdapter extends EiPropNatureAdapter implements RelationEiProp/*, EiGuiField*/ {
	use InGuiPropTrait;

	private ?Relation $relation = null;
	private PropertyAccessProxy $propertyAccessProxy;

	function __construct(private readonly RelationEntityProperty $entityProperty, PropertyAccessProxy $accessProxy,
			protected readonly RelationModel $relationModel) {
		$getterTypeConstraint = TypeConstraints::namedType($entityProperty->getTargetEntityModel()->getClass(), true);
		if ($this->relationModel->isTargetMany()) {
			$getterTypeConstraint = TypeConstraints::arrayLike(true, $getterTypeConstraint);
		}

		$this->propertyAccessProxy = $accessProxy->createRestricted($getterTypeConstraint);
	}

	function isPrivileged(): bool {
		return true;
	}

	function getNativeAccessProxy(): ?AccessProxy {
		return $this->propertyAccessProxy;
	}

	function setup(Eiu $eiu): void {
		$targetClass = $this->relationModel->getRelationEntityProperty()->getTargetEntityModel()->getClass();
		$targetEiuType = $eiu->context()->type($targetClass);


//		$targetExtensionId = $dataSet->optString(self::ATTR_TARGET_EXTENSION_ID_KEY);
//		$targetEiuMask = null;
//		if ($targetExtensionId !== null) {
//			$targetEiuMask = $targetEiuType->extensionMask($targetExtensionId, false);
//		}
//		if ($targetEiuMask === null) {
			$targetEiuMask = $targetEiuType->mask();
//		}


		$targetReadEiCommand = new TargetReadEiCommandNature(Lstr::create('Embedded Read'), (string) $eiu->mask()->getEiTypePath(),
				(string) $targetEiuMask->getEiTypePath());
		$targetReadEiCmdPath = $targetEiuMask->addCmd($targetReadEiCommand)->getEiCmdPath();
		$this->relationModel->setTargetReadEiCmdPath($targetReadEiCmdPath);

		$targetEditEiCommand = new TargetEditEiCommandNature(Lstr::create('Change this name'), (string) $eiu->mask()->getEiTypePath(),
				(string) $targetEiuMask->getEiTypePath());
		$targetEditEiCmdPath = $targetEiuMask->addCmd($targetEditEiCommand)->getEiCmdPath();
		$this->relationModel->setTargetEditEiCmdPath($targetEditEiCmdPath);


//		if ($this->relationModel->isTargetMany()) {
//			$this->relationModel->setMin($dataSet->optInt(self::ATTR_MIN_KEY,
//					$this->relationModel->getMin(), false));
//			$this->relationModel->setMax($dataSet->optInt(self::ATTR_MAX_KEY,
//					$this->relationModel->getMax(), true));
//		}

		if ($this->relationModel->isEmbedded() && $this->relationModel->isTargetMany()) {
//			$targetOrderEiPropPath = EiPropPath::build(
//					$dataSet->optString(self::ATTR_TARGET_ORDER_EI_PROP_PATH_KEY));
//			$targetEiuType->mask()->onEngineReady(function (EiuEngine $eiuEngine) use ($targetOrderEiPropPath) {
//				if ($targetOrderEiPropPath !== null && $eiuEngine->containsScalarEiProperty($targetOrderEiPropPath)) {
//					$this->relationModel->setTargetOrderEiPropPath($targetOrderEiPropPath);
//				} else {
//					$this->relationModel->setTargetOrderEiPropPath(null);
//				}
//			});
		}

//		if ($this->relationModel->isEmbedded()) {
//			$this->relationModel->setOrphansAllowed(
//					$dataSet->optBool(self::ATTR_ORPHANS_ALLOWED_KEY, $this->relationModel->isOrphansAllowed()));
//
//			$this->relationModel->setReduced(
//					$dataSet->optBool(self::ATTR_REDUCED_KEY, $this->relationModel->isReduced()));
//
//
//			$this->relationModel->setRemovable(
//					$dataSet->optBool(self::ATTR_REMOVABLE_KEY, $this->relationModel->isRemovable()));
//		}

// 		if (!$this->relationModel->isSourceMany() && $this->relationModel->isSelect()) {
// 			$this->relationModel->setFiltered(
// 					$dataSet->optBool(self::ATTR_FILTERED_KEY, $this->relationModel->isFiltered()));
// 		}

		if ($this->relationModel->isSelect()) {
//			$this->relationModel->setHiddenIfTargetEmpty(
//					$dataSet->optBool(self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY,
//							$this->relationModel->isHiddenIfTargetEmpty()));
//
//			$this->relationModel->setMaxPicksNum($dataSet->optInt(self::ATTR_MAX_PICKS_NUM_KEY,
//					$this->relationModel->getMaxPicksNum()));
		}

		if ($this->relationModel->isMaster()) {
//			$strategy = $dataSet->optEnum(self::ATTR_TARGET_REMOVAL_STRATEGY_KEY,
//					RelationVetoableActionListener::getStrategies(),
//					RelationVetoableActionListener::STRATEGY_UNSET, false);

			$strategy = RelationVetoableActionListener::STRATEGY_UNSET;

			$targetEiuType->getEiType()->registerVetoableActionListener(
					new RelationVetoableActionListener($this->relationModel, $strategy));
		}

		$this->relationModel->prepare($eiu->mask(), $targetEiuMask);
	}

	/**
	 * @return RelationModel
	 */
	function getRelationModel() {
		return $this->relationModel;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\relation\model\Relation
	 */
	protected function getRelation() {
		if ($this->relation !== null) {
			return $this->relation;
		}
		
// 		IllegalStateException::assertTrue($this->displayConfig !== null && $this->editConfig !== null);
		return $this->relation = new Relation($this, $this->getRelationModel()); 
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\relation\RelationEiProp::getRelationEntityProperty()
	 */
	function getRelationEntityProperty(): RelationEntityProperty {
		return $this->entityProperty;
	}

	function getPropertyAccessProxy(): ?AccessProxy {
		return $this->propertyAccessProxy;
	}
	
//	/**
//	 * {@inheritDoc}
//	 * @see \rocket\op\ei\component\prop\GuiEiProp::buildGuiProp()
//	 */
//	function buildGuiProp(Eiu $eiu): ?EiGuiProp {
//		return $eiu->factory()->newGuiProp(function (Eiu $eiu, bool $readOnly) {
//			return $this->getDisplayConfig()->buildGuiProp($this->buildGuiField($eiu, $readOnly), $this);
//		})->toEiGuiProp();
//	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		return null;
	}
	
	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		if ($this->getRelationModel()->isTargetMany()) {
			return null;
		}
		
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			$targetEntityObj = $eiu->object()->readNativeValue($eiu->prop()->getEiProp());
			
			if ($targetEntityObj === null) {
				return null;
			}
			
			$targetEiuEngine = $this->getRelationModel()->getTargetEiuEngine();
			return $targetEiuEngine->createIdentityString($targetEntityObj);
		})->toIdNameProp();
	}
	
	function createForkedEiFrame(Eiu $eiu, EiForkLink $eiForkLink): EiFrame {
		return $this->getRelation()->createForkEiFrame($eiu, $eiForkLink);
	}
}
