<?php
namespace rocket\script\entity\field\impl\relation;

use rocket\script\entity\field\impl\relation\model\ScriptFieldRelation;
use rocket\script\entity\field\impl\EditableScriptFieldAdapter;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\core\SetupProcess;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\Entity;
use rocket\script\entity\field\impl\ManageInfo;
use n2n\ui\html\HtmlUtils;
use n2n\l10n\Locale;
use n2n\persistence\orm\criteria\CriteriaProperty;
use rocket\script\entity\field\EntityPropertyScriptField;

abstract class RelationScriptFieldAdapter extends EditableScriptFieldAdapter implements RelationScriptField {
	/**
	 * @var ScriptFieldRelation
	 */
	protected $fieldRelation;
	
	protected function initilaize(ScriptFieldRelation $fieldRelation) {
		$this->fieldRelation = $fieldRelation;
	}
	
	public function getFieldRelation() {
		return $this->fieldRelation;
	}
	
	public function createOptionCollection() {
		return $this->fieldRelation->completeOptionCollection(parent::createOptionCollection());
	}
	
	public function setup(SetupProcess $setupProcess) {
		parent::setup($setupProcess);
		$this->fieldRelation->setup($setupProcess);
	}

	public function isReadOnly(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		return parent::isReadOnly($scriptSelectionMapping, $manageInfo)
				|| $this->fieldRelation->isReadOnlyRequired($scriptSelectionMapping, $manageInfo->getScriptState());
	}
	
	public function write(Entity $entity, $value) {
		$this->fieldRelation->write($entity, $value);
	}
	
	protected function lookupSelectableEntites(EntityManager $em) {
		$targetEntityScript = $this->fieldRelation->getTargetEntityScript();
	
		$criteria = $em->createSimpleCriteria($targetEntityScript->getEntityModel()->getClass());
	
		foreach ($this->fieldRelation->getTargetMask()->getDefaultSortDirections() as $fieldId => $direction) {
			$scriptField = $targetEntityScript->getFieldCollection()->getById($fieldId);
			if ($scriptField instanceof EntityPropertyScriptField) {
				$criteria->order(EntityManager::SIMPLE_ALIAS . '.' . $scriptField->getEntityProperty()->getName(), $direction);
			}
		}
	
		$selectableEntities = array();
		
		foreach ($criteria->fetchArray() as $targetEntity) {
			$selectableEntities[$targetEntityScript->extractId($targetEntity)] = $targetEntity;
		}	
		return $selectableEntities;
	}

	
	protected function buildEntityOptions(array $targetEntities, Locale $locale) {
		$targetMask = $this->fieldRelation->getTargetMask();
		$options = array();
		foreach ($targetEntities as $id => $targetEntity) {
			$options[$id] = $targetMask->createKnownString($targetEntity, $locale);
		}
		return $options;
	}
	
	public function getHtmlContainerAttrs(ScriptSelectionMapping $scriptSelectionMapping, ManageInfo $manageInfo) {
		$attrs = array('data-target-label' => $this->fieldRelation->getTargetMask()->getLabel());
		if (!$manageInfo->hasListModel()) {
			$attrs['class'] = 'rocket-control-group';
		}
		return HtmlUtils::mergeAttrs($attrs, parent::getHtmlContainerAttrs($scriptSelectionMapping, $manageInfo));
	}
}