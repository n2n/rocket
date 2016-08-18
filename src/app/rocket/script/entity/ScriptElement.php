<?php
namespace rocket\script\entity;

interface ScriptElement {
	/**
	 * @return string
	 */
	public function getId();
	/**
	 * @param string $id
	 */
	public function setId($id);
	/**
	 * @return string
	 */
	public function getIdBase();
	/**
	 * @return \rocket\script\entity\EntityScript
	 */
	public function getEntityScript();
	/**
	 * @param EntityScript $entityScript
	 */
	public function setEntityScript(EntityScript $entityScript);
	/**
	 * @param mixed $obj
	 * @return boolean
	 */
	public function equals($obj);
}