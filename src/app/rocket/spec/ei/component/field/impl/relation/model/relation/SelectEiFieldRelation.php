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

use rocket\spec\ei\manage\EiState;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\EiSpec;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\component\InvalidEiComponentConfigurationException;
use rocket\spec\ei\component\field\impl\relation\command\RelationAjahEiCommand;
use rocket\spec\ei\component\field\impl\relation\command\RelationAjahController;
use n2n\util\uri\Url;
use n2n\web\http\HttpContext;

class SelectEiFieldRelation extends EiFieldRelation {
	private $embeddedAddEnabled = false;
	
	protected $embeddedPseudoEiCommand;
	protected $embeddedEditPseudoEiCommand;
	
	public function init(EiSpec $targetEiSpec, EiMask $targetEiMask) {
		parent::init($targetEiSpec, $targetEiMask);

		if ($this->isEmbeddedAddEnabled() && !$this->isPersistCascaded()) {
			throw new InvalidEiComponentConfigurationException(
					'Enabled embedded add option requires EntityProperty which cascades persist.');
		}
		
// 		if ($this->isEmbeddedAddEnabled()) {
			$this->setupEmbeddedEditEiCommand();
// 		}
		
// 		if ($this->isEmbeddedAddEnabled()) {
// 			$this->embeddedEditPseudoEiCommand = new EmbeddedEditPseudoCommand(
// 					$this->getRelationEiField()->getEiEngine()->getEiSpec()->getDefaultEiDef()->getLabel() . ' > ' 
// 							. $this->relationEiField->getLabel() . ' Embedded Add', 
// 					$this->getRelationEiField()->getId(), $this->getTarget()->getId());
// 			$this->getTarget()->getEiEngine()->getEiCommandCollection()->add($this->embeddedEditPseudoEiCommand);
// 		}
		
// 		$this->embeddedPseudoEiCommand = new EmbeddedPseudoCommand($this->getTarget());
// 		$this->target->getEiEngine()->getEiCommandCollection()->add($this->embeddedPseudoEiCommand);

	}
	
	public function isEmbeddedAddEnabled(): bool {
		return $this->embeddedAddEnabled;
	}
	
	public function setEmbeddedAddEnabled(bool $embeddedAddEnabled) {
		$this->embeddedAddEnabled = $embeddedAddEnabled;
	}
	
	public function isEmbeddedAddActivated(EiState $eiState) {
		return $this->isEmbeddedAddEnabled() /*&& !$this->hasRecursiveConflict($eiState)
				&& $eiState->isEiCommandAvailable($this->embeddedEditPseudoEiCommand)*/;
	}
	
	protected function configureTargetEiState(EiState $targetEiState, EiState $eiState, 
			EiSelection $eiSelection = null, $editCommandRequired = null) {
		parent::configureTargetEiState($targetEiState, $eiState, $eiSelection, $editCommandRequired);
		
// 		if (!$this->isTargetMany()) {
// 			$targetEiState->setOverviewDisabled(true);
// 			$targetEiState->setDetailBreadcrumbLabel($this->buildDetailLabel($eiState, $eiSelection));
// 			return;
// 		}
		
// 		$targetEiState->setOverviewBreadcrumbLabel($this->buildDetailLabel($eiState, $eiSelection));
		
		
	}
	
	protected function buildDetailLabel(EiState $eiState) {
		$label = $this->relationEiField->getLabel();
		
		do {
			if ($eiState->isDetailDisabled() 
					&& null !== ($detaiLabel = $eiState->getDetailBreadcrumbLabel())) {
				$label = $detaiLabel . ' > ' . $label; 
			}
		} while (null !== ($eiState = $eiState->getParent()));
		
		return $label;
	}
	

	public function buildTargetOverviewToolsUrl(EiState $eiState, HttpContext $httpContext): Url {
		$contextUrl = $httpContext->getControllerContextPath($eiState->getControllerContext())
				->ext($this->relationEiCommand->getId(), 'rel', $this->relationAjahEiCommand->getId())->toUrl();
		return RelationAjahController::buildSelectToolsUrl($contextUrl);
	}
}
