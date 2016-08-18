<?php
namespace rocket\script\entity\modificator\impl\numeric;

use rocket\script\entity\modificator\impl\ScriptModificatorAdapter;
use rocket\script\entity\manage\ScriptState;
use rocket\script\entity\manage\mapping\ScriptSelectionMapping;
use rocket\script\entity\manage\mapping\OnWriteMappingListener;
use n2n\persistence\orm\criteria\CriteriaProperty;
use rocket\script\entity\field\impl\numeric\OrderScriptField;
use n2n\N2N;
class OrderScriptModificator extends ScriptModificatorAdapter {
	
	private $scriptField;
	
	public function __construct(OrderScriptField $scriptField) {
		$this->scriptField = $scriptField;
	}
	
	public function setupScriptSelectionMapping(ScriptState $scriptState, ScriptSelectionMapping $ssm) {
		$scriptField = $this->scriptField;
		$ssm->registerListener(new OnWriteMappingListener(function() use ($scriptState, $ssm, $scriptField) {
			$optionValue = $ssm->getValue($scriptField->getId());
			
			if (!mb_strlen($optionValue)) {
				$em = $scriptState->getEntityManager();
				$entityModel = $this->getEntityScript()->getEntityModel();
				$criteria = $scriptState->createCriteria($em, 'o', false);
				$criteria->order(new CriteriaProperty(array('o',
						$scriptField->getEntityProperty()->getName())), 'DESC');
				$criteria->select(new CriteriaProperty(array('o',
						$scriptField->getEntityProperty()->getName())));
				$criteria->limit(1);
				$scriptSelection = $ssm->getScriptSelection();
				if (!$scriptSelection->isNew()) {
					$criteria->where()->andMatch(new CriteriaProperty(array('o', $entityModel->getIdProperty()->getName())),
							'!=', $scriptSelection->getId());
				}
				$ssm->setValue($scriptField->getId(), (int) $criteria->fetchSingle() + OrderScriptField::ORDER_INCREMENT);
			}
		}));
	}
}