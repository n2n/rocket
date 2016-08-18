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
namespace rocket\spec\ei\component\field\impl\relation;

use rocket\spec\ei\manage\gui\GuiField;
use rocket\spec\ei\component\field\DraftableEiField;
use rocket\spec\ei\manage\draft\DraftProperty;
use rocket\spec\ei\component\field\impl\relation\model\relation\EiFieldRelation;
use rocket\spec\ei\manage\gui\DisplayDefinition;
use rocket\spec\ei\component\field\impl\adapter\StandardEditDefinition;
use rocket\spec\ei\component\field\FilterableEiField;
use rocket\spec\ei\manage\EiState;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\critmod\filter\impl\controller\GlobalFilterFieldController;
use rocket\spec\ei\component\CritmodFactory;
use rocket\spec\ei\component\field\impl\relation\model\filter\RelationFilterField;
use n2n\http\controller\impl\ScrRegistry;
use rocket\spec\ei\manage\util\model\GlobalEiUtils;
use rocket\spec\ei\manage\util\model\EiStateUtils;
use rocket\spec\ei\mask\EiMask;
use rocket\spec\ei\manage\critmod\filter\FilterDefinition;
use rocket\spec\ei\manage\critmod\filter\impl\controller\FilterAjahHook;

abstract class SimpleRelationEiFieldAdapter extends RelationEiFieldAdapter implements GuiField, DraftableEiField, DraftProperty, FilterableEiField {
	protected $displayDefinition;
	protected $standardEditDefinition;

	protected function initialize(EiFieldRelation $eiFieldRelation, DisplayDefinition $displayDefinition = null,
			StandardEditDefinition $standardEditDefinition = null) {
		parent::initialize($eiFieldRelation);

		if ($displayDefinition !== null) {
			$this->displayDefinition = $displayDefinition;
		} else {
			$this->displayDefinition = new DisplayDefinition();
		}

		if ($standardEditDefinition !== null) {
			$this->standardEditDefinition = $standardEditDefinition;
		} else {
			$this->standardEditDefinition = new StandardEditDefinition();
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::getDisplayLabel()
	 */
	public function getDisplayLabel() {
		return $this->getLabelCode();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\spec\ei\manage\gui\GuiField::getDisplayDefinition()
	 */
	public function getDisplayDefinition() {
		return $this->displayDefinition;
	}
	
	public function getStandardEditDefinition(): StandardEditDefinition {
		return $this->standardEditDefinition;
	}
	
	public function getGuiField() {
		return $this;
	}
	
	public function getGuiFieldFork() {
		return null;
	}
	
	/**
	 * @return boolean
	 */
	public function isStringRepresentable(): bool {
		return true;
	}
	

	public function buildManagedFilterField(EiState $eiState) {
		$targetEiState = $this->eiFieldRelation->createTargetReadPseudoEiState($eiState);
		
		return new RelationFilterField($this->getLabelLstr(), $this->getEntityProperty(),
				new EiStateUtils($targetEiState), 
				new class($targetEiState) implements TargetFilterDef {
					private $targetEiState;
					
					public function __construct(EiState $targetEiState) {
						$this->targetEiState = $targetEiState;
					}
					
					public function getFilterDefinition(): FilterDefinition {
						return $this->targetEiState->getContextEiMask()->getEiEngine()
								->createManagedFilterDefinition($this->targetEiState);
					}
	
					public function getFilterAjahHook(): FilterAjahHook {
						$targetEiMask = $this->targetEiState->getContextEiMask();
						
						return GlobalFilterFieldController::buildFilterAjahHook(
								$this->targetEiState->getN2nContext()->lookup(ScrRegistry::class),
								$targetEiMask);
					}
				});
	}
	
	public function buildFilterField(N2nContext $n2nContext) {
		$targetEiMask = $this->eiFieldRelation->getTargetEiMask();

		return new RelationFilterField($this->getLabelLstr(), $this->getEntityProperty(),
				new GlobalEiUtils($targetEiMask, $n2nContext),
				new class($targetEiMask, $n2nContext) implements TargetFilterDef {
					private $targetEiMask;
					private $n2nContext;
					
					public function __construct(EiMask $targetEiMask, N2nContext $n2nContext) {
						$this->targetEiMask = $targetEiMask;
						$this->n2nContext = $n2nContext;
					}
					
					public function getFilterDefinition(): FilterDefinition {
						return $this->targetEiMask->createFilterDefinition($this->n2nContext);
					}
	
					public function getFilterAjahHook(): FilterAjahHook {
						return GlobalFilterFieldController::buildFilterAjahHook(
								$this->n2nContext->lookup(ScrRegistry::class), $this->targetEiMask);
					}
				});
	}
	

	public function buildEiMappingFilterField(N2nContext $n2nContext) {
		return null;
	}
	
	protected function createAdvTargetFilterDef(N2nContext $n2nContext): TargetFilterDef {
		$targetEiMask = $this->eiFieldRelation->getTargetEiMask();
		return new class($targetEiMask, $n2nContext) implements TargetFilterDef {
			private $targetEiMask;
			private $n2nContext;
			
			public function __construct(EiMask $targetEiMask, N2nContext $n2nContext) {
				$this->targetEiMask = $targetEiMask;
				$this->n2nContext = $n2nContext;
			}
				
			public function getFilterDefinition(): FilterDefinition {
				return (new CritmodFactory($this->targetEiMask->getEiEngine()->getEiFieldCollection(),
								$this->targetEiMask->getEiEngine()->getEiModificatorCollection()))
						->createEiMappingFilterDefinition($this->n2nContext);
			}

			public function getFilterAjahHook(): FilterAjahHook {
				$targetEiMask = $this->targeteiState->getTargetEiMask();

				return GlobalFilterFieldController::buildEiMappingFilterAjahHook(
						$this->n2nContext->lookup(ScrRegistry::class),
						$this->targetEiMask->getEiEngine()->getEiSpec()->getId(), $this->targetEiMask->getId());
			}
		};
	}

}


interface TargetFilterDef {
	public function getFilterDefinition(): FilterDefinition;
	
	public function getFilterAjahHook(): FilterAjahHook;
}
