<?php
namespace rocket\ei\manage\security;

use rocket\ei\manage\entry\EiEntry;
use rocket\ei\EiCommandPath;

interface EiEntryAccessFactory {
	
	
	/**
	 * @param EiCommandPath $eiCommandPath
	 * @return bool
	 */
	function isExecutableBy(EiCommandPath $eiCommandPath): bool;
	
	/**
	 * @param EiEntry $eiEntry
	 */
	function createEiEntryAccess(EiEntry $eiEntry): EiEntryAccess;
}

