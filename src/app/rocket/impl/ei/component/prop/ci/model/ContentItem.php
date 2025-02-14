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
namespace rocket\impl\ei\component\prop\ci\model;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\persistence\orm\InheritanceType;
use n2n\reflection\annotation\AnnoInit;
use n2n\reflection\ObjectAdapter;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\annotation\AnnoInheritance;
use rocket\attribute\EiType;
use n2n\persistence\orm\attribute\Inheritance;
use n2n\persistence\orm\attribute\Table;
use rocket\attribute\EiPreset;
use n2n\util\ex\IllegalStateException;
use rocket\attribute\impl\EiPropDecimal;
use rocket\attribute\impl\EiPropString;
use rocket\attribute\impl\EiDefaultDisplay;
use rocket\ui\gui\ViewMode;

#[Table('rocket_content_item')]
#[Inheritance(InheritanceType::JOINED)]
#[EiType]
#[EiPreset(editProps: ['panel', 'orderIndex'])]
abstract class ContentItem extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('rocket_content_item'));
		$ai->c(new AnnoInheritance(InheritanceType::JOINED));
	}

	private $id;
	#[EiPropString(readOnly: false, mandatory: false)]
	#[EiDefaultDisplay()]
	private string $panel;
	private int $orderIndex;
// 	private $online;

	public function getId() {
		return $this->id;
	}

	public function getPanel() {
		return $this->panel ?? null;
	}

	public function setPanel(string $panel) {
		$this->panel = $panel;
	}

	public function getOrderIndex() {
		return $this->orderIndex ?? null;
	}

	public function setOrderIndex(int $orderIndex) {
		$this->orderIndex = $orderIndex;
	}

	public function isOnline() {
		return true;
	}

	public abstract function createUiComponent(HtmlView $view);
}
