<?php
namespace rocket\script\core;

use rocket\script\entity\field\InvalidScriptFieldConfigurationException;
use rocket\script\entity\InvalidScriptConfigurationException;
use rocket\script\entity\EntityScript;
use n2n\core\Module;
use n2n\persistence\orm\EntityModel;
use n2n\reflection\ReflectionUtils;
use n2n\io\IoUtils;
use n2n\core\config\source\ConfigSource;
use n2n\persistence\DbhPool;
use rocket\script\entity\field\InvalidScriptCommandConfigurationException;
use n2n\persistence\orm\EntityModelManager;
use rocket\script\entity\field\ScriptField;
use rocket\script\entity\field\ScriptFieldOperationFailedException;
use rocket\core\model\ModuleSeparatedConfigSource;
use rocket\script\core\extr\ScriptExtraction;
use rocket\script\entity\adaptive\translation\TranslationModelFactory;
use rocket\script\entity\adaptive\draft\DraftModelFactory;
use n2n\core\Message;

class ScriptManager {	
	private $scriptConfigSource;
	private $dbhPool;
	private $entityModelManager;
	private $entityScriptSetupQueue;
	
	private $scripts = array();
	private $entityScripts = array();
	private $menuGroups;

	private $manageConfig;
	private $scriptConfigs = array();
	private $scriptComponentsStorage;

	public function __construct(ConfigSource $manageConfigSource, ModuleSeparatedConfigSource $scriptConfigSource, 
			DbhPool $dbhPool, EntityModelManager $entityModelManager) {
		$this->manageConfig = new ManageConfig($manageConfigSource);
		$this->scriptConfigSource = $scriptConfigSource;
		$this->dbhPool = $dbhPool;
		$this->entityModelManager = $entityModelManager;
		$this->entityScriptSetupQueue = new EntityScriptSetupQueue($this);
		
		foreach ($scriptConfigSource->getExistingModules() as $module) {
			$this->getOrCreateScriptConfig($module);
		}
	}
	/**
	 * @return \n2n\persistence\orm\EntityModelManager
	 */
	public function getEntityModelManager() {
		return $this->entityModelManager;
	}

	private function getOrCreateScriptConfig(Module $module) {
		$namespace = (string) $module;
		if (isset($this->scriptConfigs[$namespace])) {
			return $this->scriptConfigs[$namespace];
		}
		
		return $this->scriptConfigs[$namespace] = new ScriptConfig(
				$this->scriptConfigSource->getConfigSourceByModule($namespace),
				$module, $this->entityModelManager);
	}
	
	public function getMenuGroups() {
		if ($this->menuGroups !== null) {
			return $this->menuGroups;
		}
		return $this->manageConfig->extractMenuGroups();
	}
	
	public function setMenuGroups(array $menuGroups) {
		$this->menuGroups = $menuGroups;
	}
	
	public function getMenuItemById($id) {
		foreach ($this->getMenuGroups() as $menuGroup) {
			if ($menuGroup->containsMenuItemId($id)) {
				return $menuGroup->getMenuItemById($id);
			}
		}
		
		throw new UnknownMenuItemException($id);
	}
	
	public function containsScriptId($id) {
		foreach ($this->scriptConfigs as $scriptConfig) {
			if ($scriptConfig->containsScriptId($id)) {
				return true;
			}
		}
		
		return false;
	}
					
	public function containsEntityScriptClass(\ReflectionClass $class) {
		foreach ($this->scriptConfigs as $scriptConfig) {
			if ($scriptConfig->containsEntityScriptClass($class)) {
				return true;
			}
		}
		
		return false;
	}
	
