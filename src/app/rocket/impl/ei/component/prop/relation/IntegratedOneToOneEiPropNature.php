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

use rocket\impl\ei\component\prop\relation\model\ToOneEiField;
use rocket\op\ei\manage\LiveEiObject;
use n2n\util\type\CastUtils;
use n2n\impl\web\dispatch\mag\model\ObjectMagAdapter;
use n2n\web\dispatch\Dispatchable;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\Raw;
use n2n\web\ui\UiComponent;
use n2n\util\type\ArgUtils;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\op\ei\util\Eiu;
use n2n\web\dispatch\mag\UiOutfitter;
use rocket\op\ei\manage\gui\GuiProp;
use n2n\web\dispatch\mag\Mag;
use rocket\op\ei\manage\gui\field\GuiFieldForkEditable;
use rocket\op\ei\util\gui\EiuGuiEntryAssembler;
use rocket\op\ei\manage\gui\GuiDefinition;
use rocket\op\ei\manage\entry\EiFieldNature;
use rocket\op\ei\manage\gui\EiFieldAbstraction;
use rocket\impl\ei\component\prop\adapter\entry\EiFieldWrapperCollection;
use rocket\op\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\op\ei\manage\EiObject;
use n2n\reflection\property\PropertyAccessProxy;

class IntegratedOneToOneEiPropNature extends RelationEiPropNatureAdapter /*implements GuiPropFork*/ {

	public function __construct(ToOneEntityProperty $entityProperty, PropertyAccessProxy $accessProxy) {
		ArgUtils::assertTrue($entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_ONE);

		parent::__construct($entityProperty, $accessProxy,
				new RelationModel($this, false, false, RelationModel::MODE_INTEGRATED, null));
	}
	
	private GuiDefinition $forkedGuiDefinition;
	
//	public function buildGuiPropFork(Eiu $eiu): ?GuiPropFork {
//		$this->forkedGuiDefinition = $eiu->context()->engine($this->eiPropRelation->getTargetEiMask())
//				->getGuiDefinition();
//
//		return $this;
//	}
	
//	public function getForkedGuiDefinition(): GuiDefinition {
//		return $this->forkedGuiDefinition;
//	}
	
	public function buildEiField(Eiu $eiu): ?EiFieldNature {
		$targetEiuFrame = $eiu->frame()->forkSelect($eiu->prop(), $eiu->object())
				->frame()->exec($this->getRelationModel()->getTargetReadEiCmdPath());

		$field = new ToOneEiField($eiu, $targetEiuFrame, $this, $this->getRelationModel());
		$field->setMandatory($this->getRelationModel()->isMandatory());
		return $field;
	}
	
//	/**
//	 * @param Eiu $eiu
//	 * @return GuiFieldFork
//	 */
//	public function createGuiFieldFork(Eiu $eiu): GuiFieldFork {
//		$eiFrame = $eiu->frame()->getEiFrame();
//		$eiEntry = $eiu->entry()->getEiEntry();
//
//		$targetEiFrame = null;
//		if ($eiu->entryGui()->isReadOnly()) {
//			$targetEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame, $eiEntry);
//		} else {
//			$targetEiFrame = $this->eiPropRelation->createTargetEditPseudoEiFrame($eiFrame, $eiEntry);
//		}
//
//		$targetEiuFrame = (new Eiu($targetEiFrame))->frame();
//
//		$eiuField = $eiu->field();
//		$targetRelationEntry = $eiuField->getValue();
//		CastUtils::assertTrue($targetRelationEntry instanceof RelationEntry || $targetRelationEntry === null);
//
//		if ($targetRelationEntry === null) {
//			$targetRelationEntry = RelationEntry::fromM($targetEiuFrame->newEntry()->getEiEntry());
//		} else if (!$targetRelationEntry->hasEiEntry()) {
//			$targetRelationEntry = RelationEntry::fromM(
//					$targetEiuFrame->entry($targetRelationEntry->getEiObject())->getEiEntry());
//		}
//
//		$targetEiuGuiEntryAssembler = $targetEiuFrame->entry($targetRelationEntry->getEiEntry())
//				->newEntryGuiAssembler($eiu->guiFrame()->getViewMode());
//
//		return new OneToOneGuiFieldFork($eiuField->getEiField(), $targetRelationEntry, $targetEiuGuiEntryAssembler);
//	}
	
//	/**
//	 * {@inheritDoc}
//	 * @see \rocket\op\ei\manage\gui\GuiPropFork::determineForkedEiObject()
//	 */
//	public function determineForkedEiObject(Eiu $eiu): ?EiObject {
//		$targetObject = $eiu->object()->readNativeValue($eiu->prop()->getEiProp());
//		if ($targetObject === null) {
//			return null;
//		}
//		return LiveEiObject::create($this->eiPropRelation->getTargetEiType(), $targetObject);
//	}
//
//	public function determineEiFieldAbstraction(Eiu $eiu, DefPropPath $defPropPath): EiFieldAbstraction {
//		$eiEntry = $eiu->entry()->getEiEntry();
//
//		$targetRelationEntry = $eiEntry->getValue(EiPropPath::from($this->eiPropRelation->getRelationEiProp()));
//		if ($targetRelationEntry === null || !$targetRelationEntry->hasEiEntry()) {
//			return new EiFieldWrapperCollection([]);
//		}
//
//		return $this->getForkedGuiDefinition()->determineEiFieldAbstraction($eiu->getN2nContext(),
//				$targetRelationEntry->getEiEntry(), $defPropPath);
//	}

	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		return null;	
	}


}

