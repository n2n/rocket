<?php

namespace rocket\script\entity\manage;

use n2n\core\IllegalStateException;
use rocket\script\entity\EntityScript;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\mapping\FlushMappingListener;

class EntryManager {
	private $scriptState;
	private $scriptSelectionMapping;
	
	public function __construct(ScriptState $scriptState, ScriptSelectionMapping $scriptSelectionMapping = null) {
		$this->scriptState = $scriptState;
		$this->scriptSelectionMapping = $scriptSelectionMapping;
	}
	
	public function getScriptState() {
		return $this->scriptState;
	}
		
	public function getScriptSelectionMapping() {
		return $this->scriptSelectionMapping;
	}
	
	private function changeInheritanceType(ScriptSelectionMapping $newScriptSelectionMapping) {
		$oldEntityScript = $this->scriptSelectionMapping->determineEntityScript();
		$oldEntity = $this->scriptSelectionMapping->getScriptSelection()->getEntity();
		$newEntity = $newScriptSelectionMapping->getScriptSelection()->getEntity();
		
		$scriptState = $this->scriptState;
		switch ($oldEntityScript->getTypeChangeMode()) {
			case EntityScript::TYPE_CHANGE_MODE_CHANGE:
				$newScriptSelectionMapping->registerListener(
						new FlushMappingListener(function() use ($scriptState, $oldEntity, $newEntity) {
							$scriptState->getEntityManager()->swap($oldEntity, $newEntity);
						}));
				break;
			case EntityScript::TYPE_CHANGE_MODE_REPLACE:
				$newScriptSelectionMapping->registerListener(
						new FlushMappingListener(function() use ($scriptState, $oldEntity, $newEntity) {
							$em = $scriptState->getEntityManager();
							$em->remove($oldEntity);
							$em->persist($newEntity);
						}));
				break;
			default:
				throw new IllegalStateException('Inheritance type changing disabled');
		}
		
// 		if (!$oldEntityScript->isTranslationEnabled()) return;
		
// 		$translationManager = $this->scriptState->getTranslationManager();
// 		$oldTranslations = $translationManager->findAll($oldEntity);
		
// 		if (!$newScriptSelectionMapping->determineEntityScript()->isTranslationAvailable()) return;
// 		$newTranslations = array();
// 		foreach ($oldTranslations as $oldTranslation) {
// 			$newTranslation = $translationManager->create($newEntity, $oldTranslation->getLocale());
// 		}
		
		
// 		$newScriptSelectionMapping->registerListener(
// 				new MappingFlushListener(function() use ($translationManager, $oldTranslations) {
// 					foreach ($oldTranslations as $oldTranslation) {
// 						$translationManager->remove($oldTranslation);
// 					}
// 				}));
		
		
	}
		
	public function save(ScriptSelectionMapping $scriptSelectionMapping = null) {
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		if ($this->scriptSelectionMapping === null) {
			if ($scriptSelectionMapping === null) {
				throw new \InvalidArgumentException();
			}
			return $this->create($scriptSelectionMapping);
		}
		
		if ($this->scriptSelectionMapping->getScriptSelection()->hasDraft()) {
			return $this->draft($scriptSelectionMapping);
		}
	
		return $this->publish($scriptSelectionMapping);
	}
	
	public function create(ScriptSelectionMapping $scriptSelectionMapping) {
		if ($this->scriptSelectionMapping !== null || !$scriptSelectionMapping->getScriptSelection()->isNew()) {
			throw new IllegalStateException();
		}
	}
	
	private function ensureMappingIsset(ScriptSelectionMapping $scriptSelectionMapping = null) {
		if ($this->scriptSelectionMapping === null) {
			throw new IllegalStateException();
		}
		
		if ($scriptSelectionMapping !== null) {
			if (($this->scriptSelectionMapping->getScriptSelection()->hasDraft() != $scriptSelectionMapping->getScriptSelection()->hasDraft())
					&& ($this->scriptSelectionMapping->getScriptSelection()->hasTranslation() != $scriptSelectionMapping->getScriptSelection()->hasTranslation())) {
				throw new \InvalidArgumentException();
			}
			
			return $scriptSelectionMapping;
		}

		return $this->scriptSelectionMapping;
	}
	
	private function saveTranslation() {
		$scriptSelection = $this->scriptSelectionMapping->getScriptSelection();
		if (!$scriptSelection->hasTranslation()) return false;
		
		$translationManager = null;
		if ($scriptSelection->hasDraft()) {
			$translationManager = $this->scriptState->getDraftManager()->getTranslationManager();
		} else {
			$translationManager = $this->scriptState->getTranslationManager();
		}
		
		$that = $this;
		$this->scriptSelectionMapping->registerListener(
				new FlushMappingListener(function () use ($translationManager, $that) {
					$translationManager->persist($that->scriptSelectionMapping->getScriptSelection()->getTranslation());
				}));
		
		return true;
	}
	
