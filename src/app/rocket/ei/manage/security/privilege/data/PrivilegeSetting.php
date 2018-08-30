<?php
namespace rocket\ei\manage\security\privilege\data;

use n2n\reflection\ArgUtils;
use rocket\ei\EiCommandPath;
use n2n\util\config\Attributes;
use n2n\reflection\property\TypeConstraint;

class PrivilegeSetting {
	const ATTR_EI_COMMAND_PATHS = 'eiCommandPaths';
	const ATTR_EI_PROP_PROPS = 'ePropProps';
	
	private $eiCommandPaths = array();
	private $eiPropAttributes = array();
	
	/**
	 * @param EiCommandPath[] $eiCommandPaths
	 * @param Attributes $eiPropAttributes
	 */
	function __construct(array $eiCommandPaths = array(), Attributes $eiPropAttributes = null) {
		$this->setEiCommandPaths($eiCommandPaths);
		$this->setEiPropAttributes($attributes ?? new Attributes());
	}
	
	/**
	 * @return EiCommandPath[]
	 */
	function getEiCommandPaths() {
		return $this->eiCommandPaths;
	}
	
	/**
	 * @param EiCommandPath[] $eiCommandPaths
	 */
	function setEiCommandPaths(array $eiCommandPaths) {
		ArgUtils::valArray($eiCommandPaths, EiCommandPath::class);
		$this->eiCommandPaths = $eiCommandPaths;
	}
	
	/**
	 * @return \n2n\util\config\Attributes
	 */
	function getEiPropAttributes() {
		return $this->eiPropAttributes;
	}
	
	/**
	 * @param Attributes $attributes
	 */
	function setEiPropAttributes(Attributes $attributes) {
		$this->eiPropAttributes = $attributes;
	}
	
	/**
	 * @return array
	 */
	function toAttrs() {
		$eiCommandPathAttrs = array();
		foreach ($this->eiCommandPaths as $eiCommandPath) {
			$eiCommandPathAttrs[] = (string) $eiCommandPath;
		}
		
		return array(
				self::ATTR_EI_COMMAND_PATHS => $eiCommandPathAttrs,
				self::ATTR_EI_PROP_PROPS => $this->eiPropAttributes->toArray());
	}
	
	/**
	 * @param Attributes $attributes
	 * @return \rocket\ei\manage\security\privilege\data\PrivilegeSetting
	 */
	static function create(Attributes $attributes) {
		$eiCommandPaths = array();
		foreach ($attributes->getScalarArray(self::ATTR_EI_COMMAND_PATHS, false) as $eiCommandPathStr) {
			$eiCommandPaths[] = EiCommandPath::create($eiCommandPathStr);
		}
		
		$eiPropAttributes = new Attributes($attributes->getArray(self::ATTR_EI_PROP_PROPS, false, array(),
				TypeConstraint::createArrayLike('array')));
		
		return new PrivilegeSetting($eiCommandPaths, $eiPropAttributes);
	}
}