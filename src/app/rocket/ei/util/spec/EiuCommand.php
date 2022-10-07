<?php
namespace rocket\ei\util\spec;

use rocket\ei\EiPropPath;
use rocket\ei\EiCmdPath;
use rocket\ei\component\command\IndependentEiCmd;
use rocket\impl\ei\component\config\EiConfiguratorAdapter;
use rocket\ei\util\privilege\EiuCommandPrivilege;
use rocket\ei\util\factory\EiuFactory;

class EiuCommand {
	private $eiCmdPath;
	private $eiuEngine;
	
	/**
	 * @param EiPropPath $eiCmdPath
	 * @param EiuEngine $eiuEngine
	 */
	function __construct(EiCmdPath $eiCmdPath, EiuEngine $eiuEngine) {
		$this->eiCmdPath = $eiCmdPath;
		$this->eiuEngine = $eiuEngine;
	}
	
	/**
	 * @return \rocket\ei\EiCmdPath
	 */
	function getEiCmdPath() {
		return $this->eiCmdPath;
	}
	
	/**
	 * @return \rocket\ei\component\command\EiCmdNature
	 */
	function getEiCommand() {
		return $this->eiuEngine->getEiEngine()->getEiMask()->getEiCmdCollection()
				->getById((string) $this->eiCmdPath);
	}
	
	/**
	 * @return string
	 */
	function getTypeName() {
		$eiCmd = $this->getEiCommand();
		if ($eiCmd instanceof IndependentEiCmd) {
			return $eiCmd->createEiConfigurator()->getTypeName();
		}
		
		return EiConfiguratorAdapter::createAutoTypeName($eiCmd, ['Ei', 'Command']);
	}
	
	/**
	 * @param string $label
	 * @return EiuCommandPrivilege
	 */
	function newPrivilegeCommand(string $label = null) {
		return (new EiuFactory())->newCommandPrivilege($label ?? $this->getTypeName());
	}
}