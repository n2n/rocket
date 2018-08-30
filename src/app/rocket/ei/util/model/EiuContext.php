<?php
namespace rocket\ei\util\model;

use rocket\spec\Spec;
use n2n\core\container\N2nContext;
use rocket\spec\UnknownTypeException;
use n2n\reflection\ArgUtils;
use rocket\ei\EiType;
use rocket\ei\component\EiComponent;
use rocket\spec\TypePath;
use rocket\ei\EiTypeExtension;

class EiuContext {
	private $spec;
	private $eiuFactory;
	private $n2nContext;
	
	/**
	 * @param Spec $spec
	 * @param N2nContext $n2nContext
	 */
	function __construct(Spec $spec, EiuFactory $eiuFactory = null) {
		$this->spec = $spec;
		$this->eiuFactory = $eiuFactory;
	}
	
	/**
	 * @return \rocket\spec\Spec
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
		
		if ($this->eiuFactory !== null) {
			return $this->n2nContext = $this->eiuFactory->getN2nContext($required);
		}
		
		if ($required) {
			throw new EiuPerimeterException('No N2nContext available.');
		}
		
		return null;
	}
	
	/**
	 * @param string|\ReflectionClass|EiType|EiComponent $eiTypeArg id, entity class name of the affiliated EiType or the EiType itself.
	 * @param bool $required
	 * @return EiuMask
	 * @throws UnknownTypeException required is false and the EiEngine was not be found.
	 * @throws \InvalidArgumentException
	 */
	function mask($eiTypeArg, bool $required = true) {
		ArgUtils::valType($eiTypeArg, ['string', 'object', TypePath::class, \ReflectionClass::class, EiType::class, EiComponent::class]);
		
		if ($eiTypeArg instanceof EiType) {
			return new EiuMask($eiTypeArg->getEiMask(), null, $this->eiuFactory);
		}
		
		if ($eiTypeArg instanceof EiComponent) {
			return new EiuMask($eiTypeArg->getEiMask(), null, $this->eiuFactory);
		}
		
		if ($eiTypeArg instanceof EiTypeExtension) {
			return new EiuMask($eiTypeArg->getEiMask(), null, $this->eiuFactory);
		}
		
		$eiEngine = null;
		try {
			if ($eiTypeArg instanceof TypePath) {
				return new EiuMask($this->getEiMaskByEiTypePath($eiTypeArg), null,
						$this->eiuFactory);
			}
			
			if ($eiTypeArg instanceof \ReflectionClass) {
				return new EiuMask($this->spec->getEiTypeByClass($eiTypeArg)->getEiMask(), null,
						$this->eiuFactory);
			}
			
			if (is_object($eiTypeArg)) {
				return new EiuMask($this->spec->getEiTypeOfObject($eiTypeArg)->getEiMask(), 
						null, $this->eiuFactory);
			}
			
			if (class_exists($eiTypeArg, false)) {
				return new EiuMask($this->spec->getEiTypeByClassName($eiTypeArg)->getEiMask(), null,
						$this->eiuFactory);
			}
						
			return new EiuMask($this->spec->getEiTypeById($eiTypeArg)->getEiMask(), null,
					$this->eiuFactory);
		} catch (UnknownTypeException $e) {
			if (!$required) return null;
			
			throw $e;
		}
	}
	
	/**
	 * @param TypePath $eiTypePath
	 * @return \rocket\ei\mask\EiMask
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
	 * @param string|\ReflectionClass|EiType|EiComponent $eiTypeArg id, entity class name of the affiliated EiType or the EiType itself.
	 * @param bool $required
	 * @return EiuEngine
	 * @throws UnknownTypeException required is false and the EiEngine was not be found.
	 */
	function engine($eiTypeArg, bool $required = true) {
		$eiuMask = $this->mask($eiTypeArg, $required);
		if ($eiuMask !== null) {
			return $eiuMask->getEiuEngine($required);
		}
		
		return null;
	}
}