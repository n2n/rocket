<?php
namespace rocket\op\ei\component\command;

use rocket\op\ei\EiCmdPath;
use rocket\op\ei\component\EiComponent;
use rocket\op\ei\component\EiComponentNature;

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
	 * @return \rocket\op\ei\EiCmdPath
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
	 * @return \rocket\op\ei\component\command\EiCmdCollection
	 */
	public function getEiCommandCollection() {
		return $this->eiCmdCollection;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\op\ei\component\EiComponentNature::__toString()
	 */
	public function __toString(): string {
		return (new \ReflectionClass($this->nature))->getShortName() . ' (id: ' . $this->getEiCmdPath() . ')';
	}
}