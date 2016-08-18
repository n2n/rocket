<?php
namespace rocket\script\entity\field\impl\ci;


use n2n\reflection\ReflectionUtils;
use n2n\persistence\orm\property\OneToManyProperty;

use n2n\persistence\orm\property\EntityProperty;
use rocket\script\core\SetupProcess;
use n2n\dispatch\option\impl\OptionCollectionArrayOption;
use n2n\dispatch\option\impl\StringOption;
use n2n\dispatch\option\impl\EnumArrayOption;
use n2n\reflection\ArgumentUtils;
use rocket\script\entity\field\impl\ci\model\PanelConfig;
use rocket\script\entity\field\impl\ci\model\ContentItemOption;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\relation\EmbeddedOneToManyScriptField;
use rocket\script\entity\field\impl\ManageInfo;
use n2n\util\Attributes;
use n2n\dispatch\option\impl\BooleanOption;
use rocket\script\entity\field\impl\TranslatableScriptFieldAdapter;
use n2n\core\DynamicTextCollection;
use n2n\ui\html\HtmlUtils;
use n2n\util\StringUtils;

class ContentItemsScriptField extends EmbeddedOneToManyScriptField/* implements PreviewableScriptField */ {
	private $scriptManager;
	private $contentItemScript;
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		$this->displayInListViewDefault = false;
		$this->optionRequiredDefault = false;
	}
	
	public function getTypeName() {
		return 'ContentItems';
	}
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		$this->scriptManager = $setupProcess->getScriptManager();
		$this->contentItemScript = $this->scriptManager->getEntityScriptByClass(
				new \ReflectionClass('rocket\script\entity\field\impl\ci\model\ContentItem'));
	}
	
	public function isDisplayInAddViewEnabled() {
		if (!$this->hasPanelConfigs()) return false;
		return parent::isDisplayInAddViewEnabled();
	}
	
	public function isDisplayInDetailViewEnabled() {
		if (!$this->hasPanelConfigs()) return false;
		return parent::isDisplayInDetailViewEnabled();
	}
	
	public function isDisplayInEditViewEnabled() {
		if (!$this->hasPanelConfigs()) return false;
		return parent::isDisplayInEditViewEnabled();
	}
	
	public function isDisplayInListViewEnabled() {
		if (!$this->hasPanelConfigs()) return false;
		return parent::isDisplayInListViewEnabled();
	}
	
	public function hasPanelConfigs() {
		return count($this->getPanelConfigs()) > 0;
	}
	
	public function createOptionCollection() {	
		$dtc = new DynamicTextCollection('rocket');
		$allowedContentItemOptions = array(null => null);
		foreach ($this->contentItemScript->getAllSubEntityScripts() as $subEntityScript) {
			$allowedContentItemOptions[$subEntityScript->getId()] = $subEntityScript->getLabel();
		}
		
		$optionCollection = new OptionCollectionImpl();
		$this->applyDisplayOptions($optionCollection, false, false, false, false, true);
		$this->applyEditOptions($optionCollection, true, false, true);
		$optionCollection->addOption(TranslatableScriptFieldAdapter::OPTION_TRANSLATION_ENABLED_KEY,
				new BooleanOption($dtc->translate('script_impl_translatable_label')));
				
		$optionCollection->addOption('panels', new OptionCollectionArrayOption('Panels',
		 		function() use ($allowedContentItemOptions) {
		 			$optionCollection = new OptionCollectionImpl();
		 			$optionCollection->addOption('panelName', new StringOption('Name'));
		 			$optionCollection->addOption('panelLabel', new StringOption('Label'));
		 			$optionCollection->addOption('allowedContentItemIds', new EnumArrayOption(
		 					'Allowed ContentItems', $allowedContentItemOptions));
		 			return $optionCollection;
		 		}));
		
		return $optionCollection;
	}
	
	public function getAttributes() {
		$this->cleanUpAttributes();
		return parent::getAttributes();
	}
	
	public function getPanelConfigs() {
		$this->cleanUpAttributes();
		
		$panelConfigs = array();
		foreach ((array) $this->getAttributes()->get('panels') as $panelAttrs) {
			$panelConfigs[] = new PanelConfig($panelAttrs['panelName'], $panelAttrs['panelLabel'],
					$panelAttrs['allowedContentItemIds']);
		}
		return $panelConfigs;
	}
	
	public function setPanelConfigs(array $panelConfigs) {
		$panelsAttrs = array();
		foreach ($panelConfigs as $panelConfig) {
			ArgumentUtils::assertTrue($panelConfig instanceof PanelConfig);
			$panelsAttrs[] = array('panelName' => $panelConfig->getName(), 'panelLabel' => $panelConfig->getLabel(),
					'allowedContentItemIds' => $panelConfig->getAllowedContentItemIds());
		}
	}
	
	private function cleanUpAttributes() {
		$attributes = parent::getAttributes();
		$panelsAttrs = (array) $attributes->get('panels');
		foreach ($panelsAttrs as $key => $panelAttrs) {
			if (isset($panelAttrs['panelName']) && isset($panelAttrs['panelLabel'])
		 			&& isset($panelAttrs['allowedContentItemIds'])) {
				continue;
			}
			unset($panelsAttrs[$key]);
		}
		$attributes->set('panels', $panelsAttrs);
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof OneToManyProperty
				&& $this->isClassCompatible($entityProperty->getTargetEntityClass());
	}
	
	private function isClassCompatible(\ReflectionClass $class) {
		return ReflectionUtils::isClassA($class, new \ReflectionClass('rocket\script\entity\field\impl\ci\model\ContentItem'));
	}
	
	public function getHtmlContainerAttrs(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$panelData = array();
		foreach ($this->getPanelConfigs() as $panelConfig) {
			$panelData[$panelConfig->getName()] = $panelConfig->getLabel();
		}
		return HtmlUtils::mergeAttrs(parent::getHtmlContainerAttrs($scriptSelectionMapping, $manageInfo), 
				array('class' => 'rocket-content-items', 'data-panels' => StringUtils::jsonEncode($panelData)));
	}
	
