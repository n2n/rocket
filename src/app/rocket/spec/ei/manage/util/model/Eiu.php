<?php
namespace rocket\spec\ei\manage\util\model;

use n2n\core\container\N2nContext;
use n2n\context\Lookupable;
use rocket\spec\ei\manage\util\model\EiuFrame;
use n2n\util\ex\NotYetImplementedException;

class Eiu implements Lookupable {
	private $eiUtilFactory;
	private $eiFrame;
	private $eiCtrlUtils;
	private $eiEntryUtils;
	private $eiFieldUtils;
	
	private function _init(N2nContext $n2nContext) {
		$this->eiUtilFactory = new EiuFactory();
		$this->eiUtilFactory->applyEiArgs($n2nContext);
	}
	
	public function __construct(...$eiArgs) {
		$this->eiUtilFactory = new EiuFactory();
		$this->eiUtilFactory->applyEiArgs(...$eiArgs);
	}
	
	public function ctrl(bool $required = true) {
		return $this->eiUtilFactory->getEiuCtrl($required);
	}

	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuFrame
	 */
	public function frame(bool $required = true)  {
		return $this->eiUtilFactory->getEiuFrame($required);
	}
	
	/**
	 * @param unknown $eiEntryObj
	 * @param bool $assignToEiu
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function entry($eiEntryObj = null, bool $assignToEiu = false) {
		if ($eiEntryObj === null) {
			return $this->eiUtilFactory->getEiuEntry(true);
		}
		
		if ($assignToEiu) {
			return $this->frame()->assignEiuEntry($eiEntryObj);
		}
			
		return $this->frame()->toEiuEntry($eiEntryObj);
	}
	
	public function gui() {
		return $this->eiUtilFactory->getEiuGui(true);	
	}
	
	public function field($fieldPath, bool $assignToEiu = false) {
		throw new NotYetImplementedException();
	}
}