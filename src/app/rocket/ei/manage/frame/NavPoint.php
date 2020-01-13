<?php 
namespace rocket\ei\manage\frame;

use n2n\util\uri\Url;

class NavPoint {
	private $url;
	private $ref;
	
	function __construct(Url $url = null, bool $siref = true) {
		$this->url = $url;
		$this->ref = $siref;
	}
	
	/**
	 * @return boolean
	 */
	function isUrlComplete() {
		return $this->url !== null && (!$this->url->isRelative() || $this->url->getPath()->hasLeadingDelimiter());
	}
	
	/**
	 * @param Url $contextUrl
	 * @return \rocket\ei\manage\frame\NavPoint
	 */
	function complete(Url $contextUrl) {
		$this->url = $contextUrl->ext($this->url);
		return $this;
	}
	
	/**
	 * @return Url 
	 */
	function getUrl() {
		if ($this->isUrlComplete()) {
			return $this->url;
		}
	
		throw new IncompleteNavPointException('Incomplete url: ' . $this->url);
	}
	
	/**
	 * @param Url $urlExt
	 * @return \rocket\ei\manage\frame\NavPoint
	 */
	static function href(Url $url = null) {
		return new NavPoint($url, false);
	}
	
	/**
	 * @param Url $urlExt
	 * @return \rocket\ei\manage\frame\NavPoint
	 */
	static function siref(Url $url = null) {
		return new NavPoint($url, true);
	}
}