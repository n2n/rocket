<?php
namespace rocket\ei\util\entry;

use rocket\ei\manage\security\EiEntryAccess;
use rocket\ei\EiPropPath;
use rocket\ei\EiCmdPath;
use rocket\ei\component\command\EiCmdNature;

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

