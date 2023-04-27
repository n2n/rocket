<?php

namespace rocket\ei\util\si;

use rocket\ei\util\EiuAnalyst;

class EiuSiFactory {

	function __construct(private EiuAnalyst $eiuAnalyst) {
	}

	function newBulkyEntryEiGui(): EifBulkyEntrySiGui {
		return new EifBulkyEntrySiGui($this->eiuAnalyst);
	}

}