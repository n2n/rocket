<?php

namespace rocket\impl\ei\component\mod\callback;

use rocket\impl\ei\component\mod\adapter\EiModNatureAdapter;
use rocket\ei\util\Eiu;
use rocket\attribute\impl\EiSetup;
use rocket\attribute\impl\EiEntrySetup;

class StaticCallbackEiModNature extends EiModNatureAdapter {
	private CallbackFinder $callbackFinder;

	function __construct(private \ReflectionClass $class) {
		$this->callbackFinder = new CallbackFinder($this->class, true);
	}

	function init(Eiu $eiu): void {
		foreach ($this->callbackFinder->find(EiSetup::class, $eiu) as $invoker) {
			$invoker->invoke();
		}
	}

	function setupEiEntry(Eiu $eiu): void {
		foreach ($this->callbackFinder->find(EiEntrySetup::class, $eiu) as $invoker) {
			$invoker->invoke();
		}
	}
}