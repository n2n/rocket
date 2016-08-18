<?php
namespace rocket\script\entity\command\impl\common\model;

use n2n\dispatch\DispatchAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use rocket\script\entity\manage\model\EntryForm;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\Dispatchable;
use n2n\persistence\orm\OrmUtils;
use n2n\reflection\ReflectionContext;
use n2n\dispatch\val\ValEnum;
use rocket\script\entity\manage\EntryManager;
use n2n\core\MessageContainer;
use rocket\script\entity\manage\mapping\MappingValidationResult;

class EditModel implements EntryCommandModel, Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->c(DispatchAnnotations::MANAGED_PROPERTIES, array('names' => array('saveMode', 'entryForm')));
		$as->m('save', DispatchAnnotations::MANAGED_METHOD);
	}
	
	const SAVE_MODE_DRAFT = 'draft';
	const SAVE_MODE_LIVE = 'live';
	
	private $publishAllowed = true;
		
	private $saveMode = self::SAVE_MODE_LIVE;
	private $entryManager;
	private $entryForm;
		
	public function __construct(EntryManager $entryManager, EntryForm $entryForm) {
		$this->entryManager = $entryManager;
		$this->entryForm = $entryForm;
	}
	
	public function setPublishAllowed($publishAllowed) {
		$this->publishAllowed = $publishAllowed;
	}
	
	public function isPublishAllowed() {
		return $this->publishAllowed || !$this->isDraftable();
	}
	
	public function getSaveMode() {
		return $this->saveMode;
	}
	
	public function setSaveMode($saveMode) {
		$this->saveMode = $saveMode;
	}
	
	public function getEntryModel() {
		return $this->entryForm->getMainEntryFormPart();
	}
	
	public function getEntryForm() {
		return $this->entryForm;
	}
	
	public function setEntryForm(EntryForm $entryForm) {
		$this->entryForm = $entryForm;
	}
	
	private function _validation(BindingConstraints $bc) {
		$saveModes = array();
		if ($this->entryForm->getMainEntryFormPart()->getDisplayDefinition()->getScriptMask()->isDraftEnabled()) {
			$saveModes[] = self::SAVE_MODE_DRAFT;
		}
		if ($this->isPublishAllowed()) {
			$saveModes[] = self::SAVE_MODE_LIVE;
		}
		$bc->val('saveMode', new ValEnum($saveModes));
	}
	
	private function createDraft($markAsPublished) {
		$currentEntity = $this->scriptSelection->getEntity();
		
		$selectedEntityScript = $this->entryForm->getSelectedEntityScript();
		$selectedEntityModel = $selectedEntityScript->getEntityModel();
		$baseEntity = ReflectionContext::createObject($selectedEntityModel->getClass());
		$lowestCommonEntityModel = OrmUtils::findLowestCommonEntityModel($this->entityScript->getEntityModel(), $selectedEntityModel); 
		$lowestCommonEntityModel->copy($currentEntity, $baseEntity);
		
		if (!$this->scriptSelection->hasTranslation()) {
			$this->entryForm->writeToObject($baseEntity);
		}
		
		$draft = $this->draftModel->createDraft(new \DateTime(), $markAsPublished, 
				$this->scriptSelection->getId(), $baseEntity);
		if (null === $this->translationModel) {
			return $draft;
		}
		
		$draftTranslationModel = $this->draftModel->getTranslationModel();
		
		foreach ($this->translationModel->getTranslationsByElementId($this->getTranslationElementId(), $baseEntity) as $translation) {
			$newTranslatatedEntity = ReflectionContext::createObject($selectedEntityModel->getClass());
			$lowestCommonEntityModel->copy($translation->getTranslatedEntity(), $newTranslatatedEntity);
			$newTranslation = $draftTranslationModel->getOrCreateTranslationByLocaleAndElementId($translation->getLocale(), 
					$draft->getId(), $newTranslatatedEntity);
			$draftTranslationModel->saveTranslation($newTranslation);
		}
		
		$selectionTranslation = $this->scriptSelection->getTranslation();
		if (isset($selectionTranslation)) {
			$translation = $draftTranslationModel->getOrCreateTranslationByLocaleAndElementId(
					$selectionTranslation->getLocale(), $draft->getId(), $baseEntity);
			$this->entryForm->writeToObject($translation->getTranslatedEntity());
			$draftTranslationModel->saveTranslation($translation);
		}
		
		return $draft;
	}
	
	private function saveSelectedDraft($markAsPublished = false) {
		$selectedDraft = $this->scriptSelection->getDraft();
		
		if ($this->scriptSelection->hasTranslation()) {
			$translation = $this->scriptSelection->getTranslation();
			$this->entryForm->writeToObject($translation->getTranslatedEntity());
			$this->translationModel->saveTranslation($translation);
			
			if ($markAsPublished) {
				$selectedDraft = $this->getCurrentDraft();
				$selectedDraft->setLastMod(new \DateTime());
				$selectedDraft->setPublished(true);
				$this->draftModel->saveDraft($selectedDraft);
			}
			return $selectedDraft;
		}
		
		$selectedEntityScript = $this->entryForm->getSelectedEntityScript();
		if ($this->entityScript->equals($selectedEntityScript)) {
			$this->entryForm->writeToObject($selectedDraft->getDraftedEntity());
			$selectedDraft->setPublished($markAsPublished);
			$selectedDraft->setLastMod(new \DateTime());
			$this->draftModel->saveDraft($selectedDraft);
				
			$originalObject = $this->scriptSelection->getOriginalEntity();
			return $selectedDraft;
		}
		
		$this->draftModel->removeDraft($draft);
		return $this->createDraft($markAsPublished);
	}
	
	public function save(MessageContainer $messageContainer) {
		$scriptSelectionMapping = $this->entryForm->buildScriptSelectionMapping();
		
// 		if (!$entryFormResult->isValid()) {
// 			$messageContainer->addAll($messageContainer->getAll());
// 			return false;
// 		}
		
		if ($this->saveMode == self::SAVE_MODE_DRAFT) {
			return $this->entryManager->saveDraft($scriptSelectionMapping);
		} else {
			$mappingValidationResult = new MappingValidationResult();
			$this->entryManager->publish($scriptSelectionMapping);
			if (!$scriptSelectionMapping->save($mappingValidationResult)) {
				$messageContainer->addAll($mappingValidationResult->getMessages());
				return false;
			}
		}
		
		return true;
	}
	
	private function saveDraft(/*ScriptManager $scriptManager*/) {
		if (!$this->isDraftable()) return null;
		
		$draft = $this->scriptSelection->getDraft();
		
		if (isset($draft) && !$draft->isPublished()) {
			return $this->saveSelectedDraft(false);
		}
		
		return $this->createDraft(false);
	}
	
	private function publish() {
		if ($this->scriptSelection->hasDraft()) {
			
			if (!$this->scriptSelection->getDraft()->isPublished()) {
				$draft = $this->saveSelectedDraft(true);
			} else {
				$draft = $this->createDraft(true);
			}
			$this->draftModel->publishDraft($draft, $this->em, $this->scriptSelection->getOriginalEntity(), $this->translationModel);
			return;
		}
		
		if (isset($this->draftModel)) {
			$this->createDraft(true);
		}
		
		if ($this->scriptSelection->hasTranslation()) {
			$translation = $this->scriptSelection->getTranslation();
			$translatedEntity = $translation->getTranslatedEntity();
			$this->entryForm->writeToObject($translatedEntity);
			
			$this->translationModel->saveTranslation($translation);
			return;
		}
			
		$entity = $this->scriptSelection->getOriginalEntity();
		
		$selectedEntityScript = $this->entryForm->getSelectedEntityScript();
		if ($this->entityScript->equals($selectedEntityScript)) {
			$this->entryForm->writeToObject($entity);
			return;
		}
		
		$inheritanceTypeChanger = $this->em->getPersistenceContext()->createTypeChangeActionQueue();
		$inheritanceTypeChanger->initializeWithNewEntityModel($this->scriptSelection->getOriginalEntity(), 
				$selectedEntityScript->getEntityModel());
		$this->entryForm->writeToObject($inheritanceTypeChanger->getNewEntity());
		$inheritanceTypeChanger->activate();
		
		if ($this->translationModel === null) {
			return;
		}		
	}
	
	private function copyTranslations() {
		foreach ($this->translationModel->getTranslationsByElementId($this->getTranslationElementId(), $baseEntity) as $translation) {
			$newTranslatatedEntity = ReflectionContext::createObject($selectedEntityModel->getClass());
			$lowestCommonEntityModel->copy($translation->getTranslatedEntity(), $newTranslatatedEntity);
			$newTranslation = $draftTranslationModel->getOrCreateTranslationByLocaleAndElementId($translation->getLocaleId(),
					$draft->getId(), $newTranslatatedEntity);
			$draftTranslationModel->saveTranslation($newTranslation);
		}
		
	}
}