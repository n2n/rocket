<?php
namespace rocket\ei\util;

use n2n\l10n\N2nLocale;
use rocket\ei\util\privilege\EiuCommandPrivilege;
use rocket\ei\util\control\EiuControlResponse;
use rocket\ei\manage\idname\IdNameProp;
use n2n\util\type\ArgUtils;

class EiuFactory {
	private $eiuAnalyst;
	
	/**
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(EiuAnalyst $eiuAnalyst) {
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
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
		return new EiuControlResponse($this->eiuAnalyst);
	}
	
	/**
	 * @param \Closure $callback
	 * @return IdNameProp
	 */
	function newIdNameProp(\Closure $callback) {
		return new ClosureIdNameProp($callback);	
	}
}

class ClosureIdNameProp implements IdNameProp {
	private $callback;
	
	function __construct(\Closure $callback) {
		$this->callback = $callback;
	}
	
	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		$callback = $this->callback;
		$idName = $callback($eiu, $n2nLocale);
		ArgUtils::valTypeReturn($idName, ['string', 'null'], null, $callback);
		return $idName;
	}	
}