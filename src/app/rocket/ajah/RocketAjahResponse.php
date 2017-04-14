<?php
namespace rocket\ajah;

use n2n\web\http\BufferedResponseContent;
use n2n\impl\web\ui\view\json\JsonResponse;
use n2n\impl\web\ui\view\html\AjahResponse;
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
