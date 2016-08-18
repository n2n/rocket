<?php

namespace rocket\script\entity\manage\mapping;

interface MappingValidator {
	public function validate(MappingValidationResult $mappingValidationResult, ScriptSelectionMapping $scriptSelectionMapping);
}