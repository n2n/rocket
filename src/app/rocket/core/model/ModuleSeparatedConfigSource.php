<?php
namespace rocket\core\model;

interface ModuleSeparatedConfigSource {
	/**
	 * @param mixed $module
	 * @return \n2n\core\config\source\WritableConfigSource
	 */
	public function getConfigSourceByModule($module);
	/**
	 * @return WritableConfigSource[] key must be module namespace and value the WritableConfigSource 
	 */
	public function getExistingConfigSources();
	/**
	 * @param string $namespace
	 * @return Module[]
	 */
	public function getExistingModules();
}