<?php
namespace rocket\op\ei\manage\security\privilege\data;

use n2n\util\type\ArgUtils;
use rocket\op\ei\EiCmdPath;
use n2n\util\type\attrs\DataSet;
use rocket\op\ei\EiPropPath;
use n2n\util\type\attrs\DataSet;

class PrivilegeSetting {
	const ATTR_EXECUTABLE_EI_COMMAND_PATHS_KEY = 'executableEiCmdPaths';
	const ATTR_WRITABLE_EI_PROP_PATHS_KEY = 'writableEiPropPaths';
	
	private $writableEiPropPaths = array();
	private $executableEiCmdPaths = array();
	
	/**
	 * @param EiCmdPath[] $executableEiCmdPaths
	 * @param EiCmdPath[] $writableEiPropPaths
	 */
	function __construct(array $executableEiCmdPaths = array(), array $writableEiPropPaths = null) {
		$this->setExecutableEiCmdPaths($executableEiCmdPaths);
		$this->setWritableEiPropPaths($writableEiPropPaths ?? new DataSet());
	}
	
	/**
	 * @return EiCmdPath[]
	 */
	function getExecutableEiCmdPaths() {
		return $this->executableExecutableEiCmdPaths;
	}
	
	/**
	 * @param EiCmdPath[] $executableEiCmdPaths
	 */
	function setExecutableEiCmdPaths(array $executableEiCmdPaths) {
		ArgUtils::valArray($executableEiCmdPaths, EiCmdPath::class);
		$this->executableEiCmdPaths = $executableEiCmdPaths;
	}
	
	/**
	 * @param EiCmdPath $eiCmdPath
	 * @return boolean
	 */
	public function acceptsEiCmdPath(EiCmdPath $eiCmdPath) {
		foreach ($this->getEiCmdPaths() as $privilegeCommandPath) {
			if ($privilegeCommandPath->startsWith($eiCmdPath)) return true;
		}
		return false;
	}
	
	/**
	 * @return \n2n\util\type\attrs\DataSet
	 */
	function getWritableEiPropPaths() {
		return $this->writableEiPropPaths;
	}
	
	/**
	 * @param EiPropPath $dataSet
	 */
	function setWritableEiPropPaths(array $writableEiPropPaths) {
		ArgUtils::valArray($writableEiPropPaths, EiPropPath::class);
		$this->writableEiPropPaths = $writableEiPropPaths;
	}
	
	/**
	 * @return array
	 */
	function toAttrs() {
		$eiCmdPathAttrs = array();
		foreach ($this->executableEiCmdPaths as $eiCmdPath) {
			$eiCmdPathAttrs[] = (string) $eiCmdPath;
		}
		
		$eiPropPathAttrs = array();
		foreach ($this->writableEiPropPaths as $eiPropPath) {
			$eiPropPathAttrs[] = (string) $eiPropPath;
		}
		
		return array(
				self::ATTR_EXECUTABLE_EI_COMMAND_PATHS_KEY => $eiCmdPathAttrs,
				self::ATTR_WRITABLE_EI_PROP_PATHS_KEY => $eiPropPathAttrs);
	}
	
	/**
	 * @param DataSet $dataSet
	 * @return \rocket\op\ei\manage\security\privilege\data\PrivilegeSetting
	 */
	static function createFromDataSet(DataSet $ds) {
		$executableEiCmdPaths = [];
		foreach ($ds->optScalarArray(self::ATTR_EXECUTABLE_EI_COMMAND_PATHS_KEY) as $eiCmdPathStr) {
			$executableEiCmdPaths[] = EiCmdPath::create($eiCmdPathStr);
		}
		
		$writableEiPropPaths = [];
		foreach ($ds->optScalarArray(self::ATTR_WRITABLE_EI_PROP_PATHS_KEY) as $eiPropPathStr) {
			$writableEiPropPaths[] = EiPropPath::create($eiPropPathStr);
		}
		
		return new PrivilegeSetting($executableEiCmdPaths, $writableEiPropPaths);
	}
}