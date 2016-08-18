<?php
namespace rocket\script\entity\manage\model;

use rocket\script\entity\manage\model\ManageModel;

interface EntryListModel extends ManageModel {
	/**
	 * @return \rocket\script\entity\manage\EntryModel[]  
	 */
	public function getEntryModels();
}