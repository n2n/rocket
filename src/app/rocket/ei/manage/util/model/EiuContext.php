<?php
namespace rocket\ei\manage\util\model;

use rocket\spec\Spec;
use n2n\core\container\N2nContext;
use rocket\spec\UnknownSpecException;
use n2n\reflection\ArgUtils;
use rocket\ei\EiType;
use rocket\ei\component\EiComponent;

class EiuContext {
	private $spec;
	private $n2nContext;
	
	/**
	 * @param Spec $spec
	 * @param N2nContext $n2nContext
	 */
	function __construct(Spec $spec, N2nContext $n2nContext) {
		$this->spec = $spec;
		$this->n2nContext = $n2nContext;
	}
	
	/**
	 * @param string|\ReflectionClass|EiType|EiComponent $eiTypeArg id, entity class name of the affiliated EiType or the EiType itself.
	 * @param bool $required
	 * @return EiuEngine
	 * @throws UnknownSpecException required is false and the EiEngine was not be found.
	 */
	function engine($eiTypeArg, bool $required = true) {
		ArgUtils::valType($eiTypeArg, ['string', \ReflectionClass::class, EiType::class, EiComponent::class]);
		
		if ($eiTypeArg instanceof EiType) {
			return new EiuEngine($eiTypeArg->getEiEngine(), $this->n2nContext);
		}
		
		if ($eiTypeArg instanceof EiComponent) {
			return new EiuEngine($eiTypeArg->getEiEngine(), $this->n2nContext);
		}
		
		$eiEngine = null;
		try {
			if ($eiTypeArg instanceof \ReflectionClass) {
				return new EiuEngine($this->spec->getEiTypeByClass($eiTypeArg)->getEiMask()->getEiEngine(),
						$this->n2nContext);
			}
			
			if (class_exists($eiTypeArg, false)) {
				return new EiuEngine($this->spec->getEiTypeByClassName($eiTypeArg)->getEiEngine(), 
						$this->n2nContext);
			}
			
			return $this->spec->getEiTypeById($eiTypeArg)->getEiEngine();	
		} catch (UnknownSpecException $e) {
			if (!$required) return null;
			
			throw $e;
		}
		
		return new EiuEngine($eiEngine, $this->n2nContext);
	}
}