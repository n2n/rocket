<?php

namespace rocket\script\entity\field\impl\relation\model;

use rocket\script\entity\manage\CriteriaFactory;
use n2n\persistence\orm\Entity;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\property\RelationProperty;
use n2n\persistence\orm\criteria\CriteriaProperty;
	  
class RelationCriteriaFactory implements CriteriaFactory {
	const MOTS_ALIAS_SUFFIX = 'otms';

	private $relationProperty;
	private $entity;

	public function __construct(RelationProperty $relationProperty, Entity $entity) {
		$this->relationProperty = $relationProperty;
		$this->entity = $entity;
	}

	public function create(EntityManager $em, $entityAlias) {
		$mtmsAlias = $entityAlias . self::MOTS_ALIAS_SUFFIX;
		$criteria = $em->createCriteria($this->relationProperty->getEntityModel()->getClass(), $mtmsAlias);
		$criteria->joinProperty(new CriteriaProperty(array($mtmsAlias, $this->relationProperty->getName())), $entityAlias);
		$criteria->where(array($mtmsAlias => $this->entity));
		$criteria->setBaseEntityAlias($entityAlias);
		return $criteria;
	}
}