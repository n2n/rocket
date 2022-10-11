<?php
namespace rocket\ei\component\modificator;

use rocket\ei\EiModificatorPath;
use rocket\ei\component\EiComponent;

class EiMod implements EiComponent {

	/**
	 * @param EiModificatorPath $eiModificatorPath
	 * @param EiModNature $nature
	 * @param EiModCollection $eiModificatorCollection
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
	public function getNature(): EiModNature {
		return $this->nature;
	}
	
	/**
	 * @return \rocket\ei\component\modificator\\EiModCollection
	 */
	public function getEiModCollection() {
		return $this->eiModificatorCollection;
	}
}