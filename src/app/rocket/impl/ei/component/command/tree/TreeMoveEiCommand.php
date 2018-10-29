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
namespace rocket\impl\ei\component\command\tree;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\l10n\DynamicTextCollection;
use rocket\impl\ei\component\command\tree\controller\TreeMoveController;
use rocket\ei\component\command\control\EntryControlComponent;
use rocket\ei\manage\control\ControlButton;
use rocket\ei\manage\control\IconType;
use rocket\impl\ei\component\command\IndependentEiCommandAdapter;
use rocket\ei\manage\control\HrefControl;
use rocket\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use n2n\core\container\N2nContext;
use n2n\l10n\N2nLocale;

class TreeMoveEiCommand extends IndependentEiCommandAdapter implements EntryControlComponent {
	const ID_BASE = 'tree-move';
	const CONTROL_INSERT_BEFORE_KEY = 'insertBefore';
	const CONTROL_INSERT_AFTER_KEY = 'insertAfter';
	const CONTROL_INSERT_CHILD_KEY = 'insertChild';
	
	public function getIdBase(): ?string {
		return self::ID_BASE;
	}
	
	public function getOverviewPathExt() {
		return $this->getId();
	}
	
	public function getTypeName(): string {
		return 'Tree Move';
	}
	
	public function lookupController(Eiu $eiu): Controller {
		return $eiu->lookup(TreeMoveController::class);
	}
	
	public function createEntryControls(Eiu $eiu, HtmlView $view): array {
		$httpContext = $view->getHttpContext();
		$dtc = new DynamicTextCollection('rocket', $view->getRequest()->getN2nLocale());
	
		if (!$eiu->entryGui()->isCompact()) {
			return array();
		}
	
		$eiFrame = $eiu->frame()->getEiFrame();
		$eiEntry = $eiu->entry()->getEiEntry();
	
		return array(
				self::CONTROL_INSERT_BEFORE_KEY => new HrefControl(
						$httpContext->getControllerContextPath($eiFrame->getControllerContext())
								->ext($this->getId(), 'before', $eiEntry->getPid())
								->toUrl(array('refPath' => (string) $eiFrame->getCurrentUrl($httpContext))),
						new ControlButton($dtc->translate('ei_impl_tree_insert_before_label'),
								$dtc->translate('ei_impl_tree_insert_after_tooltip'),
								true, ControlButton::TYPE_INFO, IconType::ICON_CARET_UP, array('class' => 'rocket-impl-insert-before'), false, false)),
				self::CONTROL_INSERT_AFTER_KEY => new HrefControl(
						$httpContext->getControllerContextPath($eiFrame->getControllerContext())
								->ext($this->getId(), 'after', $eiEntry->getPid())
								->toUrl(array('refPath' => (string) $eiFrame->getCurrentUrl($httpContext))),
						new ControlButton($dtc->translate('ei_impl_tree_insert_after_label'),
								$dtc->translate('ei_impl_tree_insert_after_tooltip'),
								true, ControlButton::TYPE_INFO, IconType::ICON_CARET_DOWN, array('class' => 'rocket-impl-insert-after'), false, false)),
				self::CONTROL_INSERT_CHILD_KEY => new HrefControl(
						$httpContext->getControllerContextPath($eiFrame->getControllerContext())
								->ext($this->getId(), 'child', $eiEntry->getPid())
								->toUrl(array('refPath' => (string) $eiFrame->getCurrentUrl($httpContext))),
						new ControlButton($dtc->translate('ei_impl_tree_insert_child_label'),
								$dtc->translate('ei_impl_tree_insert_child_tooltip'),
								true, ControlButton::TYPE_INFO, IconType::ICON_CARET_RIGHT, array('class' => 'rocket-impl-insert-as-child'), false, false)));
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\component\command\control\EntryControlComponent::getEntryControlOptions()
	 */
	public function getEntryControlOptions(N2nContext $n2nContext, N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_INSERT_BEFORE_KEY => $dtc->translate('ei_impl_tree_insert_before_label'),
				self::CONTROL_INSERT_AFTER_KEY => $dtc->translate('ei_impl_tree_insert_after_label'));
	}
}
