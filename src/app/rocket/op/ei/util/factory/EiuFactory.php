<?php
namespace rocket\op\ei\util\factory;

use rocket\op\ei\util\privilege\EiuCommandPrivilege;
use rocket\op\util\OpfControlResponse;
use rocket\op\ei\util\EiuAnalyst;
use n2n\util\type\TypeConstraint;
use rocket\op\ei\util\Eiu;
use rocket\ui\si\content\SiField;
use rocket\op\ei\manage\gui\EiGuiField;
use rocket\op\ei\util\control\EiuGuiControlFactory;
use rocket\op\ei\util\si\EiuSiFactory;

class EiuFactory {
	private $eiu;
	private $eiuAnalyst;
	
	/**
	 * @param Eiu $eiu
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(Eiu $eiu, EiuAnalyst $eiuAnalyst) {
		$this->eiu = $eiu;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @param string $label
	 * @return \rocket\op\ei\util\privilege\EiuCommandPrivilege
	 */
	function newCommandPrivilege(string $label) {
		return new EiuCommandPrivilege($label);
	}
	
	/**
	 * @return OpfControlResponse
	 */
	function newControlResponse() {
		return new OpfControlResponse($this->eiuAnalyst);
	}
	
	/**
	 * @return EiuGuiControlFactory
	 */
	function guiControl() {
		return new EiuGuiControlFactory($this->eiuAnalyst);
	}

	/**
	 * @return EiuGuiControlFactory
	 */
	function gc(): EiuGuiControlFactory {
		return $this->guiControl();
	}

	function si(): EiuSiFactory {
		return new EiuSiFactory($this->eiuAnalyst);
	}
	
	/**
	 * @param \Closure $callback
	 * @return EifIdNameProp
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
	
	/**
	 * @param \Closure $closure
	 * @return \rocket\op\ei\util\factory\EifGuiProp
	 */
	function newGuiProp(\Closure $closure) {
		return new EifGuiProp($closure);
	}
	
	/**
	 * @param \Closure|EiGuiField $eiGuiCallbackOrAssembler
	 * @return \rocket\op\ei\util\factory\EifGuiPropSetup
	 */
	function newGuiPropSetup($eiGuiCallbackOrAssembler) {
		return new EifGuiPropSetup($eiGuiCallbackOrAssembler);
	}
	
	/**
	 * @return EifGuiField
	 */
	function newGuiField(SiField $siField) {
		return new EifGuiField($this->eiu, $siField);
	}
}
