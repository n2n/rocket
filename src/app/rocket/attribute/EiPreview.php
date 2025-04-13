<?php

namespace rocket\attribute;

use rocket\op\ei\manage\frame\EiFrame;

#[\Attribute(\Attribute::TARGET_CLASS)]
class EiPreview {

	function __construct(public string $previewControllerLookupId) {

	}
}