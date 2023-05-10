<?php
namespace rocket\op\ei\component\prop;

use rocket\op\ei\EiPropPath;
use rocket\op\ei\component\EiComponent;

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
	 * @return \rocket\op\ei\EiPropPath
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
	 * @return \rocket\op\ei\component\prop\EiPropCollection
	 */
	public function getEiPropCollection() {
		return $this->eiPropCollection;
	}

	public function __toString(): string {
		return (new \ReflectionClass($this->nature))->getShortName() . ' (id: ' . $this->getEiPropPath() . ')';
	}
}