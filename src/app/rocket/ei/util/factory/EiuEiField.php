<?php
namespace rocket\ei\util\factory;

use rocket\ei\manage\entry\EiFieldValidationResult;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter;
use n2n\validation\plan\Validator;
use n2n\util\type\TypeConstraint;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\validation\build\impl\Validate;

class EiuEiField extends EiFieldAdapter {
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var TypeConstraint|null
	 */
	private $typeConstraint;
	/**
	 * @var MagicMethodInvoker
	 */
	private $readerMmi;
	/**
	 * @var MagicMethodInvoker|null
	 */
	private $readMapperClosure;
	/**
	 * @var MagicMethodInvoker|null
	 */
	private $writerMmi;
	/**
	 * @var MagicMethodInvoker|null
	 */
	private $writeMapperMmi;
	/**
	 * @var Validator[]
	 */
	private $validators = [];
	
	/**
	 * @param Eiu $eiu
	 * @param TypeConstraint $typeConstraint
	 * @param \Closure $reader
	 */
	function __construct(Eiu $eiu, ?TypeConstraint $typeConstraint, \Closure $reader) {
		$this->eiu = $eiu;
		$this->typeConstraint = $typeConstraint;
		$this->setReader($reader);
	}
	
	/**
	 * @param \Closure $closure
	 * @return EiuEiField
	 */
	private function setReader(\Closure $closure) {
		$this->readerMmi = new MagicMethodInvoker($this->eiu->getN2nContext());
		$this->readerMmi->setClassParamObject(Eiu::class, $this->eiu);
		$this->readerMmi->setReturnTypeConstraint($this->typeConstraint);
		$this->readerMmi->setMethod(new \ReflectionFunction($closure));
		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 * @return EiuEiField
	 */
	function setReadMapper(?\Closure $closure) {
		$this->readMapperMmi = new MagicMethodInvoker($this->eiu->getN2nContext());
		$this->readMapperMmi->setClassParamObject(Eiu::class, $this->eiu);
		$this->readMapperMmi->setReturnTypeConstraint($this->typeConstraint);
		$this->readMapperMmi->setMethod(new \ReflectionFunction($closure));
		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 * @return EiuEiField
	 */
	private function setWriter(\Closure $closure) {
		$this->writerMmi = new MagicMethodInvoker($this->eiu->getN2nContext());
		$this->writerMmi->setClassParamObject(Eiu::class, $this->eiu);
		$this->writerMmi->setMethod(new \ReflectionFunction($closure));
		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 * @return EiuEiField
	 */
	function setWriteMapper(?\Closure $closure) {
		$this->writeMapperMmi = new MagicMethodInvoker($this->eiu->getN2nContext());
		$this->writeMapperMmi->setClassParamObject(Eiu::class, $this->eiu);
		$this->writeMapperMmi->setReturnTypeConstraint($this->typeConstraint);
		$this->writeMapperMmi->setMethod(new \ReflectionFunction($closure));
		return $this;
	}
	
	/**
	 * @param Validator ...$validators
	 * @return EiuEiField
	 */
	function val(Validator ...$validators) {
		array_push($this->validators, ...$validators);
		return $this;
	}
	
	protected function checkValue($value) {
		if ($this->typeConstraint !== null) {
			$this->typeConstraint->validate($value);
		}
	}
	
	protected function readValue() {
		$value = $this->readerMmi->invoke();
		if ($this->readerMmi !== null) {
			$value = $this->readerMmi->invoke(null, null, [$value]);
		}
		return $value;
	}
	
	public function isWritable(): bool {
		return $this->writerMmi !== null;
	}
	
	protected function writeValue($value) {
		if ($this->writeMapperMmi !== null) {
			$value = $this->writeMapperMmi->invoke(null, null, [$value]);
		}
		
		return $this->writerMmi->invoke(null, null, [$value]);
	}
	
	
	protected function isValueValid($value) {
	}
	
	protected function validateValue($value, EiFieldValidationResult $validationResult) {
		if (empty($this->validators)) {
			return;
		}
		
		Validate::value($value, ...$this->validators);
	}

	
	public function isCopyable(): bool {
	}
	
	public function copyValue(Eiu $copyEiu) {
	}

	

	

	
	
}