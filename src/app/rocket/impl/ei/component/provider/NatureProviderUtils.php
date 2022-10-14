<?php

namespace rocket\impl\ei\component\provider;

use rocket\spec\setup\EiPresetProp;
use n2n\util\type\TypeConstraints;

class NatureProviderUtils {

	static function compileTypeNames(EiPresetProp $eiPresetProp, bool &$nullAllowed) {
		$accessProxy = $eiPresetProp->getPropertyAccessProxy();

		$namedTypeConstraints = [];
		if ($accessProxy->isWritable()) {
			array_push($namedTypeConstraints,
					...$accessProxy->getSetterConstraint()->getNamedTypeConstraints());
		}
		if (!$eiPresetProp->isEditable()) {
			array_push($namedTypeConstraints,
					...$accessProxy->getGetterConstraint()->getNamedTypeConstraints());
		}

		if (null !== ($type = $accessProxy->getProperty()?->getType())) {
			array_push($namedTypeConstraints,
					...TypeConstraints::type($type)->getNamedTypeConstraints());
		}

		$typeNames = [];
		$nullAllowed = false;
		foreach ($namedTypeConstraints as $namedTypeConstraint) {
			if ($namedTypeConstraint->allowsNull()) {
				$nullAllowed = true;
			}

			if ($namedTypeConstraint->isMixed()) {
				continue;
			}

			$typeName = $namedTypeConstraint->getTypeName();
			$typeNames[$typeName] = $typeName;
		}


		return $typeNames;
	}
}