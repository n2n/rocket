<?php

namespace rocket\op\ei\manage\gui;

use rocket\op\ei\manage\veto\EiLifecycleMonitor;

class EiGuiCallResponse {

	function __construct(private EiLifecycleMonitor $eiLifecycleMonitor) {

	}
}