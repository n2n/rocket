<?php

namespace rocket\script\entity\field;

interface ReadableScriptField extends MappableScriptField {
	public function getReadable();
}