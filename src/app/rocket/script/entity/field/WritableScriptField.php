<?php

namespace rocket\script\entity\field;

interface WritableScriptField extends MappableScriptField {
	public function getWritable();
}