// 	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view, ManageInfo $manageInfo) {
// 		throw new NotYetImplementedException();
// 		$view->getHtmlBuilder()->addJs('js/command.js', 'rocket');
		
// 		$contentItems = $this->getPropertyAccessProxy()->getValue($scriptSelection->getEntity());
		
// 		$translationModel = null;
// 		if ($scriptSelection->hasTranslation()) {
// 			$translationModel = TranslationModelFactory::createTranslationModel($scriptState->getEntityManager(),
// 					$this->contentItemScript);
// 		}
		
// 		$groupedEntryInfos = array();
// 		foreach ($contentItems as $contentItem) {
// 			$ciScriptSelection = $this->createCiScriptSelection($contentItem, $scriptSelection, $translationModel, 
// 					$entityScript, $id, $readOnly);
// 			$entryInfo = new EntryInfo($scriptState, $entityScript, $ciScriptSelection);
// 			$entryInfo->removeVisibleScriptFieldById('id');
// 			$entryInfo->removeVisibleScriptFieldById('panel');
// 			$entryInfo->removeVisibleScriptFieldById('orderIndex');
			
// 			$panelName = $contentItem->getPanel();
// 			if (!isset($groupedEntryInfos[$panelName])) {
// 				$groupedEntryInfos[$panelName] = array();
// 			}
// 			$groupedEntryInfos[$panelName][] = $entryInfo;
// 		}
		
// 		return $view->getImport('script\entity\field\impl\ci\view\ciList.html', 
// 				array('groupedEntryInfos' => $groupedEntryInfos));
// 	}
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return new ContentItemOption(parent::createOption($scriptSelectionMapping, $manageInfo), 
				$this->getPanelConfigs(), $scriptSelectionMapping->getScriptSelection()->hasTranslation());
	}
	
// 	public function draftCopy($entities) {
// 		$entityModelManager = $this->scriptManager->getEntityModelManager();
// 		$draftValue = new \ArrayObject();
// 		foreach ($entities as $key => $entity) {
// 			$fieldEntityScript = $this->contentItemScript->determineEntityScript(
// 					$entityModelManager->getEntityModelByObject($entity));
// 			$copy = OrmUtils::copy($entity, $fieldEntityScript->getEntityModel(), true);
			
// 			foreach ($fieldEntityScript->getFieldCollection()->toArray() as $field) {
// 				if ($field instanceof DraftableScriptField && !($field->getEntityProperty() instanceof IdProperty)) {
// 					$accessProxy = $field->getPropertyAccessProxy();
// 					$accessProxy->setValue($copy, $field->draftCopy($accessProxy->getValue($entity)));
// 				}
// 			}
			
// 			$draftValue[$key] = $copy;
// 		}
		
// 		return $draftValue;
// 	}
	
// 	public function publishCopy($entities) {
// 		$entityModelManager = $this->scriptManager->getEntityModelManager();
// 		$publishedValue = new \ArrayObject();
// 		foreach ($entities as $key => $entity) {
// 			$fieldEntityScript = $this->contentItemScript->determineEntityScript(
// 					$entityModelManager->getEntityModelByObject($entity));
			