	public function getScriptIds() {
		$scriptIds = array();
		foreach ($this->scriptConfigs as $scriptConfig) {
			$scriptIds = array_merge($scriptIds, $scriptConfig->getScriptIds());
		}
		return $scriptIds;
	}
	/**
	 * 
	 * @param unknown_type $id
	 * @throws UnknownScriptException
	 * @throws InvalidScriptConfigurationException
	 */
	public function getScriptById($id) {
		if (isset($this->scripts[$id])) {
			return $this->scripts[$id];
		}

		$scriptExtraction = $this->extractScript($id);
		$script = $scriptExtraction->createScript($this);
		$this->scripts[$id] = $script;
		
		if ($script instanceof EntityScript) {
			$this->entityScripts[$script->getEntityModel()->getClass()->getName()] = $script;
			if ($script->getEntityModel()->hasSuperEntityModel()) {
				$script->setSuperEntityScript($this->getEntityScriptByClass(
						$script->getEntityModel()->getSuperEntityModel()->getClass()));
			}
			
			foreach ($script->getEntityModel()->getSubEntityModels() as $subEntityModel) {
				if ($this->containsEntityScriptClass($subEntityModel->getClass())) { 
					$this->getEntityScriptByClass($subEntityModel->getClass());
				}
			}
			$this->entityScriptSetupQueue->push($script);
		}
		
		$this->entityScriptSetupQueue->trigger();
		
		return $script;
	}
	
	public function getLenientEntityScriptById($id) {
		$this->entityScriptSetupQueue->setLenient(true);
		
		$this->entityScriptSetupQueue->setLenient(true);
		$entityScript = $this->getEntityScriptById($id);
		$this->entityScriptSetupQueue->trigger();
		
		return new LenientResult($entityScript, 
				$this->entityScriptSetupQueue->buildErrorMessages($entityScript->getId()));
	}
	/**
	 * @param \ReflectionClass $class
	 * @return \rocket\script\entity\EntityScript
	 */
	public function getEntityScriptByClass(\ReflectionClass $class) {
		$className = $class->getName();
		if (isset($this->entityScripts[$className])) {
			return $this->entityScripts[$className];
		}
		
		$entityScript = $this->extractEntityScriptByClass($class);
		
		$this->entityScriptSetupQueue->trigger();
		
		return $entityScript;
	}
	
	private function extractEntityScriptByClass(\ReflectionClass $class) {
		$entityScriptExtraction = $this->extractEntityScript($class);
		$entityScript = $entityScriptExtraction->createScript($this);
		
		$this->scripts[$entityScript->getId()] = $entityScript;
		$this->entityScripts[$class->getName()] = $entityScript;
		
		if ($entityScript->getEntityModel()->hasSuperEntityModel()) {
			$entityScript->setSuperEntityScript($this->getEntityScriptByClass(
					$entityScript->getEntityModel()->getSuperEntityModel()->getClass()));
		}
		foreach ($entityScript->getEntityModel()->getSubEntityModels() as $subEntityModel) {
			if (!$this->containsEntityScriptClass($subEntityModel->getClass())) continue;
			$this->getEntityScriptByClass($subEntityModel->getClass());
		}
		
		$this->entityScriptSetupQueue->push($entityScript);
		
		return $entityScript;
	}
	/**
	 * @param string $id
	 * @throws UnknownScriptException
	 * @throws InvalidScriptConfigurationException
	 * @return EntityScript
	 */
	public function getEntityScriptById($id) {
		$script = $this->getScriptById($id);
		if ($script instanceof EntityScript) {
			return $script;
		}
	
		throw new UnknownScriptException('Script with id  \'' . $id . '\' is no EntityScript');
	}
	
	public function isScriptSealed(Script $script) {
		return $this->isScriptOfIdSealed($script->getId());
	}
	
	public function isScriptOfIdSealed($id) {
		return $this->manageConfig->isScriptOfIdSealed($id);
	}
	
	public function unsealScriptById($scriptId) {
		$this->manageConfig->registerAsUnsealed($scriptId);
	}
	
	public function unsealScript(Script $script) {
		$this->manageConfig->registerAsUnsealed($script->getId());
	}
	
	public function sealScriptById($scriptId) {
		$this->manageConfig->registerAsSealed($scriptId);
	}
	
	public function sealScript(Script $script) {
		$this->manageConfig->registerAsSealed($script->getId());
	}

	public function getScripts() {
		$scripts = array();
		foreach ($this->getScriptIds() as $scriptId) {
			$scripts[$scriptId] = $this->getScriptById($scriptId);
		}
		return $scripts;
	}
	
