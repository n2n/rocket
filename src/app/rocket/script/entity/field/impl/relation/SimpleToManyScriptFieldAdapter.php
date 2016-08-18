<?php

namespace rocket\script\entity\field\impl\relation;

use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\ui\html\HtmlView;
use rocket\script\entity\field\impl\relation\option\ToManyOption;
use n2n\core\DynamicTextCollection;
use rocket\core\model\Rocket;
use rocket\script\entity\manage\EntryManageUtils;
use rocket\script\entity\manage\ScriptSelection;
use rocket\script\entity\field\impl\ManageInfo;
use rocket\script\entity\manage\ScriptState;
use n2n\core\N2nContext;
use n2n\N2N;
use rocket\script\entity\field\impl\relation\model\ToManyFilterItem;
use rocket\script\entity\field\FilterableScriptField;
use rocket\user\model\RestrictionScriptField;
use rocket\script\entity\field\impl\relation\model\ToManySelectorItem;
use rocket\script\entity\manage\security\SelectionPrivilegeConstraint;

abstract class SimpleToManyScriptFieldAdapter extends RelationScriptFieldAdapter implements FilterableScriptField, RestrictionScriptField {

	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view, ManageInfo $manageInfo) {
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		if ($scriptSelection->isNew()) return null;
	
		$scriptState = $manageInfo->getScriptState();
		$targetScriptState = $this->fieldRelation->createTargetPseudoScriptState($scriptState, $scriptSelection, false);
		$criteria = $targetScriptState->createCriteria($targetScriptState->getEntityManager(), 'e');
		$criteria->select('COUNT(e)');
	
		$num = $criteria->fetchSingle();
		$dtc = new DynamicTextCollection(Rocket::ROCKET_NAMESPACE, $scriptState->getLocale());
		if ($num == 1) {
			$label = $num . ' ' . $targetScriptState->getScriptMask()->getLabel();
		} else {
			$label = $num . ' ' . $targetScriptState->getScriptMask()->getPluralLabel();
		}
		$html = $view->getHtmlBuilder();
	
		if (null !== ($relation = $scriptState->getScriptRelation($this->getId()))) {
			return $this->createUiLink($relation->getScriptState(), $label, $view);
		}
	
		return $this->createUiLink($targetScriptState, $label, $view);
	}
	
	private function createUiLink(ScriptState $targetScriptState, $label, HtmlView $view) {
		$html = $view->getHtmlBuilder();
		
		if (!$targetScriptState->isOverviewPathAvailable()) return $html->getEsc($label);
		
		return $html->getLink($targetScriptState->getOverviewPath($view->getRequest()), $label);
		
	}
	
	private function createTargetScriptSelections(ScriptSelectionMapping $scriptSelectionMapping) {
		$values = $scriptSelectionMapping->getValue($this->id);
		if ($values === null) return array();
	
		$scriptSelection = $scriptSelectionMapping->getScriptSelection();
		$targetEntityScript = $this->fieldRelation->getTargetEntityScript();
		$targetScriptSelections = array();
		foreach ($values as $key => $targetEntity) {
			$targetScriptSelections[$key] = new ScriptSelection($targetEntityScript->extractId($targetEntity), $targetEntity);
		}
	
		return $targetScriptSelections;
	}
	
	const DEFAULT_ADDABLES_NUM = 6;
	
	public function createOption(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$scriptState = $manageInfo->getScriptState();
		$embeddedAddActivated = $this->fieldRelation->isEmbeddedAddActivated($scriptState);
		
		$targetUtils = new EntryManageUtils($this->fieldRelation->createTargetPseudoScriptState(
				$scriptState, $scriptSelectionMapping->getScriptSelection(), 
				$embeddedAddActivated));
		$toManyOption = new ToManyOption($this->getId(), $this->getLabel(), $scriptSelectionMapping, $targetUtils,
				($this->isRequired($scriptSelectionMapping, $manageInfo) ? 1 : null));
	
		if ($embeddedAddActivated) {
			$toManyOption->setEmbeddedAddablesNum(self::DEFAULT_ADDABLES_NUM);
		}
		
		$targetEntities = $this->lookupSelectableEntites($scriptState->getEntityManager());
		
		$toManyOption->setSelectableEntities($this->filterAccessableTargetEntities($targetEntities, 
				$scriptSelectionMapping->getSelectionPrivilegeConstraint()));
		
		$toManyOption->setTargetScriptSelections($this->createTargetScriptSelections($scriptSelectionMapping));
	
		return $toManyOption;
	}
	
	public function createFilterItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		$tx = N2N::createTransaction(true);
		$em = null;
		if ($scriptState !== null) {
			$em = $scriptState->getEntityManager();
		} else {
			$em = $this->entityScript->lookupEntityManager($n2nContext->getDbhPool());
		}
	
		$targetEntities = $this->lookupSelectableEntites($em);
		$options = $this->buildEntityOptions($targetEntities, $n2nContext->getLocale());
		
		$tx->commit();
		
		$dtc = new DynamicTextCollection('rocket', $n2nContext->getLocale());
		$idPropertyName = $this->fieldRelation->getTargetEntityScript()->getEntityModel()->getIdProperty()->getName();
	
		return new ToManyFilterItem(array($this->getEntityProperty()->getName(), $idPropertyName), $this->getLabel(), 
				array('contains' => $dtc->translate('script_impl_contains_label')), $options);
	}
	
	public function createRestrictionSelectorItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		$tx = N2N::createTransaction(true);
		$em = null;
		if ($scriptState !== null) {
			$em = $scriptState->getEntityManager();
		} else {
			$em = $this->entityScript->lookupEntityManager($n2nContext->getDbhPool());
		}
		$tx->commit();
	
		$dtc = new DynamicTextCollection('rocket', $n2nContext->getLocale());
		$targetEntities = $this->lookupSelectableEntites($em);
		$options = $this->buildEntityOptions($targetEntities, $n2nContext->getLocale());
		$idPropertyName = $this->fieldRelation->getTargetEntityScript()->getEntityModel()->getIdProperty()->getName();
		
		return new ToManySelectorItem(array($this->getEntityProperty()->getName(), $idPropertyName), $this->getLabel(),
				array('contains' => $dtc->translate('script_impl_contains_label')),
				$options, $this->fieldRelation->getTargetEntityScript());
	}
	
	protected function filterAccessableTargetEntities(array $targetEntities, 
			SelectionPrivilegeConstraint $selectionPrivilegeContraint = null) {
		if (null === $selectionPrivilegeContraint) return $targetEntities;
		foreach ($targetEntities as $key => $targetEntity) {
			if (!$selectionPrivilegeContraint->acceptsValue($this->id, new \ArrayObject(array($targetEntity)))) {
				unset($targetEntities[$key]);
			}
		}
		return $targetEntities;
	}
}
