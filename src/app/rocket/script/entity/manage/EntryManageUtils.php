<?php
namespace rocket\script\entity\manage;

use n2n\l10n\Locale;
use n2n\l10n\IllegalLocaleFormatException;
use rocket\script\entity\EntityScript;
use rocket\script\entity\adaptive\draft\Draft;
use n2n\reflection\ReflectionContext;
use n2n\persistence\orm\Entity;
use n2n\core\IllegalStateException;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\model\EntryInfo;
use rocket\script\entity\manage\model\EntryForm;
use rocket\script\entity\manage\model\EntryFormPart;
use n2n\http\ForbiddenException;
use n2n\persistence\orm\criteria\CriteriaProperty;
use n2n\persistence\orm\criteria\CriteriaComparator;
use n2n\persistence\orm\OrmUtils;
use n2n\reflection\ArgumentUtils;
use rocket\script\controller\preview\PreviewController;
use rocket\script\entity\preview\PreviewModel;
use rocket\script\entity\manage\model\EntryModel;

class EntryManageUtils {
	private $scriptState;
	private $draftModel;
	private $translationModel;
	
	public function __construct(ScriptState $scriptState) {
		$this->scriptState = $scriptState;
	}
	
	/**
	 * @return \rocket\script\entity\manage\ScriptState
	 */
	public function getScriptState() {
		return $this->scriptState;
	}
	
	public function createScriptSelectionFromEntity(Entity $entity) {
		return new ScriptSelection($this->scriptState->getContextEntityScript()->extractId($entity), $entity);
	}
	
	private function createScriptSelection($entityId) {
		$criteria = $this->scriptState->createCriteria($this->scriptState->getEntityManager(), 'e');
		$criteria->where()->match(new CriteriaProperty(array('e',
						$this->getScriptState()->getContextEntityScript()->getEntityModel()->getIdProperty()->getName())),
				CriteriaComparator::OPERATOR_EQUAL, $entityId);
		$entity = $criteria->fetchSingle();
		if ($entity === null) {
			throw new \InvalidArgumentException('unkown entity');
		}
		
		return new ScriptSelection($entityId, $entity);
	}
	
	private function lookupTranslation(Entity $entity, $httpLocaleId) {
		try {
			$translationLocale = new Locale(Locale::parseHttpLocaleId($httpLocaleId));
			$translationManager = $this->scriptState->getTranslationManager();
			
			return $translationManager->find($entity, $translationLocale, true);			
		} catch (IllegalLocaleFormatException $e) {
			throw new \InvalidArgumentException('Invalid Http Locale Id', 0, $e);
		}
	}
	
	public function createScriptSelectionFromEntityId($entityId, $httpLocaleId = null) {
		$scriptSelection = $this->createScriptSelection($entityId);
		
		if ($httpLocaleId !== null) {
			$scriptSelection->setTranslation($this->lookupTranslation($scriptSelection->getOriginalEntity(), $httpLocaleId));
		}

		return $scriptSelection;
	}
	
	public function createScriptSelectionFromDraftId($entityId, $draftId = null, $httpLocaleId = null) {
		$scriptSelection = $this->createScriptSelection($entityId);
		
		if ($this->scriptState->getScriptMask()->isDraftEnabled()) {
			$draftModel = $this->getOrCreateDraftModel();
			if ($draftId === null) {
				$latestDraft = $draftModel->getLatestDraftByEntityId($entityId, $scriptSelection->getOriginalEntity());
				if (!$latestDraft->isPublished()) {
					$scriptSelection->setDraft($latestDraft);
				}
			} else {
				$draft = $draftModel->getLatestDraftsById($draftId, $entityId);
				if ($draft === null) {
					throw new \InvalidArgumentException('Unknown draft id');
				}
				$scriptSelection->setDraft($draft);	
			}
		}

		if ($httpLocaleId !== null) {
			$scriptSelection->setTranslation($this->lookupTranslation($httpLocaleId));
		}
		
		return $scriptSelection;
	}
	
