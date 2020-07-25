<?php
namespace rocket\ei\util\factory;

use rocket\ei\util\privilege\EiuCommandPrivilege;
use rocket\ei\util\control\SifControlResponse;
use rocket\ei\manage\idname\IdNameProp;
use rocket\ei\util\EiuAnalyst;
use n2n\util\type\TypeConstraint;
use rocket\ei\util\Eiu;

class EiuFactory {
	private $eiu;
	private $eiuAnalyst;
	
	/**
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(Eiu $eiu, EiuAnalyst $eiuAnalyst) {
		$this->eiu = $eiu;
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
	 * @return \rocket\ei\util\control\SifControlResponse
	 */
	function newControlResponse() {
		return new SifControlResponse($this->eiuAnalyst);
	}
	
	/**
	 * @param \Closure $callback
	 * @return IdNameProp
	 */
	function newIdNameProp(\Closure $callback) {
		return new EifIdNameProp($callback);	
	}
	
	/**
	 * @param TypeConstraint $typeConstraint
	 * @param \Closure $reader
	 * @return EifField
	 */
	function newField(?TypeConstraint $typeConstraint, \Closure $reader) {
		return new EifField($this->eiu, $typeConstraint, $reader);
	}
	
	function newGuiField() {
		
	}
}
