<?php
namespace rocket\script\core;

use rocket\script\core\ScriptCommandGroup;
use n2n\reflection\ReflectionUtils;
use n2n\core\TypeNotFoundException;
use n2n\core\config\source\WritableConfigSource;
use rocket\core\model\ModuleSeparatedConfigSource;

class ScriptElementStore {
	const RAW_DATA_SCRIPT_FIELD_CLASSES_KEY = 'scriptFieldClasses';
	const RAW_DATA_SCRIPT_COMMAND_CLASSES_KEY = 'scriptCommandClasses';
	const RAW_DATA_SCRIPT_COMMAND_GROUPS_KEY = 'scriptCommandGroups';
	const RAW_DATA_SCRIPT_CONSTRAINT_CLASSES_KEY = 'scriptModificatorClasses';
	const RAW_DATA_SCRIPT_LISTENER_CLASSES_KEY = 'scriptListenerClasses';
	
	private $elementConfigSource;
	private $fieldClasses = array();
	private $fieldClassesByModule = array();
	private $commandClasses = array();
	private $commandClassesByModule = array();
	private $commandGroups = array();
	private $commandGroupsByModule = array();
	private $constraintClasses = array();
	private $constraintClassesByModule = array();
	private $listenerClasses = array();
	private $listenerClassesByModule = array();
	
	public function __construct(ModuleSeparatedConfigSource $elementConfigSource) {
		$this->elementConfigSource = $elementConfigSource;
		foreach ($elementConfigSource->getExistingConfigSources() as $namespace => $configSource) {
			$this->analyzeModuleRawData($namespace, $configSource->readArray());
		}
	}
	
	private function extractElementArray($elementKey, array $rawData) {
		if (isset($rawData[$elementKey]) && is_array($rawData[$elementKey])) {
			return $rawData[$elementKey];
		}
		
		return array();
	}
	