	public function createScriptSelectionMapping(ScriptSelection $scriptSelection) {
		$entityScript = $this->scriptState->getContextEntityScript()
				->determineAdequateEntityScript(new \ReflectionClass($scriptSelection->getEntity()));
		$mappingDefinition = $entityScript->createMappingDefinition();
		$mapping = $entityScript->createScriptSelectionMapping($mappingDefinition, $this->scriptState, $scriptSelection);
		if (!$mapping->isAccessableBy($this->scriptState->getExecutedScriptCommand(), $this->scriptState->getExecutedPrivilegeExt())) {
			throw new ForbiddenException('No access to this ScriptSelection. This Exception will be replaced after the next security update.');
		}
		return $mapping;
	}
		
	public function createEntryManager(ScriptSelectionMapping $scriptSelectionMapping = null, $applyToScriptState = true) {
		$entryManager = new EntryManager($this->scriptState, $scriptSelectionMapping);
// 		$entryManager->setDraftModel($this->draftModel);
// 		$entryManager->setTranslationModel($this->translationModel);
		if ($scriptSelectionMapping !== null) {
			$this->applyToScriptState($scriptSelectionMapping->getScriptSelection());
		}
		return $entryManager;
	}

	public function applyToScriptState(ScriptSelection $scriptSelection) {
		$this->scriptState->setScriptSelection($scriptSelection);
// 		$this->scriptState->setTranslationLocale($scriptSelection->getTranslationLocale());
	}
	
	public function createEntryInfo(ScriptSelectionMapping $scriptSelectionMapping) {
		$contextEntityScript = $this->scriptState->getContextEntityScript();
		$scriptMask = $this->scriptState->getScriptMask()
				->determineScriptMask($scriptSelectionMapping->determineEntityScript()->getId());
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		
		return new EntryInfo($scriptMask->createDisplayDefinition($this->scriptState, 
						$scriptSelection->hasDraft(), $scriptSelection->hasTranslation()), 
				$this->scriptState, $scriptSelectionMapping);
	}	
	
	public function createEntryForm(ScriptSelectionMapping $scriptSelectionMapping = null) {
		$contextEntityScript = $this->scriptState->getContextEntityScript();
		$entryForm = new EntryForm();
		
		$orgScriptSelection = null;
		$orgEntityScript = null;
		if ($scriptSelectionMapping !== null) {
			$orgEntityScript = $scriptSelectionMapping->determineEntityScript();
			$entryForm->setSelectedTypeId($orgEntityScript->getId());
			$entryForm->addTypeOption($scriptSelectionMapping);
			
			if ($orgEntityScript->getTypeChangeMode() === EntityScript::TYPE_CHANGE_MODE_DISABLED
					|| $scriptSelectionMapping->getScriptSelection()->hasTranslation()) {
				$entryForm->setMainEntryFormPart($this->createEntryFormPart($scriptSelectionMapping->determineEntityScript(),
						$scriptSelectionMapping, false));
			
				return $entryForm;
			}
				
			$orgScriptSelection = $scriptSelectionMapping->getScriptSelection();
		}

		$this->applyEntryFormLevel($entryForm, $contextEntityScript, $orgScriptSelection, $orgEntityScript);
	
		return $entryForm;
	}
	