	public function cleanUp() {		
		foreach ($this->getScripts() as $script) {
			if (!($script instanceof EntityScript)) continue;

			$dbh = $script->lookupEntityManager($this->dbhPool, false)->getDbh();
			TranslationModelFactory::checkMeta($dbh, $script);
			DraftModelFactory::checkMeta($dbh, $script);
		}
	}

	public function extractScripts() {
		$scriptExtractions = array();
		foreach ($this->getScriptIds() as $scriptId) {
			$scriptExtractions[$scriptId] = $this->extractScript($scriptId);
		}
		return $scriptExtractions;
	}
		
	public function extractScript($id) {
		foreach ($this->scriptConfigs as $namespace => $scriptConfig) {
			if (!$scriptConfig->containsScriptId($id)) continue;
			return $scriptConfig->extractScript($id);
		}
		
		throw new UnknownScriptException('No Script with id \'' . $id . '\' found.');
	}

	public function writeScriptExtraction(ScriptExtraction $scriptExtraction) {
		$scriptConfig = $this->getOrCreateScriptConfig($scriptExtraction->getModule());
		$scriptConfig->putScriptExtraction($scriptExtraction);
		$scriptConfig->flush();
	}
	
	public function extractEntityScript(\ReflectionClass $class) {
		$className = $class->getName();
		
		foreach ($this->scriptConfigs as $scriptConfig) {
			if (!$scriptConfig->containsEntityScriptClass($class)) continue;
			return $scriptConfig->extractEntityScript($class);
		}
		
		throw new UnknownScriptException('No EntityScript for class  \'' . $class->getName() . '\' found.');
	}
	
	
	private function generateScriptId($module, $id) {
		$prefix = ReflectionUtils::encodeNamespace((string) $module, '-');
		$baseId = mb_strtolower($prefix . '-' . IoUtils::stripSpecialChars($id));
		$id = $baseId;
		$ext = 0;
		while ($this->containsScriptId($id)) {
			$id = $baseId . ($ext++);
		}
		return $id;
	}
	
	public function putScript(Script $script) {
		$this->scripts[$script->getId()] = $script;
		
		if ($script instanceof EntityScript) {
			$this->entityScripts[$script->getEntityModel()->getClass()->getName()] = $script;
		}
		
		$scriptConfig = $this->getOrCreateScriptConfig($script->getModule());
		$scriptConfig->putScriptExtraction($script->toScriptExtraction());
	}
	/**
	 * @param Module $module
	 * @param unknown_type $idBase
	 * @param unknown_type $label
	 * @param EntityModel $entityModel
	 * @return EntityScript
	 */
	public function createEntityScript(Module $module, $idBase, $label, $pluralLabel, EntityModel $entityModel) {
		if ($this->containsEntityScriptClass($entityModel->getClass())) {
			throw new \InvalidArgumentException('An EntityScript for class \'' . $entityModel->getClass()->getName() 
					. '\' is already defined.');
		}
		
		$id = $this->generateScriptId($module, $idBase);
		$entityScript = new EntityScript($id, $label, $pluralLabel, $module, $entityModel);
		
		if ($entityModel->hasSuperEntityModel()) {
			$this->entityScriptSetupQueue->setLenient(true);
			$superEntityScript = $this->getEntityScriptByClass($entityModel->getSuperEntityModel()->getClass());
			$entityScript->setSuperEntityScript($superEntityScript);
			$entityScript->setKnownStringPattern($superEntityScript->getKnownStringPattern());
		}
		
// 		$scriptFieldsFinder = $this->getScriptElementStore()->createScriptFieldManager($entityScript);
		$knownStringPattern = '';
		$idPlaceHolder = 'unknown';
// 		foreach ($entityModel->getLevelProperties() as $property) {
// 			$scriptField = $scriptFieldsFinder->suggestPropertyScriptField($property);
// 			$entityScript->getFieldCollection()->add($scriptField);
			
// 			if (!($scriptField instanceof HighlightableScriptField) || $entityModel->hasSuperEntityModel()) continue;

// 			if ($property instanceof IdProperty)  {
// 				$idPlaceHolder = EntityScript::KNOWN_STRING_FIELD_OPEN_DELIMITER
// 						. $scriptField->getId() . EntityScript::KNOWN_STRING_FIELD_CLOSE_DELIMITER;
// 			} else if (!mb_strlen($knownStringPattern)) {
// 				$knownStringPattern = EntityScript::KNOWN_STRING_FIELD_OPEN_DELIMITER
// 						. $scriptField->getId() . EntityScript::KNOWN_STRING_FIELD_CLOSE_DELIMITER . ' ';
// 			}

// 		}

// 		if (!$entityModel->hasSuperEntityModel()) {
			$knownStringPattern .= '[' . $idPlaceHolder . ']';
			$entityScript->setKnownStringPattern($knownStringPattern);
// 		}

		$this->scripts[$id] = $entityScript;
		$this->entityScripts[$entityModel->getClass()->getName()] = $entityScript;
// 		$this->initializeEntityScript($entityScript, true);
		
		$scriptConfig = $this->getOrCreateScriptConfig($module);
		$scriptConfig->putScriptExtraction($entityScript->toScriptExtraction());
		
		$this->manageConfig->registerAsUnsealed($id);

		return $entityScript;
	}
	
