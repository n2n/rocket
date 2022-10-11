<?php
namespace rocket\ei\util\spec;

use rocket\ei\EiPropPath;
use rocket\ei\EiCmdPath;
use rocket\ei\component\command\IndependentEiCmd;
use rocket\impl\ei\component\config\EiConfiguratorAdapter;
use rocket\ei\util\privilege\EiuCommandPrivilege;
use rocket\ei\util\factory\EiuFactory;
use rocket\ei\component\command\EiCmd;

class EiuCmd {

	function __construct(private readonly EiCmd $eiCmd) {
	}
	
	/**
	 * @return EiCmdPath
	 */
	function getEiCmdPath() {
		return $this->eiCmd->getEiCmdPath();
	}

	/**
	 * @return EiCmd
	 */
	function getEiCmd() {
		return $this->eiCmd;
	}
	
	/**
	 * @return string
	 */
	function getTypeName() {
		return EiConfiguratorAdapter::createAutoTypeName($this->eiCmd->getNature(), ['Ei', 'Cmd', 'Nature']);
	}
	
	/**
	 * @param string $label
	 * @return EiuCommandPrivilege
	 */
	function newPrivilegeCommand(string $label = null) {
		return (new EiuFactory())->newCommandPrivilege($label ?? $this->getTypeName());
	}
}