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

namespace rocket\tool\mail\ui;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\tool\xml\MailItem;
use n2n\web\ui\Raw;

class MailHtmlBuilder {
	
	private $view;
	
	public function __construct(HtmlView $view) {
		$this->view = $view;
	}
	
	public function getMessage(MailItem $mailItem) {
		return new Raw(preg_replace("/((http:\/\/)|(www\.)|(http:\/\/www.))(([^\s<]{4,68})[^\s<]*)/i",'<a href="http://$3$5" target="_blank">$3$5</a>', $mailItem->getMessage()));
	}
	
	public function message(MailItem $mailItem) {
		$this->view->out($this->getMessage($mailItem));
	}
}