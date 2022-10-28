<?php
namespace rocket\ei\component\command;

use rocket\ei\EiCmdPath;
use rocket\ei\component\EiComponent;
use rocket\ei\component\EiComponentNature;

class EiCmd implements EiComponent {

	/**
	 * @param EiCmdPath $eiCmdPath
	 * @param EiCmdNature $nature
	 * @param EiCmdCollection $eiCmdCollection
	 */
	public function __construct(private EiCmdPath $eiCmdPath, private EiCmdNature $nature,
			private EiCmdCollection $eiCmdCollection) {

	}
	
	/**
	 * @return \rocket\ei\EiCmdPath
	 */
	public function getEiCmdPath() {
		return $this->eiCmdPath;
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
		return $this->eiCmdCollection;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponentNature::__toString()
	 */
	public function __toString(): string {
		return (new \ReflectionClass($this->nature))->getShortName() . ' (id: ' . $this->getEiCmdPath() . ')';
	}
}