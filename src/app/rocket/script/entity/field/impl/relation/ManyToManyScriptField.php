<?php
namespace rocket\script\entity\field\impl\relation;

use n2n\http\Path;
use n2n\ui\Raw;
use n2n\ui\html\HtmlElement;
use rocket\core\model\Rocket;
use n2n\core\DynamicTextCollection;
use n2n\persistence\orm\criteria\CriteriaProperty;
use rocket\script\entity\field\impl\relation\command\ManyToManyScriptCommand;
use n2n\ui\html\HtmlView;
use n2n\persistence\orm\property\ManyToManyProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\field\impl\relation\model\SimpleScriptFieldRelation;
use n2n\util\Attributes;
use rocket\script\entity\EntityScript;
use rocket\script\entity\field\impl\relation\option\ToManyOption;
use rocket\script\entity\manage\EntryManageUtils;

class ManyToManyScriptField extends SimpleToManyScriptFieldAdapter {
	
	public function __construct(Attributes $attributes) {
		parent::__construct($attributes);
		
		$this->initilaize(new SimpleScriptFieldRelation($this, true, true));
	}
	
	public function getTypeName() {
		return 'ManyToMany';
	}
	
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return  $entityProperty instanceof ManyToManyProperty;
	}	
}