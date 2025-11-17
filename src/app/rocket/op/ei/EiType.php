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
namespace rocket\op\ei;

use n2n\persistence\orm\model\EntityModel;
use n2n\core\container\N2nContext;
use n2n\reflection\ReflectionUtils;
use n2n\util\ex\UnsupportedOperationException;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\util\NestedSetStrategy;
use rocket\op\spec\Type;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\manage\veto\VetoableLifecycleAction;
use rocket\op\ei\manage\EiObject;
use rocket\op\ei\manage\LiveEiObject;
use rocket\op\ei\manage\EiEntityObj;
use rocket\op\ei\manage\DraftEiObject;
use rocket\op\ei\manage\draft\Draft;
use rocket\op\spec\TypePath;
use rocket\ui\si\meta\SiTypeContext;
use rocket\op\spec\Spec;
use ReflectionClass;

class EiType extends Type {
	private EntityModel $entityModel;

	private ?EiType $superEiType = null;
	protected array $subEiTypes = array();

	private EiMask $eiMask;
	private $eiTypeExtensionCollection;
	
	private ?string $dataSourceName = null;
	private ?NestedSetStrategy $nestedSetStrategy = null;
	
	/**
	 * @var EiLifecycleListener[]
	 */
	private array $eiLifecycleListeners = array();

	public function __construct(string $id, string $moduleNamespace, private ReflectionClass $class,
			string $label, string $pluralLabel, string $iconType, private Spec $spec, ?string $identityStringPattern,
			private ?\Closure $entityModelCallback, private ?\Closure $initializeCallback) {
		parent::__construct($id, $moduleNamespace);

		$this->eiMask = new EiMask($this, $label, $pluralLabel, $iconType);
		$this->eiMask->getDef()->setIdentityStringPattern($identityStringPattern);
		$this->eiTypeExtensionCollection = new EiTypeExtensionCollection($this);
	}

	/**
	 * @return ReflectionClass
	 */
	function getClass() {
		return $this->class;
	}

	/**
	 * @return Spec
	 */
	function getSpec() {
		return $this->spec;
	}

	/**
	 * @return EntityModel
	 */
	public function getEntityModel(): EntityModel {
		if ($this->entityModelCallback === null) {
			IllegalStateException::assertTrue(isset($this->entityModel));
			return $this->entityModel;
		}

		$callback = $this->entityModelCallback;
		$this->entityModelCallback = null;
		$entityModel = $callback();
		IllegalStateException::assertTrue($entityModel instanceof EntityModel
				&& $this->class->getName() === $entityModel->getClass()->getName());
		return $this->entityModel = $entityModel;
	}

	function isInitialized(): bool {
		return $this->initializeCallback === null;
	}

	function ensureInitialized(): void {
		if ($this->initializeCallback === null) {
			return;
		}

		$callback = $this->initializeCallback;
		$this->initializeCallback = null;
		$callback($this);
	}
	
	/**
	 * @param EiType $superEiType
	 */
	public function setSuperEiType(EiType $superEiType) {
		$this->superEiType = $superEiType;
		$superEiType->subEiTypes[$this->getId()] = $this;

		$eiMask = $this->getEiMask();
		$superEiMask = $superEiType->getEiMask();
		$eiMask->getEiPropCollection()->setInheritedCollection($superEiMask->getEiPropCollection());
		$eiMask->getEiCmdCollection()->setInheritedCollection($superEiMask->getEiCmdCollection());
		$eiMask->getEiModCollection()->setInheritedCollection($superEiMask->getEiModCollection());
	}
	
	/**
	 * @return EiType
	 */
	public function getSuperEiType(): EiType {
		$this->ensureInitialized();

		if ($this->superEiType !== null) {
			return $this->superEiType;
		}
		
		throw new IllegalStateException('EiType has not SuperEiType: ' . (string) $this);
	}
	
	/**
	 * @return boolean
	 */
	public function hasSuperEiType(): bool {
		$this->ensureInitialized();

		return $this->superEiType !== null;
	}
	
