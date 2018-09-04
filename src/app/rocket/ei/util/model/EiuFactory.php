<?php
namespace rocket\ei\util\model;

use rocket\ei\util\privilege\EiuCommandPrivilege;

class EiuFactory {
	
	/**
	 * @param string $label
	 * @return \rocket\ei\util\privilege\EiuCommandPrivilege
	 */
	function newCommandPrivilege(string $label) {
		return new EiuCommandPrivilege($label);
	}
}