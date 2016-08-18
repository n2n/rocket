<?php

namespace rocket\script\entity;

use rocket\script\core\SetupProcess;
use n2n\util\Attributes;
use n2n\dispatch\option\impl\OptionCollectionImpl;
use n2n\reflection\ReflectionUtils;

abstract class IndependentScriptElementAdapter extends ScriptElementAdapter implements IndependentScriptElement {
	/**
	 * @var Attributes
	 */
	protected $attributes;
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\IndependentScriptCommand::__construct()
	 */
	public function __construct(Attributes $attributes) {
		$this->attributes = $attributes;
	}
	
	public function getTypeName() {
		$class = new \ReflectionClass($this);
		return ReflectionUtils::prettyName($class->getShortName());
	}
	
	public static function shortenTypeName($typeName, array $suffixes) {
		$nameParts = explode(' ', $typeName);
		while (null !== ($suffix = array_pop($suffixes))) {
			if (end($nameParts) != $suffix) break;
			
			array_pop($nameParts);
		}
		
		return implode(' ', $nameParts);
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\command\IndependentScriptCommand::setup()
	 */
	public function setup(SetupProcess $setupProcess) {		
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\IndependentScriptElement::getAttributes()
	 */
	public function getAttributes() {
		return $this->attributes;
	}
	/* (non-PHPdoc)
	 * @see \rocket\script\entity\IndependentScriptElement::createOptionCollection()
	 */
	public function createOptionCollection() {
		return new OptionCollectionImpl();
	}
}