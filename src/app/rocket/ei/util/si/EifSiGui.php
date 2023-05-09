<?php

namespace rocket\ei\util\si;

use rocket\si\content\SiGui;
use n2n\util\uri\Url;

interface EifSiGui {

	function toSiGui(Url $zoneApiUrl = null): SiGui;
}