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
namespace rocket\impl\ei\component\prop\numeric\component;

use n2n\l10n\DynamicTextCollection;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\impl\ei\component\prop\numeric\OrderEiProp;
use rocket\ei\component\command\control\EntryControlComponent;
use rocket\ei\manage\control\ControlButton;
use rocket\ei\manage\control\IconType;
use rocket\impl\ei\component\command\EiCommandAdapter;
use rocket\ei\manage\control\HrefControl;
use rocket\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use n2n\core\container\N2nContext;
use n2n\l10n\N2nLocale;

class OrderEiCommand extends EiCommandAdapter implements EntryControlComponent {
	const ID_BASE = 'order';
	const CONTROL_INSERT_BEFORE_KEY = 'insertBefore';
	const CONTROL_INSERT_AFTER_KEY = 'insertAfter';
	
	private $orderEiProp;
		
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\EiComponentAdapter::getIdBase()
	 */
	public function getIdBase(): ?string {
		return self::ID_BASE;
	}
	
	/**
	 * @param OrderEiProp $orderEiProp
	 */
	public function setOrderEiProp(OrderEiProp $orderEiProp) {
		$this->orderEiProp = $orderEiProp;
	}
		
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\EiCommand::lookupController()
	 */
	public function lookupController(Eiu $eiu): Controller {
		$controller = $eiu->lookup(OrderController::class);
		$controller->setOrderEiProp($this->orderEiProp);
		return $controller;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\control\EntryControlComponent::createEntryControls()
	 */
	public function createEntryControls(Eiu $eiu, HtmlView $view): array {
		$httpContext = $view->getHttpContext();
		$dtc = new DynamicTextCollection('rocket', $view->getRequest()->getN2nLocale());

		if (!$eiu->entryGui()->isCompact()) return array();
		
		$eiEntry = $eiu->entry()->getEiEntry();
		$eiFrame = $eiu->frame()->getEiFrame();
		
		return array(
				self::CONTROL_INSERT_BEFORE_KEY => new HrefControl(
						$httpContext->getControllerContextPath($eiFrame->getControllerContext())
								->ext($this->getWrapper()->getEiCommandPath(), 'before', $eiEntry->getPid())
								->toUrl(array('refPath' => (string) $eiFrame->getCurrentUrl($httpContext))), 
						new ControlButton($dtc->translate('ei_impl_order_insert_before_label'), 
								$dtc->translate('ei_impl_order_insert_before_tooltip'),
								true, ControlButton::TYPE_INFO, IconType::ICON_CARET_UP, array('class' => 'rocket-impl-insert-before'), false, false)),
				self::CONTROL_INSERT_AFTER_KEY => new HrefControl(
						$httpContext->getControllerContextPath($eiFrame->getControllerContext())
								->ext($this->getWrapper()->getEiCommandPath(), 'after', $eiEntry->getPid())
								->toUrl(array('refPath' => (string) $eiFrame->getCurrentUrl($httpContext))),
						new ControlButton($dtc->translate('ei_impl_order_insert_after_label'), 
								$dtc->translate('ei_impl_order_insert_after_tooltip'),
								true, ControlButton::TYPE_INFO, IconType::ICON_CARET_DOWN, array('class' => 'rocket-impl-insert-after'), false, false)));
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\control\EntryControlComponent::getEntryControlOptions()
	 */
	public function getEntryControlOptions(N2nContext $n2nContext, N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_INSERT_BEFORE_KEY => $dtc->translate('ei_impl_order_insert_before_label'),
				self::CONTROL_INSERT_AFTER_KEY => $dtc->translate('ei_impl_order_insert_after_label'));
	}
}