	public function createCustomScript(Module $module, $idBase, $label, \ReflectionClass $controllerClass) {
		$id = $this->generateScriptId($module, $idBase);
		$script = new CustomScript($id, $label, $module, $controllerClass);
		$this->scripts[$id] = $script;
		return $script;
	}
	
	public function removeScriptById($scriptId) {
		foreach ($this->getMenuGroups() as $menuGroup) {
			$menuGroup->removeMenuPointById($scriptId);
		}
		
		$this->manageConfig->registerAsSealed($scriptId);
		foreach ($this->scriptConfigs as $scriptConfig) {
			$scriptConfig->removeScriptById($scriptId);
		}
	}
	
	public function flush() {
		if (isset($this->menuGroups)) {
			$this->manageConfig->setMenuGroups($this->menuGroups);
		}
		$this->manageConfig->flush();
		
		foreach ($this->scripts as $script) {
			$scriptConfig = $this->getOrCreateScriptConfig($script->getModule());
			$scriptConfig->putScriptExtraction($script->toScriptExtraction());
		}
		
		

		foreach ($this->scriptConfigs as $scriptConfig) {
			$scriptConfig->flush();
		}
	}
		
	public static function createInvalidScriptConfigurationException($scriptId, \Exception $previous = null, $reason = null) {
		if ($reason === null && isset($previous)) {
			$reason = $previous->getMessage();
		}
		
		return new InvalidScriptConfigurationException(
				'Configruation of script with id \'' . $scriptId  . '\' is invalid.'
				. (isset($reason) ? ' Reason: ' . $reason : ''), 0, $previous);
	}
	
	public static function createInvalidScriptFieldConfigurationException($scriptFieldId, \Exception $previous = null, $reason = null) {
		if ($reason === null && isset($previous)) {
			$reason = $previous->getMessage();
		}
	
		return new InvalidScriptFieldConfigurationException(
				'Configruation of script field with id \'' . $scriptFieldId  . '\' is invalid.'
				. (isset($reason) ? ' Reason: ' . $reason : ''), 0, $previous);
	}
	
	public static function createInvalidScriptCommandConfigurationException($className, \Exception $previous = null, $reason = null) {
		if ($reason === null && isset($previous)) {
			$reason = $previous->getMessage();
		}
	
		return new InvalidScriptCommandConfigurationException(
				'Configruation of ScriptCommand \'' . $className  . '\' is invalid.'
				. (isset($reason) ? ' Reason: ' . $reason : ''), 0, $previous);
	}
	
	public static function createInvalidScriptModificatorConfigurationException($className, \Exception $previous = null, $reason = null) {
		if ($reason === null && isset($previous)) {
			$reason = $previous->getMessage();
		}
	
		return new InvalidScriptCommandConfigurationException(
				'Configruation of ScriptModificator \'' . $className  . '\' is invalid.'
				. (isset($reason) ? ' Reason: ' . $reason : ''), 0, $previous);
	}
	
