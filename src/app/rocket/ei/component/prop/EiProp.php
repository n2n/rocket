<?php
namespace rocket\ei\component\prop;

use rocket\ei\EiPropPath;
use rocket\ei\component\EiComponent;

class EiProp implements EiComponent {
	private $eiPropPath;
	private $eiProp;
	private $eiPropCollection;

	/**
	 * @param EiPropPath $eiPropPath
	 * @param EiPropNature $eiProp
	 * @param EiPropCollection $eiPropCollection
	 */
	public function __construct(EiPropPath $eiPropPath, EiPropNature $eiProp, EiPropCollection $eiPropCollection) {
		$this->eiPropPath = $eiPropPath;
		$this->eiProp = $eiProp;
		$this->eiPropCollection = $eiPropCollection;
		
		$eiProp->setWrapper($this);
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
	public function getEiProp() {
		return $this->eiProp;
	}
	
	/**
	 * @return \rocket\ei\component\prop\EiPropCollection
	 */
	public function getEiPropCollection() {
		return $this->eiPropCollection;
	}
}