	/**
	 * @return EiType
	 */
	public function getSupremeEiType(): EiType {
		$this->ensureInitialized();

		$topEiType = $this;
		while ($topEiType->hasSuperEiType()) {
			$topEiType = $topEiType->getSuperEiType();
		}
		return $topEiType;
	}
	
	public function getAllSuperEiTypes($includeSelf = false) {
		$this->ensureInitialized();

		$superEiTypes = array();
		
		if ($includeSelf) {
			$superEiTypes[$this->getId()] = $this;
		}
		
		$eiType = $this;
		while ($eiType->hasSuperEiType()) {
			$eiType = $eiType->getSuperEiType();
			$superEiTypes[$eiType->getId()] = $eiType;
		}
		return $superEiTypes;
	}
	
	/**
	 * @return boolean
	 */
	public function hasSubEiTypes(): bool {
		$this->ensureInitialized();

		return (bool) sizeof($this->subEiTypes);
	}
	
	/**
	 * @return EiType[]
	 */
	public function getSubEiTypes() {
		$this->ensureInitialized();

		return $this->subEiTypes;
	}

	function containsSuperEiTypeId(string $eiTypeId, bool $deepCheck): bool {
		if ($this->superEiType === null) {
			return false;
		}

		if ($this->superEiType->getId() === $eiTypeId) {
			return true;
		}

		if (!$deepCheck) {
			return false;
		}

		$eiType = $this;
		while (null !== ($eiType = $eiType->getSuperEiType())) {
			if ($eiType->getId() === $eiTypeId) {
				return true;
			}
		}

		return false;
	}

	public function containsSubEiTypeId(string $eiTypeId, bool $deepCheck = false): bool {
		$this->ensureInitialized();

		if (isset($this->subEiTypes[$eiTypeId])) return true;
		
		if ($deepCheck) {
			foreach ($this->subEiTypes as $subEiType) {
				if ($subEiType->containsSubEiTypeId($eiTypeId, $deepCheck)) {
					return true;
				}
			}
		}
		
		return false;
	}

	/**
	 * @param string $eiTypeId
	 * @throws UnknownEiTypeException
	 */
	function determineEiTypeById(string $eiTypeId) {
		$this->ensureInitialized();

		if ($this->getId() === $eiTypeId) {
			return $this;
		}
		
		return $this->getSubEiTypeById($eiTypeId, true);
	}
	
	/**
	 * @param string $eiTypeId
	 * @param bool $deepCheck
	 * @throws UnknownEiTypeException
	 * @return EiType
	 */
	public function getSubEiTypeById(string $eiTypeId, bool $deepCheck = false) {
		$this->ensureInitialized();

		if (isset($this->subEiTypes[$eiTypeId])) {
			return $this->subEiTypes[$eiTypeId];
		}
		
		if ($deepCheck) {
			foreach ($this->subEiTypes as $subEiType) {
				try {
					return $subEiType->getSubEiTypeById($eiTypeId, true);
				} catch (UnknownEiTypeException $e) { }
			}
		}
		
		throw new UnknownEiTypeException('EiType ' . $this->__toString() . ' contains no sub EiType with id ' . $eiTypeId);
	}
	
	/**
	 * @return EiType[]
	 */
	public function getAllSubEiTypes(bool $includeSelf = false) {
		$this->ensureInitialized();
		
		$subEiTypes = [];
		
		if ($includeSelf) {
			$subEiTypes[] = $this;
		}
		
		return array_merge($subEiTypes, $this->lookupAllSubEiTypes($this)); 
	}
	
	/**
	 * @param EiType $eiType
	 * @return EiType[]
	 */
	private function lookupAllSubEiTypes(EiType $eiType) {
		$this->ensureInitialized();
		
		$subEiTypes = $eiType->getSubEiTypes();
		foreach ($subEiTypes as $subEiType) {
			$subEiTypes = array_merge($subEiTypes, 
					$this->lookupAllSubEiTypes($subEiType));
		}
		
		return $subEiTypes;
	}
	
