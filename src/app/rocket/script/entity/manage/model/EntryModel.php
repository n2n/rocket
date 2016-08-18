<?php
namespace rocket\script\entity\manage\model;

interface EntryModel extends ManageModel {
	/**
	 * @return ScriptSelectionMapping can be null
	 */
	public function getScriptSelectionMapping();
	
	public function hasListEntryModel();
	
	public function getListEntryModel();
}