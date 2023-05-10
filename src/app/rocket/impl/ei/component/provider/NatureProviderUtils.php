<?php

namespace rocket\impl\ei\component\provider;

use rocket\op\spec\setup\EiPresetProp;
use n2n\util\type\TypeConstraints;
use n2n\util\type\NamedTypeConstraint;

class NatureProviderUtils {

	static function compileTypeNames(EiPresetProp $eiPresetProp, bool &$nullAllowed) {
		$accessProxy = $eiPresetProp->getPropertyAccessProxy();

		$nullCheckConstraints = [];
		if ($accessProxy->isWritable()) {
			$nullCheckConstraints = $accessProxy->getSetterConstraint()->getNamedTypeConstraints();
		} else {
			$nullCheckConstraints = $accessProxy->getGetterConstraint()->getNamedTypeConstraints();
		}
		$nullAllowed = !empty(array_filter($nullCheckConstraints, fn (NamedTypeConstraint $ntc) => $ntc->allowsNull()));

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
		foreach ($namedTypeConstraints as $namedTypeConstraint) {
			if ($namedTypeConstraint->isMixed()) {
				continue;
			}

			$typeName = $namedTypeConstraint->getTypeName();
			$typeNames[$typeName] = $typeName;
		}

		return $typeNames;
	}
}