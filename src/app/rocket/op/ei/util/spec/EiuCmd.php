<?php
namespace rocket\op\ei\util\spec;

use rocket\op\ei\EiPropPath;
use rocket\op\ei\EiCmdPath;
use rocket\op\ei\component\command\IndependentEiCmd;
use rocket\impl\ei\component\config\EiConfiguratorAdapter;
use rocket\op\ei\util\privilege\EiuCommandPrivilege;
use rocket\op\ei\util\factory\EiuFactory;
use rocket\op\ei\component\command\EiCmd;
use rocket\op\ei\util\EiuAnalyst;

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