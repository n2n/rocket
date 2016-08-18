<?php
namespace rocket\script\entity\command\impl\tree\model;

use n2n\persistence\orm\NestedSetUtils;
use rocket\script\entity\command\impl\common\model\CommandEntryModelAdapter;
use rocket\script\entity\manage\ScriptSelection;


class TreeDeleteModel extends CommandEntryModelAdapter {	
	private $rootIdPropertyName;
	private $leftPropertyName;
	private $rightPropertyName;
	
	public function __construct($rootIdPropertyName, $leftPropertyName, $rightPropertyName) {
		$this->rootIdPropertyName = $rootIdPropertyName;
		$this->leftPropertyName = $leftPropertyName;
		$this->rightPropertyName = $rightPropertyName;
	}	
	
	public function delete() {
		if ($this->scriptSelection->hasDraft()) {
			$this->draftModel->removeDraft($this->scriptSelection->getDraft());
			return;
		}
		
		$class = $this->entityScript->getEntityModel()->getTopEntityModel()->getClass();
		$entity = $this->scriptSelection->getOriginalEntity();
	
		$nestedSetUtils = new NestedSetUtils($this->em, $class);
		$nestedSetUtils->setRootIdPropertyName($this->rootIdPropertyName);
		$nestedSetUtils->setLeftPropertyName($this->leftPropertyName);
		$nestedSetUtils->setRightPropertyName($this->rightPropertyName);
		
		$nestedSetItemsToDelete = $nestedSetUtils->fetchNestedSetItems($entity);
		
		foreach ($nestedSetItemsToDelete as $nesteSetItem) {
			$entity = $nesteSetItem->getObject();
			$this->scriptState->triggerOnRemoveObject($this->em, 
					new ScriptSelection($this->entityScript->extractId($entity), $entity));
			$nestedSetUtils->remove($nesteSetItem->getObject());
		}
	}	
}