	public function findEiTypeByEntityModel(EntityModel $entityModel) {
		$this->ensureInitialized();
		
		if ($this->entityModel->equals($entityModel)) {
			return $this;
		}
		
		foreach ($this->getAllSuperEiTypes() as $superEiType) {
			if ($superEiType->getEntityModel()->equals($entityModel)) {
				return $superEiType;
			}
		}
		
		foreach ($this->getAllSubEiTypes() as $subEiType) {
			if ($subEiType->getEntityModel()->equals($entityModel)) {
				return $subEiType;
			}
		}

		return null;
	}
	/**
	 * @param EntityModel $entityModel
	 * @return EiType
	 *@throws \InvalidArgumentException
	 */
	public function determineEiType(EntityModel $entityModel): EiType {
		$this->ensureInitialized();
		
		if ($this->entityModel->equals($entityModel)) {
			return $this;
		}
		
		foreach ($this->getAllSubEiTypes() as $subEiType) {
			if ($subEiType->getEntityModel()->equals($entityModel)) {
				return $subEiType;
			}
		}
				
		// @todo make better exception
		throw new \InvalidArgumentException('No EiType for Entity \'' 
				. $entityModel->getClass()->getName() . '\' defined.');
	}
		
	public function determineAdequateEiType(ReflectionClass $class): EiType {
		$this->ensureInitialized();
		
		if (!ReflectionUtils::isClassA($class, $this->entityModel->getClass())) {
			throw new \InvalidArgumentException('Class \'' . $class->getName()
					. '\' is not instance of \'' . $this->getEntityModel()->getClass()->getName() . '\'.');
		} 
		
		$eiType = $this;
		
		foreach ($this->getAllSubEiTypes() as $subEiType) {
			if (ReflectionUtils::isClassA($class, $subEiType->getClass())) {
				$eiType = $subEiType;
			}
		}
		
		return $eiType;
	}
	
	/**
	 * @param TypePath $typePath
	 * @throws UnknownEiTypeException
	 * @throws UnknownEiTypeExtensionException
	 * @return EiMask
	 */
	public function determineEiMask(TypePath $typePath): EiMask {
		$this->ensureInitialized();
		
		$eiType = $this;
		if ($this->getId() !== $typePath->getTypeId()) {
			$eiType = $this->getSubEiTypeById($typePath->getTypeId(), true);
		}
		
		$extensionId = $typePath->getEiTypeExtensionId();
		if ($extensionId === null) {
			return $eiType->getEiMask();
		}
		
		return $eiType->getEiTypeExtensionCollection()->getById($extensionId)->getEiMask();
	}
	
	private function ensureIsTop() {
		if ($this->superEiType !== null) {
			throw new UnsupportedOperationException('EiType has super EiType');
		}
	}
	
	/**
	 * @param object $object
	 * @return boolean
	 */
	public function isObjectValid($object): bool {
		return is_object($object) && ReflectionUtils::isObjectA($object, $this->getClass());
	}
	
	public function isA(EiType $eiType): bool {
		return $this->equals($eiType) || $this->getClass()->isSubclassOf($eiType->getClass());
	}

	/**
	 * 
	 * @return EiMask
	 */
	public function getEiMask(): EiMask {
		$this->ensureInitialized();

		return $this->eiMask;
	}
	
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
		$this->ensureInitialized();

		return $this->dataSourceName;
	}
	
	/**
	 * @return NestedSetStrategy
	 */
	public function getNestedSetStrategy() {
		$this->ensureInitialized();

		return $this->nestedSetStrategy;
	}
	
	/**
	 * @param NestedSetStrategy|null $nestedSetStrategy
	 */
	public function setNestedSetStrategy(?NestedSetStrategy $nestedSetStrategy) {
		$this->nestedSetStrategy = $nestedSetStrategy;
	}
	
