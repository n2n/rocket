<?php
namespace rocket\script\entity;

use rocket\script\entity\preview\PreviewModel;
use rocket\script\entity\field\PropertyScriptField;
use rocket\script\entity\field\TranslatableScriptField;
use rocket\script\entity\UnknownScriptElementException;
use rocket\script\entity\field\HighlightableScriptField;
use n2n\l10n\Locale;
use n2n\persistence\DbhPool;
use n2n\persistence\orm\OrmUtils;
use n2n\persistence\orm\Entity;
use n2n\http\Response;
use n2n\http\Request;
use n2n\persistence\orm\EntityModel;
use n2n\core\Module;
use rocket\script\core\ScriptAdapter;
use rocket\script\entity\field\DraftableScriptField;
use n2n\persistence\orm\store\EntityFlushEvent;
use rocket\script\entity\adaptive\draft\DraftModelFactory;
use n2n\reflection\ArgumentUtils;
use n2n\dispatch\option\impl\OptionCollectionOption;
use n2n\core\N2nContext;
use rocket\user\model\RestrictionScriptField;
use rocket\script\entity\command\PrivilegedScriptCommand;
use rocket\script\entity\command\PrivilegeExtendableScriptCommand;
use rocket\script\entity\field\AccessControllableScriptField;
use rocket\script\entity\security\ScriptSecurityManager;
use n2n\reflection\ReflectionUtils;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use rocket\script\entity\listener\EntityChangeEvent;
use rocket\script\core\extr\EntityScriptExtraction;
use rocket\util\IdentifiableSet;
use rocket\script\entity\mask\IndependentScriptMask;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\EntityScriptController;
use rocket\script\core\extr\ScriptMaskExtraction;
use rocket\script\entity\manage\mapping\MappingDefinition;
use n2n\N2N;
use rocket\script\entity\manage\security\PrivilegeConstraint;
use n2n\core\UnsupportedOperationException;
use rocket\script\entity\field\EntityPropertyScriptField;
use rocket\script\entity\adaptive\translation\TranslationManager;
use rocket\script\entity\adaptive\draft\DraftManager;
use rocket\script\entity\adaptive\translation\TranslationModel;
use rocket\script\entity\adaptive\draft\DraftModel;
use rocket\core\model\Rocket;
use rocket\script\entity\field\WritableScriptField;
use rocket\script\entity\field\ReadableScriptField;
use rocket\script\entity\field\MappableScriptField;
use rocket\script\entity\manage\mapping\Mappable;
use rocket\script\entity\manage\security\MappingArrayAccess;
use rocket\script\entity\manage\security\PrivilegeBuilder;

class EntityScript extends ScriptAdapter {
	const KNOWN_STRING_FIELD_OPEN_DELIMITER = '{';
	const KNOWN_STRING_FIELD_CLOSE_DELIMITER = '}';
	
	private $entityModel;
	private $superEntityScript;
	protected $subEntityScripts = array();
	
	private $commandCollection;
	private $partialControlOrder = array();
	private $overallControlOrder = array();
	private $entryControlOrder = array();
	
	private $listenerCollection;
	private $constraintCollection;
	private $fieldCollection;
	
	private $draftHistorySize = 0;
	private $draftModel = null;
	private $translationModel = null;
	private $dataSourceName = null;
	private $knownStringPattern = null;
	private $previewControllerClass = null;
	private $defaultSortDirections = array();
	private $defaultMask;
	private $maskSet;

	const TYPE_CHANGE_MODE_DISABLED = null;
	const TYPE_CHANGE_MODE_REPLACE = 'replace';
	const TYPE_CHANGE_MODE_CHANGE = 'change';
	
