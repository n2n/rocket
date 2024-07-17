<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */

namespace rocket\impl\ei\component\mod\callback;

use n2n\reflection\ReflectionContext;
use n2n\reflection\attribute\AttributeSet;
use n2n\util\ex\err\ConfigurationError;
use n2n\reflection\magic\MagicMethodInvoker;
use rocket\op\ei\util\Eiu;

class CallbackFinder {
	private AttributeSet $attributeSet;

	function __construct(private readonly \ReflectionClass $class, private readonly bool $static) {
		$this->attributeSet = $attributeSet = ReflectionContext::getAttributeSet($this->class);
	}


	/**
	 * @param string $attributeName
	 * @param Eiu $eiu
	 * @return MagicMethodInvoker[]
	 */
	function find(string $attributeName, Eiu $eiu) {
		$invokers = [];

		foreach ($this->attributeSet->getMethodAttributesByName($attributeName) as $methodAttribute) {
			$method = $methodAttribute->getMethod();

			if ($this->static && !$method->isStatic()) {
				throw new ConfigurationError('Methods annotated with ' . $attributeName . ' inside of '
								. $this->class->getName() . ' must be static.',
						$methodAttribute->getFile(), $methodAttribute->getLine());
			}

			$invoker = new MagicMethodInvoker($eiu->getN2nContext());
			$invoker->setMethod($method);
			$invoker->setClassParamObject(Eiu::class, $eiu);

			$invokers[] = $invoker;
		}

		return $invokers;
	}

}