	private function createEntryFormPart(EntityScript $entityScript, ScriptSelectionMapping $scriptSelectionMapping, $levelOnly) {
		$scriptMask = $this->scriptState->getScriptMask()->determineScriptMask($entityScript->getId());
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$displayDefinition = $scriptMask->createDisplayDefinition($this->scriptState, $scriptSelection->hasDraft(), $scriptSelection->hasTranslation(), $levelOnly);
		return new EntryFormPart($displayDefinition, $this->scriptState, $scriptSelectionMapping);
	}
	
	
	public function applyEntryFormLevel(EntryForm $entryForm, EntityScript $entityScript, 
			ScriptSelection $orgScriptSelection = null, EntityScript $orgEntityScript = null) {
		$latestScriptSelectionMapping = null;
		foreach ($entityScript->getSubEntityScripts() as $subEntityScript) {
			$latestScriptSelectionMapping = $this->applyEntryFormLevel($entryForm, $subEntityScript, 
					$orgScriptSelection, $orgEntityScript);
		}
		
		$entryFormPart = null;
		$scriptSelectionMapping = null;
		if ($entryForm->hasTypeOption($entityScript->getId())) {
			$scriptSelectionMapping = $entryForm->getScriptSelectionMappingByEntityScriptId($entityScript->getId());
		}
		
		if (null === $scriptSelectionMapping) {
			$entityClass = $entityScript->getEntityModel()->getClass();
			if ($entityClass->isAbstract()) {
				if ($latestScriptSelectionMapping === null) {
					throw new IllegalStateException('Cannot instance an object of ' . $entityScript->getId() . ' because it is abstract and no sub EntityScript available.');
				}
				
				$scriptSelectionMapping = $latestScriptSelectionMapping;
			} else {
				$newEntity = ReflectionContext::createObject($entityClass);
				
				$newScriptSelection = null;
				if ($orgScriptSelection === null) {
					$newScriptSelection = new ScriptSelection(null, $newEntity);
				} else {
					OrmUtils::findLowestCommonEntityModel($orgEntityScript->getEntityModel(), $entityScript->getEntityModel())
							->copy($orgScriptSelection->getEntity(), $newEntity);
					
					if (!$orgScriptSelection->hasDraft()) {
						$newScriptSelection = new ScriptSelection($orgScriptSelection->getId(), $newEntity);
					} else {
						$draft = $orgScriptSelection->getDraft();
						$newScriptSelection = new ScriptSelection($orgScriptSelection->getId(), $orgScriptSelection->getOriginalEntity());
						$newScriptSelection->setDraft(new Draft($draft->getId(), $draft->getLastMod(), $draft->isPublished(),
								$draft->getDraftedObjectId(), new \ArrayObject()));
						$newScriptSelection->getDraft()->setDraftedObject($newEntity);
					}
				}
				
				$scriptSelectionMapping = $this->createScriptSelectionMapping($newScriptSelection);
				$entryForm->addTypeOption($scriptSelectionMapping);
			}
		} 
		
		if ($entityScript->equals($this->scriptState->getContextEntityScript())) {
			$entryForm->setMainEntryFormPart(
					$this->createEntryFormPart($entityScript, $scriptSelectionMapping, false));
		} else {
			$entryForm->addLevelEntryFormPart(
					$this->createEntryFormPart($entityScript, $scriptSelectionMapping, true));
		}
		
		return $scriptSelectionMapping;
	}
	
	public function removeScriptSelection(ScriptSelection $scriptSelection) {
		if ($scriptSelection->hasTranslation()) {
			$this->scriptState->getTranslationManager()->remove($scriptSelection->getTranslation());
		} else if ($scriptSelection->hasDraft()) {
			$this->scriptState->getDraftManager()->remove($scriptSelection->getDraft());
		} else {
			$this->scriptState->getEntityManager()->remove($scriptSelection->getOriginalEntity());
		}
	}
	
	public function createPreviewController(EntryModel $entryModel, $previewType) {
		$entityScript = $entryModel->getScriptSelectionMapping()->determineEntityScript();
		$previewClass = $entityScript->getPreviewControllerClass();
		if ($previewClass === null) {
			throw new \InvalidArgumentException();
		}
		$previewController = $this->scriptState->getN2nContext()->getUsableContext()
				->lookup($previewClass->getName());
		ArgumentUtils::assertTrue($previewController instanceof PreviewController);
		
		$previewController->setPreviewType($previewType);
		$previewController->setPreviewModel(new PreviewModel($entryModel));
		return $previewController;
	}
}