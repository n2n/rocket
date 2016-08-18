<?php
namespace rocket\script\entity\command\impl\common\model;

use n2n\N2N;
use n2n\persistence\orm\EntityModelManager;
use rocket\script\entity\manage\CommandEntryModel;
use rocket\script\entity\adaptive\draft\DraftModelFactory;
use rocket\script\entity\adaptive\translation\TranslationModelFactory;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\ScriptSelection;

abstract class CommandEntryModelAdapter implements CommandEntryModel {
	protected $entityScript;
	protected $em;
	protected $scriptState;
	
	/**
	 * @var \rocket\script\entity\manage\ScriptSelection
	 */
	protected $scriptSelection = null;

	protected $draftModel = null;
	protected $translationModel = null;
	
	private $mainTranslationLocale = null;
	private $translationLocales = null;

	private $currentDraft = null;
	private $historicizedDrafts = array();
		
	public function getCurrentEntity() {
		return $this->scriptSelection->getEntity();
	}
	
	public function getScriptSelection() {
		return $this->scriptSelection;
	}
	
	public function isDraftable() {
		return isset($this->draftModel);
	}
	
	public function getDraftModel() {
		return $this->draftModel;
	}
	
	public function getCurrentDraft() {
		return $this->currentDraft;
	}
	
	public function getHistoricizedDrafts() {
		return $this->historicizedDrafts;
	}
	
	public function isTranslatable() {
		return isset($this->translationModel);
	}
	
	public function getMainTranslationLocale() {
		return $this->mainTranslationLocale;
	}
	
	public function getTranslationLocales() {
		return $this->translationLocales;
	}
	/**
	 * @return \rocket\script\entity\EntityScript 
	 */
	public function getEntityScript() {
		return $this->entityScript;
	}
	
	public function getScriptState() {
		return $this->scriptState;
	}
	
	public function initialize(ScriptState $scriptState, $id, $localeId = null, $ignoreDraft = false) {
		if (!$this->initializeSelection($scriptState, $id)) {
			return false;
		}

		if ($this->entityScript->isDraftEnabled()) {
			$this->initializeDrafts();
			if (!$ignoreDraft && isset($this->currentDraft)) {
				$this->scriptSelection->setDraft($this->currentDraft);
			}
		}
		
		return $this->initializeTranslation($localeId);
	}

	public function initializeFromDraft(ScriptState $scriptState, $id, $draftId, $localeId = null) {
		if (!$this->initializeSelection($scriptState, $id)) {
			return false;
		}
	
		if (!$this->entityScript->isDraftEnabled()) {
			return false;
		}
		
		$this->initializeDrafts();
		if (!$this->selectDraft($draftId)) {
			return false;
		}
		
		return $this->initializeTranslation($localeId);
	}
	
	private function selectDraft($draftId) {
		if (isset($this->currentDraft) && $this->currentDraft->getId() == $draftId) {
			$this->scriptSelection->setDraft($this->currentDraft);
			return true;
		}
		
		foreach ($this->historicizedDrafts as $draft) {
			if ($draft->getId() == $draftId) {
				$this->scriptSelection->setDraft($draft);
				return true;
			}
		}
		
		return false;
	}
	
	private function initializeSelection(ScriptState $scriptState, $id) {
		$this->em = $scriptState->getEntityManager();
		$this->scriptState = $scriptState;
		$entityScript = $scriptState->getContextEntityScript();
		$entity = $this->em->find($entityScript->getEntityModel()->getClass(), $id);
		if ($entity === null) return false;
		
		$this->scriptSelection = new ScriptSelection($id, $entity, $scriptState->getAccessRulesOfEntity($entity));
		
		$entityModel = EntityModelManager::getInstance()->getEntityModelByObject($entity);
		$this->entityScript  = $entityScript->determineEntityScript($entityModel);
		
		return true;
	}
	
	private function initializeDrafts() {		
		$this->draftModel = DraftModelFactory::createDraftModel($this->em, $this->entityScript);
		
		$latestDrafts = $this->draftModel->getLatestDraftsByEntityId($this->scriptSelection->getId(), 
				$this->scriptSelection->getOriginalEntity());
		
		foreach ($latestDrafts as $key => $draft) {
			if (!$draft->isPublished()) {
				$this->currentDraft = $draft;
				unset($latestDrafts[$key]);
			}
			
			$this->historicizedDrafts = $latestDrafts;
			break;
		}
	}
	
	private function initializeTranslation($localeId) {
		if (!$this->entityScript->isTranslationEnabled()) {
			return $localeId === null;
		}

		if (isset($this->draftModel)) {
			$this->translationModel = $this->draftModel->getTranslationModel();
		} else {
			$this->translationModel = TranslationModelFactory::createTranslationModel($this->em, $this->entityScript);
		}
		
		$this->mainTranslationLocale = Locale::getDefault();
		$this->translationLocales = array();
		foreach (N2N::getLocales() as $locale) {
			if ($locale->equals($this->mainTranslationLocale)) continue;
			$this->translationLocales[$locale->getId()] = $locale;
		}
		
		if ($localeId === null) {
			return true;
		}
		
		if (!isset($this->translationLocales[$localeId])) return false;
		
		$translation = $this->translationModel->getOrCreateTranslationByLocaleAndElementId(
				$this->translationLocales[$localeId], $this->getTranslationElementId(), $this->scriptSelection->getEntity());

		$this->scriptSelection->setTranslation($translation);
		return true;
	}
	
	protected function getTranslationElementId() {
		return $this->scriptSelection->hasDraft() ? $this->scriptSelection->getDraft()->getId() : $this->scriptSelection->getId();
	}
}