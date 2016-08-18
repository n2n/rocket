<?php

namespace rocket\script\entity\field\impl\relation;

use rocket\script\entity\field\FilterableScriptField;
use n2n\core\N2nContext;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\filter\item\SimpleFilterItem;
use n2n\persistence\orm\criteria\CriteriaComparator;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use n2n\ui\html\HtmlView;
use rocket\script\entity\field\HighlightableScriptField;
use n2n\l10n\Locale;
use n2n\persistence\orm\Entity;
use rocket\script\entity\filter\item\SimpleSortItem;
use rocket\script\entity\field\SortableScriptField;
use rocket\script\entity\manage\ScriptSelection;
use n2n\N2N;
use rocket\script\entity\field\impl\ManageInfo;
use rocket\script\entity\field\impl\relation\model\ToOneFilterItem;
use rocket\user\model\RestrictionScriptField;
use rocket\script\entity\field\impl\relation\model\ToOneSelectorItem;
use rocket\script\entity\manage\security\SelectionPrivilegeConstraint;

abstract class SimpleToOneScriptFieldAdapter extends RelationScriptFieldAdapter implements FilterableScriptField, 
		RestrictionScriptField, HighlightableScriptField, SortableScriptField {

	protected function createTargetScriptSelection(ScriptSelectionMapping $scriptSelectionMapping) {
		$targetEntity = $scriptSelectionMapping->getValue($this->id);
		if ($targetEntity === null) return null;

		return new ScriptSelection($this->fieldRelation->getTargetEntityScript()->extractId($targetEntity), $targetEntity);
	}
	
	public function createUiOutputField(ScriptSelectionMapping $scriptSelectionMapping, HtmlView $view, ManageInfo $manageInfo) {
		$targetScriptSelection = $this->createTargetScriptSelection($scriptSelectionMapping);
		if ($targetScriptSelection === null) return null;
	
		$scriptState = $manageInfo->getScriptState();
		if (null !== ($relation = $scriptState->getScriptRelation($this->getId()))) {
			return $this->createUiOutput($relation->getScriptState(), $relation->getScriptSelection(), $view);
		}
	
		$targetScriptState = $this->fieldRelation->createTargetPseudoScriptState(
				$scriptState, $scriptSelectionMapping->getScriptSelection(), false);
	
		return $this->createUiOutput($targetScriptState, $targetScriptSelection, $view);
		
// 		return $html->getLink($targetScriptState->getDetailPath($view->getRequest(),
// 						new ScriptNavPoint($targetScriptSelection->getId())),
// 				$targetScriptState->createKnownString($targetScriptSelection->getEntity()));
	}
	
	protected function createUiOutput(ScriptState $targetScriptState, ScriptSelection $targetScriptSelection, HtmlView $view) {
		$html = $view->getHtmlBuilder();
		if ($targetScriptSelection->isNew()) return null;
		$knownString = $targetScriptState->createKnownString($targetScriptSelection->getEntity());
		if (!$targetScriptState->isDetailPathAvailable()) {
			return $html->getEsc($knownString);
		} else {
			return $html->getLink($targetScriptState->getDetailPath($view->getRequest(), 
					$targetScriptSelection->toNavPoint()), $knownString);
		}
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
	
		return new ToOneFilterItem($this->getEntityProperty()->getName(), $this->getLabel(),
				SimpleFilterItem::createOperatorOptions($n2nContext->getLocale(),
						array(CriteriaComparator::OPERATOR_EQUAL, CriteriaComparator::OPERATOR_NOT_EQUAL)),
				$options, $targetEntities);
	}

	public function createKnownString(Entity $entity, Locale $locale) {
		$targetEntity = $this->getPropertyAccessProxy()->getValue($entity);
		
		if ($targetEntity !== null) {
			return $this->fieldRelation->getTargetMask()->createKnownString($targetEntity, $locale);
		}
		
		return null;
	}
	
	public function createSortItem(N2nContext $n2nContext, ScriptState $scriptState = null) {
		return new SimpleSortItem($this->getEntityProperty()->getName(), $this->getLabel());
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

		$targetEntities = $this->lookupSelectableEntites($em);
		$options = $this->buildEntityOptions($targetEntities, $n2nContext->getLocale());
		
		return new ToOneSelectorItem($this->getEntityProperty()->getName(), $this->getLabel(),
				SimpleFilterItem::createOperatorOptions($n2nContext->getLocale(),
						array(CriteriaComparator::OPERATOR_EQUAL, CriteriaComparator::OPERATOR_NOT_EQUAL)),
				$options, $targetEntities, $this->fieldRelation->getTargetEntityScript());
	}
	
	protected function filterAccessableTargetEntities(array $targetEntities, SelectionPrivilegeConstraint $selectionPrivilegeContraint = null) {
		if (null === $selectionPrivilegeContraint) return $targetEntities;
		foreach ($targetEntities as $key => $targetEntity) {
			if (!$selectionPrivilegeContraint->acceptsValue($this->id, $targetEntity)) {
				unset($targetEntities[$key]);
			}
		}
		return $targetEntities;
	}
	
// 	protected function lookupFreeSelectableEntites(EntityManager $em, Entity $current = null) {
// 		$targetEntityScript = $this->fieldRelation->getTargetEntityScript();
	
// 		$criteria = $em->createCriteria($targetEntityScript->getEntityModel()->getClass(), 'e');
		
// // 		if (!$this->fieldRelation->findTargetScriptField()) {
// // 			$criteria->where()->andGroup()->match(array($this->fieldRelation->getTargetEntityProperty()->getName() => null));
// // 		}
		
		
		
// 		// 		$targetEntityScript = $this->getTargetEntityScript();
// 		// 		foreach ($targetEntityScript->getDefaultSortConstraints() as $sortConstraint) {
// 		// 			$sortConstraint->applyToCriteria($criteria, new CriteriaProperty(array(EntityManager::SIMPLE_CRITERIA_ENTITY_ALIAS)));
// 		// 		}
	
// 		$selectableEntities = array();
// 		foreach ($criteria->fetchArray() as $entity) {
// 			$selectableEntities[$targetEntityScript->extractId($entity)] = $entity;
// 		}
	
// 		return $selectableEntities;
// 	}
}