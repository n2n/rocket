<?php
namespace rocket\script\entity\manage\mapping;

use n2n\persistence\orm\Entity;

interface Readable {
	public function read(Entity $entity);
}