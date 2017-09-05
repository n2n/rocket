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

use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\EiFrame;
use rocket\spec\ei\component\field\impl\adapter\DraftableEiFieldAdapter;
use rocket\spec\ei\component\field\impl\relation\command\EmbeddedEditPseudoCommand;
use rocket\spec\ei\component\field\impl\relation\command\EmbeddedPseudoCommand;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use n2n\reflection\ReflectionUtils;

class EmbeddedEiFieldRelation extends EiFieldRelation {
	private $embeddedPseudoCommand;
	private $embeddedEditPseudoCommand;

	public function init(EiSpec $targetEiSpec, EiMask $targetEiMask) {
		parent::init($targetEiSpec, $targetEiMask);

		if (!$this->isPersistCascaded()) {
			$entityProperty = $this->getRelationEiField()->getEntityProperty();
			throw new InvalidEiComponentConfigurationException(
					'EiField requires an EntityProperty which cascades persist: ' 
							. ReflectionUtils::prettyPropName($entityProperty->getEntityModel()->getClass(),
									$entityProperty->getName()));
		}
		
		if ($this->isDraftable() && !$this->isJoinTableRelation($this)) {
			throw new InvalidEiComponentConfigurationException(
					'Only EiFields of properties with join table relations can be drafted.');
		}
		
		$this->setupEmbeddedEditEiCommand();
		
		// reason to remove: orphans should never remain in db on embeddedeiprops
// 		if (!$this->getRelationEntityProperty()->isMaster()) {
			$entityProperty = $this->getRelationEntityProperty();
			if (!$entityProperty->getRelation()->isOrphanRemoval()
					&& (!$this->isSourceMany() /*&& !$this->getTargetMasterAccessProxy()->getConstraint()->allowsNull()*/)) {
								
				throw new InvalidEiComponentConfigurationException('EiProp requires an EntityProperty '
						. ReflectionUtils::prettyPropName($entityProperty->getEntityModel()->getClass(), $entityProperty->getName())
						. ' which removes orphans' . /* or target ' . $this->getTargetMasterAccessProxy()
						. ' must accept null*/ '.');
			}
// 		}
		
			
// 		$this->embeddedPseudoCommand = new EmbeddedPseudoCommand($this->getTarget());
// 		$this->getTarget()->getEiEngine()->getEiCommandCollection()->add($this->embeddedPseudoCommand);
		
// 		$this->embeddedEditPseudoCommand = new EmbeddedEditPseudoCommand($this->getRelationEiField()->getEiEngine()->getEiSpec()->getDefaultEiDef()->getLabel() 
// 						. ' > ' . $this->relationEiField->getLabel() . ' Embedded Edit', 
// 				$this->getRelationEiField()->getId(), $this->getTarget()->getId());
		
// 		$this->getTarget()->getEiEngine()->getEiCommandCollection()->add($this->embeddedEditPseudoCommand);
	}
	
	public function isReadOnlyRequired(EiMapping $mapping, EiFrame $eiFrame) {
		if (parent::isReadOnlyRequired($mapping, $eiFrame) || $this->hasRecursiveConflict($eiFrame)) return true;

		$esConstraint = $eiFrame->getManageState()->getSecurityManager()
				->getConstraintBy($this->getTarget());
		
		return $esConstraint !== null
				&& !$esConstraint->isEiCommandAvailable($this->embeddedEditPseudoCommand);		
	}
	
// 	public function completeMagCollection(MagCollection $magCollection) {
// 		$dtc = new DynamicTextCollection('rocket');
// 		$magCollection->addMag(DraftableEiFieldAdapter::ATTR_DRAFTABLE_KEY,
// 				new BoolMag($dtc->translate('ei_impl_draftable_label'), self::OPTION_DRAFTABLE_DEFAULT));
// 		$magCollection->addMag(TranslatableEiFieldAdapter::OPTION_TRANSLATION_ENABLED_KEY,
// 				new BoolMag($dtc->translate('ei_impl_translatable_label'), self::OPTION_TRANSLATION_ENABLED_DEFAULT));
		
// 		parent::completeMagCollection($magCollection);
// 		return $magCollection;
// 	}
	
	const OPTION_DRAFTABLE_DEFAULT = false;
	const OPTION_TRANSLATION_ENABLED_DEFAULT = false;
	
	public function isDraftable() {
		return false;
		return $this->relationEiField->getAttributes()->get(DraftableEiFieldAdapter::ATTR_DRAFTABLE_KEY, 
				self::OPTION_DRAFTABLE_DEFAULT);
	}
	
// 	public function isTranslationEnabled() {
// 		return $this->relationEiField->getAttributes()->get(TranslatableEiFieldAdapter::OPTION_TRANSLATION_ENABLED_KEY,
// 				self::OPTION_TRANSLATION_ENABLED_DEFAULT);
// 	}
	
	protected function configureTargetEiFrame(EiFrame $targetEiFrame, EiFrame $eiFrame, 
			EiSelection $eiSelection = null, $editCommandRequired = null) {
		parent::configureTargetEiFrame($targetEiFrame, $eiFrame, $eiSelection);
		
		$targetEiFrame->setOverviewDisabled(true);
		
// 		if ($targetEiFrame->isPseudo()) {
// 			if ($editCommandRequired) {
// 				$targetEiFrame->setExecutedEiCommand($this->embeddedEditPseudoCommand);
// 			} else {
// 				$targetEiFrame->setExecutedEiCommand($this->embeddedPseudoCommand);
// 			}
// 			return;
// 		}

		if ($eiSelection !== null && null !== $targetEiFrame->getOverviewUrlExt() 
				&& null !== $targetEiFrame->getDetailPathExt()) {
			$pathExt = $eiFrame->getControllerContext()->toPathExt()->ext(
					$eiFrame->getContextEiMask()->getEiEngine()->getEiSpec()->getEntryDetailPathExt($eiSelection->toEntryNavPoint()));
			$targetEiFrame->setOverviewPathExt($pathExt);
			$targetEiFrame->setDetailPathExt($pathExt);
		}
		
		$targetEiFrame->setDetailBreadcrumbLabelOverride($this->relationEiField->getLabelLstr()
				->t($targetEiFrame->getN2nLocale()));
		$targetEiFrame->setDetailDisabled(true);
	}
	
// 	public function createTargetEiSelection(EiFrame $targetEiFrame, $targetEntity) {
// 		$id = $this->relationEiField->getId();
		
// 		$targetEiSelection = new EiSelection($targetEiFrame->getContextEiMask()->getEiEngine()->getEiSpec()
// 				->extractId($targetEntity), $targetEntity);
		
// 		return $EiSelection;
// 	}
}
