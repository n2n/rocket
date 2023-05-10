<?php
namespace rocket\op\ei;

use rocket\op\util\Identifiable;
use rocket\op\ei\mask\EiMask;

class EiTypeExtension implements Identifiable {
	private $id;
	private $moduleNamespace;
	private $eiMask;
	private $extendedEiMask;
	
	/**
	 * @param string $id
	 * @param string $moduleNamespace
	 * @param EiMask $eiMask
	 * @param EiMask $extendedEiMask
	 */
	function __construct(string $id, string $moduleNamespace, EiMask $eiMask, EiMask $extendedEiMask) {
		$this->id = $id;
		$this->moduleNamespace = $moduleNamespace;
		$this->eiMask = $eiMask;
		$this->extendedEiMask = $extendedEiMask;
		
		$eiMask->extends($this);
	}
	
	/**
	 * @return string
	 */
	function getId(): string {
		return $this->id;
	}
	
	/**
	 * @return string
	 */
	function getModuleNamespace() {
		return $this->moduleNamespace;
	}
	
	/**
	 * @return \rocket\op\ei\mask\EiMask
	 */
	function getEiMask() {
		return $this->eiMask;
	}
	
	/**
	 * @return \rocket\op\ei\mask\EiMask
	 */
	function getExtendedEiMask() {
		return $this->extendedEiMask;
	}
	
	function __toString() {
		return 'Extension ' . $this->id . ' of ' . $this->eiMask->getEiType();
	}
}