	private function analyzeModuleRawData($namespace, array $moduleRawData) {		
		// ScriptFields
		$this->fieldClassesByModule[$namespace] = array();
		foreach ($this->extractElementArray(self::RAW_DATA_SCRIPT_FIELD_CLASSES_KEY, $moduleRawData) 
				as $key => $scriptFieldClassName) {
			try {
				$fieldClass = ReflectionUtils::createReflectionClass($scriptFieldClassName);
				if (!$fieldClass->implementsInterface('rocket\script\entity\field\ScriptField')
						|| !$fieldClass->implementsInterface('rocket\script\entity\IndependentScriptElement')) continue;
				
				$this->fieldClasses[$scriptFieldClassName] = $fieldClass;
				$this->fieldClassesByModule[$namespace][$scriptFieldClassName] = $fieldClass;
			} catch (TypeNotFoundException $e) { }
		}
		
		// ScriptCommands
		$this->commandClassesByModule[$namespace] = array();
		foreach ($this->extractElementArray(self::RAW_DATA_SCRIPT_COMMAND_CLASSES_KEY, $moduleRawData) 
				as $key => $scriptCommandClassName) {
			try {
				$scriptCommandClass =  ReflectionUtils::createReflectionClass($scriptCommandClassName);
				if (!$scriptCommandClass->implementsInterface('rocket\script\entity\command\ScriptCommand')
						|| !$scriptCommandClass->implementsInterface('rocket\script\entity\IndependentScriptElement')) continue;
				
				$this->commandClasses[$scriptCommandClassName] = $scriptCommandClass;
				$this->commandClassesByModule[$namespace][$scriptCommandClassName] = $scriptCommandClass;
			} catch (TypeNotFoundException $e) { }
		}
				
		// ScriptCommandGroups
		$this->commandGroupsByModule[$namespace] = array();
		foreach ($this->extractElementArray(self::RAW_DATA_SCRIPT_COMMAND_GROUPS_KEY, $moduleRawData) 
				as $groupName => $scriptCommandClassNames) {
			if (!is_array($scriptCommandClassNames)) {
				continue;
			}
		
			$scriptCommandGroup = new ScriptCommandGroup($groupName);
			foreach ($scriptCommandClassNames as $key => $scriptCommandClassName) {
				if (!isset($this->commandClasses[$scriptCommandClassName])) continue;
				$scriptCommandGroup->addCommandClass($this->commandClasses[$scriptCommandClassName]); 
			}
		
			$this->commandGroups[$groupName] = $scriptCommandGroup;
			$this->commandGroupsByModule[$namespace][$groupName] = $scriptCommandGroup;
		}
		
		// ScriptModificators
		$this->constraintClassesByModule[$namespace] = array();
		foreach ($this->extractElementArray(self::RAW_DATA_SCRIPT_CONSTRAINT_CLASSES_KEY, $moduleRawData) 
				as $key => $scriptModificatorClassName) {
			try {
				$constraintClass =  ReflectionUtils::createReflectionClass($scriptModificatorClassName);
				if (!$constraintClass->implementsInterface('rocket\script\entity\modificator\ScriptModificator')
						|| !$constraintClass->implementsInterface('rocket\script\entity\IndependentScriptElement')) continue;
		
				$this->constraintClasses[$scriptModificatorClassName] = $constraintClass;
				$this->constraintClassesByModule[$namespace][$scriptModificatorClassName] = $constraintClass;
			} catch (TypeNotFoundException $e) { }
		}
		
		// ScriptListeners
		$this->listenerClassesByModule[$namespace] = array();
		foreach ($this->extractElementArray(self::RAW_DATA_SCRIPT_CONSTRAINT_CLASSES_KEY, $moduleRawData) 
				as $key => $scriptListenerClassName) {
			try {
				$listenerClass =  ReflectionUtils::createReflectionClass($scriptListenerClassName);
				if (!$listenerClass->implementsInterface('rocket\script\entity\listener\ScriptListener')
						|| !$listenerClass->implementsInterface('rocket\script\entity\IndependentScriptElement')) continue;
		
				$this->listenerClasses[$scriptListenerClassName] = $listenerClass;
				$this->listenerClassesByModule[$namespace][$scriptListenerClassName] = $listenerClass;
			} catch (TypeNotFoundException $e) { }
		}
	}
	
	public function getFieldClasses() {
		return $this->fieldClasses;
	}
	
	public function getFieldClassesByModule($module) {
		$namespace = (string) $module;
		if (isset($this->fieldClassesByModule[$namespace])) {
			return $this->fieldClassesByModule[$namespace];
		}
		
		return array();
	}
	
	public function removeFieldClassesByModule($module) {
		$namespace = (string) $module;
		if (!isset($this->fieldClassesByModule[$namespace])) return;
		foreach ($this->fieldClassesByModule[$namespace] as $scriptFieldClass) {
			unset($this->fieldClasses[$scriptFieldClass->getName()]);
		}
		$this->fieldClassesByModule[$namespace] = array();
	}
	
	public function addFieldClass($module, \ReflectionClass $scriptFieldClass) {
		$namespace = (string) $module;
		if (!isset($this->fieldClassesByModule[$namespace])) {
			$this->fieldClassesByModule[$namespace] = array();
		}
		$className = $scriptFieldClass->getName();
		$this->fieldClasses[$className] = $scriptFieldClass;
		$this->fieldClassesByModule[$namespace][$className] = $scriptFieldClass;
	}
	
	public function getCommandClasses() {
		return $this->commandClasses;
	}
	
	public function getCommandClassesByModule($module) {
		$namespace = (string) $module;
		if (isset($this->commandClassesByModule[$namespace])) {
			return $this->commandClassesByModule[$namespace];
		}
		
		return array();
	}
	