	private $typeChangeMode = self::TYPE_CHANGE_MODE_DISABLED;
	/**
	 * @param unknown $id
	 * @param unknown $label
	 * @param unknown $pluralLabel
	 * @param Module $module
	 * @param EntityModel $entityModel
	 */
	public function __construct($id, $label, $pluralLabel, Module $module, EntityModel $entityModel) {
		parent::__construct($id, $label, $module);
		$this->pluralLabel = $pluralLabel;
		$this->entityModel = $entityModel;
		
		$this->fieldCollection = new ScriptElementCollection('ScriptField', $this, 'rocket\script\entity\field\ScriptField');
		$this->commandCollection = new ScriptElementCollection('ScriptCommand', $this, 'rocket\script\entity\command\ScriptCommand');
		$this->constraintCollection = new ScriptElementCollection('ScriptModificator', $this, 'rocket\script\entity\modificator\ScriptModificator');
		$this->listenerCollection = new ScriptElementCollection('ScriptListener', $this, 'rocket\script\entity\listener\ScriptListener');
		$this->defaultMask = null;
		$this->maskSet = new IdentifiableSet('rocket\script\entity\mask\IndependentScriptMask');
	}
	/**
	 * @return string
	 */
	public function getPluralLabel() {
		return $this->pluralLabel;
	}
	/**
	 * @return \n2n\persistence\orm\EntityModel
	 */
	public function getEntityModel() {
		return $this->entityModel;
	}
	/**
	 * @param EntityScript $superEntityScript
	 */
	public function setSuperEntityScript(EntityScript $superEntityScript) {
		$this->superEntityScript = $superEntityScript;
		$superEntityScript->subEntityScripts[$this->getId()] = $this;
		
		$this->fieldCollection->setSuperCollection($superEntityScript->getFieldCollection());
		$this->commandCollection->setSuperCollection($superEntityScript->getCommandCollection());
		$this->constraintCollection->setSuperCollection($superEntityScript->getModificatorCollection());
		$this->listenerCollection->setSuperCollection($superEntityScript->getListenerCollection());
	}
	/**
	 * @return \rocket\script\entity\EntityScript
	 */
	public function getSuperEntityScript() {
		return $this->superEntityScript;
	}
	/**
	 * @return boolean
	 */
	public function hasSuperEntityScript() {
		return isset($this->superEntityScript);
	}
	/**
	 * @return \rocket\script\entity\EntityScript
	 */
	public function getTopEntityScript() {
		$entityScript = $this;
		do {
			$topEntityScript = $entityScript;
		} while (null !== ($entityScript = $topEntityScript->getSuperEntityScript()));
		
		return $topEntityScript;
	}
	
	public function getAllSuperEntityScripts($includeSelf = false) {
		$superEntitScripts = array();
		
		if ($includeSelf) {
			$superEntitScripts[$this->getId()] = $this;
		}
		
		$entityScript = $this;
		while (null != ($entityScript = $entityScript->getSuperEntityScript())) {
			$superEntitScripts[$entityScript->getId()] = $entityScript;
		}
		return $superEntitScripts;
	}
	/**
	 * @return boolean
	 */
	public function hasSubEntityScripts() {
		return (bool)sizeof($this->subEntityScripts);
	}
// 	/**
// 	 * @param EntityScript $subEntityScript
// 	 */
// 	protected function putSubEntityScript(EntityScript $subEntityScript) {
// 		$this->subEntityScripts[$subEntityScript->getId()] = $subEntityScript;
		
// 		foreach ($this->scriptCommandCollection as $id => $scriptCommand) {
// 			$subEntityScript->applyScriptCommand($scriptCommand, true);
// 		}
// 	}
	/**
	 * @return \rocket\script\entity\EntityScript[]
	 */
	public function getSubEntityScripts() {
		return $this->subEntityScripts;
	}
	
	public function containsSubEntityScriptId($entityScriptId, $deepCheck = false) {
		if (isset($this->subEntityScripts[$entityScriptId])) return true;
		
		if ($deepCheck) {
			foreach ($this->subEntityScripts as $subEntityScript) {
				if ($subEntityScript->containsSubEntityScriptId($entityScriptId, $deepCheck)) {
					return true;
				}
			}
		}
		
		return false;
	}
	/**
	 * @return \rocket\script\entity\EntityScript[]
	 */
	public function getAllSubEntityScripts() {
		return $this->lookupAllSubEntityScripts($this); 
	}
	/**
	 * @param EntityScript $entityScript
	 * @return \rocket\script\entity\EntityScript[]
	 */
	private function lookupAllSubEntityScripts(EntityScript $entityScript) {
		$subEntityScripts = $entityScript->getSubEntityScripts();
		foreach ($subEntityScripts as $subEntityScript) {
			$subEntityScripts = array_merge($subEntityScripts, 
					$this->lookupAllSubEntityScripts($subEntityScript));
		}
		
		return $subEntityScripts;
	}
	
