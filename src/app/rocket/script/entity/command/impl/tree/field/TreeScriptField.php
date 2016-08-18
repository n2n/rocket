<?php
namespace rocket\script\entity\command\impl\tree\field;

use n2n\persistence\orm\property\DefaultProperty;
use n2n\persistence\orm\property\EntityProperty;
use rocket\script\entity\field\impl\EntityPropertyScriptFieldAdapter;
use n2n\reflection\property\PropertyAccessProxy;
use rocket\script\entity\field\FilterableScriptField;
use n2n\core\N2nContext;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\filter\item\TextFilterItem;
use rocket\script\entity\filter\item\SimpleFilterItem;

abstract class TreeScriptField extends EntityPropertyScriptFieldAdapter implements FilterableScriptField {
	public function isCompatibleWith(EntityProperty $entityProperty) {
		return $entityProperty instanceof DefaultProperty;
	}
	
	public function setPropertyAccessProxy(PropertyAccessProxy $propertyAccessProxy) { }
	
	public function createFilterItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new TextFilterItem($this->getEntityProperty()->getName(), $this->getLabel(),
				SimpleFilterItem::createOperatorOptions($n2nContext->getLocale()));
	}
}