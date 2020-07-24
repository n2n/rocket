<?php
namespace rocket\ei\util\factory;

use rocket\ei\manage\idname\IdNameProp;
use rocket\ei\util\Eiu;
use n2n\l10n\N2nLocale;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;

class EifIdNameProp implements IdNameProp {
	private $function;
	
	function __construct(\Closure $callback) {
		$this->function = new \ReflectionFunction($callback);
	}
	
	function toIdNameProp() {
		
	}
	
	public function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {		
		$mmi = new MagicMethodInvoker($eiu->getN2nContext());
		$mmi->setClassParamObject(Eiu::class, $eiu);
		$mmi->setClassParamObject(N2nLocale::class, $n2nLocale);
		$mmi->setReturnTypeConstraint(TypeConstraints::scalar(true));
		
		return $mmi->invoke(null, $this->function);
	}	
}

class ClosureIdNameProp implements IdNameProp {
	private $function;
	
	function __construct(\Closure $callback) {
		$this->function = new \ReflectionFunction($callback);
	}
	
	function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		$mmi = new MagicMethodInvoker($eiu->getN2nContext());
		$mmi->setClassParamObject(Eiu::class, $eiu);
		$mmi->setClassParamObject(N2nLocale::class, $n2nLocale);
		$mmi->setReturnTypeConstraint(TypeConstraints::scalar(true));
		
		return $mmi->invoke(null, $this->function);
	}	
}