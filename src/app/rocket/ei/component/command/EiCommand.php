<?php
namespace rocket\ei\component\command;

use rocket\ei\EiCommandPath;
use rocket\ei\component\EiComponent;
use rocket\ei\component\EiComponentNature;

class EiCommand implements EiComponent {

	/**
	 * @param EiCommandPath $eiCommandPath
	 * @param EiCmdNature $nature
	 * @param EiCmdCollection $eiCommandCollection
	 */
	public function __construct(private EiCommandPath $eiCommandPath, private EiCmdNature $nature,
			private EiCmdCollection $eiCommandCollection) {

	}
	
	/**
	 * @return \rocket\ei\EiCommandPath
	 */
	public function getEiCommandPath() {
		return $this->eiCommandPath;
	}
	
	/**
	 * @return EiCmdNature
	 */
	public function getNature(): EiComponentNature {
		return $this->nature;
	}
	
	/**
	 * @return \rocket\ei\component\command\EiCmdCollection
	 */
	public function getEiCommandCollection() {
		return $this->eiCommandCollection;
	}
}