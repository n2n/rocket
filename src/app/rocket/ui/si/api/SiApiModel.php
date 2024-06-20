<?php

namespace rocket\ui\si\api;

use rocket\ui\si\content\SiEntry;
use rocket\ui\si\meta\SiMask;

interface SiApiModel {

	function lookupSiMask(string $maskId): ?SiMask;

	function lookupSiMaskControl(string $maskId): ?SiMask;

	function lookupSiEntry(string $maskId, string $entryId): ?SiEntry;

	function lookupSiEntryControl(string $maskId, string $entryId, string $controlName)


}