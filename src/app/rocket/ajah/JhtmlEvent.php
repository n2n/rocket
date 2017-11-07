<?php
namespace rocket\ajah;

use rocket\spec\ei\manage\util\model\EiJhtmlEventInfo;

class JhtmlEvent {

	public static function common() {
		return new JhtmlEventInfo();
	}

	public static function ei() {
		return new EiJhtmlEventInfo();
	}
}