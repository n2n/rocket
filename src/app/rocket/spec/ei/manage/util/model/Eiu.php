<?php
namespace rocket\spec\ei\manage\util\model;

use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\EiState;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\EiSelection;
use rocket\spec\ei\manage\draft\Draft;
use rocket\spec\ei\manage\LiveEntry;
use n2n\core\container\N2nContext;
use rocket\spec\ei\EiSpec;
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
	private $eiUtilFactory = new EiUtilFactory();
	private $eiFrame;
	private $eiCtrlUtils;
	private $eiEntryUtils;
	private $eiFieldUtils;
	
	private function _init(N2nContext $n2nContext) {
		$this->eiUtilFactory->applyEiArg($n2nContext);
	}
	
	public function __construct(...$eiArgs) {
		foreach ($eiArgs as $eiArg) {
			$this->eiUtilFactory->applyEiArg($eiArg);
		}
	}
	
	public function ctrl() {
		if ($this->eiCtrlUtils === null) {
			$this->eiCtrlUtils = $this->eiUtilFactory->createEiuCtrl($this->frame());
		}
		
		return $this->eiCtrlUtils;
	}

	/**
	 * @return \rocket\spec\ei\manage\util\model\EiuFrame
	 */
	public function frame()  {
		if ($this->eiFrame === null) {
			$this->eiFrame = $this->eiUtilFactory->createEiuFrame();
		}
		
		return $this->eiFrame;
	}
	
	/**
	 * @param unknown $eiEntryObj
	 * @param bool $assignToEiu
	 * @return \rocket\spec\ei\manage\util\model\EiuEntry
	 */
	public function entry($eiEntryObj = null, bool $assignToEiu = false) {
		if ($eiEntryObj === null) {
			return $this->frame()->getAssignedEiuEntry();
		}
		
		if ($assignToEiu) {
			return $this->frame()->assignEiuEntry($eiEntryObj);
		}
			
		return $this->frame()->toEiuEntry($eiEntryObj);
	}
	
	public function field($fieldPath, bool $assignToEiu = false) {
		throw new NotYetImplementedException();
	}
}

class EiUtilFactory {
	const EI_ENTRY_TYPES = array(EiSelection::class, EiMapping::class, LiveEntry::class, Draft::class);
	const EI_UTIL_TYPES = array(EiState::class, N2nContext::class);
	const EI_TYPES = array_merge(self::EI_UTIL_TYPES, self::EI_ENTRY_TYPES);
	
	private $eiState;
	private $n2nContext;
	private $entryGuiModel;
	private $eiSelection;
	private $eiMapping;
	
	public function applyEiArg($eiArg) {
		if ($eiArg instanceof EiState) {
			$this->eiState = $eiArg;
			return;
		}
		
		if ($eiArg instanceof N2nContext) {
			$this->n2nContext = $eiArg;
			return;
		}
		
		if ($eiArg instanceof EntryGuiModel) {
			$this->entryGuiModel = $eiArg;
			return;
		}

		try {
			$this->eiSelection = self::determineEiSelection($eiArg, $this->eiMapping);
		} catch (\InvalidArgumentException $e) {
			ArgUtils::valType($eiArg, self::EI_TYPES, false, 'eiArg');
		}
	}
	
	/**
	 * @throws EiuPerimeterException
	 * @return \rocket\spec\ei\manage\util\model\EiuFrame
	 */
	public function createEiuFrame() {
		if ($this->eiState !== null) {
			return new EiuFrame($this->eiState);
		} 
		
		if ($this->n2nContext !== null) {
			try {
				return new EiuFrame($this->n2nContext->lookup(ManageState::class)->preakEiState());
			} catch (ManageException $e) {
				throw new EiuPerimeterException('Can not create EiuFrame in invalid context.', 0, $e);
			}
		}
		
		throw new EiuPerimeterException(
				'Can not create EiuFrame because non of the following types were provided as eiArgs: ' 
						. implode(', ', self::EI_UTIL_TYPES));
	}

	public function createEiuCtrl(EiuFrame $eiFrame) {
		try {
			return EiuCtrl::from($eiFrame->getN2nContext()->getHttpContext(), $eiFrame);
		} catch (HttpContextNotAvailableException $e) {
			throw new EiuPerimeterException('Can not create EiuCtrl.', 0, $e);
		}
	}
	
	/**
	 * @param unknown $eiEntryObj
	 * @return rocket\spec\ei\manage\util\model\EiSelection
	 */
	public static function determineEiSelection($eiEntryObj, &$eiMapping) {
		if ($eiEntryObj instanceof EiSelection) {
			return $eiEntryObj;
		} else if ($eiEntryObj instanceof EiMapping) {
			return $eiEntryObj->getEiSelection();
		} else if ($eiEntryObj instanceof LiveEntry) {
			return new LiveEiSelection($eiEntryObj);
		} else if ($eiEntryObj instanceof Draft) {
			return new DraftEiSelection($eiEntryObj);
		} else {
			ArgUtils::valType($eiEntryObj, self::EI_ENTRY_TYPES, false, 'eiEntryObj');
		}
	}
}