<?php

namespace rocket\spec\ei\manage\util\model;

use n2n\web\http\BufferedResponseContent;
use n2n\impl\web\ui\view\json\JsonResponse;
use n2n\impl\web\ui\view\html\AjahResponse;
use rocket\spec\ei\manage\EiEntry;
use rocket\spec\ei\EiSpec;
use n2n\impl\web\ui\view\html\HtmlView;

class RocketAjahResponse implements BufferedResponseContent {
	private $jsonResponse;
	
	private function __construct(array $attrs) {
		$this->jsonResponse = new JsonResponse(array(AjahResponse::ADDITIONAL_KEY => $attrs));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\web\http\BufferedResponseContent::getBufferedContents()
	 */
	public function getBufferedContents(): string {
		return $this->jsonResponse->getBufferedContents();
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\http\ResponseThing::prepareForResponse()
	 */
	public function prepareForResponse(\n2n\web\http\Response $response) {
		$this->jsonResponse->prepareForResponse($response);
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\http\ResponseThing::toKownResponseString()
	 */
	public function toKownResponseString(): string {
		return $this->jsonResponse->toKownResponseString();
	}
	
	const ATTR_DIRECTIVE = 'directive';
	const ATTR_FALLBACK_URL = 'fallbackUrl';
	
	const DIRECTIVE_REDIRECT_BACK = 'redirectBack';
	
	const ATTR_EVENTS = 'events';
	const ATTR_MODIFICATIONS = 'modifications';
	
	const MOD_TYPE_CHANGED = 'changed';
	const MOD_TYPE_REMOVED = 'removed';
	
	const ATTR_EXEC_CONFIG = 'execConfig';
	
	/**
	 * @param unknown $fallbackUrl
	 * @param AjahEventInfo $ajahEventInfo
	 * @param AjahExec $ajahExec
	 * @return BufferedResponseContent
	 */
	public static function redirectBack($fallbackUrl, AjahEventInfo $ajahEventInfo = null, AjahExec $ajahExec = null) {
		$attrs = array(
				self::ATTR_DIRECTIVE => self::DIRECTIVE_REDIRECT_BACK,
				self::ATTR_FALLBACK_URL => $fallbackUrl);
		
		if ($ajahEventInfo !== null) {
			$attrs[self::ATTR_EVENTS] = $ajahEventInfo->toAttrs();
		}
		
		if ($ajahExec !== null) {
			$attrs[self::ATTR_EXEC_CONFIG] = $ajahExec->toAttrs();
		}
		
		return new RocketAjahResponse($attrs);
	}
	
	/**
	 * @param AjahEventInfo $ajahEventInfo
	 * @return BufferedResponseContent
	 */
	public static function events(AjahEventInfo $ajahEventInfo) {
		return new RocketAjahResponse(array(
				self::ATTR_EVENTS => $ajahEventInfo === null ? array() : $ajahEventInfo->toAttrs()));
	}
	
	public static function view(HtmlView $htmlView, AjahEventInfo $ajahEventInfo = null) {
		return new AjahResponse($htmlView, 
				($ajahEventInfo !== null ? array(self::ATTR_EVENTS => $ajahEventInfo->toAttrs()) : null));
	}
}

class AjahExec {
	private $forceReload = false; 
	private $showLoadingContext = true;
	
	public function __construct(bool $forceReload = false, bool $showLoadingContext = true) {
		$this->forceReload = $forceReload;
		$this->showLoadingContext = $showLoadingContext;
	}
	
	public function toAttrs() {
		return array(
				'forceReload' => $this->forceReload,
				'showLoadingcontext' => $this->showLoadingContext);
	}
}

class AjahEvent {
	
	public static function common() {
		return new AjahEventInfo();
	}
	
	public static function ei() {
		return new EiAjahEventInfo();
	}
}

class AjahEventInfo {
	private $resfreshMod;
	private $eventMap = array();
	
	public function groupChanged(string $groupId) {
		$this->eventMap[$groupId] = RocketAjahResponse::MOD_TYPE_CHANGED;
	}
	
	/**
	 * @param string $typeId
	 * @param string $entryId
	 * @return \rocket\spec\ei\manage\util\model\AjahModInfoAdapter
	 */
	public function itemChanged(string $typeId, string $entryId) {
		$this->item($typeId, $entryId, RocketAjahResponse::MOD_TYPE_CHANGED);
		return $this;
	}
	
	/**
	 * @param string $typeId
	 * @param string $entryId
	 * @return \rocket\spec\ei\manage\util\model\AjahModInfoAdapter
	 */
	public function itemRemoved(string $typeId, string $entryId) {
		$this->item($typeId, $entryId, RocketAjahResponse::MOD_TYPE_REMOVED);
		return $this;
	}
	
	/**
	 * @param string $typeId
	 * @param string $entryId
	 * @param string $modType
	 */
	public function item(string $typeId, string $entryId, string $modType) {
		if (!isset($this->eventMap[$typeId])) {
			$this->eventMap[$typeId] = array();
		} else if ($this->eventMap[$typeId] == RocketAjahResponse::MOD_TYPE_CHANGED) {
			return;
		}
		
		$this->eventMap[$typeId][$entryId] = $modType;
		return $this;
	}
	
	public function toAttrs(): array {
		return $this->eventMap;
	}
}

class EiAjahEventInfo extends AjahEventInfo {
	
	public function eiSpecChanged(...$eiSpecArgs) {
		foreach ($eiSpecArgs as $eiSpecArg) {
			$this->groupChanged(self::buildTypeId(EiuFactory::buildEiSpecFromEiArg($eiSpecArg)));
		}
	}
	
	public function eiEntryChanged(...$eiEntryArgs) {
		foreach ($eiEntryArgs as $eiEntryArg) {
			$this->eiEntryMod($eiEntryArg, false);
		}
	}
	
	public function eiEntryRemoved(...$eiEntryArgs) {
		foreach ($eiEntryArgs as $eiEntryArg) {
			$this->eiEntryMod($eiEntryArg, true);
		}
	}
	
	private function eiEntryMod($eiEntryArg, bool $removed) {
		$eiEntry = EiuFactory::buildEiEntryFromEiArg($eiEntryArg, 'eiEntryArg', null, true);
		if ($removed) {
			$this->itemRemoved(self::buildTypeId($eiEntry->getEiSpec()), self::buildItemId($eiEntry));
		} else {
			$this->itemChanged(self::buildTypeId($eiEntry->getEiSpec()), self::buildItemId($eiEntry));
		}
	}
	
	public static function buildTypeId(EiSpec $eiSpec) {
		return $eiSpec->getLiveEntry()->getEiSpec()->getSupremeEiSpec()->getId();	
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @return string
	 */
	public static function buildItemId(EiEntry $eiEntry) {
		if ($eiEntry->isDraft()) {
			return 'draft-id-' . $eiEntry->getDraft()->getId();
		}
		
		return 'live-id-rep-' . $eiEntry->getLiveEntry()->getId();
	}
	
	public function toAttrs(): array {
	
	}
}
