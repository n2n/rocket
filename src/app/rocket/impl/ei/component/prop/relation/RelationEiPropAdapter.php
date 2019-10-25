<?php
/*
 * Copyright (c) 2012-2016, HofmÃ¤nner New Media.
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
 * Bert HofmÃ¤nner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas GÃ¼nther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\impl\ei\component\prop\relation;

use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\impl\ei\component\prop\adapter\PropertyEiPropAdapter;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\GuiProp;
use n2n\l10n\N2nLocale;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\impl\ei\component\prop\relation\model\gui\ToOneGuiField;
use rocket\impl\ei\component\prop\relation\model\gui\ToManyGuiField;
use rocket\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\model\Relation;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\ei\component\prop\ForkEiProp;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\frame\EiForkLink;
use rocket\impl\ei\component\prop\relation\conf\RelationConfig;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;

abstract class RelationEiPropAdapter extends PropertyEiPropAdapter implements RelationEiProp, GuiEiProp, GuiProp, ForkEiProp {
			
	/**
	 * @var RelationConfig
	 */
	private $relationConfig;
	/**
	 * @var DisplayConfig
	 */
	private $displayConfig;
	/**
	 * @var EditConfig
	 */
	private $editConfig;
	
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	
	/**
	 * @var Relation
	 */
	private $relation;
			
	/**
	 * 
	 */
	function __construct() {
		parent::__construct();
	}
	
	function isPrivileged(): bool {
		return true;
	}
	
	/**
	 * @param RelationModel $relationModel
	 */
	protected function setup(?DisplayConfig $displayConfig, RelationModel $relationModel,
			AdaptableEiPropConfigurator $configurator = null) {
		$this->relationConfig = $configurator ?? new RelationEiPropConfigurator($this);
				
		if ($displayConfig !== null) {
			$this->displayConfig = $displayConfig;
			$this->relationConfig->registerDisplayConfig($displayConfig);
		}
		
		if (null !== ($this->editConfig = $relationModel->getEditConfig())) {
			$this->relationConfig->registerEditConfig($this->editConfig);
		}
		
		$this->relationModel = $relationModel;
		$this->relationConfig->setRelationModel($relationModel);
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\relation\conf\RelationModel
	 */
	protected function getRelationModel() {
		IllegalStateException::assertTrue($this->relationModel !== null, get_class($this));
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
		return $this->requireEntityProperty();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\PropertyEiPropAdapter::createEiPropConfigurator()
	 */
	function createEiPropConfigurator(): EiPropConfigurator {
		return $this->relationConfig;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\GuiEiProp::buildGuiProp()
	 */
	function buildGuiProp(Eiu $eiu): ?GuiProp {
	    return GuiProps::configAndFieldFactory($this, $this->displayConfig);
	}
	
	function isStringRepresentable(): bool {
		return $this->getRelation()->isTargetOne();
	}
	
	function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		$targetEntityObj = $eiu->object()->readNativValue($this->eiu->prop()->getEiProp());
		
		if ($targetEntityObj === null) {
			return null;
		}
		
		$targetEiuEngine = $this->getRelation()->getTargetEiuEngine();
		return $targetEiuEngine->createIdentityString($targetEntityObj);
	}
	
	function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
		return $this->displayConfig->toDisplayDefinition($eiu->gui()->getViewMode(), $eiu->prop()->getLabel(),
				$eiu->prop()->getHelpText());
	}
	
	function buildGuiField(Eiu $eiu): ?GuiField {
		if (!$this->getRelationModel()->isTargetMany()) {
			return new ToOneGuiField($eiu, $this->getRelationModel());
		}
		
		return new ToManyGuiField($eiu, $this->getRelationModel());
	}
	
	function createForkedEiFrame(Eiu $eiu, EiForkLink $eiForkLink): EiFrame {
		return $this->getRelation()->createForkEiFrame($eiu, $eiForkLink);
	}
}