// 			$copy = OrmUtils::copy($entity, $fieldEntityScript->getEntityModel(), true);
			
// 			foreach ($fieldEntityScript->getFields() as $field) {
// 				if ($field instanceof DraftableScriptField  && !($field->getEntityProperty() instanceof IdProperty)) {
// 					$accessProxy = $field->getPropertyAccessProxy();
// 					$accessProxy->setValue($copy, $field->publishCopy($accessProxy->getValue($entity)));
// 				}
// 			}
			
// 			$publishedValue[] = $copy;
// 		}
// 		return $publishedValue;
// 	}
	
// 	public function optionAttributeValueToPropertyValue(Attributes $attributes, ScriptSelectionMapping $scriptSelectionMapping) { throw new NotYetImplementedException(); throw new NotYetImplementedException();
// 		$contentItems = new \ArrayObject();
		
// 		foreach ($optionValue as $entryForm) {
// 			$contentItem = null;
// 			$ciScriptSelection = $entryForm->getScriptSelection();	
			
// 			if (isset($ciScriptSelection)) {
// 				$contentItem = $ciScriptSelection->getOriginalEntity();
// 				if ($ciScriptSelection->hasTranslation()) {
// 					$translation = $ciScriptSelection->getTranslation();
				
// 					$entryForm->writeToObject($translation->getTranslatedEntity());
// 					$entryForm->getEntityScript()->getTranslationModel()->saveTranslation($translation);
// 				} else {
// 					$entryForm->writeToObject($ciScriptSelection->getEntity());
// 				}
// 			} else {
// 				$contentItem = ReflectionContext::createObject(
// 						$entryForm->getSelectedEntityScript()->getEntityModel()->getClass());
// 				$entryForm->writeToObject($contentItem);
// 			}
// 			$contentItems[] = $contentItem;
// 		}
		
// 		return $contentItems;
// 	}
	
// 	private function createCiScriptSelection(ContentItem $contentItem, ScriptSelection $scriptSelection = null, 
// 			TranslationModel $translationModel = null, &$entityScript, &$id, &$readOnly) {
// 		$entityModelManager = $this->scriptManager->getEntityModelManager();
// 		$entityModel = $entityModelManager->getEntityModelByObject($contentItem);
// 		$entityScript = $this->contentItemScript->determineEntityScript($entityModel);
// 		$id = $entityScript->extractId($contentItem);
		
// 		$ciScriptSelection = new ScriptSelection($id, $contentItem);
		
// 		$readOnly = false;
// 		if (isset($scriptSelection) && $scriptSelection->hasTranslation()) {
// 			if (isset($translationModel) && $entityScript->isTranslationEnabled()) {
// 				$ciScriptSelection->setTranslation(
// 						$translationModel->getOrCreateTranslationByLocaleAndElementId(
// 								$scriptSelection->getTranslationLocale(), $id, $contentItem));
// 			} else {
// 				$readOnly = true;
// 			}
// 		}
		
// 		return $ciScriptSelection;
// 	}
	
// 	public function propertyValueToOptionAttributeValue(ScriptSelectionMapping $scriptSelectionMapping, Attributes $attributes) { throw new NotYetImplementedException();
// 		$entryForms = array();
// 		if ($propertyValue === null) return $entryForms;
	
// 		$translationModel = null;
// 		if (isset($scriptSelection) && $scriptSelection->hasTranslation()) {
// 			$translationModel = TranslationModelFactory::createTranslationModel($scriptState->getEntityManager(), 
// 					$this->contentItemScript);
// 		}
		 
// 		foreach ($propertyValue as $contentItem) {
// 			$ciScriptSelection = $this->createCiScriptSelection($contentItem, $scriptSelection, $translationModel, 
// 					$entityScript, $id, $readOnly);
// 			$entryForms[$id] = new EntryForm($scriptState, $entityScript, $ciScriptSelection, $readOnly);
// 		}
	
// 		return $entryForms;
// 	}
	
// 	public function checkDraftMeta(Pdo $dbh) {
// 		$toManyRelation = $this->getEntityProperty()->getRelation();
// 		$database = $dbh->getMetaData()->getDatabase();
// 		$joinTableName = $toManyRelation->getJoinTableName();	
// 		$darftJoinTableName = DraftMetaProvider::createDraftTableName($joinTableName);
// 		if (!$database->containsMetaEntityName($darftJoinTableName)) {
// 			$database->addMetaEntity($database->getMetaEntityByName($joinTableName)
// 					->copy($darftJoinTableName));
// 		}
// 	}
	
