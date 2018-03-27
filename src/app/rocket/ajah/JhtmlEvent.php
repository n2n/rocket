<?php
namespace rocket\ajah;

use rocket\ei\util\model\EiJhtmlEventInfo;

class JhtmlEvent {

	public static function common() {
		return new JhtmlEventInfo();
	}

	/**
	 * @return \rocket\ei\util\model\EiJhtmlEventInfo
	 */
	public static function ei() {
		return new EiJhtmlEventInfo();
	}
}