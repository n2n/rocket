<?php
namespace rocket\script\entity\command\impl\numeric\controller;

use n2n\persistence\orm\criteria\CriteriaProperty;
use n2n\persistence\orm\criteria\CriteriaItem;
use n2n\persistence\orm\criteria\Criteria;
use rocket\script\entity\field\impl\numeric\OrderScriptField;
use rocket\script\core\ManageState;
use n2n\http\PageNotFoundException;
use n2n\http\ControllerAdapter;
use n2n\persistence\orm\criteria\CriteriaComparator;
use rocket\script\entity\manage\EntryManageUtils;

class OrderController extends ControllerAdapter {
	const DIRECTION_UP = 'up';
	const DIRECTION_DOWN = 'down';
	
	private $orderScriptField;
	private $utils;
	/**
	 * @var \rocket\script\entity\EntityScript
	 */
	private $entityScript;
	
	private function _init(ManageState $manageState) {
		$this->utils = new EntryManageUtils($manageState->peakScriptState());
	}
	
	public function setOrderScriptField(OrderScriptField $orderScriptField) {
		$this->orderScriptField = $orderScriptField;
		$this->entityScript = $orderScriptField->getEntityScript();
	}
	
	public function doUp($id) {
		$this->move($id, self::DIRECTION_UP);
	}
	
	public function doDown($id) {
		$this->move($id);
	}
	
	private function move($id, $direction = self::DIRECTION_DOWN) {		
		$em = $this->utils->getScriptState()->getEntityManager();
		$scriptClass = $this->entityScript->getEntityModel()->getClass();
		$object = $em->find($scriptClass, $id);
		if (!isset($object)) {
			throw new PageNotFoundException();
		}
		
		$orderDirection = Criteria::ORDER_DIRECTION_ASC;
		$operator = CriteriaComparator::OPERATOR_LARGER_THAN;
		if ($direction == self::DIRECTION_UP) { 
			$orderDirection = Criteria::ORDER_DIRECTION_DESC;
			$operator = CriteriaComparator::OPERATOR_SMALLER_THAN;	
		}
		
		$scriptState = $this->utils->getScriptState();
		$accessProxy = $this->orderScriptField->getPropertyAccessProxy();
		$orderIndex = $accessProxy->getValue($object);
		
		$criteria = $scriptState->createCriteria($em, 'a', false);
		$criteria->order(new CriteriaProperty(array('a', $accessProxy->getPropertyName())), $orderDirection);
		$criteria->limit(1);
		$criteria->where()->match(CriteriaItem::createFromExpression($accessProxy->getPropertyName(), 
				'a'), $operator, $orderIndex);
		
		if (null !== ($referenceField = $this->orderScriptField->getReferenceField())) {
			$referenceAccessProxy = $referenceField->getEntityProperty()->getAccessProxy();
			$criteria->where()->andMatch(CriteriaItem::createFromExpression($referenceAccessProxy->getPropertyName(), 
					'a'), CriteriaComparator::OPERATOR_EQUAL, $referenceAccessProxy->getValue($object));
		}
		
		$previousObject = $criteria->fetchSingle();
		
		if (null !== $previousObject) {
			$previousValue = $accessProxy->getValue($previousObject);
			$accessProxy->setValue($previousObject, $orderIndex);
			$accessProxy->setValue($object, $previousValue);
				
			$em->merge($previousObject);
			$em->merge($object);
		}
				
		$this->redirect($this->utils->getScriptState()->getOverviewPath($this->getRequest()));
		return;
	}
}
