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
namespace rocket\impl\ei\component\prop\relation\model;

use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\gui\GuiFieldDisplayable;
use rocket\ei\manage\gui\GuiFieldEditable;
use n2n\util\ex\IllegalStateException;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\gui\GuiField;
use rocket\core\model\Rocket;
use rocket\ei\component\prop\EiProp;
use rocket\ei\util\Eiu;
use n2n\l10n\N2nLocale;
use rocket\ei\EiPropPath;

class ToManySelectGuiField implements GuiField, GuiFieldDisplayable {
	private $eiProp;
	private $eiu;
	private $targetEiFrame;
	private $editable;
	private $toOneMag;
	
	public function __construct(EiProp $eiProp, Eiu $eiu, EiFrame $targetEiFrame, 
			GuiFieldEditable $editable = null) {
		$this->eiProp = $eiProp;
		$this->eiu = $eiu;
		$this->targetEiFrame = $targetEiFrame;
		$this->editable = $editable;
	}
	
	public function isReadOnly(): bool {
		return $this->editable === null;
	}
	
	public function getDisplayItemType(): string {
		return null;
	}
	
	/**
	 * @return string
	 */
	public function getUiOutputLabel(N2nLocale $n2nLocale): string {
		return $this->eiProp->getLabelLstr()->t($n2nLocale);
	}
	
	/**
	 * @return array
	 */
	public function getHtmlContainerAttrs(): array {
// 		if ($this->eiu->entryGui()->isBulky()) {
// 			return array('class' => 'rocket-block');
// 		}
		
		return array();
	}
	
	public function createUiComponent(HtmlView $view) {
		if ($this->eiu->entry()->getEiEntry()->isNew()) {
			return null;
		}
		
		$criteria = $this->targetEiFrame->createCriteria('e');
		$criteria->select('COUNT(e)');
		$num = $criteria->toQuery()->fetchSingle();

		$targetEiuFrame = (new Eiu($this->targetEiFrame))->frame();
		if ($num == 1) {
			$label = $num . ' ' . $targetEiuFrame->getGenericLabel();
		} else {
			$label = $num . ' ' . $targetEiuFrame->getGenericPluralLabel();
		}

// 		if (null !== ($relation = $this->eiu->frame()->getEiFrame()
// 				->getEiRelation(EiPropPath::from($this->eiProp)))) {
// 			return $this->createUiLink($relation->getEiFrame(), $label, $view);
// 		}

		return $this->createUiLink($this->targetEiFrame, $label, $view);
	}

	private function createUiLink(EiFrame $targetEiFrame, $label, HtmlView $view) {
		$html = $view->getHtmlBuilder();

		if (!$targetEiFrame->isOverviewUrlAvailable()) return $html->getEsc($label);

		return $html->getLink($targetEiFrame->getOverviewUrl($view->getHttpContext()), $label, array('data-jhtml' => 'true'));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::getDisplayable()
	 */
	public function getDisplayable(): GuiFieldDisplayable {
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiField::createEditable()
	 */
	public function getEditable(): GuiFieldEditable {
		if ($this->editable !== null) {
			return $this->editable;
		}
		
		throw new IllegalStateException('GuiField read only.');
	}
}
