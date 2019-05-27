<?php
namespace rocket\ei\util;

use rocket\ei\util\privilege\EiuCommandPrivilege;
use rocket\ei\util\control\EiuControlResponse;

class EiuFactory {
	
	/**
	 * @param string $label
	 * @return \rocket\ei\util\privilege\EiuCommandPrivilege
	 */
	function newCommandPrivilege(string $label) {
		return new EiuCommandPrivilege($label);
	}
	
	/**
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function newControlResponse() {
		return new EiuControlResponse();
	}
}