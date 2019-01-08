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
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use rocket\ei\component\prop\FilterableEiProp;
use rocket\ei\manage\frame\EiFrame;
use n2n\core\container\N2nContext;
use rocket\ei\util\filter\controller\ScrFilterPropController;
use rocket\ei\component\CritmodFactory;
use rocket\impl\ei\component\prop\relation\model\filter\RelationFilterProp;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\util\filter\controller\FilterJhtmlHook;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\manage\gui\ui\DisplayItem;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\manage\security\filter\SecurityFilterProp;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\security\InaccessibleEiCommandPathException;
use rocket\core\model\Rocket;
use n2n\l10n\Lstr;

abstract class SimpleRelationEiPropAdapter extends RelationEiPropAdapter implements GuiProp, DraftableEiProp, 
		DraftProperty, FilterableEiProp {
	protected $displayConfig;

	protected function initialize(EiPropRelation $eiPropRelation, EditConfig $editConfig = null, 
			DisplayConfig $displayDefinition = null) {
		parent::initialize($eiPropRelation, $editConfig);

		if ($displayDefinition !== null) {
			$this->displayConfig = $displayDefinition;
		} else {
			$this->displayConfig = new DisplayConfig(ViewMode::all());
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::getDisplayLabelLstr()
	 */
	public function getDisplayLabelLstr(): Lstr {
		return $this->getLabelLstr();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::getDisplayHelpTextLstr()
	 */
	public function getDisplayHelpTextLstr(): ?Lstr {
		$helpText = $this->displayConfig->getHelpText();
		if ($helpText === null) {
			return null;
		}
		
		return Rocket::createLstr($helpText, $this->getWrapper()->getEiPropCollection()->getEiMask()->getModuleNamespace());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::getDisplayConfig()
	 */
	public function getDisplayConfig(): DisplayConfig {
		return $this->displayConfig;
	}
	
	public function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
		$viewMode = $eiu->gui()->getViewMode();
		
		if (!$this->displayConfig->isViewModeCompatible($viewMode)) {
			return null;
		}
		
		return new DisplayDefinition($this->getDisplayItemType(), 
				$this->displayConfig->isViewModeDefaultDisplayed($viewMode));
	}
	
	protected function getDisplayItemType(): string {
		return DisplayItem::TYPE_SIMPLE_GROUP;
	}
	
	/**
	 * @var GuiDefinition
	 */
	protected $targetGuiDefinition;
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\GuiEiProp::buildGuiProp()
	 */
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		$this->targetGuiDefinition = $eiu->context()->engine($this->eiPropRelation->getTargetEiMask())->getGuiDefinition();
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function isStringRepresentable(): bool {
		return true;
	}
	
	public function buildManagedFilterProp(EiFrame $eiFrame): ?FilterProp  {
		return null;
		
		$targetEiFrame;
		try {
			$targetEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame);
		} catch (InaccessibleEiCommandPathException $e) {
			return null;
		}
		
		return new RelationFilterProp($this->getLabelLstr(), $this->getEntityProperty(),
				(new Eiu($targetEiFrame))->frame(), 
				new class($targetEiFrame) implements TargetFilterDef {
					private $targetEiFrame;
					
					public function __construct(EiFrame $targetEiFrame) {
						$this->targetEiFrame = $targetEiFrame;
					}
					
					public function getFilterDefinition(): FilterDefinition {
						return $this->targetEiFrame->getContextEiEngine()
								->createFramedFilterDefinition($this->targetEiFrame);
					}
	
					public function getFilterJhtmlHook(): FilterJhtmlHook {
						$targetEiMask = $this->targetEiFrame->getContextEiEngine()->getEiMask();
						
						return ScrFilterPropController::buildFilterJhtmlHook(
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

		$eiuFrame = $eiu->frame(false);
		if (null === $eiuFrame) return null;
		
		return new RelationFilterProp($this->getLabelLstr(), $this->getEntityProperty(), $eiuFrame,
				new class($targetEiMask, $n2nContext) implements TargetFilterDef {
					private $targetEiMask;
					private $n2nContext;
					
					public function __construct(EiMask $targetEiMask, N2nContext $n2nContext) {
						$this->targetEiMask = $targetEiMask;
						$this->n2nContext = $n2nContext;
					}
					
					public function getFilterDefinition(): FilterDefinition {
						return $this->targetEiMask->getEiEngine()->createFilterDefinition($this->n2nContext);
					}
	
					public function getFilterJhtmlHook(): FilterJhtmlHook {
						return ScrFilterPropController::buildFilterJhtmlHook(
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
						->createSecurityFilterDefinition($this->n2nContext);
			}

			public function getFilterJhtmlHook(): FilterJhtmlHook {
				$targetEiMask = $this->targeteiFrame->getTargetEiMask();

				return ScrFilterPropController::buildEiEntryFilterJhtmlHook(
						$this->n2nContext->lookup(ScrRegistry::class),
						$this->targetEiMask->getEiEngine()->getEiMask()->getEiType()->getId(), $this->targetEiMask->getExtension()->getId());
			}
		};
	}

}


interface TargetFilterDef {
	public function getFilterDefinition(): FilterDefinition;
	
	public function getFilterJhtmlHook(): FilterJhtmlHook;
}
