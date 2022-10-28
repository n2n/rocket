<?php
namespace rocket\ei\component\modificator;

use rocket\ei\EiModPath;
use rocket\ei\component\EiComponent;

class EiMod implements EiComponent {

	/**
	 * @param EiModPath $eiModPath
	 * @param EiModNature $nature
	 * @param EiModCollection $eiModCollection
	 */
	public function __construct(private EiModPath $eiModPath,
			private EiModNature $nature,
			private EiModCollection $eiModCollection) {
	}
	
	/**
	 * @return \rocket\ei\EiModPath
	 */
	public function getEiModPath() {
		return $this->eiModPath;
	}
	
	/**
	 * @return EiModNature
	 */
	public function getNature(): EiModNature {
		return $this->nature;
	}
	
	/**
	 * @return \rocket\ei\component\modificator\\EiModCollection
	 */
	public function getEiModCollection() {
		return $this->eiModCollection;
	}


	public function __toString(): string {
		return (new \ReflectionClass($this->nature))->getShortName() . ' (id: ' . $this->getEiModPath() . ')';
	}
}