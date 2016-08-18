<?php
namespace rocket\core\model;

use n2n\core\VarStore;
use n2n\N2N;
use n2n\core\config\source\JsonFileConfigSource;

class RocketModuleSeparatedConfigSource implements ModuleSeparatedConfigSource {
	private $varStore;
	private $fileName;
	private $configSources = array();

	public function __construct(VarStore $varStore, $fileName) {
		$this->varStore = $varStore;
		$this->fileName = $fileName;
	}
	/* (non-PHPdoc)
	 * @see \rocket\core\model\ModuleSeparatedConfigSource::getConfigSourceByModule()
	*/
	public function getConfigSourceByModule($module) {
		$namespace = (string) $module;
		if (isset($this->configSources[$namespace])) {
			return $this->configSources[$namespace];
		}
		
		return new JsonFileConfigSource((string) $this->varStore->requestFilePath(VarStore::CATEGORY_ETC, $namespace,
				Rocket::ROCKET_CONFIG_FOLDER, $this->fileName, true, true, true));
	}
	/* (non-PHPdoc)
	 * @see \rocket\core\model\ModuleSeparatedConfigSource::getExistingConfigSources()
	*/
	public function getExistingConfigSources() {
		$this->configSources = array();
		
		foreach (N2N::getModules() as $module) {
			$namespace = $module->getNamespace();
			
			if (isset($this->configSources[$namespace])) continue;
			
			$filePath = $this->varStore->requestFilePath(VarStore::CATEGORY_ETC,
					$namespace, Rocket::ROCKET_CONFIG_FOLDER, $this->fileName, false, false, false);
			if ($filePath->exists()) {
				$this->configSources[$namespace] = new JsonFileConfigSource((string)$filePath);
			}
		}
		return $this->configSources;
	}
	
	public function getExistingModules() {
		$modules = array();
		foreach ($this->getExistingConfigSources() as $namespace => $configSource) {
			$modules[$namespace] = N2N::getModule($namespace);
		}
		return $modules;
	}
}