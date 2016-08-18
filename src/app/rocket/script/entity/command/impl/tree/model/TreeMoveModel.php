<?php
namespace rocket\script\entity\command\impl\tree\model;

use n2n\persistence\orm\NestedSetUtils;
use n2n\dispatch\Dispatchable;
use n2n\dispatch\map\BindingConstraints;
use n2n\dispatch\val\ValEnum;
use n2n\reflection\annotation\AnnotationSet;
use n2n\dispatch\DispatchAnnotations;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\ScriptSelection;

class TreeMoveModel implements Dispatchable {
	private static function _annotations(AnnotationSet $as) {
		$as->m('move', DispatchAnnotations::MANAGED_METHOD);
	}
	
	private $entityScript;
	private $scriptState;
	private $scriptSelection;
	private $nestedSetUtils;
	
	private $nestedSetItems;
	public $parentId;
	private $parentIdOptions;
	
	public function __construct(ScriptState $scriptState) {
		$this->entityScript = $scriptState->getContextEntityScript();
		$this->scriptState = $scriptState;
	}
	
	public function initialize($id) {
		$em = $this->scriptState->getEntityManager();
		$class = $this->entityScript->getEntityModel()->getClass();
		
		$object = $em->find($class, $id);
		if (!isset($object)) {
			return false;
		}
		
		$this->nestedSetUtils = $nestedSetUtils = new NestedSetUtils($em, $class);
		$this->scriptSelection = new ScriptSelection($id, $object);
		$this->scriptState->setScriptSelection($this->scriptSelection);
		
		$this->nestedSetItems = array();
		$this->parentIdOptions = array(null => 'Root');
		$currentLevelObjectIds = array();
		$disabledLevel = null;
		foreach ($nestedSetUtils->fetchNestedSetItems() as $nestedSetItem) {
			$objectId = $this->entityScript->extractId($nestedSetItem->getObject());
			$level = $nestedSetItem->getLevel();
			
			if (isset($disabledLevel)) {
				if ($level > $disabledLevel) {
					continue;
				}
				$disabledLevel = null;
			}
			
			if ($id == $objectId) {
				$disabledLevel = $level;
				
				if (isset($currentLevelObjectIds[$level - 1])) {
					$this->parentId = $currentLevelObjectIds[$level - 1];
				}
				
				continue;
			}
			
			$currentLevelObjectIds[$level] = $objectId;
			$this->nestedSetItems[$objectId] = $nestedSetItem;
			$this->parentIdOptions[$objectId] = str_repeat('..', $level + 1) . 
					$this->entityScript->createKnownString($nestedSetItem->getObject(), $this->scriptState->getLocale());
		}
				
		return true;		
	}
			
	public function getEntityScript() {
		return $this->entityScript;
	}
	
	public function getScriptState() {
		return $this->scriptState;
	}
	
	public function getParentIdOptions() {
		return $this->parentIdOptions;
	}
	
	public function getTitle() {
		return $this->entityScript->createKnownString($this->scriptSelection->getOriginalEntity(), 
				$this->scriptState->getLocale());
	}
	
	private function _validation(BindingConstraints $bc) {
		$keys = array_keys($this->parentIdOptions);
		$keys[0] = null;
		$bc->val('parentId', new ValEnum($keys));
	}
	
	public function move() {
		$parentObject = null;
		if (isset($this->nestedSetItems[$this->parentId])) {
			$parentObject = $this->nestedSetItems[$this->parentId]->getObject();
		}
		$this->nestedSetUtils->move($this->scriptSelection->getOriginalEntity(), $parentObject);
	}
}