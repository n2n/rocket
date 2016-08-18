<?php
namespace rocket\script\entity\field\impl\relation;

use n2n\persistence\orm\property\OneToManyProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\field\impl\relation\model\SimpleScriptFieldRelation;
use n2n\util\Attributes;

class OneToManyScriptField extends SimpleToManyScriptFieldAdapter {
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		
		$this->optionReadOnlyDefault = true;
		$this->initilaize(new SimpleScriptFieldRelation($this, false, true));
	}
	
	public function getTypeName() {
		return 'OneToMany';
	}
		
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof OneToManyProperty;
	}

}