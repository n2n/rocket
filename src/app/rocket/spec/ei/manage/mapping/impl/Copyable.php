<?php
namespace rocket\spec\ei\manage\mapping\impl;

use rocket\spec\ei\manage\util\model\Eiu;
use rocket\spec\ei\manage\EiObject;

interface Copyable {
	
	/**
	 * @param EiObject $eiObject
	 * @param mixed $value
	 * @param Eiu $copyEiu
	 * @return mixed
	 */
	public function copy(EiObject $eiObject, $value, Eiu $copyEiu);
}

