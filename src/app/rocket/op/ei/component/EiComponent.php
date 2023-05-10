<?php

namespace rocket\op\ei\component;

interface EiComponent extends \Stringable {

	function getNature(): EiComponentNature;
}