	public function findEntityScriptByEntityModel(EntityModel $entityModel) {
		if ($this->entityModel->equals($entityModel)) {
			return $this;
		}
		
		foreach ($this->getAllSuperEntityScripts() as $superEntityScript) {
			if ($superEntityScript->getEntityModel()->equals($entityModel)) {
				return $superEntityScript;
			}
		}
		
		foreach ($this->getAllSubEntityScripts() as $subEntityScript) {
			if ($subEntityScript->getEntityModel()->equals($entityModel)) {
				return $subEntityScript;
			}
		}
	}
	/**
	 * @param EntityModel $entityModel
	 * @throws \InvalidArgumentException
	 * @return \rocket\script\entity\EntityScript
	 */
	public function determineEntityScript(EntityModel $entityModel) {
		if ($this->entityModel->equals($entityModel)) {
			return $this;
		}
		
		foreach ($this->getAllSubEntityScripts() as $subEntityScript) {
			if ($subEntityScript->getEntityModel()->equals($entityModel)) {
				return $subEntityScript;
			}
		}
				
		// @todo make better exception
		throw new \InvalidArgumentException('No EntityScript for Entity \'' 
				. $entityModel->getClass()->getName() . '\' defined.');
	}
		
	public function determineAdequateEntityScript(\ReflectionClass $class) {
		if (!ReflectionUtils::isClassA($class, $this->entityModel->getClass())) {
			throw new \InvalidArgumentException('Class \'' . $class->getName()
					. '\' is not instance of \'' . $this->getEntityModel()->getClass() . '\' defined.');
		} 
		
		$entityScript = $this;
		
		foreach ($this->getAllSubEntityScripts() as $subEntityScript) {
			if (ReflectionUtils::isClassA($class, $subEntityScript->getEntityModel()->getClass())) {
				$entityScript = $subEntityScript;
			}
		}
		
		return $entityScript;
	}
	/**
	 * @return \rocket\script\entity\ScriptElementCollection
	 */
	public function getCommandCollection() {
		return $this->commandCollection;
	}
	/**
	 * @return \rocket\script\entity\ScriptElementCollection
	 */
	public function getFieldCollection() {
		return $this->fieldCollection;
	}
	
	public function getScriptFieldByEntityPropertyName($name) {
		foreach ($this->fieldCollection as $field) {
			if ($field instanceof EntityPropertyScriptField
					&& $field->getEntityProperty()->getName() == $name) {
				return $field;
			}
		}
		
		throw new UnknownScriptElementException('No ScriptField with EntityProperty ' . $name 
				. ' found in EntityScript ' . $this->getId() . '.');
	}
	
	public function createTranslationModel(TranslationManager $translationManager, $draftableOnly) {
		$translatables = array();
		foreach ($this->fieldCollection as $id => $field) {
			if ($field instanceof TranslatableScriptField && $field->isTranslationEnabled() 
					&& (!$draftableOnly || ($field instanceof DraftableScriptField && $field->isDraftEnabled()))) {
				$translatables[$id] = $field;
			}
		}
		
		return new TranslationModel($translationManager, $this->getEntityModel(), $translatables);
	}
	
	public function createDraftModel(DraftManager $draftManager) {
		$draftModel = new DraftModel($draftManager, $this->entityModel, $this->getDraftables(false),
				$this->getDraftables(true));
		
		return $draftModel;
	}
	/**
	 * @return \rocket\script\entity\ScriptElementCollection
	 */				
	public function getModificatorCollection() {
		return $this->constraintCollection;
	}
	
	public function setupScriptState(ScriptState $scriptState) {
		foreach ($this->constraintCollection as $constraint) {
			$constraint->setupScriptState($scriptState);
		}
	}
	/**
	 * @return \rocket\script\entity\ScriptElementCollection
	 */
	public function getListenerCollection() {
		return $this->listenerCollection;
	}
	
	public function hasSecurityOptions() {
		return $this->superEntityScript === null;
	}
	
	public function getPrivilegeOptions(N2nContext $n2nContext) {
		if ($this->superEntityScript !== null) return null;
		
		return $this->buildPrivilegeOptions($this, $n2nContext, array());
	}
	
