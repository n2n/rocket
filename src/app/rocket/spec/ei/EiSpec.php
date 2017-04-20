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
namespace rocket\spec\ei;

use n2n\core\container\PdoPool;
use n2n\persistence\orm\model\EntityModel;
use n2n\core\module\Module;
use n2n\reflection\ArgUtils;
use n2n\core\container\N2nContext;
use rocket\spec\ei\component\command\PrivilegedEiCommand;
use n2n\reflection\ReflectionUtils;
use rocket\spec\ei\manage\EiFrame;
use n2n\util\ex\UnsupportedOperationException;
use rocket\spec\ei\manage\security\PrivilegeBuilder;
use rocket\spec\ei\mask\EiMaskCollection;
use rocket\spec\ei\EiSpec;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\EntityManager;
use rocket\spec\ei\EiThing;
use n2n\persistence\orm\util\NestedSetStrategy;
use rocket\spec\ei\manage\veto\VetoableActionListener;
use rocket\spec\ei\manage\veto\VetoableRemoveAction;
use rocket\spec\ei\EiEngine;
use rocket\spec\config\Spec;
use n2n\l10n\Lstr;

class EiSpec extends Spec implements EiThing {
	private $entityModel;
	private $eiDef;
	private $eiEngine;
	
	private $superEiSpec;
	protected $subEiSpecs = array();
	
	private $defaultEiMask;
	private $eiMaskCollection;
	
	private $dataSourceName = null;
	private $nestedSetStrategy;
	
	private $vetoListeners = array();
	
	/**
	 * @param string $id
	 * @param Module $moduleNamespace
	 * @param EntityModel $entityModel
	 */
	public function __construct($id, $moduleNamespace) {
		parent::__construct($id, $moduleNamespace);
		
		$this->eiDef = new EiDef();
		$this->eiEngine = new EiEngine($this);
		$this->eiMaskCollection = new EiMaskCollection($this);
	}

	public function getEiThingPath(): EiThingPath {
		return new EiThingPath(array($this->getId()));
	}

	public function setEntityModel(EntityModel $entityModel) {
		IllegalStateException::assertTrue($this->entityModel === null);
		$this->entityModel = $entityModel;
	}
	
	/**
	 * @return \n2n\persistence\orm\model\EntityModel
	 */
	public function getEntityModel(): EntityModel {
		IllegalStateException::assertTrue($this->entityModel !== null);
		return $this->entityModel;
	}
	
	/**
	 * @param EiSpec $superEiSpec
	 */
	public function setSuperEiSpec(EiSpec $superEiSpec) {
		$this->superEiSpec = $superEiSpec;
		$superEiSpec->subEiSpecs[$this->getId()] = $this;
		
		$superEiEngine = $superEiSpec->getEiEngine();
		$this->eiEngine->getEiFieldCollection()->setInheritedCollection($superEiEngine->getEiFieldCollection());
		$this->eiEngine->getEiCommandCollection()->setInheritedCollection($superEiEngine->getEiCommandCollection());
		$this->eiEngine->getEiModificatorCollection()->setInheritedCollection(
				$superEiEngine->getEiModificatorCollection());
	}
	
	public function getLabelLstr(): Lstr {
		return new Lstr($this->eiDef->getLabel(), $this->moduleNamespace);
	}
		
	public function getPluralLabelLstr(): Lstr {
		return new Lstr($this->eiDef->getPluralLabel(), $this->moduleNamespace);
	}
	
	/**
	 * @return \rocket\spec\ei\EiDef
	 */
	public function getDefaultEiDef(): EiDef {
		return $this->eiDef;
	}
	
	public function getEiEngine(): EiEngine {
		return $this->eiEngine;
	}
	
	public function getMaskedEiThing() {
		return null;
	}
	
	/**
	 * @return \rocket\spec\ei\EiSpec
	 */
	public function getSuperEiSpec(): EiSpec {
		if ($this->superEiSpec !== null) {
			return $this->superEiSpec;
		}
		
		throw new IllegalStateException('EiSpec has not SuperEiSpec: ' . (string) $this);
	}
	
	/**
	 * @return boolean
	 */
	public function hasSuperEiSpec(): bool {
		return $this->superEiSpec !== null;
	}
	
