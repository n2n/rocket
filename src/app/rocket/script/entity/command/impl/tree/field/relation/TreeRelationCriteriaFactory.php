<?php
namespace rocket\script\entity\command\impl\tree\field\relation;

use rocket\script\entity\manage\CriteriaFactory;
use n2n\persistence\orm\property\RelationProperty;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\criteria\CriteriaProperty;
use n2n\persistence\orm\criteria\CriteriaComparator;
use n2n\persistence\orm\criteria\CriteriaConstant;

class TreeRelationCriteriaFactory implements CriteriaFactory {
	const MOTS_ALIAS_SUFFIX = 'totms';

	private $relationProperty;
	private $targetRootId;
	private $targetLft;
	private $targetRgt;
	private $targetRootIdPropName;
	private $targetLftPropName;
	private $targetRgtPropName;

	public function __construct(RelationProperty $relationProperty,
			$targetRootId, $targetLft, $targetRgt, $targetRootIdPropName, $targetLftPropName, $targetRgtPropName) {
		$this->relationProperty = $relationProperty;
		$this->targetRootId = $targetRootId;
		$this->targetLft = $targetLft;
		$this->targetRgt = $targetRgt;
		$this->targetRootIdPropName = $targetRootIdPropName;
		$this->targetLftPropName = $targetLftPropName;
		$this->targetRgtPropName = $targetRgtPropName;
	}

	public function create(EntityManager $em, $entityAlias) {
		$criteria = $em->createCriteria($this->relationProperty->getTargetEntityClass(), $entityAlias);
		$criteria->where()
				->match(new CriteriaProperty(array($entityAlias, $this->targetRootIdPropName)),
						CriteriaComparator::OPERATOR_EQUAL, new CriteriaConstant($this->targetRootId))
				->andMatch(new CriteriaProperty(array($entityAlias, $this->targetLftPropName)),
						CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO, new CriteriaConstant($this->targetLft))
				->andMatch(new CriteriaProperty(array($entityAlias, $this->targetRgtPropName)),
						CriteriaComparator::OPERATOR_SMALLER_THAN_OR_EQUAL_TO, new CriteriaConstant($this->targetRgt));
						$criteria->setBaseEntityAlias($entityAlias);
		return $criteria;
	}
}