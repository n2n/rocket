<?php
namespace rocket\script\entity\manage\model;

interface ManageModel {
	/**
	 * @return \rocket\script\entity\manage\display\DisplayDefinition
	 */
	public function getDisplayDefinition();
	/**
	 * @return \rocket\script\entity\manage\ScriptState 
	 */
	public function getScriptState();
}