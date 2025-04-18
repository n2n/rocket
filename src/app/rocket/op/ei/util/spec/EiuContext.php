<?php
namespace rocket\op\ei\util\spec;

use rocket\op\spec\Spec;
use n2n\core\container\N2nContext;
use rocket\op\spec\UnknownTypeException;
use n2n\util\type\ArgUtils;
use rocket\op\ei\EiType;
use rocket\op\ei\component\EiComponentNature;
use rocket\op\spec\TypePath;
use rocket\op\ei\EiTypeExtension;
use rocket\op\ei\mask\EiMask;
use rocket\op\ei\util\EiuAnalyst;
use rocket\op\ei\util\EiuPerimeterException;

class EiuContext {
	private $spec;
	private $eiuAnalyst;
	private $n2nContext;
	
	/**
	 * @param Spec $spec
	 * @param N2nContext $n2nContext
	 */
	function __construct(Spec $spec, ?EiuAnalyst $eiuAnalyst = null) {
		$this->spec = $spec;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\op\spec\Spec
	 */
	function getSpec() {
		return $this->spec;
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \n2n\core\container\N2nContext|null
	 */
	function getN2nContext(bool $required = false) {
		if ($this->n2nContext !== null) {
			return $this->n2nContext;
		}
		
		if ($this->eiuAnalyst !== null) {
			return $this->n2nContext = $this->eiuAnalyst->getN2nContext($required);
		}
		
		if ($required) {
			throw new EiuPerimeterException('No N2nContext available.');
		}
		
		return null;
	}
	
	/**
	 * @param string|\ReflectionClass id, classname or class object
	 * @param bool $required
	 * @return EiuType
	 */
	function type($eiTypeId, bool $required = true) {
		$eiType = EiuAnalyst::lookupEiTypeFromEiArg($eiTypeId, $this->spec);

		return new EiuType($eiType, $this->eiuAnalyst->looseCopy());
	}
	
	/**
	 * @param string|\ReflectionClass|EiType|EiComponentNature $eiTypeArg id, entity class name of the affiliated EiType or the EiType itself.
	 * @param bool $required
	 * @return EiuMask
	 * @throws UnknownTypeException required is false and the EiEngine was not be found.
	 * @throws \InvalidArgumentException
	 */
	function mask($eiTypeArg, bool $required = true) {
		ArgUtils::valType($eiTypeArg, ['string', 'object', TypePath::class, \ReflectionClass::class, EiType::class, EiComponentNature::class]);
		
		if ($eiTypeArg instanceof EiMask) {
			return new EiuMask($eiTypeArg, null, $this->eiuAnalyst->looseCopy());
		}
		
		if ($eiTypeArg instanceof EiType) {
			return new EiuMask($eiTypeArg->getEiMask(), null, $this->eiuAnalyst->looseCopy());
		}
		
		if ($eiTypeArg instanceof EiComponentNature) {
			return new EiuMask($eiTypeArg->getEiMask(), null, $this->eiuAnalyst->looseCopy());
		}
		
		if ($eiTypeArg instanceof EiTypeExtension) {
			return new EiuMask($eiTypeArg->getEiMask(), null, $this->eiuAnalyst->looseCopy());
		}
		
		try {
			if ($eiTypeArg instanceof TypePath) {
				return new EiuMask($this->getEiMaskByEiTypePath($eiTypeArg), null,
						$this->eiuAnalyst->looseCopy());
			}
			
			if ($eiTypeArg instanceof \ReflectionClass) {
				return new EiuMask($this->spec->getEiTypeByClass($eiTypeArg)->getEiMask(), null,
						$this->eiuAnalyst->looseCopy());
			}
			
			if (is_object($eiTypeArg)) {
				return new EiuMask($this->spec->getEiTypeOfObject($eiTypeArg)->getEiMask(), 
						null, $this->eiuAnalyst->looseCopy());
			}
			
			if (class_exists($eiTypeArg, false)) {
				return new EiuMask($this->spec->getEiTypeByClassName($eiTypeArg)->getEiMask(), null,
						$this->eiuAnalyst->looseCopy());
			}
						
			return new EiuMask($this->spec->getEiTypeById($eiTypeArg)->getEiMask(), null,
					$this->eiuAnalyst->looseCopy());
		} catch (UnknownTypeException $e) {
			if (!$required) return null;
			
			throw $e;
		}
	}
	
	/**
	 * @param TypePath $eiTypePath
	 * @return \rocket\op\ei\mask\EiMask
	 */
	private function getEiMaskByEiTypePath(TypePath $eiTypePath) {
		$eiType = $this->spec->getEiTypeById($eiTypePath->getTypeId());
		if (null !== ($extIt = $eiTypePath->getEiTypeExtensionId())) {
			return $eiType->getEiTypeExtensionCollection()->getById($extIt)->getEiMask();
		} else {
			return $eiType->getEiMask();
		}
	}
	
	/**
	 * @param string|\ReflectionClass|EiType|EiComponentNature $eiTypeArg id, entity class name of the affiliated EiType or the EiType itself.
	 * @param bool $required
	 * @return EiuEngine
	 * @throws UnknownTypeException required is false and the EiEngine was not be found.
	 */
	function engine($eiTypeArg, bool $required = true) {
		$eiuMask = $this->mask($eiTypeArg, $required);
		if ($eiuMask !== null) {
			return $eiuMask->engine($required);
		}
		
		return null;
	}
}