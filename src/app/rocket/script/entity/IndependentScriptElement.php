<?php
namespace rocket\script\entity;

use rocket\script\core\SetupProcess;
use n2n\util\Attributes;

interface IndependentScriptElement extends ScriptElement {
	/**
	 * @param EntityScript $entityScript
	 * @param Attributes $attributes
	 */
	public function __construct(Attributes $attributes);
	/**
	 * @return string 
	 */
	public function getTypeName();
	/**
	 * @return \n2n\util\Attributes
	 */
	public function getAttributes();
	/**
	 * @return \n2n\dispatch\option\OptionCollection
	 */
	public function createOptionCollection();
	/**
	 * @param SetupProcess $setupProcess
	 */
	public function setup(SetupProcess $setupProcess);
}