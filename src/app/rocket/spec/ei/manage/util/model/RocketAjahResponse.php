<?php

namespace lib\rocket\spec\ei\manage\util\model;

use n2n\web\http\BufferedResponseContent;
use n2n\impl\web\ui\view\json\JsonResponse;
use n2n\impl\web\ui\view\html\AjahResponse;
use n2n\reflection\ArgUtils;

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
	const ATTR_REL_CONFIG = 'relConfig';
	const ATTR_EXEC_CONFIG = 'execConfig';

	const DIRECTIVE_REDIRECT_BACK = 'redirectBack';
	
	public static function redirectBack($fallbackUrl, AjahRel $ajahRel = null, AjahExec $ajahExec = null) {
		return new RocketAjahResponse(array(
				self::ATTR_DIRECTIVE => self::DIRECTIVE_REDIRECT_BACK,
				self::ATTR_FALLBACK_URL => $fallbackUrl,
				self::ATTR_REL_CONFIG => $ajahRel === null ? array() : $ajahRel->toAttrs(),
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

class AjahRel {
	private $refreshMode = null;
	
	const REFRESH_ENTRY = 'entry';
	const REFRESH_CONTEXT = 'context';
	
	public function __construct(string $refreshMode = null) {
		ArgUtils::valEnum($refreshMode, self::getRefreshModes(), null, true);
		$this->refreshMode = $refreshMode;
	}
	
	public function toAttrs() {
		return array('refreshMode' => $this->refreshMode);
	}

	public static function getRefreshModes() {
		return array(self::REFRESH_CONTEXT, self::REFRESH_ENTRY);
	}
	
	public static function refreshContext() {
		return new AjahRel(self::REFRESH_CONTEXT);
	}
	
	public static function refreshEntry() {
		return new AjahRel(self::REFRESH_ENTRY);
	}
	
	
}