	/**
	 * @return \rocket\spec\ei\EiSpec
	 */
	public function getSupremeEiSpec(): EiSpec {
		$topEiSpec = $this;
		while ($topEiSpec->hasSuperEiSpec()) {
			$topEiSpec = $topEiSpec->getSuperEiSpec();
		}
		return $topEiSpec;
	}
	
	public function getAllSuperEiSpecs($includeSelf = false) {
		$superEiSpecs = array();
		
		if ($includeSelf) {
			$superEiSpecs[$this->getId()] = $this;
		}
		
		$eiSpec = $this;
		while (null != ($eiSpec = $eiSpec->getSuperEiSpec())) {
			$superEiSpecs[$eiSpec->getId()] = $eiSpec;
		}
		return $superEiSpecs;
	}
	
	/**
	 * @return boolean
	 */
	public function hasSubEiSpecs() {
		return (bool) sizeof($this->subEiSpecs);
	}
	
	/**
	 * @return \rocket\spec\ei\EiSpec[]
	 */
	public function getSubEiSpecs() {
		return $this->subEiSpecs;
	}
	
	public function containsSubEiSpecId($eiSpecId, $deepCheck = false) {
		if (isset($this->subEiSpecs[$eiSpecId])) return true;
		
		if ($deepCheck) {
			foreach ($this->subEiSpecs as $subEiSpec) {
				if ($subEiSpec->containsSubEiSpecId($eiSpecId, $deepCheck)) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * @return \rocket\spec\ei\EiSpec[]
	 */
	public function getAllSubEiSpecs() {
		return $this->lookupAllSubEiSpecs($this); 
	}
	
	/**
	 * @param EiSpec $eiSpec
	 * @return \rocket\spec\ei\EiSpec[]
	 */
	private function lookupAllSubEiSpecs(EiSpec $eiSpec) {
		$subEiSpecs = $eiSpec->getSubEiSpecs();
		foreach ($subEiSpecs as $subEiSpec) {
			$subEiSpecs = array_merge($subEiSpecs, 
					$this->lookupAllSubEiSpecs($subEiSpec));
		}
		
		return $subEiSpecs;
	}
	
	public function findEiSpecByEntityModel(EntityModel $entityModel) {
		if ($this->entityModel->equals($entityModel)) {
			return $this;
		}
		
		foreach ($this->getAllSuperEiSpecs() as $superEiSpec) {
			if ($superEiSpec->getEntityModel()->equals($entityModel)) {
				return $superEiSpec;
			}
		}
		
		foreach ($this->getAllSubEiSpecs() as $subEiSpec) {
			if ($subEiSpec->getEntityModel()->equals($entityModel)) {
				return $subEiSpec;
			}
		}
	}
	/**
	 * @param EntityModel $entityModel
	 * @throws \InvalidArgumentException
	 * @return \rocket\spec\ei\EiSpec
	 */
	public function determineEiSpec(EntityModel $entityModel): EiSpec {
		if ($this->entityModel->equals($entityModel)) {
			return $this;
		}
		
		foreach ($this->getAllSubEiSpecs() as $subEiSpec) {
			if ($subEiSpec->getEntityModel()->equals($entityModel)) {
				return $subEiSpec;
			}
		}
				
		// @todo make better exception
		throw new \InvalidArgumentException('No EiSpec for Entity \'' 
				. $entityModel->getClass()->getName() . '\' defined.');
	}
		
	public function determineAdequateEiSpec(\ReflectionClass $class): EiSpec {
		if (!ReflectionUtils::isClassA($class, $this->entityModel->getClass())) {
			throw new \InvalidArgumentException('Class \'' . $class->getName()
					. '\' is not instance of \'' . $this->getEntityModel()->getClass()->getName() . '\'.');
		} 
		
		$eiSpec = $this;
		
		foreach ($this->getAllSubEiSpecs() as $subEiSpec) {
			if (ReflectionUtils::isClassA($class, $subEiSpec->getEntityModel()->getClass())) {
				$eiSpec = $subEiSpec;
			}
		}
		
		return $eiSpec;
	}
	
// 	public function createDraftModel(DraftManager $draftManager) {
// 		$draftModel = new DraftModel($draftManager, $this->entityModel, $this->getDraftables(false),
// 				$this->getDraftables(true));
		
// 		return $draftModel;
// 	}

	
	public function setupEiFrame(EiFrame $eiFrame) {
		foreach ($this->getEiEngine()->getEiModificatorCollection() as $eiModificator) {
			$eiModificator->setupEiFrame($eiFrame);
		}
	}
	
	public function hasSecurityOptions() {
		return $this->superEiSpec === null;
	}
	
	public function getPrivilegeOptions(N2nContext $n2nContext) {
		if ($this->superEiSpec !== null) return null;
		
		return $this->buildPrivilegeOptions($this, $n2nContext, array());
	}
	
	private function buildPrivilegeOptions(EiSpec $eiSpec, N2nContext $n2nContext, array $options) {
		$n2nLocale = $n2nContext->getN2nLocale();
		foreach ($eiSpec->getEiCommandCollection()->filterLevel() as $eiCommand) {
			if ($eiCommand instanceof PrivilegedEiCommand) {
				$options[PrivilegeBuilder::buildPrivilege($eiCommand)]
						= $eiCommand->getPrivilegeLabel($n2nLocale);
			}
				
			if ($eiCommand instanceof PrivilegeExtendableEiCommand) {
				$privilegeOptions = $eiCommand->getPrivilegeExtOptions($n2nLocale);
					
				ArgUtils::valArrayReturnType($privilegeOptions, 'scalar', $eiCommand, 'getPrivilegeOptions');
					
				foreach ($privilegeOptions as $privilegeExt => $label) {
					if ($eiSpec->hasSuperEiSpec()) {
						$label . ' (' . $eiSpec->getLabel() . ')';
					}
					
					$options[PrivilegeBuilder::buildPrivilege($eiCommand, $privilegeExt)] = $label;
				}
			}
		}
		
		foreach ($eiSpec->getSubEiSpecs() as $subEiSpec) {
			$options = $this->buildPrivilegeOptions($subEiSpec, $n2nContext, $options);
		}
		
		return $options;
	}
	
	private function ensureIsTop() {
		if ($this->superEiSpec !== null) {
			throw new UnsupportedOperationException('EiSpec has super EiSpec');
		}
	}
	
// 	public function createRestrictionSelectorItems(N2nContext $n2nContext) {
// 		$this->ensureIsTop();
		
// 		$restrictionSelectorItems = array();
// 		foreach ($this->eiFieldCollection as $eiField) {
// 			if (!($eiField instanceof RestrictionEiField)) continue;
			
// 			$restrictionSelectorItem = $eiField->createRestrictionSelectorItem($n2nContext);
			
// 			ArgUtils::valTypeReturn($restrictionSelectorItem, 'rocket\spec\ei\manage\critmod\filter\impl\field\SelectorItem', 
// 					$eiField, 'createRestrictionSelectorItem');
			
// 			$restrictionSelectorItems[$eiField->getId()] = $restrictionSelectorItem;
// 		}
		
// 		return $restrictionSelectorItems;
// 	}
	
	public function isObjectValid($object) {
		return is_object($object) && ReflectionUtils::isObjectA($object, $this->getEntityModel()->getClass());
	}
// 	/**
// 	 * @param unknown $propertyName
// 	 * @return \rocket\spec\ei\component\field\ObjectPropertyEiField
// 	 */
// 	public function containsEiFieldPropertyName($propertyName) {
// 		foreach ($this->eiFieldCollection as $eiField) {
// 			if ($eiField instanceof ObjectPropertyEiField
// 					&& $eiField->getPropertyName() == $propertyName) {
// 				return true;
// 			}
// 		}
		
// 		return false;
// 	}
	/**
	 * @param string $dataSourceName
	 */
	public function setDataSourceName($dataSourceName) {
		$this->dataSourceName = $dataSourceName;
	}
	/**
	 * @return string
	 */
	public function getDataSourceName() {
		return $this->dataSourceName;
	}
	
	/**
	 * @return \n2n\persistence\orm\util\NestedSetStrategy
	 */
	public function getNestedSetStrategy() {
		return $this->nestedSetStrategy;
	}
	
	/**
	 * @param NestedSetStrategy $nestedSetStrategy
	 */
	public function setNestedSetStrategy(NestedSetStrategy $nestedSetStrategy = null) {
		$this->nestedSetStrategy = $nestedSetStrategy;
	}
	

// 	private $mainTranslationN2nLocale;
// 	private $translationN2nLocales;
	
// 	public function getMainTranslationN2nLocale() {
// 		if ($this->superEiSpec !== null) {
// 			return $this->superEiSpec->getMainTranslationN2nLocale();	
// 		} 
		
// 		if ($this->mainTranslationN2nLocale === null) {
// 			return N2nLocale::getDefault();
// 		}
		
// 		return $this->mainTranslationN2nLocale;
// 	}
	
// 	public function setMainTranslationN2nLocale(N2nLocale $mainTranslationN2nLocale = null) {
// 		if ($this->superEiSpec !== null) {
// 			return $this->superEiSpec->setMainTranslationN2nLocale($mainTranslationN2nLocale);	
// 		}
		
// 		$this->mainTranslationN2nLocale = $mainTranslationN2nLocale;
// 	}
	
// 	public function getTranslationN2nLocales() {
// 		if ($this->superEiSpec !== null) {
// 			return $this->superEiSpec->getTranslationN2nLocales();	
// 		}
		
// 		if ($this->translationN2nLocales === null) {
// 			$n2nLocales = N2N::getN2nLocales();
// 			unset($n2nLocales[$this->getMainTranslationN2nLocale()->getId()]);
// 			return $n2nLocales;
// 		}
		
// 		return $this->translationN2nLocales;
// 	}
	
// 	public function setTranslationN2nLocales(array $translationN2nLocales = null) {
// 		if ($this->superEiSpec === null) {
// 			$this->translationN2nLocales = $translationN2nLocales;
// 		}
		
// 		$this->superEiSpec->setTranslationN2nLocales($translationN2nLocales);
// 	}
	
	/**
	 * @param \n2n\core\container\PdoPool $dbhPool
	 * @return \n2n\persistence\orm\EntityManager
	 */
	public function lookupEntityManager(PdoPool $dbhPool, $transactional = false): EntityManager {
		$emf = $this->lookupEntityManagerFactory($dbhPool);
		if ($transactional) {
			return $emf->getTransactional();
		} else {
			return $emf->getExtended();
		}
	}
	/**
	 * @param PdoPool $dbhPool
	 * @return \n2n\persistence\orm\EntityManagerFactory
	 */
	public function lookupEntityManagerFactory(PdoPool $dbhPool) {
		return $dbhPool->getEntityManagerFactory($this->dataSourceName);
	}
	/**
	 * @param $entity
	 * @return mixed
	 */
	public function extractId($entity) {
		return $this->entityModel->getIdDef()->getEntityProperty()->readValue($entity);
	}
	
	/**
	 * @param mixed $id
	 * @return string
	 * @throws \InvalidArgumentException if null is passed as id.
	 */
	public function idToIdRep($id): string {
		return $this->entityModel->getIdDef()->getEntityProperty()->valueToRep($id);
	}
	
	/**
	 * @param string $idRep
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function idRepToId(string $idRep) {
		return $this->entityModel->getIdDef()->getEntityProperty()->repToValue($idRep);
	}
	
	/**
	 * @return EiMaskCollection
	 */
	public function getEiMaskCollection(): EiMaskCollection {
		return $this->eiMaskCollection;
	}
	
	public function __toString(): string {
		return 'EiSpec [id: ' . $this->getId() . ']';
	}
	
	public function isAbstract(): bool {
		return $this->entityModel->getClass()->isAbstract();
	}
	
	public function registerVetoableActionListener(VetoableActionListener $vetoListener) {
		$this->vetoListeners[spl_object_hash($vetoListener)] = $vetoListener;
	}
	
	public function unregisterVetoableActionListener(VetoableActionListener $vetoListener) {
		unset($this->vetoListeners[spl_object_hash($vetoListener)]);
	}
	
	public function onRemove(VetoableRemoveAction $vetoableRemoveAction, N2nContext $n2nContext) {
		foreach ($this->vetoListeners as $vetoListener) {
			$vetoListener->onRemove($vetoableRemoveAction, $n2nContext);
		}
	}
}