	private function buildPrivilegeOptions(EntityScript $entityScript, N2nContext $n2nContext, array $options) {
		$locale = $n2nContext->getLocale();
		foreach ($entityScript->getCommandCollection()->filterLevel() as $scriptCommand) {
			if ($scriptCommand instanceof PrivilegedScriptCommand) {
				$privilegeName = $scriptCommand->getPrivilegeLabel($locale);
		
				$options[PrivilegeBuilder::buildPrivilege($scriptCommand)]
						= $scriptCommand->getPrivilegeLabel($locale);
			}
				
			if ($scriptCommand instanceof PrivilegeExtendableScriptCommand) {
				$privilegeOptions = $scriptCommand->getPrivilegeExtOptions($locale);
					
				ArgumentUtils::validateArrayReturnType($privilegeOptions, 'scalar', $scriptCommand,
						'getPrivilegeOptions');
					
				foreach ($privilegeOptions as $privilegeExt => $label) {
					if ($entityScript->hasSuperEntityScript()) {
						$label . ' (' . $entityScript->getLabel() . ')';
					}
					
					$options[PrivilegeBuilder::buildPrivilege($scriptCommand, $privilegeExt)] = $label;
				}
			}
		}
		
		foreach ($entityScript->getSubEntityScripts() as $subEntityScript) {
			$options = $this->buildPrivilegeOptions($subEntityScript, $n2nContext, $options);
		}
		
		return $options;
	}
	
	private function ensureIsTop() {
		if ($this->superEntityScript !== null) {
			throw new UnsupportedOperationException('EntityScript has super EntityScript');
		}
	}
	
	public function createRestrictionSelectorItems(N2nContext $n2nContext) {
		$this->ensureIsTop();
		
		$restrictionSelectorItems = array();
		foreach ($this->fieldCollection as $scriptField) {
			if (!($scriptField instanceof RestrictionScriptField)) continue;
			
			$restrictionSelectorItem = $scriptField->createRestrictionSelectorItem($n2nContext);
			
			ArgumentUtils::validateReturnType($restrictionSelectorItem, 'rocket\script\entity\filter\item\SelectorItem', 
					$scriptField, 'createRestrictionSelectorItem');
			
			$restrictionSelectorItems[$scriptField->getId()] = $restrictionSelectorItem;
		}
		
		return $restrictionSelectorItems;
	}
	
	public function createAccessOptionCollection(N2nContext $n2nContext) {
		$this->ensureIsTop();
		
		$optionCollection = null;
		
		foreach ($this->getFieldCollection()->combineAll() as $scriptField) {
			if (!($scriptField instanceof AccessControllableScriptField)) {
				continue;
			}
				
			$accessOptionCollection = $scriptField->createAccessOptionCollection($n2nContext);
			if ($accessOptionCollection === null) continue;
				
			ArgumentUtils::validateReturnType($accessOptionCollection, 
					'n2n\dispatch\option\OptionCollection',
					$scriptField, 'createAccessOptionCollection');
				
			if ($optionCollection === null) {
				$optionCollection = new OptionCollectionImpl();
			}
			
			$label = $scriptField->getLabel();
			$entityScript = $scriptField->getEntityScript();
			if ($entityScript->hasSuperEntityScript()) {
				$label .= ' (' . $entityScript->getLabel() . ')';
			}
							
			$optionCollection->addOption($scriptField->getId(), 
					new OptionCollectionOption($label, $accessOptionCollection));
		}
		
		return $optionCollection;
	}
	