	public function removeCommandClassesByModule($module) {
		$namespace = (string) $module;
		if (!isset($this->commandClassesByModule[$namespace])) return;
		foreach ($this->commandClassesByModule[$namespace] as $scriptCommandClass) {
			unset($this->commandClasses[$scriptCommandClass->getName()]);
		}
		$this->commandClassesByModule[$namespace] = array();
	}
	
	public function addCommandClass($module, \ReflectionClass $scriptCommandClass) {
		$namespace = (string) $module;
		if (!isset($this->commandClassesByModule[$namespace])) {
			$this->commandClassesByModule[$namespace] = array();
		}
		$className = $scriptCommandClass->getName();
		$this->commandClasses[$className] = $scriptCommandClass;
		$this->commandClassesByModule[$namespace][$className] = $scriptCommandClass;
	}
	
	public function getCommandGroups() {
		return $this->commandGroups;
	}
	
	public function getCommandGroupsByModule($module) {
		$namespace = (string) $module;
		if (isset($this->commandGroupsByModule[$namespace])) {
			return $this->commandGroupsByModule[$namespace];
		}
		
		return array();
	}
	
	public function removeCommandGroupsByModule($module) {
		$namespace = (string) $module;
		if (!isset($this->commandGroupsByModule[$namespace])) return;
		foreach ($this->commandGroupsByModule[$namespace] as $scriptCommandGroup) {
			unset($this->commandGroups[$scriptCommandGroup->getName()]);
		}
		$this->commandGroupsByModule[$namespace] = array();
	}
	
	public function addCommandGroup($module, ScriptCommandGroup $scriptCommandGroup) {
		$namespace = (string) $module;
		if (!isset($this->commandGroupsByModule[$namespace])) {
			$this->commandGroupsByModule[$namespace] = array();
		}
		
		$className = $scriptCommandGroup->getName();
		$this->commandGroups[$className] = $scriptCommandGroup;
		$this->commandGroupsByModule[$namespace][$className] = $scriptCommandGroup;
	}
	
	public function getConstraintClasses() {
		return $this->constraintClasses;
	}
	
	public function getConstraintClassesByModule($module) {
		$namespace = (string) $module;
		if (isset($this->constraintClassesByModule[$namespace])) {
			return $this->constraintClassesByModule[$namespace];
		}
		
		return array();
	}
	
	public function removeConstraintClassesByModule($module) {
		$namespace = (string) $module;
		if (!isset($this->constraintClassesByModule[$namespace])) return;
		foreach ($this->constraintClassesByModule[$namespace] as $scriptModificatorClass) {
			unset($this->constraintClasses[$scriptModificatorClass->getName()]);
		}
		$this->constraintClassesByModule[$namespace] = array();
	}
	
	public function addConstraintClass($module, \ReflectionClass $scriptModificatorClass) {
		$namespace = (string) $module;
		if (!isset($this->constraintClassesByModule[$namespace])) {
			$this->constraintClassesByModule[$namespace] = array();
		}
		$className = $scriptModificatorClass->getName();
		$this->constraintClasses[$className] = $scriptModificatorClass;
		$this->constraintClassesByModule[$namespace][$className] = $scriptModificatorClass;
	}
	
	public function getListenerClasses() {
		return $this->listenerClasses;
	}
	
	public function getListenerClassesByModule($module) {
		$namespace = (string) $module;
		if (isset($this->listenerClassesByModule[$namespace])) {
			return $this->listenerClassesByModule[$namespace];
		}
		
		return array();
	}
	
	public function removeListenerClassesByModule($module) {
		$namespace = (string) $module;
		if (!isset($this->listenerClassesByModule[$namespace])) return;
		foreach ($this->listenerClassesByModule[$namespace] as $scriptListenerClass) {
			unset($this->listenerClasses[$scriptListenerClass->getName()]);
		}
		$this->listenerClassesByModule[$namespace] = array();
	}
	
