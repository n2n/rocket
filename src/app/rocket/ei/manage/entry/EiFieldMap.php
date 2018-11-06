<?php
namespace rocket\ei\manage\entry;

use rocket\ei\EiPropPath;

class EiFieldMap {
	private $forkEiPropPath = null;
	private $eiFieldWrappers = array();
	
	public function __construct(EiPropPath $forkEiPropPath) {
		$this->forkEiPropPath = $forkEiPropPath;
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
	 * @return \rocket\ei\manage\entry\EiFieldWrapperImpl
	 */
	public function put(string $id, EiField $eiField) {
		return $this->eiFieldWrappers[$id] = new EiFieldWrapperImpl($eiField);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws EiFieldOperationFailedException
	 * @return EiField
	 */
	public function get(EiPropPath $eiPropPath) {
		return $this->getWrapper($eiPropPath)->getEiField();
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws EiFieldOperationFailedException
	 * @return EiFieldWrapper
	 */
	public function getWrapper(string $id) {
		if (isset($this->eiFieldWrappers[$id])) {
			return $this->eiFieldWrappers[$id];
		}
		
		throw new EiFieldOperationFailedException('No EiField defined for id \'' . $id . '\'.');
	}
	
	public function getWrappers() {
		return $this->eiFieldWrappers;
	}
	
}