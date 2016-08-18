<?php
namespace rocket\script\entity\manage\mapping;

use n2n\persistence\orm\Entity;

interface Writable {
	public function write(Entity $entity, $value);
}