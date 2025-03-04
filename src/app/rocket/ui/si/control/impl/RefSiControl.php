<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ui\si\control\impl;

use n2n\util\uri\Url;
use rocket\ui\si\control\SiControl;
use rocket\ui\si\control\SiButton;
use n2n\core\container\N2nContext;
use rocket\ui\si\api\response\SiCallResponse;
use n2n\util\ex\NotYetImplementedException;

class RefSiControl implements SiControl {
	private $url;
	private $button;
	private $newWindow;

	function __construct(Url $url, SiButton $button, bool $newWindow) {
		$this->url = $url;
		$this->button = $button;
		$this->newWindow = $newWindow;
	}
	
	function getType(): string {
		return 'ref';
	}
	
	function getData(): array {
		return [
			'url' => (string) $this->url,
			'button' => $this->button,
			'newWindow' => $this->newWindow
		];
	}

	function handleCall(N2nContext $n2nContext): SiCallResponse {
		throw new NotYetImplementedException();
	}
}
