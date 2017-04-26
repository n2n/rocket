<?php
namespace rocket\ajah;

use rocket\spec\ei\manage\util\model\EiAjahEventInfo;

class AjahEvent {

	public static function common() {
		return new AjahEventInfo();
	}

	public static function ei() {
		return new EiAjahEventInfo();
	}
}