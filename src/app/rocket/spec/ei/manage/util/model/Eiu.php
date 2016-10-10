<?php
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\EiState;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\LiveEntry;
use n2n\core\container\N2nContext;
use rocket\spec\ei\manage\LiveEiSelection;
use rocket\spec\ei\manage\DraftEiSelection;
use rocket\spec\ei\manage\util\model\EiuCtrl;
use n2n\context\Lookupable;
use rocket\spec\ei\manage\util\model\EiuFrame;
use rocket\spec\ei\manage\ManageState;
use rocket\spec\ei\manage\ManageException;
use n2n\web\http\HttpContextNotAvailableException;
use n2n\util\ex\NotYetImplementedException;
use rocket\spec\ei\manage\model\EntryGuiModel;

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