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

use rocket\spec\ei\manage\EiFrame;
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
	
	public function isEmbeddedAddActivated(EiFrame $eiFrame) {
		return $this->isEmbeddedAddEnabled() /*&& !$this->hasRecursiveConflict($eiFrame)
				&& $eiFrame->isEiCommandAvailable($this->embeddedEditPseudoEiCommand)*/;
	}
	
	protected function configureTargetEiFrame(EiFrame $targetEiFrame, EiFrame $eiFrame, 
			EiSelection $eiSelection = null, $editCommandRequired = null) {
		parent::configureTargetEiFrame($targetEiFrame, $eiFrame, $eiSelection, $editCommandRequired);
		
// 		if (!$this->isTargetMany()) {
// 			$targetEiFrame->setOverviewDisabled(true);
// 			$targetEiFrame->setDetailBreadcrumbLabel($this->buildDetailLabel($eiFrame, $eiSelection));
// 			return;
// 		}
		
// 		$targetEiFrame->setOverviewBreadcrumbLabel($this->buildDetailLabel($eiFrame, $eiSelection));
		
		
	}
	
	protected function buildDetailLabel(EiFrame $eiFrame) {
		$label = $this->relationEiField->getLabel();
		
		do {
			if ($eiFrame->isDetailDisabled() 
					&& null !== ($detaiLabel = $eiFrame->getDetailBreadcrumbLabel())) {
				$label = $detaiLabel . ' > ' . $label; 
			}
		} while (null !== ($eiFrame = $eiFrame->getParent()));
		
		return $label;
	}
	

	public function buildTargetOverviewToolsUrl(EiFrame $eiFrame, HttpContext $httpContext): Url {
		$contextUrl = $httpContext->getControllerContextPath($eiFrame->getControllerContext())
				->ext($this->relationEiCommand->getId(), 'rel', $this->relationAjahEiCommand->getId())->toUrl();
		return RelationAjahController::buildSelectToolsUrl($contextUrl);
	}
}
