<?php

namespace rocket\spec\ei\manage\util\model;

use n2n\web\http\BufferedResponseContent;
use n2n\impl\web\ui\view\json\JsonResponse;
use n2n\impl\web\ui\view\html\AjahResponse;
use n2n\reflection\ArgUtils;
use rocket\spec\ei\manage\EiEntry;

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
	
	public static function redirectBack($fallbackUrl, AjahEventInfo $ajahRel = null, AjahExec $ajahExec = null) {
		return new RocketAjahResponse(array(
				self::ATTR_DIRECTIVE => self::DIRECTIVE_REDIRECT_BACK,
				self::ATTR_FALLBACK_URL => $fallbackUrl,
				self::ATTR_EVENTS => $ajahRel === null ? array() : $ajahRel->toAttrs(),
				self::ATTR_EXEC_CONFIG => $ajahExec === null ? array() : $ajahExec->toAttrs()));
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
	private $refreshCotext;
	private $refreshMode = null;
	
	public function __construct($draftMods) {
		ArgUtils::valEnum($refreshMode, self::getRefreshModes(), null, true);
		$this->refreshMode = $refreshMode;
	}
	
	public static function common() {
		return new AjahEventInfo();
	}
	
	public static function ei() {
		return new AjahEiEventInfo();
	}
	
	public function toAttrs() {
		return array('refreshMode' => $this->refreshMode);
	}
}

class AjahEventInfo implements AjahModInfo {
	private $resfreshMod;
	private $marks = array();
	
	public function groupChanged(string $groupId) {
		$this->marks[$groupId] = RocketAjahResponse::MOD_TYPE_CHANGED;
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
		if (!isset($this->marks[$typeId])) {
			$this->marks[$typeId] = array();
		} else if ($type->marks[$typeId] == RocketAjahResponse::MOD_TYPE_CHANGED) {
			return;
		}
		
		$this->marks[$typeId][$entryId] = $modType;
		return $this;
	}
	
	public function toAttrs(): array {
		if ($this->resfreshMod == self::REFRESH_MODE_CONTEXT) {
			return array(self::ATTR_REFRESH_MODE => self::REFRESH_MODE_CONTEXT);
		}
		
		return array(
				self::ATTR_REFRESH_MODE => self::REFRESH_MODE_ENTRY,
				self::ATTR_MARKS => $this->marks);
	}
}

class EiAjahEventInfo extends AjahEventInfo {
	
	public function eiTypeChanged(...$eiSpecArgs) {
		$this->categoryChanged(self::buildTypeId($eiEntry));
	}
	
	public function eiEntryChanged(...$eiEntryArgs) {
		foreach ($eiEntryArgs as $eiEntryArg) {
			$eiEntry = EiuFactory::determineEiEntry($eiEntryArg, null, null, false);
			$this->eiEntryMod($eiEntry, true);
		}
	}
	
	public function eiEntryRemoved(...$eiEntryArgs) {
		foreach ($eiEntryArgs as $eiEntryArg) {
			$eiEntry = EiuFactory::determineEiEntry($eiEntryArg, null, null, true);
			$this->eiEntryMod($eiEntry, true);
		}
	}
	
	private function eiEntryMod(EiEntry $eiEntry, bool $removed) {
		if ($removed) {
			$this->itemRemoved(self::buildTypeId($eiEntry), self::buildItemId($eiEntry));
		} else {
			$this->itemChanged(self::buildTypeId($eiEntry), self::buildItemId($eiEntry));
		}
	}
	
	public static function buildTypeId(EiEntry $eiEntry) {
		return $eiEntry->getLiveEntry()->getEiSpec()->getSupremeEiSpec()->getId();	
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
