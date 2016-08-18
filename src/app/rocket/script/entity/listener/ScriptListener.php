<?php
namespace rocket\script\entity\listener;

use rocket\script\entity\ScriptElement;

interface ScriptListener extends ScriptElement {
	public function onEntityChanged(EntityChangeEvent $event);
}