<?php
namespace rocket\ei\component\modificator;

use rocket\ei\EiModificatorPath;
use rocket\ei\component\EiComponent;

class EiModificator implements EiComponent {
	private $eiModificatorPath;
	private $nature;
	private $eiModificatorCollection;
	
	/**
	 * @param EiModificatorPath $eiModificatorPath
	 * @param EiModNature $nature
	 */
	public function __construct(private EiModificatorPath $eiModificatorPath,
			private EiModNature $nature,
			private EiModCollection $eiModificatorCollection) {
	}
	
	/**
	 * @return \rocket\ei\EiModificatorPath
	 */
	public function getEiModificatorPath() {
		return $this->eiModificatorPath;
	}
	
	/**
	 * @return EiModNature
	 */
	public function getNature() {
		return $this->nature;
	}
	
	/**
	 * @return \rocket\ei\component\modificator\\EiModificatorCollection
	 */
	public function getEiModificatorCollection() {
		return $this->eiModificatorCollection;
	}
}