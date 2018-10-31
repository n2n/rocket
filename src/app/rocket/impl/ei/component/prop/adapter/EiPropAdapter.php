<?php
namespace rocket\impl\ei\component\prop\adapter;

use rocket\ei\component\prop\EiProp;
use n2n\l10n\Lstr;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\EiComponentAdapter;
use rocket\ei\component\prop\EiPropWrapper;
use n2n\util\StringUtils;

class EiPropAdapter extends EiComponentAdapter implements EiProp {
	private $wrapper;
	
	public function setWrapper(EiPropWrapper $wrapper) {
		$this->wrapper = $wrapper;
	}
	
	public function getWrapper(): EiPropWrapper {
		if ($this->wrapper !== null) {
			return $this->wrapper;
		}
		
		throw new IllegalStateException(get_class($this) . ' is not assigned to a Wrapper.');
	}
	
	public function getIdBase(): ?string {
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponent::equals()
	 */
	public function equals($obj) {
		return $obj instanceof EiProp && $this->getWrapper()->getEiPropPath()->equals(
				$obj->getWrapper()->getEiPropPath());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiComponent::__toString()
	 */
	public function __toString(): string {
		return (new \ReflectionClass($this))->getShortName()
				. ' (id: ' . ($this->wrapper ? $this->wrapper->getEiPropPath() : 'unknown') . ')';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\EiProp::getLabelLstr()
	 */
	public function getLabelLstr(): Lstr {
		return Lstr::create(StringUtils::pretty($this->getWrapper()->getEiPropPath()->getLastId()));
	}

}