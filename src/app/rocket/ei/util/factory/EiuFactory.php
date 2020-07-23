<?php
namespace rocket\ei\util\factory;

use n2n\l10n\N2nLocale;
use rocket\ei\util\privilege\EiuCommandPrivilege;
use rocket\ei\util\control\EiuControlResponse;
use rocket\ei\manage\entry\EiFieldValidationResult;
use rocket\ei\manage\idname\IdNameProp;
use n2n\util\type\ArgUtils;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;
use rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter;

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
	
	function newEiField() {
		return new EiuEiField();
	}
}

class ClosureIdNameProp implements IdNameProp {
	private $function;
	
	function __construct(\Closure $callback) {
		$this->function = new \ReflectionFunction($callback);
	}
	
	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {		$mmi = new MagicMethodInvoker($eiu->getN2nContext());
		$mmi->setClassParamObject(Eiu::class, $eiu);
		$mmi->setClassParamObject(N2nLocale::class, $n2nLocale);
		$mmi->setReturnTypeConstraint(TypeConstraints::scalar(true));
		
		return $mmi->invoke(null, $this->function);
	}	
}

class EiuEiField extends EiFieldAdapter {
	
	
	protected function readValue() {
	}

	protected function validateValue($value, EiFieldValidationResult $validationResult) {
	}

	public function copyValue(Eiu $copyEiu) {
	}

	public function isWritable(): bool {
	}

	public function isCopyable(): bool {
	}

	protected function checkValue($value) {
	}

	protected function isValueValid($value) {
	}

	protected function writeValue($value) {
	}

	
}