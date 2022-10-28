<?php

namespace rocket\ei\component;

interface EiComponent extends \Stringable {

	function getNature(): EiComponentNature;
}