//	/**
//	 * @param \n2n\core\container\PdoPool $dbhPool
//	 * @return \n2n\persistence\orm\EntityManager
//	 */
//	public function lookupEntityManager(PdoPool $dbhPool, $transactional = false): EntityManager {
//		$emf = $this->lookupEntityManagerFactory($dbhPool);
//		if ($transactional) {
//			return $emf->getTransactional();
//		} else {
//			return $emf->getExtended();
//		}
//	}
//	/**
//	 * @param PdoPool $dbhPool
//	 * @return \n2n\persistence\orm\EntityManagerFactory
//	 */
//	public function lookupEntityManagerFactory(PdoPool $dbhPool) {
//		return $dbhPool->getEntityManagerFactory($this->getDataSourceName());
//	}
	/**
	 * @param object $entityObj
	 * @return mixed
	 */
	public function extractId($entityObj) {
		return $this->getEntityModel()->getIdDef()->getEntityProperty()->readValue($entityObj);
	}
	
	/**
	 * <p>Converts the id of an entity object of this {@see EiType} into a pid. In rocket pid stands for 
	 * <strong>Practical Identifier</strong>. Ids of entities can have diffrent types which isn&apos;t practical.</p>
	 * 
	 * <p>Pids are always strings which can&apos;t contain slashes or backslashes. This allowes a pid to be used 
	 * in a url path (most servers can&apos;t handle urlencoded slashes in paths).</p>
	 * 
	 * <p><strong>Note:</strong> This method currently uses <code>urlencode()</code> to mask slashes and backslahes 
	 * which could change in further versions. If you want to embed a pid in a url you stil have to encode it even if 
	 * this means that a pid gets urlencoded a second time.</p>
	 * 
	 * @param mixed $id
	 * @return string
	 * @throws \InvalidArgumentException if null is passed as id.
	 */
	public function idToPid($id): string {
		return urlencode($this->entityModel->getIdDef()->getEntityProperty()->valueToRep($id));
	}
	
	/**
	 * <p>Converts a pid back to an id. {@see self::idToPid()} for further informations.</p>
	 * 
	 * @param string $pid
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function pidToId(string $pid) {
		return $this->entityModel->getIdDef()->getEntityProperty()->repToValue(urldecode($pid));
	}
	
	/**
	 * @return EiTypeExtensionCollection
	 */
	public function getEiTypeExtensionCollection(): EiTypeExtensionCollection {
		return $this->eiTypeExtensionCollection;
	}
	
	/**
	 * @param bool $draft
	 * @return EiObject
	 */
	public function createNewEiObject(bool $draft = false): EiObject {
		if (!$draft) {
			return new LiveEiObject(EiEntityObj::createNew($this));
		}
		
		return new DraftEiObject(new Draft(null, EiEntityObj::createNew($this), new \DateTime()));
	}

	function createEiObject(object $obj): EiObject {
		return LiveEiObject::create($this, $obj);
	}

	public function __toString(): string {
		return 'EiType [id: ' . $this->getId() . ']';
	}
	
	public function isAbstract(): bool {
		return $this->entityModel->getClass()->isAbstract();
	}
	
	public function registerVetoableActionListener(EiLifecycleListener $eiLifecycleListener) {
		$this->eiLifecycleListeners[spl_object_hash($eiLifecycleListener)] = $eiLifecycleListener;
	}
	
	public function unregisterVetoableActionListener(EiLifecycleListener $eiLifecycleListener) {
		unset($this->eiLifecycleListeners[spl_object_hash($eiLifecycleListener)]);
	}
	
	public function validateLifecycleAction(VetoableLifecycleAction $vetoableLifecycleAction, N2nContext $n2nContext) {
		foreach ($this->eiLifecycleListeners as $eiLifecycleListener) {
			if ($vetoableLifecycleAction->isPersist()) {
				$eiLifecycleListener->onPersist($vetoableLifecycleAction, $n2nContext);
			} else if ($vetoableLifecycleAction->isUpdate()) {
				$eiLifecycleListener->onUpdate($vetoableLifecycleAction, $n2nContext);
			} else {
				$eiLifecycleListener->onRemove($vetoableLifecycleAction, $n2nContext);
			}
		}
	}
	
	/**
	 * @return SiTypeContext
	 */
	function createSiTypeContext() {
		return (new SiTypeContext($this->getSupremeEiType()->getId(), array_map(
						function ($subEiType) { return $subEiType->getId(); },
						$this->getAllSubEiTypes(true))))
				->setTreeMode(null !== $this->nestedSetStrategy);
	}
}
