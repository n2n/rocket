<?php
namespace rocket\ei\util\entry;

use rocket\ei\manage\security\EiEntryAccess;
use rocket\ei\EiPropPath;
use rocket\ei\EiCommandPath;

class EiuEntryAccess {
	private $eiEntryAccess;
	private $eiuEntry;
	
	function __construct(EiEntryAccess $eiEntryAccess, EiuEntry $eiuEntry) {
		$this->eiEntryAccess = $eiEntryAccess;
		$this->eiuEntry = $eiuEntry;
	}
	
	function isExecutableBy($eiCommandPath) {
		return $this->eiEntryAccess->isExecutableBy(EiCommandPath::create($eiCommandPath));
	}
	
	function getEiFieldAccess($eiPropPath) {
		return $this->eiEntryAccess->getEiFieldAccess(EiPropPath::create($eiPropPath));
	}
}

