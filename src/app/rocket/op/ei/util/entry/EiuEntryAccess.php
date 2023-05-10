<?php
namespace rocket\op\ei\util\entry;

use rocket\op\ei\manage\security\EiEntryAccess;
use rocket\op\ei\EiPropPath;
use rocket\op\ei\EiCmdPath;
use rocket\op\ei\component\command\EiCmdNature;

class EiuEntryAccess {
	private $eiEntryAccess;
	private $eiuEntry;
	
	function __construct(EiEntryAccess $eiEntryAccess, EiuEntry $eiuEntry) {
		$this->eiEntryAccess = $eiEntryAccess;
		$this->eiuEntry = $eiuEntry;
	}
	
	/**
	 * @param string|EiCmdPath|EiCmdNature $eiCmdPath
	 * @return boolean
	 */
	function isExecutableBy($eiCmdPath) {
		return $this->eiEntryAccess->isEiCommandExecutable(EiCmdPath::create($eiCmdPath));
	}
	
	function isPropWritable($eiPropPath) {
		return $this->eiEntryAccess->isEiPropWritable(EiPropPath::create($eiPropPath));
	}
}