	public function isObjectValid($object) {
		return is_object($object) && ReflectionUtils::isObjectA($object, $this->getEntityModel()->getClass());
	}
	
// 	private function findOverviewScriptCommand() {
// 		foreach ($this->scriptCommandCollection as $scriptCommand) {
// 			if ($scriptCommand instanceof OverviewScriptCommand) {
// 				return $scriptCommand;
// 			}
// 		}
		
// 		return null;
// 	}	
// 	/**
// 	 * @return boolean 
// 	 */
// 	public function hasOverviewScriptCommand() {
// 		return null !== $this->findOverviewCommand();
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \rocket\script\core\Script::getOverviewPathExt()
// 	 */
// 	public function getOverviewPathExt() {
// 		$overviewScirptCommand = $this->findOverviewScriptCommand();
// 		if ($overviewScirptCommand === null) {
// 			throw new IllegalStateException('No OverviewScriptCommand assigned to EntityScript \'' . $this->getId() . '\'.');
// 		}
		
// 		return $overviewScriptCommand->getOverviewPathExt();
// 	}
	
// 	private function findEntryDetailScriptCommand() {
// 		foreach ($this->scriptCommandCollection as $scriptCommand) {
// 			if ($scriptCommand instanceof EntryDetailScriptCommand) {
// 				return $scriptCommand;
// 			}
// 		}
		
// 		return null;
// 	}
// 	/**
// 	 * @return boolean 
// 	 */
// 	public function hasEntryDetailScriptCommand() {
// 		return null !== $this->findEntryDetailScriptCommand();
// 	}
// 	/**
// 	 * @param ScriptNavPoint $scriptNavPoint
// 	 * @return string
// 	 * @throws IllegalStateException
// 	 */
// 	public function getEntryDetailPathExt(ScriptNavPoint $scriptNavPoint) {
// 		$entryDetailScriptCommand = $this->findEntryDetailScriptCommand();
// 		if ($entryDetailScriptCommand === null) {
// 			throw new IllegalStateException('No EntryDetailScriptCommand assigned to EntityScript \'' . $this->getId() . '\'.');
// 		}
		
// 		return $entryDetailScriptCommand->getEntryDetailPathExt($scriptNavPoint);
// 	}
	
// 	private function findEntryAddScriptCommand() {
// 		foreach ($this->scriptCommandCollection as $scriptCommand) {
// 			if ($scriptCommand instanceof EntryAddScriptCommand) {
// 				return $scriptCommand;
// 			}
// 		}
		
// 		return null;
// 	}
// 	/**
// 	 * @return boolean 
// 	 */
// 	public function hasEntryAddScriptCommand() {
// 		return null !== $this->findEntryAddScriptCommand();
// 	}
	
// 	public function getEntryAddPathExt() {
// 		$entryAddScriptCommand = $this->findEntryAddScriptCommand();
// 		if ($entryAddScriptCommand === null) {			
// 			throw new IllegalStateException('No EntryAddScriptCommand assigned to EntityScript \'' . $this->getId() . '\'.');
// 		}
		
// 		return $this->mainEntryAddScriptCommand->getAddPathExt();
// 	}
// 	/**
// 	 * @param array $partialControlOrder
// 	 */
// 	public function setPartialControlOrder(array $partialControlOrder) {
// 		$this->partialControlOrder = $partialControlOrder;
// 	}
// 	/**
// 	 * @return array
// 	 */
// 	public function getPartialControlOrder() {
// 		return $this->partialControlOrder;
// 	}
// 	/**
// 	 * @param array $overallControlOrder
// 	 */
// 	public function setOverallControlOrder(array $overallControlOrder) {
// 		$this->overallControlOrder = $overallControlOrder;
// 	}
// 	/**
// 	 * @return array
// 	 */
// 	public function getOverallControlOrder() {
// 		return $this->overallControlOrder;
// 	}
// 	/**
// 	 * @param array $entryControlOrder
// 	 */
// 	public function setEntryControlOrder(array $entryControlOrder) {
// 		$this->entryControlOrder = $entryControlOrder;
// 	}
// 	/**
// 	 * @return array
// 	 */
// 	public function getEntryControlOrder() {
// 		return $this->entryControlOrder;
// 	}
	/**
	 * @param unknown $propertyName
	 * @return \rocket\script\entity\field\PropertyScriptField
	 */
	public function containsScriptFieldPropertyName($propertyName) {
		foreach ($this->fieldCollection as $scriptField) {
			if ($scriptField instanceof PropertyScriptField
					&& $scriptField->getPropertyName() == $propertyName) {
				return true;
			}
		}
		
		return false;
	}
	/**
	 * @param unknown $propertyName
	 * @throws UnknownScriptElementException
	 * @return \rocket\script\entity\field\PropertyScriptField
	 */
	public function getScriptFieldByPropertyName($propertyName) {
		foreach ($this->fieldCollection as $scriptField) {
			if ($scriptField instanceof PropertyScriptField
					&& $scriptField->getPropertyName() == $propertyName) {
				return $scriptField;
			}
		}
		
		throw new UnknownScriptElementException('No ScriptField for property name \'' . (string) $propertyName . '\' found in script \''
				. $this->getId() . '\'.');
	}
	
// 	public function setTranslationDisabled($translationDisabled) {
// 		$this->translationDisabled = (boolean) $translationDisabled;
// 	}
	
// 	public function isTranslationDisabled() {
// 		return $this->translationDisabled;
// 	}
	
// 	public function setDraftDisabled($draftDisabled) {
// 		$this->draftDisabled = (boolean) $draftDisabled;
// 	}
	
// 	public function isDraftDisabled() {
// 		return $this->draftDisabled;
// 	}
	
	public function setDraftHistorySize($draftHistorySize) {
		$this->draftHistorySize = (int)$draftHistorySize;
	}
	
