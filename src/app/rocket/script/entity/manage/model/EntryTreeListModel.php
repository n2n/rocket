<?php
namespace rocket\script\entity\manage\model;

interface EntryTreeListModel extends EntryListModel {
	
	public function getEntryLevels();
}