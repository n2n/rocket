<?php
namespace rocket\ei\component\prop;

use rocket\ei\EiPropPath;
use rocket\ei\component\EiComponent;

class EiProp implements EiComponent {

	/**
	 * @param EiPropPath $eiPropPath
	 * @param EiPropNature $nature
	 * @param EiPropCollection $eiPropCollection
	 */
	public function __construct(private EiPropPath $eiPropPath, private EiPropNature $nature,
			private EiPropCollection $eiPropCollection) {
	}
	
	/**
	 * @return \rocket\ei\EiPropPath
	 */
	public function getEiPropPath() {
		return $this->eiPropPath;
	}
	
	/**
	 * @return EiPropNature
	 */
	public function getNature(): EiPropNature {
		return $this->nature;
	}
	
	/**
	 * @return \rocket\ei\component\prop\EiPropCollection
	 */
	public function getEiPropCollection() {
		return $this->eiPropCollection;
	}
}