<?php
namespace rocket\ajah;

use n2n\web\http\payload\BufferedPayload;
use n2n\impl\web\ui\view\json\JsonResponse;
use n2n\impl\web\ui\view\jhtml\JhtmlJsonResponse;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\impl\web\ui\view\jhtml\JhtmlRedirect;
use n2n\impl\web\ui\view\jhtml\JhtmlExec;
use rocket\spec\ei\manage\util\model\EiJhtmlEventInfo;

class RocketJhtmlResponse extends BufferedPayload {
	private $jsonResponse;

	private function __construct(array $attrs) {
		$this->jsonResponse = new JsonResponse(array(JhtmlJsonResponse::ADDITIONAL_KEY => $attrs));
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\http\payload\BufferedPayload::getBufferedContents()
	 */
	public function getBufferedContents(): string {
		return $this->jsonResponse->getBufferedContents();
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\http\payload\Payload::prepareForResponse()
	 */
	public function prepareForResponse(\n2n\web\http\Response $response) {
		$this->jsonResponse->prepareForResponse($response);
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\http\payload\Payload::toKownPayloadString()
	 */
	public function toKownPayloadString(): string {
		return $this->jsonResponse->toKownPayloadString();
	}

	const ATTR_EI_EVENT = 'rocketEvent';
	const ATTR_MODIFICATIONS = 'modifications';

	const ATTR_EXEC_CONFIG = 'execConfig';

	/**
	 * @param string $fallbackUrl
	 * @param EiJhtmlEventInfo $eventInfo
	 * @param JhtmlExec $jhtmlExec
	 * @return BufferedPayload
	 */
	public static function redirectBack(string $fallbackUrl, EiJhtmlEventInfo $eventInfo = null, 
			JhtmlExec $jhtmlExec = null) {
		$attrs = array();

		if ($eventInfo !== null) {
			$attrs[self::ATTR_EI_EVENT] = $eventInfo->toAttrs();
		}

		return JhtmlRedirect::back($fallbackUrl, $jhtmlExec, $attrs);
	}

	/**
	 * @param string $fallbackUrl
	 * @param EiJhtmlEventInfo $ajahEventInfo
	 * @param JhtmlExec $jhtmlExec
	 * @return BufferedPayload
	 */
	public static function redirectToReferer(string $fallbackUrl, EiJhtmlEventInfo $ajahEventInfo = null,
            JhtmlExec $jhtmlExec = null) {
        $attrs = array();
        
        if ($ajahEventInfo !== null) {
            $attrs[self::ATTR_EI_EVENT] = $ajahEventInfo->toAttrs();
        }
        
        return JhtmlRedirect::referer($fallbackUrl, $jhtmlExec, $attrs);
	}
	
	/**
	 * @param string $url
	 * @param EiJhtmlEventInfo $ajahEventInfo
	 * @param JhtmlExec $jhtmlExec
	 * @return BufferedPayload
	 */
	public static function redirect(string $url, EiJhtmlEventInfo $ajahEventInfo = null, JhtmlExec $jhtmlExec = null) {
		$attrs = array();
		
		if ($ajahEventInfo !== null) {
			$attrs[self::ATTR_EI_EVENT] = $ajahEventInfo->toAttrs();
		}
		
		return JhtmlRedirect::redirect($url, $jhtmlExec, $attrs);
	}
	
	/**
	 * @param EiJhtmlEventInfo $ajahEventInfo
	 * @return BufferedPayload
	 */
	public static function events(EiJhtmlEventInfo $ajahEventInfo) {
		return new RocketJhtmlResponse(array(
				self::ATTR_EI_EVENT => $ajahEventInfo === null ? array() : $ajahEventInfo->toAttrs()));
	}

	public static function view(HtmlView $htmlView, EiJhtmlEventInfo $jhtmlEventInfo = null) {
		$attrs = array();
		
		if ($jhtmlEventInfo !== null) {
			$attrs[self::ATTR_EI_EVENT] = $jhtmlEventInfo->toAttrs();
		}
		
		return new JhtmlJsonResponse($htmlView, $attrs);
	}
}