	public function getDraftHistorySize() {
		return $this->draftHistorySize;
	}
	/**
	 * Returns true if this EntityScript has a ScriptField which has Drafts enabled.
	 * @return boolean
	 */
	public function isDraftEnabled() {
		foreach ($this->fieldCollection as $scriptField) {
			if ($scriptField instanceof DraftableScriptField && $scriptField->isDraftEnabled()) {
				return true;
			}
		}
		
		return false;
	}
	/**
	 * Returns true if this EntityScript has a ScriptField which has Translations enabled.
	 * @return boolean
	 */
	public function isTranslationEnabled() {
		foreach ($this->fieldCollection as $scriptField) {
			if ($scriptField instanceof TranslatableScriptField && $scriptField->isTranslationEnabled()) {
				return true;
			}
		}
		
		return false;
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
		return $this->dataSourceName;
	}
	/**
	 * @param string $knownStringPattern
	 */
	public function setKnownStringPattern($knownStringPattern) {
		$this->knownStringPattern = $knownStringPattern;
	}
	/**
	 * @return string
	 */
	public function getKnownStringPattern() {
		return $this->knownStringPattern;
	}
	/**
	 * @param \ReflectionClass $previewControllerClass
	 */
	public function setPreviewControllerClass(\ReflectionClass $previewControllerClass = null) {
		$this->previewControllerClass = $previewControllerClass;
	}
	/**
	 * @return \ReflectionClass
	 */
	public function getPreviewControllerClass() {
		return $this->previewControllerClass;
	}
	/**
	 * @return boolean
	 */
	public function isPreviewAvailable() {
		return isset($this->previewControllerClass);
	}
	/**
	 * @return boolean
	 */
	public function isEditablePreviewAvailable() {
		return isset($this->previewControllerClass) 
				&& $this->previewControllerClass->implementsInterface('rocket\script\controller\preview\EditablePreviewController');
	}
	
	public function createPreviewController(PreviewModel $previewModel, Request $request, Response $response) {
		$previewController = $this->previewControllerClass->newInstance($request, $response);
		$previewController->setPreviewModel($previewModel);
		return $previewController;
	}
	
	public function getTypeChangeMode() {
		return $this->typeChangeMode;
	}
	
	public function setTypeChangeMode($typeChangeMode) {
		ArgumentUtils::validateEnum($typeChangeMode, self::getTypeChangeModes());
		$this->typeChangeMode = $typeChangeMode;
	}
	
	public static function getTypeChangeModes() {
		return array(self::TYPE_CHANGE_MODE_DISABLED, self::TYPE_CHANGE_MODE_REPLACE, 
				self::TYPE_CHANGE_MODE_CHANGE);
	}
	
	private $mainTranslationLocale;
	private $translationLocales;
	
	public function getMainTranslationLocale() {
		if ($this->superEntityScript !== null) {
			return $this->superEntityScript->getMainTranslationLocale();	
		} 
		
		if ($this->mainTranslationLocale === null) {
			return Locale::getDefault();
		}
		
		return $this->mainTranslationLocale;
	}
	
	public function setMainTranslationLocale(Locale $mainTranslationLocale = null) {
		if ($this->superEntityScript !== null) {
			return $this->superEntityScript->setMainTranslationLocale($mainTranslationLocale);	
		}
		
		$this->mainTranslationLocale = $mainTranslationLocale;
	}
	
	public function getTranslationLocales() {
		if ($this->superEntityScript !== null) {
			return $this->superEntityScript->getTranslationLocales();	
		}
		
		if ($this->translationLocales === null) {
			$locales = N2N::getLocales();
			unset($locales[$this->getMainTranslationLocale()->getId()]);
			return $locales;
		}
		
		return $this->translationLocales;
	}
	
	public function setTranslationLocales(array $translationLocales = null) {
		if ($this->superEntityScript === null) {
			$this->translationLocales = $translationLocales;
		}
		
		$this->superEntityScript->setTranslationLocales($translationLocales);
	}
	
