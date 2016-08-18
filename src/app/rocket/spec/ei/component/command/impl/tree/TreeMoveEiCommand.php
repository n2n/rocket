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
namespace rocket\spec\ei\component\command\impl\tree;

use rocket\spec\ei\manage\EiState;
use n2n\web\ui\view\impl\html\HtmlView;
use n2n\l10n\DynamicTextCollection;
use rocket\spec\ei\component\command\impl\tree\controller\TreeMoveController;
use n2n\l10n\N2nLocale;
use rocket\spec\ei\manage\control\EntryControlComponent;
use rocket\spec\ei\manage\control\ControlButton;
use rocket\spec\ei\manage\control\IconType;
use rocket\spec\ei\component\command\impl\IndependentEiCommandAdapter;
use rocket\spec\ei\manage\mapping\EiMapping;
use rocket\spec\ei\manage\model\EntryGuiModel;
use rocket\spec\ei\manage\control\HrefControl;
use rocket\core\model\Rocket;

class TreeMoveEiCommand extends IndependentEiCommandAdapter implements EntryControlComponent {
	const ID_BASE = 'tree-move';
	const CONTROL_INSERT_BEFORE_KEY = 'insertBefore';
	const CONTROL_INSERT_AFTER_KEY = 'insertAfter';
	
	public function getIdBase() {
		return self::ID_BASE;
	}
	
	public function getOverviewPathExt() {
		return $this->getId();
	}
	
	public function getTypeName(): string {
		return 'Tree Move';
	}
	
	public function lookupController(EiState $eiState) {
		$controller = new TreeMoveController($eiState);
		$eiState->getN2nContext()->magicInit($controller);
		return $controller;
	}
	
	public function createEntryHrefControls(EntryGuiModel $entryGuiModel, EiState $eiState, HtmlView $view): array {
		$eiMapping = $entryGuiModel->getEiMapping();
		$httpContext = $view->getHttpContext();
		$dtc = new DynamicTextCollection('rocket', $view->getRequest()->getN2nLocale());
	
		if (!$entryGuiModel->getEiSelectionGui()->isViewModeOverview()) return array();
	
		$view->getHtmlBuilder()->meta()->addJs('js/script/impl/order.js', Rocket::NS);
	
		return array(
				self::CONTROL_INSERT_BEFORE_KEY => new HrefControl(
						$httpContext->getControllerContextPath($eiState->getControllerContext())
								->ext($this->getId(), 'before', $eiMapping->getIdRep())
								->toUrl(array('refPath' => (string) $eiState->getCurrentUrl($httpContext))),
						new ControlButton($dtc->translate('ei_impl_tree_insert_before_label'),
								$dtc->translate('ei_impl_tree_insert_after_tooltip'),
								true, ControlButton::TYPE_INFO, IconType::ICON_ARROW_UP, array('class' => 'rocket-order-before-cmd'))),
				self::CONTROL_INSERT_AFTER_KEY => new HrefControl(
						$httpContext->getControllerContextPath($eiState->getControllerContext())
								->ext($this->getId(), 'after', $eiMapping->getIdRep())
								->toUrl(array('refPath' => (string) $eiState->getCurrentUrl($httpContext))),
						new ControlButton($dtc->translate('ei_impl_tree_insert_after_label'),
								$dtc->translate('ei_impl_tree_insert_after_tooltip'),
								true, ControlButton::TYPE_INFO, IconType::ICON_ARROW_DOWN, array('class' => 'rocket-order-after-cmd'))));
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\ei\manage\control\EntryControlComponent::getEntryControlOptions()
	 */
	public function getEntryControlOptions(\n2n\l10n\N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_INSERT_BEFORE_KEY => $dtc->translate('ei_impl_tree_insert_before_label'),
				self::CONTROL_INSERT_AFTER_KEY => $dtc->translate('ei_impl_tree_insert_after_label'));
	}
}
