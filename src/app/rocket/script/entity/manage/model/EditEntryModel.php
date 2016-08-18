<?php
namespace rocket\script\entity\manage\model;

use n2n\dispatch\PropertyPath;

interface EditEntryModel extends EntryModel {
	public function createPropertyPath($propertyName, PropertyPath $basePropertyPath = null);
}