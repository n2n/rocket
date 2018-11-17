<?php
namespace rocket\ei\manage\entry;

use rocket\ei\EiPropPath;

class EiFieldMap {
	private $eiEntry;
	private $forkEiPropPath;
	private $object;
	private $eiFieldWrappers = array();
	
	public function __construct(EiEntry $eiEntry, EiPropPath $forkEiPropPath, object $object) {
		$this->eiEntry = $eiEntry;
		$this->forkEiPropPath = $forkEiPropPath;
		$this->object = $object;
	}
	
	/**
	 * @return \rocket\ei\manage\entry\EiEntry
	 */
	public function getEiEntry() {
		return $this->eiEntry;
	}
	
	/**
	 * @return \rocket\ei\EiPropPath
	 */
	public function getForkEiPropPath() {
		return $this->forkEiPropPath;
	}
	
	public function getObject() {
		return $this->object;
	}
	
	/**
	* @param EiPropPath $eiPropPath
	* @return bool
	*/
	public function containsId(string $id): bool {
		return isset($this->eiFieldWrappers[$id]);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 */
	public function removeById(string $id) {
		unset($this->eiFieldWrappers[$id]);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param EiField $eiField
	 * @return \rocket\ei\manage\entry\EiFieldWrapper
	 */
	public function put(string $id, EiField $eiField) {
		return $this->eiFieldWrappers[$id] = new EiFieldWrapper($this->forkEiPropPath->ext($id), $eiField);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws EiFieldOperationFailedException
	 * @return EiField
	 */
	public function get(string $id) {
		return $this->getWrapper($id)->getEiField();
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws UnknownEiFieldExcpetion
	 * @return EiFieldWrapper
	 */
	public function getWrapper(string $id) {
		if (isset($this->eiFieldWrappers[$id])) {
			return $this->eiFieldWrappers[$id];
		}
		
		throw new UnknownEiFieldExcpetion('No EiField defined for id \'' . $id . '\'.');
	}
	
	public function getWrappers() {
		return $this->eiFieldWrappers;
	}
	
	public function validate(EiEntryValidationResult $eiEntryValidationResult) {
		foreach ($this->eiFieldWrappers as $eiPropPathStr => $eiFieldWrapper) {
			if ($eiFieldWrapper->isIgnored()) continue;
			
			$eiFieldWrapper->getEiField()->validate($eiEntryValidationResult->getEiFieldValidationResult(
					EiPropPath::create($eiPropPathStr)));
		}
	}
	
	
}