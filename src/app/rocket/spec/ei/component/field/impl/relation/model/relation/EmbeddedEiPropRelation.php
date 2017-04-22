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
namespace rocket\spec\ei\component\field\impl\relation\model\relation;

use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\EiFrame;
use n2n\web\dispatch\mag\MagCollection;
use rocket\spec\ei\component\field\impl\adapter\DraftableEiPropAdapter;
use rocket\spec\ei\component\field\impl\TranslatableEiPropAdapter;
use rocket\spec\ei\component\field\impl\relation\command\EmbeddedEditPseudoCommand;
use rocket\spec\ei\component\field\impl\relation\command\EmbeddedPseudoCommand;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\l10n\DynamicTextCollection;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use n2n\reflection\ReflectionUtils;

class EmbeddedEiPropRelation extends EiPropRelation {
	private $embeddedPseudoCommand;
	private $embeddedEditPseudoCommand;

	public function init(EiSpec $targetEiSpec, EiMask $targetEiMask) {
		parent::init($targetEiSpec, $targetEiMask);

		if (!$this->isPersistCascaded()) {
			$entityProperty = $this->getRelationEiProp()->getEntityProperty();
			throw new InvalidEiComponentConfigurationException(
					'EiProp requires an EntityProperty which cascades persist: ' 
							. ReflectionUtils::prettyPropName($entityProperty->getEntityModel()->getClass(),
									$entityProperty->getName()));
		}
		
		if ($this->isDraftable() && !$this->isJoinTableRelation($this)) {
			throw new InvalidEiComponentConfigurationException(
					'Only EiProps of properties with join table relations can be drafted.');
		}
		
		$this->setupEmbeddedEditEiCommand();
		
		if (!$this->getRelationEntityProperty()->isMaster()) {
			$entityProperty = $this->getRelationEntityProperty();
			if (!$entityProperty->getRelation()->isOrphanRemoval()
					&& (!$this->isSourceMany() && !$this->getTargetMasterAccessProxy()->getConstraint()->allowsNull())) {
								
				throw new InvalidEiComponentConfigurationException('EiProp requires an EntityProperty '
						. ReflectionUtils::prettyPropName($entityProperty->getEntityModel()->getClass(), $entityProperty->getName())
						. ' which removes orphans or target ' . $this->getTargetMasterAccessProxy()
						. ' must accept null.');
			}
		}
		
			
// 		$this->embeddedPseudoCommand = new EmbeddedPseudoCommand($this->getTarget());
// 		$this->getTarget()->getEiEngine()->getEiCommandCollection()->add($this->embeddedPseudoCommand);
		
// 		$this->embeddedEditPseudoCommand = new EmbeddedEditPseudoCommand($this->getRelationEiProp()->getEiEngine()->getEiSpec()->getDefaultEiDef()->getLabel() 
// 						. ' > ' . $this->relationEiProp->getLabel() . ' Embedded Edit', 
// 				$this->getRelationEiProp()->getId(), $this->getTarget()->getId());
		
// 		$this->getTarget()->getEiEngine()->getEiCommandCollection()->add($this->embeddedEditPseudoCommand);
	}
	
	public function isReadOnlyRequired(EiMapping $mapping, EiFrame $eiFrame) {
		if (parent::isReadOnlyRequired($mapping, $eiFrame) || $this->hasRecursiveConflict($eiFrame)) return true;

		$esConstraint = $eiFrame->getManageState()->getSecurityManager()
				->getConstraintBy($this->getTarget());
		
		return $esConstraint !== null
				&& !$esConstraint->isEiCommandAvailable($this->embeddedEditPseudoCommand);		
	}
	
	public function completeMagCollection(MagCollection $magCollection) {
		$dtc = new DynamicTextCollection('rocket');
		$magCollection->addMag(DraftableEiPropAdapter::ATTR_DRAFTABLE_KEY,
				new BoolMag($dtc->translate('ei_impl_draftable_label'), self::OPTION_DRAFTABLE_DEFAULT));
		$magCollection->addMag(TranslatableEiPropAdapter::OPTION_TRANSLATION_ENABLED_KEY,
				new BoolMag($dtc->translate('ei_impl_translatable_label'), self::OPTION_TRANSLATION_ENABLED_DEFAULT));
		
		parent::completeMagCollection($magCollection);
		return $magCollection;
	}
	
	const OPTION_DRAFTABLE_DEFAULT = false;
	const OPTION_TRANSLATION_ENABLED_DEFAULT = false;
	
	public function isDraftable() {
		return false;
		return $this->relationEiProp->getAttributes()->get(DraftableEiPropAdapter::ATTR_DRAFTABLE_KEY, 
				self::OPTION_DRAFTABLE_DEFAULT);
	}
	
// 	public function isTranslationEnabled() {
// 		return $this->relationEiProp->getAttributes()->get(TranslatableEiPropAdapter::OPTION_TRANSLATION_ENABLED_KEY,
// 				self::OPTION_TRANSLATION_ENABLED_DEFAULT);
// 	}
	
	protected function configureTargetEiFrame(EiFrame $targetEiFrame, EiFrame $eiFrame, 
			EiObject $eiObject = null, $editCommandRequired = null) {
		parent::configureTargetEiFrame($targetEiFrame, $eiFrame, $eiObject);
		
		$targetEiFrame->setOverviewDisabled(true);
		
// 		if ($targetEiFrame->isPseudo()) {
// 			if ($editCommandRequired) {
// 				$targetEiFrame->setExecutedEiCommand($this->embeddedEditPseudoCommand);
// 			} else {
// 				$targetEiFrame->setExecutedEiCommand($this->embeddedPseudoCommand);
// 			}
// 			return;
// 		}

		if ($eiObject !== null && null !== $targetEiFrame->getOverviewUrlExt() 
				&& null !== $targetEiFrame->getDetailPathExt()) {
			$pathExt = $eiFrame->getControllerContext()->toPathExt()->ext(
					$eiFrame->getContextEiMask()->getEiEngine()->getEiSpec()->getEntryDetailPathExt($eiObject->toEntryNavPoint()));
			$targetEiFrame->setOverviewPathExt($pathExt);
			$targetEiFrame->setDetailPathExt($pathExt);
		}
		
		$targetEiFrame->setDetailBreadcrumbLabelOverride($this->relationEiProp->getLabelLstr()
				->t($targetEiFrame->getN2nLocale()));
		$targetEiFrame->setDetailDisabled(true);
	}
	
	public function createTargetEiObject(EiFrame $targetEiFrame, $targetEntity) {
		$id = $this->relationEiProp->getId();
		
		$targetEiObject = new EiObject($targetEiFrame->getContextEiMask()->getEiEngine()->getEiSpec()
				->extractId($targetEntity), $targetEntity);
		
		return $EiObject;
	}
}