//class OneToOneGuiFieldFork implements GuiFieldFork {
//	private $toOneEiField;
//	private $targetRelationEntry;
//	private $targetEiuGuiEntryAssembler;
//
//	public function __construct(ToOneEiField $toOneEiField, RelationEntry $targetRelationEntry,
//			EiuGuiEntryAssembler $targetEiuGuiEntryAssembler) {
//		$this->toOneEiField = $toOneEiField;
//		$this->targetRelationEntry = $targetRelationEntry;
//		$this->targetEiuGuiEntryAssembler = $targetEiuGuiEntryAssembler;
//	}
//
//	public function assembleGuiField(DefPropPath $defPropPath): GuiField {
//		return $this->targetEiuGuiEntryAssembler->assembleGuiField($defPropPath);
//	}
//
//	public function isReadOnly(): bool {
//		return null === $this->targetEiuGuiEntryAssembler->getEiuGuiEntry()->getDispatchable();
//	}
//
//	public function getEditable(): ?GuiFieldForkEditable {
//		if ($this->isReadOnly()) return null;
//
//		return new OneToOneGuiFieldForkEditable($this->toOneEiField, $this->targetEiuGuiEntryAssembler,
//				$this->targetRelationEntry);
//	}
//}

//class OneToOneGuiFieldForkEditable implements GuiFieldForkEditable {
//	private $toOneEiField;
//	private $targetEiuGuiEntryAssembler;
//	private $targetRelationEntry;
//
//	/**
//	 * @param ToOneEiField $toOneEiField
//	 * @param EiGuiValueBoundaryAssembler $targetEiGuiValueBoundaryAssembler
//	 */
//	public function __construct(ToOneEiField $toOneEiField, EiuGuiEntryAssembler $targetEiuGuiEntryAssembler,
//			RelationEntry $targetRelationEntry) {
//		$this->toOneEiField = $toOneEiField;
//		$this->targetEiuGuiEntryAssembler = $targetEiuGuiEntryAssembler;
//		$this->targetRelationEntry = $targetRelationEntry;
//	}
//
//	public function isForkMandatory(): bool {
//		return true;
//	}
//
//	/**
//	 * {@inheritDoc}
//	 * @see \rocket\op\ei\manage\gui\field\GuiFieldForkEditable::getForkMag()
//	 */
//	public function getForkMag(): Mag {
//		$dispatchable = $this->targetEiuGuiEntryAssembler->getEiuGuiEntry()->getDispatchable();
//
//		if ($dispatchable !== null) {
//			return new OneToOneForkMag($dispatchable);
//		}
//
//		return null;
//	}
//
//	/**
//	 * {@inheritDoc}
//	 * @see \rocket\op\ei\manage\gui\field\GuiFieldForkEditable::getAdditionalForkMagPropertyPaths()
//	 */
//	public function getInheritForkMagAssemblies(): array {
//		return $this->targetEiuGuiEntryAssembler->getEiuGuiEntry()->getAllForkMagAssemblies();
//	}
//
//	/**
//	 *
//	 */
//	public function save() {
//// 		$this->targetEiGuiValueBoundaryAssembler->save();
//		$this->toOneEiField->setValue($this->targetRelationEntry);
//	}
//}
//
//class OneToOneForkMag extends ObjectMagAdapter {
//	private $dispatchable;
//
//	/**
//	 * @param Dispatchable $dispatchable
//	 */
//	public function __construct(Dispatchable $dispatchable) {
//		parent::__construct('', $dispatchable);
//	}
//
//	/**
//	 * {@inheritDoc}
//	 * @see \n2n\web\dispatch\mag\Mag::createUiField()
//	 */
//	public function createUiField(PropertyPath $propertyPath, HtmlView $view, UiOutfitter $uiOutfitter): UiComponent {
//		return new Raw();
//	}
//}
