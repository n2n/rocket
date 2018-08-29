<?php
namespace rocket\ei\component\prop;

use rocket\ei\util\model\Eiu;
use rocket\ei\manage\security\filter\SecurityFilterProp;

interface FilterableEiProp extends EiProp {
	
	/**
	 * @param Eiu $eiu
	 * @return \rocket\ei\manage\security\filter\SecurityFilterProp|null
	 */
	public function buildSecurityFilterProp(Eiu $eiu): ?SecurityFilterProp;
}