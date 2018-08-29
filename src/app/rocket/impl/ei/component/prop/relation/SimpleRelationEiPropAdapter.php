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

use rocket\ei\manage\gui\GuiProp;
use rocket\ei\component\prop\DraftableEiProp;
use rocket\ei\manage\draft\DraftProperty;
use rocket\impl\ei\component\prop\relation\model\relation\EiPropRelation;
use rocket\impl\ei\component\prop\adapter\DisplaySettings;
use rocket\impl\ei\component\prop\adapter\StandardEditDefinition;
use rocket\ei\component\prop\FilterableEiProp;
use rocket\ei\manage\frame\EiFrame;
use n2n\core\container\N2nContext;
use rocket\ei\manage\critmod\filter\impl\controller\GlobalFilterPropController;
use rocket\ei\component\CritmodFactory;
use rocket\impl\ei\component\prop\relation\model\filter\RelationFilterProp;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\ei\util\model\EiuFrame;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\manage\critmod\filter\impl\controller\FilterAjahHook;
use rocket\ei\util\model\EiuMask;
use rocket\ei\util\model\Eiu;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\manage\gui\ui\DisplayItem;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\GuiPropFork;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\manage\security\filter\SecurityFilterProp;

abstract class SimpleRelationEiPropAdapter extends RelationEiPropAdapter implements GuiProp, DraftableEiProp, 
		DraftProperty, FilterableEiProp {
	protected $displaySettings;

	protected function initialize(EiPropRelation $eiPropRelation, StandardEditDefinition $standardEditDefinition = null, 
			DisplaySettings $displayDefinition = null) {
		parent::initialize($eiPropRelation, $standardEditDefinition, $standardEditDefinition);

		if ($displayDefinition !== null) {
			$this->displaySettings = $displayDefinition;
		} else {
			$this->displaySettings = new DisplaySettings(ViewMode::all());
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::getDisplayLabel()
	 */
	public function getDisplayLabel(): string {
		return $this->getLabelCode();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::getDisplaySettings()
	 */
	public function getDisplaySettings(): DisplaySettings {
		return $this->displaySettings;
	}
	
	public function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
		$viewMode = $eiu->gui()->getViewMode();
		
		if (!$this->displaySettings->isViewModeCompatible($viewMode)) {
			return null;
		}
		
		return new DisplayDefinition($this->getLabelLstr(), $this->getDisplayItemType(), 
				$this->displaySettings->isViewModeDefaultDisplayed($viewMode));
	}
	
	protected function getDisplayItemType(): ?string {
		return DisplayItem::TYPE_SIMPLE_GROUP;
	}
	
	public function getGuiProp(): ?GuiProp {
		return $this;
	}
	
	public function getGuiPropFork(): ?GuiPropFork {
		return null;
	}
	
	/**
	 * @return boolean
	 */
	public function isStringRepresentable(): bool {
		return true;
	}
	
	public function buildManagedFilterProp(EiFrame $eiFrame): ?FilterProp  {
		$targetEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame);
		
		return new RelationFilterProp($this->getLabelLstr(), $this->getEntityProperty(),
				new EiuFrame($targetEiFrame), 
				new class($targetEiFrame) implements TargetFilterDef {
					private $targetEiFrame;
					
					public function __construct(EiFrame $targetEiFrame) {
						$this->targetEiFrame = $targetEiFrame;
					}
					
					public function getFilterDefinition(): FilterDefinition {
						return $this->targetEiFrame->getContextEiEngine()
								->createFilterDefinition($this->targetEiFrame);
					}
	
					public function getFilterAjahHook(): FilterAjahHook {
						$targetEiMask = $this->targetEiFrame->getContextEiEngine()->getEiMask();
						
						return GlobalFilterPropController::buildFilterAjahHook(
								$this->targetEiFrame->getN2nContext()->lookup(ScrRegistry::class),
								$targetEiMask);
					}
				});
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\FilterableEiProp::buildFilterProp()
	 */
	public function buildFilterProp(Eiu $eiu): ?FilterProp {
		$targetEiMask = $this->eiPropRelation->getTargetEiMask();
		$n2nContext = $eiu->getN2nContext();

		return new RelationFilterProp($this->getLabelLstr(), $this->getEntityProperty(),
				$eiu->frame(),
				new class($targetEiMask, $n2nContext) implements TargetFilterDef {
					private $targetEiMask;
					private $n2nContext;
					
					public function __construct(EiMask $targetEiMask, N2nContext $n2nContext) {
						$this->targetEiMask = $targetEiMask;
						$this->n2nContext = $n2nContext;
					}
					
					public function getFilterDefinition(): FilterDefinition {
						return $this->targetEiMask->getEiEngine()->createGeneralFilterDefinition($this->n2nContext);
					}
	
					public function getFilterAjahHook(): FilterAjahHook {
						return GlobalFilterPropController::buildFilterAjahHook(
								$this->n2nContext->lookup(ScrRegistry::class), $this->targetEiMask);
					}
				});
	}
	

	public function buildSecurityFilterProp(Eiu $eiu): ?SecurityFilterProp {
		return null;
	}
	
	protected function createAdvTargetFilterDef(N2nContext $n2nContext): TargetFilterDef {
		$targetEiMask = $this->eiPropRelation->getTargetEiMask();
		return new class($targetEiMask, $n2nContext) implements TargetFilterDef {
			private $targetEiMask;
			private $n2nContext;
			
			public function __construct(EiMask $targetEiMask, N2nContext $n2nContext) {
				$this->targetEiMask = $targetEiMask;
				$this->n2nContext = $n2nContext;
			}
				
			public function getFilterDefinition(): FilterDefinition {
				return (new CritmodFactory($this->targetEiMask->getEiEngine()->getEiMask()->getEiPropCollection(),
								$this->targetEiMask->getEiEngine()->getEiModificatorCollection()))
						->createEiEntryFilterDefinition($this->n2nContext);
			}

			public function getFilterAjahHook(): FilterAjahHook {
				$targetEiMask = $this->targeteiFrame->getTargetEiMask();

				return GlobalFilterPropController::buildEiEntryFilterAjahHook(
						$this->n2nContext->lookup(ScrRegistry::class),
						$this->targetEiMask->getEiEngine()->getEiMask()->getEiType()->getId(), $this->targetEiMask->getExtension()->getId());
			}
		};
	}

}


interface TargetFilterDef {
	public function getFilterDefinition(): FilterDefinition;
	
	public function getFilterAjahHook(): FilterAjahHook;
}
