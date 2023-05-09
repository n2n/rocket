<?php

namespace rocket\impl\ei\component\mod\callback;

use n2n\reflection\ReflectionContext;
use n2n\reflection\attribute\AttributeSet;
use n2n\util\ex\err\ConfigurationError;
use n2n\reflection\magic\MagicMethodInvoker;
use rocket\ei\util\Eiu;

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