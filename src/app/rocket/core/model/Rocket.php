<?php
/*
 * Copyright (c) 2013, Hofmänner New Media. All rights reserved.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * ROCKET
 * Bert Hofmänner.............: Idea, Frontend UX, Concept
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Thomas Günther.............: Developer, Frontend UI
 * Yves Lüthi.................: Frontend UI/UX
 * Silvan Bauser..............: Frontend UI
 *
 * License....................: http://www.n2n.ch/modules/rocket/license
 */
namespace rocket\core\model;

use n2n\persistence\orm\EntityManager;
use n2n\l10n\Locale;
use n2n\persistence\orm\EntityModelManager;
use n2n\persistence\orm\Entity;
use n2n\core\VarStore;
use n2n\N2N;
use rocket\script\core\ScriptManager;
use n2n\model\RequestScoped;
use n2n\core\config\source\JsonFileConfigSource;
use n2n\reflection\ReflectionUtils;
use n2n\persistence\orm\store\EntityStateListener;
use n2n\persistence\orm\store\EntityFlushEvent;
use n2n\persistence\orm\EntityModel;
use rocket\script\core\ScriptElementStore;
use rocket\script\entity\ScriptTranslationManager;
use rocket\script\entity\ScriptDraftManager;
use n2n\persistence\DbhPool;
use rocket\script\entity\adaptive\translation\TranslationManager;

class Rocket implements RequestScoped {
	const VERSION = '1.0.0';
	const ROCKET_NAMESPACE = 'rocket';
	const ROCKET_CONFIG_FOLDER = 'rocket';
	const MANAGE_CONFIG_FILE = 'manage.json';
	const SCRIPT_CONFIG_FILE = 'scripts.json';
	const COMPONENT_STORAGE_FILE = 'elements.json';
	
	private $scriptManager;
	private $scriptElementStore;
	private $entityModelManager;
	private $translationModels = array();
	private $rocketEntityStateListener;
	private $dbhPool;
	
	private function _init(DbhPool $dbhPool) {
		$this->dbhPool = $dbhPool;
	}
	
	public function __construct() {
		$this->entityModelManager = EntityModelManager::getInstance();
	}
	/**
	 * @return \rocket\script\core\ScriptManager
	 */
	public function getScriptManager() {
		if ($this->scriptManager === null) {
			$rocketFolderName = ReflectionUtils::encodeNamespace(Rocket::ROCKET_NAMESPACE);
			
			$this->scriptManager = new ScriptManager(
					new JsonFileConfigSource(N2N::getVarStore()->requestFilePath(VarStore::CATEGORY_SRV, Rocket::ROCKET_NAMESPACE,
							null, self::MANAGE_CONFIG_FILE, true, true)),
					new RocketModuleSeparatedConfigSource(N2N::getVarStore(), Rocket::SCRIPT_CONFIG_FILE), 
					N2N::getDbhPool(), EntityModelManager::getInstance());
		}
		
		return $this->scriptManager;
	}
	
	public function getScriptElementStore() {
		if ($this->scriptElementStore === null) {
			$this->scriptElementStore = new ScriptElementStore(new RocketModuleSeparatedConfigSource(
					N2N::getVarStore(), Rocket::COMPONENT_STORAGE_FILE));
		}
		
		return $this->scriptElementStore;
	}
	/**
	 * @param EntityManager $em
	 * @return TranslationManager
	 */
	public function getOrCreateTranslationManager(EntityManager $em) {
		$emObjHash = spl_object_hash($em);
		if (!isset($this->translationManagers[$emObjHash])) {
			$this->translationManagers[$emObjHash] = new ScriptTranslationManager($this->getScriptManager(), $em);
		}
		
		return $this->translationManagers[$emObjHash];
	}
	
	public function getOrCreateDraftManager(EntityManager $em) {
		$emObjHash = spl_object_hash($em);
		if (!isset($this->draftManagers[$emObjHash])) {
			$this->draftManagers[$emObjHash] = new ScriptDraftManager($this->getScriptManager(), $em);
		}
		
		return $this->draftManagers[$emObjHash];
	}
	
// 	private function getTranslationModel($entity, EntityManager $em = null) {
// 		$className = get_class($entity);
// 		if (isset($this->translationModels[$className])) {
// 			return $this->translationModels[$className];
// 		}
		
// 		$entityModel = $this->entityModelManager->getEntityModelByObject($entity);
// 		$entityScript = $this->getScriptManager()->getEntityScriptByClass($entityModel->getClass());
// 		if ($em === null) {
// 			$em = $entityScript->lookupEntityManager(N2N::getDbhPool());
// 		}
		
// 		$translationModel = TranslationModelFactory::createTranslationModel($em, $entityScript);
// 		if (!isset($this->translationModels[$className])) {
// 			$this->translationModels[$className] = $translationModel;
// 		}
		
// 		return $this->translationModels[$className];
// 	}
	
	public function translate(Entity $entity, Locale $locale, EntityManager $em = null) {
		if (Locale::getDefault()->equals($locale)) {
			return $entity;
		}

		$entityModel = $this->entityModelManager->getEntityModelByObject($entity);
		$entityScript = $this->getScriptManager()->getEntityScriptByClass($entityModel->getClass());
		if ($em === null) {
			$em = $entityScript->lookupEntityManager($this->dbhPool);
		}
		
		$translationManager = $this->getOrCreateTranslationManager($em);
			
		return $translationManager->find($entity, $locale, true)
				->getTranslatedEntity();
	}
	
	public function translateArray($entries, Locale $locale, EntityManager $em = null) {		
		$translatedEntries = array();
		if ($entries instanceof \ArrayObject) {
			$translatedEntries = new \ArrayObject();
		}
		
		foreach ($entries as $key => $entry) {
			$translatedEntries[$key] = $this->translate($entry, $locale, $em);
		}
		return $translatedEntries;
	}
	
	public function listen(EntityManager $em) {
		if ($this->rocketEntityStateListener === null) {
			$this->rocketEntityStateListener = new RocketEntityStateListener($this);
		}
		
		$em->getPersistenceContext()->registerEntityStateListener($this->rocketEntityStateListener);
	}
}

class RocketEntityStateListener implements EntityStateListener {
	private $rocket;
	
	public function __construct(Rocket $rocket) {
		$this->rocket = $rocket;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\EntityStateListener::entityAdded()
	 */
	public function entityAdded(Entity $object) {}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\EntityStateListener::rawDataMapUpdated()
	 */
	public function rawDataMapUpdated(Entity $entity, \ArrayObject $rawDataMap) {
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\EntityStateListener::entityIdentified()
	 */
	public function entityIdentified(EntityModel $entityModel, $id, Entity $entity) {
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\EntityStateListener::entityDetached()
	 */
	public function entityDetached(EntityModel $entityModel, $id, Entity $entity) {
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\EntityStateListener::entityRemoved()
	 */
	public function entityRemoved(EntityModel $entityModel, $id, Entity $entity) {
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\EntityStateListener::onEntityFlushEvent()
	 */
	public function onEntityFlushEvent(EntityFlushEvent $event) {
		$scriptManager = $this->rocket->getScriptManager();
		$class = $event->getEntityModel()->getClass();
		if ($scriptManager->containsEntityScriptClass($class)) {
			$scriptManager->getEntityScriptByClass($class)->triggerEntityFlushEvent($event, $this->rocket);
		}
	}	
}