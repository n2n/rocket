<?php

namespace lib\rocket\spec\ei\manage\util\model;

use n2n\web\http\BufferedResponseContent;
use n2n\impl\web\ui\view\json\JsonResponse;
use n2n\impl\web\ui\view\html\AjahResponse;

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
	
	public static function redirectBack($fallbackUrl) {
		return new RocketAjahResponse(array(
				self::ATTR_DIRECTIVE => self::DIRECTIVE_REDIRECT_BACK,
				self::ATTR_FALLBACK_URL => $fallbackUrl));
	}
}