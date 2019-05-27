<?php
namespace rocket\ei\util\control;

use rocket\si\control\SiResult;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\EiType;
use n2n\l10n\Message;
use n2n\util\uri\Url;

class EiuControlResponse {
	private $eiuAnalyst;
	/**
	 * @var SiResult
	 */
	private $siResult;
	/**
	 * @var bool
	 */
	private $noAutoEvents = false;
	
	/**
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(EiuAnalyst $eiuAnalyst) {
		$this->eiuAnalyst = $eiuAnalyst;
		$this->siResult = new SiResult();
	}
	
	/**
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function redirectBack() {
		$this->siResult->setDirective(SiResult::DIRECTIVE_REDIRECT_BACK);
		
		$eiFrame = $this->eiuAnalyst->getEiFrame(true);
		
		if (null !== ($overviewUrl = $eiFrame->getOverviewUrl(false))) {
			$this->siResult->setRef($overviewUrl);
		}
		
		return $this;
	}
	
	/**
	 * @param Url $url
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function redirectBackOrRef(Url $url) {
		$this->siResult->setDirective(SiResult::DIRECTIVE_REDIRECT_BACK);
		$this->siResult->setRef($url);
		return $this;
	}
	
	/**
	 * @param Url $url
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function redirectBackOrHref(Url $url) {
		$this->siResult->setDirective(SiResult::DIRECTIVE_REDIRECT_BACK);
		$this->siResult->setHref($url);
		return $this;
	}
	
	/**
	 * @param Url $url
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function redirectToRef(Url $url) {
		$this->siResult->setDirective(SiResult::DIRECTIVE_REDIRECT);
		$this->siResult->setRef($url);
		return $this;
	}
	
	/**
	 * @param Url $url
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function redirectToHref(Url $url) {
		$this->siResult->setDirective(SiResult::DIRECTIVE_REDIRECT_BACK);
		$this->siResult->setHref($url);
		return $this;
	}
	
	/**
	 * @param Message|string $message
	 */
	function message($message) {
		$this->siResult->addMessage(Message::create($message), 
				$this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
	
// 	/**
// 	 * @param mixed ...$eiTypeArgs
// 	 * @return EiuControlResponse
// 	 */
// 	public function eiTypeChanged(...$eiTypeArgs) {
// 		foreach ($eiTypeArgs as $eiTypeArg) {
// 			$this->groupChanged(self::buildTypeId(EiuAnalyst::buildEiTypeFromEiArg($eiTypeArg)));
// 		}
// 		return $this;
// 	}

	/**
	 * @param mixed ...$eiObjectArgs
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function highlight(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', null, true);
			
			$this->siResult->addHighlight(
					self::buildCategory($eiObject->getEiEntityObj()->getEiType()), 
					$eiObject->getEiEntityObj()->getPid());
		}
		
		return $this;
	}
	
	/**
	 * @param bool $noAutoEvents
	 * @return EiuControlResponse
	 */
	function noAutoEvents(bool $noAutoEvents = true) {
		$this->noAutoEvents = true;
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return EiuControlResponse
	 */
	function entryAdded(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, SiResult::MOD_TYPE_ADDED);
		}
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return EiuControlResponse
	 */
	function entryChanged(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, SiResult::MOD_TYPE_CHANGED);
		}
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return EiuControlResponse
	 */
	function entryRemoved(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, SiResult::MOD_TYPE_REMOVED);
		}
		return $this;
	}
	
	private function eiObjectMod($eiObjectArg, string $modType) {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', null, true);
		
		$eiTypeId = self::buildCategory($eiObject->getEiEntityObj()->getEiType());
		
		$pid = null;
		if ($eiObject->getEiEntityObj()->hasId()) {
			$pid = $eiObject->getEiEntityObj()->getPid();
		}
		
		$this->siResult->addEvent($eiTypeId, $pid, $modType);
	}
	
	/**
	 * @param EiType $eiType
	 * @return string
	 */
	private static function buildCategory(EiType $eiType) {
		return $eiType->getSupremeEiType()->getId();
	}
}