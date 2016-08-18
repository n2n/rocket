<?php
namespace rocket\script\entity\field\impl\relation\command\model;

use n2n\persistence\orm\OrmUtils;
use n2n\persistence\orm\property\relation\MappedRelation;
use n2n\persistence\orm\property\relation\Relation;
use n2n\dispatch\DispatchAnnotations;
use n2n\reflection\annotation\AnnotationSet;
use n2n\persistence\orm\EntityManager;
use rocket\script\entity\field\impl\relation\ManyToManyScriptField;
use n2n\dispatch\Dispatchable;
use rocket\script\entity\manage\ScriptState;

class ManyToManyAssignatorForm implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->annotateMethod('save', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $manyToManyScriptField;
	private $scriptState;
	
	protected $assignedIds = array();
	
	private $prevAssignedObjects = array();
	private $assignedIdOptions = array();
	private $availableObjects = array();
	
	public function __construct(ManyToManyScriptField $manyToManyScriptField, EntityManager $em, ScriptState $scriptState) {
		$this->manyToManyScriptField = $manyToManyScriptField;
		$this->em = $em;
		$this->scriptState = $scriptState;
		
		$assignedObjects = $manyToManyScriptField->getPropertyAccessProxy()->getValue(
				$scriptState->getScriptSelection()->getOriginalEntity());
		$targetEntityScript = $manyToManyScriptField->getTargetEntityScript();
		
		foreach ($assignedObjects as $assignedObject) {
			$id = $targetEntityScript->extractId($assignedObject);
			$this->prevAssignedObjects[$id] = $assignedObject;
			$this->assignedIds[] = $id;
		}
		
		$availableObjects = $em->createSimpleCriteria($targetEntityScript->getEntityModel()->getClass())->fetchArray();
		foreach ($availableObjects as $availableObject) {
			$id = $targetEntityScript->extractId($availableObject);
			$this->availableObjects[$id] = $availableObject;
			$this->assignedIdOptions[$id] = 
					$targetEntityScript->createKnownString($availableObject, $scriptState->getLocale());
		}
	}
	
	public function getManyToManyScriptField() {
		return $this->manyToManyScriptField;	
	}
	
	public function getAssignedIds() {
		return $this->assignedIds;
	}
	
	public function setAssignedIds(array $assignedIds) {
		$this->assignedIds = $assignedIds;
	}
	
	public function getAssignedIdOptions() {
		return $this->assignedIdOptions;
	}

	private function _validation() {
	}
	
	public function save() {
		$relation = $this->manyToManyScriptField->getEntityProperty()->getRelation();	
		
		if ($relation instanceof MappedRelation) {
			$this->mappedSave($relation);
		} else {
			$this->masterSave($relation);
		}
	}
	
	private function masterSave(Relation $relation) {
		$assigedObjects = new \ArrayObject();
		
		foreach ($this->assignedIds as $assignedId) {
			if (!isset($this->availableObjects[$assignedId])) continue;
				
			$assigedObjects[] = $this->availableObjects[$assignedId];
		}
		
		$object = $this->scriptState->getScriptSelection()->getOriginalEntity();
		$this->manyToManyScriptField->getPropertyAccessProxy()->setValue($object, $assigedObjects);

		$this->em->merge($object);
	}
	
	private function mappedSave(MappedRelation $mappedRelation) {
		$scriptSelection = $this->scriptState->getScriptSelection();
		$object = $scriptSelection->getOriginalEntity();
		$id = $scriptSelection->getId();
		
		$targetPropertyName = $mappedRelation->getTargetEntityProperty()->getName();
		$targetEntityScript = $this->manyToManyScriptField->getTargetEntityScript();
		$targetScriptField = $targetEntityScript->getScriptFieldByPropertyName($targetPropertyName);
		
		foreach ($this->assignedIds as $assignedId) {
			if (!isset($this->availableObjects[$assignedId])) continue;
				
			$assignedObject = $this->availableObjects[$assignedId];
			unset($this->prevAssignedObjects[$assignedId]);
			
			$inverseAssignedObjects = $targetScriptField->getPropertyAccessProxy()->getValue($assignedObject);
			
			$alreadyAssigned = false;
			foreach ($inverseAssignedObjects as $inverseAssignedObject) {
				if (!OrmUtils::areObjectsEqual($object, $inverseAssignedObject)) continue;
				
				$alreadyAssigned = true; 
				break;
			}
			
			if (!$alreadyAssigned) {
				$inverseAssignedObjects[] = $object;
				$this->em->merge($assignedObject);
			}
		}
		
		while (null !== ($prevAssignedObject = array_pop($this->prevAssignedObjects))) {
			$inverseAssignedObjects = $targetScriptField->getPropertyAccessProxy()->getValue($prevAssignedObject);
			
			foreach ($inverseAssignedObjects as $key => $inverseAssignedObject) {
				if (!OrmUtils::areObjectsEqual($object, $inverseAssignedObject)) continue;
				
				unset($inverseAssignedObjects[$key]);
				$this->em->merge($prevAssignedObject);
			}
		}
	}
}
