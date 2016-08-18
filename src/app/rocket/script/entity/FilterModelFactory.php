<?php

namespace rocket\script\entity;

use rocket\script\entity\EntityScript;
use rocket\script\entity\field\FilterableScriptField;
use n2n\core\N2nContext;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\field\SortableScriptField;
use rocket\script\entity\field\QuickSearchableScriptField;
use rocket\script\entity\filter\FilterModel;
use rocket\script\entity\filter\SortModel;
use rocket\script\entity\filter\QuickSearchModel;

class FilterModelFactory {
	public static function createFilterModel(EntityScript $entityScript, N2nContext $n2nContext) {
		return self::createFilterModelInstance($entityScript, $n2nContext);
	}
	
	public static function createFilterModelFromScriptState(ScriptState $scriptState) {
		return self::createFilterModelInstance($scriptState->getContextEntityScript(), 
				$scriptState->getN2nContext(), $scriptState);
	}
	
	private static function createFilterModelInstance(EntityScript $entityScript, N2nContext $n2nContext, 
			ScriptState $scriptState = null) {
		$filterModel = new FilterModel();
		foreach ($entityScript->getFieldCollection() as $field) {
			if ($field instanceof FilterableScriptField) {
				$filterModel->putFilterItem($field->getId(), $field->createFilterItem($n2nContext, $scriptState));
			}	
		}		
		return $filterModel;
	}
	
	public static function createSortModel(EntityScript $entityScript, N2nContext $n2nContext) {
		return self::createSortModelInstance($entityScript, $n2nContext);
	}
	
	public static function createSortModelFromScriptState(ScriptState $scriptState) {
		return self::createSortModelInstance($scriptState->getContextEntityScript(), $scriptState->getN2nContext());
	}
	
	private static function createSortModelInstance(EntityScript $entityScript, N2nContext $n2nContext,
			ScriptState $scriptState = null) {
		$sortModel = new SortModel();
		foreach ($entityScript->getFieldCollection() as $field) {
			if ($field instanceof SortableScriptField) {
				$sortModel->putSortItem($field->getId(), $field->createSortItem($n2nContext, $scriptState));
			}
		}
		return $sortModel;
	}
		
	public static function createQuickSearchableModel(ScriptState $scriptState) {
		$quickSerachModel = new QuickSearchModel();
		foreach ($scriptState->getContextEntityScript()->getFieldCollection() as $field) {
			if ($field instanceof QuickSearchableScriptField) {
				$quickSerachModel->addQuickSearchable($field);
			}
		}
		return $quickSerachModel;
	}
}