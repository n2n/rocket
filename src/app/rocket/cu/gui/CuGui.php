<?php

namespace rocket\cu\gui;

use rocket\si\content\SiGui;
use n2n\util\uri\Url;

interface CuGui {

	function toSiGui(?Url $zoneApiUrl): SiGui;
}