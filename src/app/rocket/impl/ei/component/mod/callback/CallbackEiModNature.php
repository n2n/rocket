<?php

namespace rocket\impl\ei\component\mod\callback;

use rocket\impl\ei\component\mod\adapter\EiModNatureAdapter;
use rocket\ei\util\Eiu;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\reflection\attribute\ClassAttribute;
use rocket\attribute\impl\EiMods;
use n2n\util\magic\MagicObjectUnavailableException;
use n2n\util\ex\err\ConfigurationError;
use rocket\ei\component\modificator\EiMod;
use rocket\attribute\impl\EiSetup;
use rocket\attribute\impl\EiEntrySetup;

class CallbackEiModNature extends EiModNatureAdapter {
	private CallbackFinder $finder;

	function __construct(private readonly object $obj) {
		$this->finder = new CallbackFinder(new \ReflectionClass($obj), false);
	}

	function setup(Eiu $eiu): void {
		$this->trigger(EiSetup::class, $eiu);
	}

	function setupEiEntry(Eiu $eiu) {
		$this->trigger(EiEntrySetup::class, $eiu);
	}

	private function trigger(string $attributeName, Eiu $eiu) {
		foreach ($this->finder->find($attributeName, $eiu) as $invoker) {
			$invoker->invoke($this->obj);
		}
	}
}