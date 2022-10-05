<?php
namespace rocket\ei\util\spec;

use rocket\ei\EiPropPath;
use rocket\ei\EiCommandPath;
use rocket\ei\component\command\IndependentEiCmd;
use rocket\impl\ei\component\config\EiConfiguratorAdapter;
use rocket\ei\util\privilege\EiuCommandPrivilege;
use rocket\ei\util\factory\EiuFactory;

class EiuCommand {
	private $eiCommandPath;
	private $eiuEngine;
	
	/**
	 * @param EiPropPath $eiCommandPath
	 * @param EiuEngine $eiuEngine
	 */
	function __construct(EiCommandPath $eiCommandPath, EiuEngine $eiuEngine) {
		$this->eiCommandPath = $eiCommandPath;
		$this->eiuEngine = $eiuEngine;
	}
	
	/**
	 * @return \rocket\ei\EiCommandPath
	 */
	function getEiCommandPath() {
		return $this->eiCommandPath;
	}
	
	/**
	 * @return \rocket\ei\component\command\EiCmdNature
	 */
	function getEiCommand() {
		return $this->eiuEngine->getEiEngine()->getEiMask()->getEiCmdCollection()
				->getById((string) $this->eiCommandPath);
	}
	
	/**
	 * @return string
	 */
	function getTypeName() {
		$eiCommand = $this->getEiCommand();
		if ($eiCommand instanceof IndependentEiCmd) {
			return $eiCommand->createEiConfigurator()->getTypeName();
		}
		
		return EiConfiguratorAdapter::createAutoTypeName($eiCommand, ['Ei', 'Command']);
	}
	
	/**
	 * @param string $label
	 * @return EiuCommandPrivilege
	 */
	function newPrivilegeCommand(string $label = null) {
		return (new EiuFactory())->newCommandPrivilege($label ?? $this->getTypeName());
	}
}