	public static function createInvalidScriptListenerConfigurationException($className, \Exception $previous = null, $reason = null) {
		if ($reason === null && isset($previous)) {
			$reason = $previous->getMessage();
		}
	
		return new InvalidScriptCommandConfigurationException(
				'Configruation of ScriptListener \'' . $className  . '\' is invalid.'
				. (isset($reason) ? ' Reason: ' . $reason : ''), 0, $previous);
	}
	
	public static function createScriptFieldOperationFailedException(ScriptField $scriptField, \Exception $previous = null, $reason = null) {
		if ($reason === null && isset($previous)) {
			$reason = $previous->getMessage();
		}
		
		return new ScriptFieldOperationFailedException(
				'Error occurred in ScriptField \'' . $scriptField->getLabel()  . '\' (EntityScript \'' . $scriptField->getEntityScript()->getLabel() . '\').'
				. (isset($reason) ? ' Reason: ' . $reason : ''), 0, $previous);
	}
}

class EntityScriptSetupQueue {
	private $lenient;
	private $scriptManager;
	private $entityScripts = array();
	private $setupProcesses = array();
	
	private $running = false;
	
	public function __construct(ScriptManager $scriptManager) {
		$this->scriptManager = $scriptManager;
	}
	
	public function isLenient() {
		return $this->lenient;
	}
	
	public function setLenient($lenient) {
		$this->lenient = (boolean) $lenient;
	}
	
	public function isRunning() {
		return $this->running;
	}
	
	public function push(EntityScript $entityScript) {
		$this->entityScripts[] = $entityScript;
	}
	
	public function trigger() {
		if ($this->running) return;
		$this->running = true;
		
		while (null !== ($entityScript = array_shift($this->entityScripts))) {
			$setupProcess = new SetupProcess($this->scriptManager, $entityScript);
			
			foreach ($entityScript->getFieldCollection()->filterLevel(true) as $scriptField) {
				$scriptField->setup($setupProcess);
				
				if ($setupProcess->hasFailed()) {
					if ($this->lenient) {
						$this->setupProcesses[$entityScript->getId()] = $setupProcess;
					} else {
						throw $setupProcess->getFailE();
					}
				}
			}
			
			foreach ($entityScript->getCommandCollection()->filterLevel(true) as $scriptCommand) {
				$scriptCommand->setup($setupProcess);
				
				if ($setupProcess->hasFailed()) {
					if ($this->lenient) {
						$this->setupProcesses[$entityScript->getId()] = $setupProcess;
					} else {
						throw $setupProcess->getFailE();
					}
				}
			}
			
			foreach ($entityScript->getModificatorCollection()->filterLevel(true) as $scriptModificator) {
				$scriptModificator->setup($setupProcess);
				
				if ($setupProcess->hasFailed()) {
					if ($this->lenient) {
						$this->setupProcesses[$entityScript->getId()] = $setupProcess;
					} else {
						throw $setupProcess->getFailE();
					}
				}
			}
			
			foreach ($entityScript->getListenerCollection()->filterLevel(true) as $entityChangeListener) {
				$entityChangeListener->setup($setupProcess);
				
				if ($setupProcess->hasFailed()) {
					if ($this->lenient) {
						$this->setupProcesses[$entityScript->getId()] = $setupProcess;
					} else {
						throw $setupProcess->getFailE();
					}
				}
			}
		}
		
		$this->running = false;
	}
	
	public function buildErrorMessages($entityScriptId) {
		$errorMessages = array();
		if (isset($this->setupProcesses[$entityScriptId])) {
			foreach ($this->setupProcesses[$entityScriptId]->getFailEs() as $failE) {
				$errorMessages[] = new Message($failE->getMessage());
			}
			
		}
		return $errorMessages;
	}
}

class LenientResult {
	private $entityScript;
	private $errorMessages;
	
	public function __construct(EntityScript $entityScript, array $errorMessages) {
		$this->entityScript = $entityScript;
		$this->errorMessages = $errorMessages;
	}
	
	public function getEntityScript() {
		return $this->entityScript;
	}
	
	public function getErrorMessages() {
		return $this->errorMessages;
	}
}