<?php

namespace rocket\script\entity\field\impl\relation\model;

use rocket\script\entity\manage\CriteriaFactory;
use n2n\persistence\orm\Entity;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\property\relation\MappedRelation;
use n2n\persistence\orm\criteria\CriteriaProperty;

class MappedOneToCriteriaFactory implements CriteriaFactory {
	private $mappedRelation;
	private $entity;

	public function __construct(MappedRelation $mappedRelation, Entity $entity) {
		$this->mappedRelation = $mappedRelation;
		$this->entity = $entity;
	}

	public function create(EntityManager $em, $entityAlias) {
		$criteria = $em->createCriteria($this->mappedRelation->getTargetEntityClass(), $entityAlias);
		$criteria->where()->match(
				new CriteriaProperty(array($entityAlias, $this->mappedRelation->getTargetEntityProperty()->getName())),
				'=', $this->entity);
		return $criteria;

	}
}