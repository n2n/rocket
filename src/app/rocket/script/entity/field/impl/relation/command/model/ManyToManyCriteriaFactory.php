<?php

namespace rocket\script\entity\field\impl\relation\command\model;

use n2n\persistence\orm\property\ManyToManyProperty;
use rocket\script\entity\manage\CriteriaFactory;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\criteria\CriteriaProperty;
use n2n\persistence\orm\Entity;

class ManyToManyCriteriaFactory implements CriteriaFactory {
	const MTMS_ALIAS_SUFFIX = 'mtms';
	
	private $manyToManyProperty;
	private $object;
	
	public function __construct(ManyToManyProperty $manyToManyProperty, Entity $object) {
		$this->manyToManyProperty = $manyToManyProperty;
		$this->object = $object;
	}
	
	public function create(EntityManager $em, $entityAlias) {
		$relation = $this->manyToManyProperty->getRelation();
		$mtmsAlias = $entityAlias . self::MTMS_ALIAS_SUFFIX;
		$criteria = $em->createCriteria($relation->getEntityModel()->getClass(), $mtmsAlias);
		$criteria->joinProperty(new CriteriaProperty(array($mtmsAlias, $this->manyToManyProperty->getName())), $entityAlias);
		$criteria->where(array($entityAlias . self::MTMS_ALIAS_SUFFIX => $this->object));
		$criteria->setBaseEntityAlias($entityAlias);
		return $criteria;
	}
}