	public function addListenerClass($module, \ReflectionClass $scriptListenerClass) {
		$namespace = (string) $module;
		if (!isset($this->listenerClassesByModule[$namespace])) {
			$this->listenerClassesByModule[$namespace] = array();
		}
		$className = $scriptListenerClass->getName();
		$this->listenerClasses[$className] = $scriptListenerClass;
		$this->listenerClassesByModule[$namespace][$className] = $scriptListenerClass;
	}
	
	public function flush($module = null) {
		if ($module !== null) {
			$this->persistByModule((string) $module, $this->configSources[$namespace]);
			return;
		}
		
		$namespaces = array_unique(array_merge(
				array_keys($this->fieldClasses), array_keys($this->scriptFieldClassesByModule), 
				array_keys($this->scriptCommandClasses), array_keys($this->scriptCommandClassesByModule), 
				array_keys($this->scriptCommandGroups), array_keys($this->scriptCommandGroupsByModule), 
				array_keys($this->scriptModificatorClasses), array_keys($this->scriptModificatorClassesByModule), 
				array_keys($this->scriptListenerClasses), array_keys($this->scriptListenerClassesByModule)));
		
		foreach ($namespaces as $namespace) {
			$this->persistByModule($namespace);
		}
	}
	
	private function persistByModule($namespace) {
		$namespace = (string) $namespace;
		
		$write = false;
		$moduleRawData = array();
		
		if (isset($this->fieldClassesByModule[$namespace])) {
			$write = true;
			$moduleRawData[self::RAW_DATA_SCRIPT_FIELD_CLASSES_KEY] = array();
			foreach ($this->fieldClassesByModule[$namespace] as $scriptFieldClass) {
				$moduleRawData[self::RAW_DATA_SCRIPT_FIELD_CLASSES_KEY][] = $scriptFieldClass->getName();
			} 
		}
		
		if (isset($this->commandClassesByModule[$namespace])) {
			$write = true;
			$moduleRawData[self::RAW_DATA_SCRIPT_COMMAND_CLASSES_KEY] = array();
			foreach ($this->commandClassesByModule[$namespace] as $scriptCommandClass) {
				$moduleRawData[self::RAW_DATA_SCRIPT_COMMAND_CLASSES_KEY][] = $scriptCommandClass->getName();
			}
		}
		
		if (isset($this->commandGroupsByModule[$namespace])) {
			$write = true;
			$moduleRawData[self::RAW_DATA_SCRIPT_COMMAND_GROUPS_KEY] = array();
			foreach ($this->commandGroupsByModule[$namespace] as $scriptCommandGroup) {
				$groupName = $scriptCommandGroup->getName();
				$moduleRawData[self::RAW_DATA_SCRIPT_COMMAND_GROUPS_KEY][$groupName] = array();
				foreach ($scriptCommandGroup->getCommandClasses() as $scriptCommandClass) {
					$moduleRawData[self::RAW_DATA_SCRIPT_COMMAND_GROUPS_KEY][$groupName][] = $scriptCommandClass->getName();
				}
			}
		}
		
		if (isset($this->constraintClassesByModule[$namespace])) {
			$write = true;
			$moduleRawData[self::RAW_DATA_SCRIPT_CONSTRAINT_CLASSES_KEY] = array();
			foreach ($this->constraintClassesByModule[$namespace] as $scriptModificatorClass) {
				$moduleRawData[self::RAW_DATA_SCRIPT_CONSTRAINT_CLASSES_KEY][] = $scriptModificatorClass->getName();
			} 
		}
		
		if (isset($this->listenerClassesByModule[$namespace])) {
			$write = true;
			$moduleRawData[self::RAW_DATA_SCRIPT_LISTENER_CLASSES_KEY] = array();
			foreach ($this->listenerClassesByModule[$namespace] as $listenerClass) {
				$moduleRawData[self::RAW_DATA_SCRIPT_LISTENER_CLASSES_KEY][] = $listenerClass->getName();
			} 
		}
		
		if ($write) {
			$this->elementConfigSource->getConfigSourceByModule($namespace)->writeArray($moduleRawData);
		}
	}
}