	/**
	 * @param \n2n\persistence\DbhPool $dbhPool
	 * @return \n2n\persistence\orm\EntityManager
	 */
	public function lookupEntityManager(DbhPool $dbhPool, $transactional = false) {
		$emf = $this->lookupEntityManagerFactory($dbhPool);
		if ($transactional) {
			return $emf->getTransactional();
		} else {
			return $emf->getShared();
		}
	}
	/**
	 * @param DbhPool $dbhPool
	 * @return \n2n\persistence\orm\EntityManagerFactory
	 */
	public function lookupEntityManagerFactory(DbhPool $dbhPool) {
		return $dbhPool->getEntityManagerFactory($this->dataSourceName);
	}
	/**
	 * @param Entity $entity
	 * @return mixed
	 */
	public function extractId(Entity $entity) {
		return OrmUtils::extractId($entity, $this->entityModel);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\core\Script::createController()
	 */
	public function createController() {
		$controller = new EntityScriptController();
// 		$scriptState = $manageState->createScriptState($this, $controller->getControllerContext());
// 		$controller->setScriptState($scriptState);
		
// 		foreach ($this->constraintCollection as $constraint) {
// 			$constraint->setupScriptState($scriptState);
// 		}
		
		return $controller;
	}
	/**
	 * @param Entity $entity
	 * @param Locale $locale
	 * @return string
	 */
	public function createKnownString(Entity $entity, Locale $locale) {
		$search = array();
		$replace = array();
		foreach ($this->fieldCollection as $scriptField) {
			if (!($scriptField instanceof HighlightableScriptField)) continue;
			$fieldPlaceHoler = self::KNOWN_STRING_FIELD_OPEN_DELIMITER . $scriptField->getId() 
					. self::KNOWN_STRING_FIELD_CLOSE_DELIMITER;
			if (false === strpos($this->knownStringPattern, $fieldPlaceHoler)) continue;
			$search[] = $fieldPlaceHoler;
			$replace[] = $scriptField->createKnownString($entity, $locale);
		}
		
		return str_replace($search, $replace, $this->knownStringPattern);
	}
	
	public function getDefaultSortDirections() {
		return $this->defaultSortDirections;
	}
	
	public function setDefaultSortDirections(array $defaultSortDirections) {
		$this->defaultSortDirections = $defaultSortDirections;
	}
	/**
	 * @param IndependentScriptMask $defaultMask
	 */
	public function setDefaultMask(IndependentScriptMask $defaultMask = null) {
		$this->defaultMask = $defaultMask;
	}
	/**
	 * @return \rocket\script\entity\mask\IndependentScriptMask
	 */
	public function getDefaultMask() {
		return $this->defaultMask;
	}
	
	public function getOrCreateDefaultMask() {
		if ($this->defaultMask !== null) {
			return $this->defaultMask;
		}
		
		return new IndependentScriptMask($this, new ScriptMaskExtraction());
	}
	/**
	 * @return \rocket\script\entity\mask\IndependentScriptMask[]
	 */
	public function getMaskSet() {
		return $this->maskSet;
	}
	/**
	 * @param string $id
	 * @return \rocket\script\entity\mask\IndependentScriptMask
	 */
	public function getMaskById($id) {
		if ($this->maskSet->offsetExists($id)) {
			return $this->maskSet[$id];
		}

		throw new UnknownScriptMaskException('EntityScript ' . $this->getId()
				. ' contains no ScriptMask with Id ' . $id);
	}
	
	public function triggerEntityFlushEvent(EntityFlushEvent $event, Rocket $rocket) {
		switch ($event->getType()) {
			case EntityFlushEvent::TYPE_REMOVED:
				if ($this->isTranslationEnabled()) {
					$translationManager = $rocket->getOrCreateTranslationManager($event->getPersistenceContext()->getEntityManager());
					foreach ($translationManager->findAll($event->getEntity()) as $translation) {
						$translationManager->remove($translation);
					}
				}
				
				if ($this->isDraftEnabled()) {
					$draftModel = DraftModelFactory::createDraftModel( 
							$event->getPersistenceContext()->getEntityManager(), $this);
					$draftModel->removeDraftsByEntityId($event->getId(), $event->getEntity());
				}
				break;
			case EntityFlushEvent::TYPE_TYPE_CHANGED:
				if ($this->isTranslationEnabled()) {
					$translationManager = $rocket->getOrCreateTranslationManager($event->getPersistenceContext()->getEntityManager());
					$lowestCommonEntityScript = $this->findEntityScriptByEntityModel($event->getLowestCommonEntityModel());
					$lowestCommonMappingDefinition = $lowestCommonEntityScript->createMappingDefinition();
					
					foreach ($translationManager->findAll($event->getEntity()) as $translation) {
						$newTranslation = $translationManager->find($event->getNewEntity(), $translation->getLocale(), true);
						
						$lowestCommonMappingDefinition->translationWriteAll($newTranslation,
								$lowestCommonMappingDefinition->translationCopyAll(
										$lowestCommonMappingDefinition->translationReadAll($translation), 
										$translation->getLocale(), $translationManager, true));
																			
						$translationManager->persist($newTranslation);
						$translationManager->remove($translation);
					}
				}
				
				break;
		}
		
		foreach ($this->listenerCollection as $entityChangeListener) {
			$entityChangeListener->onEntityChanged(
					new EntityChangeEvent($event->getType(), $event->getId(), $event->getEntity(), $this, 
							$event->getPersistenceContext()->getEntityManager()));
		}
	}
	
	public function toScriptExtraction() {
		return EntityScriptExtraction::createFromEntityScript($this);
	}
	
	public function createMappingDefinition() {
		$mappingDefinition = new MappingDefinition();
		foreach ($this->fieldCollection as $field) {
			if (!($field instanceof MappableScriptField)) continue;
				
			$typeConstraints = $field->getTypeConstraints();
			
			$readable = null;
			if ($field instanceof ReadableScriptField) {
				$readable = $field->getReadable();
				ArgumentUtils::validateReturnType($readable,
						'rocket\script\entity\manage\mapping\Readable', $field, 'getReadable');
			}
				
			$writable = null;
			if ($field instanceof WritableScriptField) {
				$writable = $field->getWritable();
				ArgumentUtils::validateReturnType($writable,
						'rocket\script\entity\manage\mapping\Writable', $field, 'getWritable');
			}
				
			$draftable = null;
			if ($field instanceof DraftableScriptField && $field->isDraftEnabled()) {
				$draftable = $field->getDraftable();
				ArgumentUtils::validateReturnType($draftable,
						'rocket\script\entity\adaptive\draft\Draftable', $field, 'getDraftable');
			}
				
			$translatable = null;
			if ($field instanceof TranslatableScriptField && $field->isTranslationEnabled()) {
				$translatable = $field->getTranslatable();
				ArgumentUtils::validateReturnType($translatable, 
						'rocket\script\entity\adaptive\translation\Translatable', $field, 'getTranslatable');
			}
			
			$mappingDefinition->putMappable($field->getId(), new Mappable($typeConstraints, 
					$readable, $writable, $draftable, $translatable));
		}
		
		foreach ($this->constraintCollection as $constraint) {
			$constraint->setupMappingDefinition($mappingDefinition);
		}
		
		return $mappingDefinition;
	}
	
	public function createScriptSelectionMapping(MappingDefinition $mappingDefinition, ScriptState $scriptState, 
			ScriptSelection $scriptSelection, PrivilegeConstraint $privilegeConstraint = null) {
		$mapping = new ScriptSelectionMapping($this, $mappingDefinition, $scriptSelection);
		
		if (!$scriptSelection->isNew() && null !== ($entityScriptConstraint = $scriptState->getEntityScriptConstraint())) {
			$mapping->registerAccessRestrictor(
					$entityScriptConstraint->createAccessRestrictor(new MappingArrayAccess($mapping, true)));
		}
		
		if (null !== ($constraint = $scriptState->getCommandExecutionConstraint())) {
			$mapping->registerConstraint($constraint);
		}
		
		foreach ($this->constraintCollection as $constraint) {
			$constraint->setupScriptSelectionMapping($scriptState, $mapping);
		}
		
		return $mapping;
	}
	
// 	public function createEditEntryModel(ScriptSelectionMapping $entityMapping, $levelOnly = false) {
// 		$entryFormPart = new EntryFormPart($entityMapping);
		
// 		foreach ($this->fieldCollection->filter($levelOnly) as $field) {
// 			if ($field instanceof EditableScriptField && !$field->isReadOnly($scriptState, $scriptSelection)) {
// 				$entryFormPart->addEditable($field);
// 				continue;
// 			}
				
// 			if ($field instanceof DisplayableScriptField) {
// 				$entryFormPart->addDisplayable($field);
// 			}		
// 		}
		
// 		if ($entityMapping->getScriptSelection()->isNew() || $this->isTypeChangable()) {
// 			foreach ($this->getSubEntityScripts() as $subEntityScript) {
// 				$this->buildEntryFormPart($entryFormPart->create, $subEntityScript->getFieldCollection()->filterLevel());
// 			}
// 		}
				
// 		foreach ($this->constraintCollection as $constraint) {
// 			$constraint->setupEditEntryModel($entryFormPart);
// 		}

// 		return $entryFormPart;
// 	}
}