// 	public function mapDraftValue($draftId, MappingJob $mappingJob, \ArrayObject $rawDataMap, \ArrayObject $mappedValues) {
// 		$entityModel = $this->getEntityProperty()->getEntityModel();
// 		$toManyRelation = $this->getEntityProperty()->getRelation();
		
// 		$em = $mappingJob->getPersistenceContext()->getEntityManager();
		
// 		$manyLoader = new JoinTableToManyLoader($em, $draftId, $toManyRelation->getTargetEntityModel(), 
// 				DraftMetaProvider::createDraftTableName($toManyRelation->getJoinTableName()), 
// 				$toManyRelation->getJoinColumnName(), $toManyRelation->getInverseJoinColumnName(), $toManyRelation->getRelationAnno());

// 		$that = $this;
// 		$mappingJob->executeAtEnd(function(/*MappingJob $mappingJob*/) use ($toManyRelation, $manyLoader, $that, $mappedValues) {
// 			$entityProperty = $that->getEntityProperty();
// 			if ($toManyRelation->getFetchType() == FetchType::EAGER) {
// 				$mappedValues[$entityProperty->getName()] = $manyLoader->load();
// 			} else {
// 				$mappedValues[$entityProperty->getName()] = new ArrayObjectProxy($manyLoader);
// 			}
// 		});
// 	}
	
// 	public function supplyDraftPersistingJob($mappedValue, PersistingJob $persistingJob) {
// 		$toManyRelation = $this->getEntityProperty()->getRelation();
// 		ArgumentUtils::assertTrue($toManyRelation instanceof JoinTableToManyRelation);
// 		$actionQueue = $persistingJob->getPersistenceActionQueue();
		
// 		$relationActionJob = new JoinTableRelationActionJob($actionQueue, 
// 				DraftMetaProvider::createDraftTableName($toManyRelation->getJoinTableName()), 
// 				$toManyRelation->getJoinColumnName(), $toManyRelation->getInverseJoinColumnName());
// 		$relationActionJob->addDependent($persistingJob);
// 		$toManyRelation->applyTargetPeristingJobs($actionQueue, $mappedValue, $relationActionJob);
		
// 		if ($persistingJob->getPersistenceMeta()->hasId()) {
// 			$relationActionJob->setId($persistingJob->getPersistenceMeta()->getId());
// 			$actionQueue->add($relationActionJob);
// 			return;
// 		}
		
// 		$persistingJob->executeAtEnd(function(PersistingJob $persistingJob) use ($relationActionJob) {
// 			if (!$persistingJob->getPersistenceMeta()->hasId()) return;
// 			$relationActionJob->setId($persistingJob->getPersistenceMeta()->getId());
// 			$persistingJob->getPersistenceActionQueue()->add($relationActionJob);
// 		});
// 	}
	
// 	public function supplyDraftRemovingJob($mappedValue, RemovingJob $deletingJob) {
// 		$toManyRelation = $this->getEntityProperty()->getRelation();
		
// 		if (!$deletingJob->getRemoveMeta()->hasId()) {
// 			return;
// 		}
		
// 		$actionQueue = $deletingJob->getRemoveActionQueue();
// 		$relationActionJob = new JoinTableRelationActionJob($actionQueue, 
// 				DraftMetaProvider::createDraftTableName($toManyRelation->getJoinTableName()), 
// 				$toManyRelation->getJoinColumnName(), $toManyRelation->getInverseJoinColumnName());
// 		$relationActionJob->setId($deletingJob->getRemoveMeta()->getObjectId());
// 		$relationActionJob->addDependent($deletingJob);
		
// 		$actionQueue->add($relationActionJob);
		
// 		if ($mappedValue === null || !($toManyRelation->getCascadeType() & CascadeType::REMOVE)) return;
		
// 		$actionQueue = $deletingJob->getRemoveActionQueue();
// 		foreach ($mappedValue as $targetObject) {
// 			$actionQueue->getOrCreateRemovingJob($targetObject);
// 		}
// 	}
	
// 	public function createEditablePreviewUiComponent(PreviewModel $previewModel, PropertyPath $propertyPath, 
// 			HtmlView $view, \Closure $createCustomUiElementCallback = null) {
		
// 		$editEntryModels = $previewModel->getEntryModel()->getPropertyValueByName($this->getPropertyName());
		
// 		foreach ($editEntryModels as $key => $editEntryModel) {
// 			$previewModel = new PreviewModel($editEntryModel, $propertyPath->createArrayFieldExtendedPath($key));
// 			$view->out($editEntryModel->getScriptSelection()->getEntity()
// 					->createEditablePreviewUiComponent($previewModel, $view));
// 		}
		
// 		return null;
// 	}
}