<?php
namespace rocket\script\entity\manage;

use n2n\persistence\orm\EntityManager;

interface CriteriaFactory {
	public function create(EntityManager $em, $entityAlias);
}