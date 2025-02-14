<?php
namespace rocket\op\ei\manage\entry;

use rocket\op\ei\EiPropPath;
use n2n\util\type\ArgUtils;
use rocket\op\ei\manage\DefPropPath;

class EiFieldMap {
	private $eiEntry;
	private $forkEiPropPath;
	private $object;
	/**
	 * @var EiField[]
	 */
	private $eiFieldWrappers = array();
	
	/**
	 * @param EiEntry $eiEntry
	 * @param EiPropPath $forkEiPropPath
	 * @param object $object
	 */
	function __construct(EiEntry $eiEntry, EiPropPath $forkEiPropPath, object $object) {
		$this->eiEntry = $eiEntry;
		$this->forkEiPropPath = $forkEiPropPath;
		$this->object = $object;
	}
	
	/**
	 * @return \rocket\op\ei\manage\entry\EiEntry
	 */
	function getEiEntry() {
		return $this->eiEntry;
	}
	
	/**
	 * @return \rocket\op\ei\EiPropPath
	 */
	function getForkEiPropPath() {
		return $this->forkEiPropPath;
	}
	
	/**
	 * @return object
	 */
	function getObject() {
		return $this->object;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return bool
	 */
	function containsId(string $id): bool {
		return isset($this->eiFieldWrappers[$id]);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 */
	function removeById(string $id) {
		unset($this->eiFieldWrappers[$id]);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param EiFieldNature $eiField
	 * @return \rocket\op\ei\manage\entry\EiField
	 */
	function put(string $id, EiFieldNature $eiField) {
		return $this->eiFieldWrappers[$id] = new EiField($this, $this->forkEiPropPath->ext($id), $eiField);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return EiFieldNature
	 *@throws EiFieldOperationFailedException
	 */
	function getNature(string $id) {
		return $this->getWrapper($id)->getEiFieldNature();
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return EiField
	 *@throws UnknownEiFieldExcpetion
	 */
	function getWrapper(string $id) {
		if (isset($this->eiFieldWrappers[$id])) {
			return $this->eiFieldWrappers[$id];
		}
		
		throw new UnknownEiFieldExcpetion('No EiField defined for id \'' . $id . '\'.');
	}
	
	/**
	 * @return EiField[]
	 */
	function getWrappers() {
		return $this->eiFieldWrappers;
	}

	/**
	 * @return boolean
	 */
	function isValid() {
		foreach ($this->eiFieldWrappers as $eiPropPathStr => $eiFieldWrapper) {
			if ($eiFieldWrapper->isIgnored()) continue;
			
			if (!$eiFieldWrapper->getEiFieldNature()->isValid()) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * @param EiEntryValidationResult $eiEntryValidationResult
	 */
	function validate(EiEntryValidationResult $eiEntryValidationResult) {
		foreach ($this->eiFieldWrappers as $eiPropPathStr => $eiFieldWrapper) {
			if ($eiFieldWrapper->isIgnored()) continue;
			
			$eiFieldWrapper->validate($eiEntryValidationResult->getEiFieldValidationResult(
					EiPropPath::create($eiPropPathStr)));
		}
	}
	
	function read() {
		foreach ($this->eiFieldWrappers as $eiFieldWrapper) {
			$eiFieldWrapper->read();
		}
	}
	
	function write() {
		foreach ($this->eiFieldWrappers as $key => $eiFieldWrapper) {
			if ($eiFieldWrapper->isIgnored() || !$eiFieldWrapper->isWritable(true)) {
				continue;
			}

			$eiFieldWrapper->write();
		}
	}

	function hasChanges(): bool {
		foreach ($this->eiFieldWrappers as $eiFieldWrapper) {
			if ($eiFieldWrapper->hasChanges()) {
				return true;
			}

			return false;
		}
	}
	
}