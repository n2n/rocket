<?php
namespace rocket\ei\util\spec;

use rocket\ei\EiPropPath;
use rocket\ei\EiCmdPath;
use rocket\ei\component\command\IndependentEiCmd;
use rocket\impl\ei\component\config\EiConfiguratorAdapter;
use rocket\ei\util\privilege\EiuCommandPrivilege;
use rocket\ei\util\factory\EiuFactory;
use rocket\ei\component\command\EiCmd;
use rocket\ei\util\EiuAnalyst;

class EiuCmd {
	private ?EiCmd $eiCmd;

	function __construct(private readonly EiCmdPath $eiCmdPath, private readonly EiuMask $eiuMask) {
	}
	
	/**
	 * @return EiCmdPath
	 */
	function getEiCmdPath() {
		return $this->eiCmdPath;
	}

	/**
	 * @return EiCmd
	 */
	function getEiCmd() {
		return $this->eiCmd
				?? $this->eiCmd = $this->eiuMask->getEiMask()->getEiCmdCollection()->getByPath($this->eiCmdPath);
	}
	
	/**
	 * @return string
	 */
	function getTypeName() {
		return EiConfiguratorAdapter::createAutoTypeName($this->getEiCmd()->getNature(), ['Ei', 'Cmd', 'Nature']);
	}
	
	/**
	 * @param string $label
	 * @return EiuCommandPrivilege
	 */
	function newPrivilegeCommand(string $label = null) {
		return (new EiuFactory())->newCommandPrivilege($label ?? $this->getTypeName());
	}
}