	public function draft(ScriptSelectionMapping $scriptSelectionMapping = null) {
		$this->ensureMappingIsset($scriptSelectionMapping);
		
		if ($this->saveTranslation()) {
			return $this->scriptSelectionMapping;
		}
		
		if ($scriptSelectionMapping === null) {
			$scriptSelectionMapping = $this->scriptSelectionMapping;
		}
		
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		if (!$scriptSelectionMapping->getScriptSelection()->hasDraft()) {
			$scriptSelectionMapping = $scriptSelectionMapping->createDraftCopy();
		}
		
		$scriptSelectionMapping->registerListener(
				new FlushMappingListener(function() use ($scriptSelectionMapping) {
					$draftModel = $this->scriptState->getOrCreateDraftModel();
					$draftModel->saveDraft($scriptSelectionMapping->getScriptSelection()->getDraft());
				}));
		
		return $scriptSelectionMapping;
	}
	
	public function publish(ScriptSelectionMapping $scriptSelectionMapping = null) {
		$scriptSelectionMapping = $this->ensureMappingIsset($scriptSelectionMapping);
		
		if ($this->saveTranslation()) {
			return $this->scriptSelectionMapping;
		}
		
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		if ($scriptSelectionMapping->getScriptSelection()->hasDraft()) {
			$scriptSelectionMapping = $scriptSelectionMapping->createPublishCopy();
			$scriptSelectionMapping->registerListener(
					new FlushMappingListener(function() use ($scriptSelectionMapping) {
						$draftModel = $this->scriptState->getOrCreateDraftModel();
						$draftModel->saveDraft($scriptSelectionMapping->getScriptSelection()->getDraft());
					}));
			return $scriptSelectionMapping;
		}
				
		if (!$this->scriptSelectionMapping->determineEntityScript()->equals($scriptSelectionMapping->determineEntityScript())) {
			$this->changeInheritanceType($scriptSelectionMapping);
		}

		return $scriptSelectionMapping;
	}
	
	
// 	public function overwrite(ScriptSelectionMapping $entityMapping) {
// 		if (!$this->scriptSelectionMapping->equals($entityMapping->getEntityScript())) {
// // 			$entityMapping->copy($this->entityMapping);

// 			throw new NotYetImplementedException();
// 		}
		
// 		if ($this->scriptSelectionMapping->getScriptSelection()->hasDraft()) {
// 			$entityMapping->draftCopy($this->scriptSelectionMapping);
// 		} else if ($this->scriptSelectionMapping->getScriptSelection()->hasTranslation()) {
// 			$entityMapping->translationCopy($this->scriptSelectionMapping);
// 		} else if ($entityMapping->getScriptSelection()->hasDraft()) {
// 			$entityMapping->publishCopy($this->scriptSelectionMapping);
// 		} else {
// 			$entityMapping->copy($this->scriptSelectionMapping);
// 		}
		
// 		$this->scriptSelectionMapping = $entityMapping;
// 	}
		
// 	public function saveAsDraft() {
// 		throw new NotYetImplementedException();
// 		$this->scriptSelectionMapping->write();
		
// 		switch ($this->scriptSelectionMapping->getScriptSelection()->getType()) {
// 			case ScriptSelection::TYPE_TRANSLATION:
// 				$this->translationManager->saveTranslation($this->scriptSelectionMapping->getScriptSelection()->getTranslation());
// 				break;
// 			case ScriptSelection::TYPE_DRAFT:
// 				$this->translationManager->saveDraft($this->scriptSelectionMapping->getScriptSelection()->getTranslation());
// 				break;
// 			case ScriptSelection::TYPE_ORIGINAL:
// 				$em = $this->scriptSelectionMapping->getScriptState()->getEntityManager();
// 				if ($this->oldScriptSelectionMapping !== null) {
// 					$em->swap($this->oldScriptSelectionMapping->getScriptSelection()->getEntity(), 
// 							$this->scriptSelectionMapping->getScriptSelection()->getEntity());
// 				} else if ($this->scriptSelectionMapping->getScriptSelection()->isNew()) {
// 					$em->persist($this->scriptSelectionMapping->getScriptSelection()->getEntity());
// 				}
// 				break;
// 		}
// 	}
	
// 	public function publish(ScriptSelectionMapping $scriptSelectionMapping = null) {
// 		if ($scriptSelectionMapping !== null && !$scriptSelectionMapping->equals($this->scriptSelectionMapping)) {
// 			throw new NotYetImplementedException();
// 		}
		
// 		$this->scriptSelectionMapping->write();
// 	}
}

class ScriptSelectionMappingErrors {
	
}