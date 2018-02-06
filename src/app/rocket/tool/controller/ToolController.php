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
namespace rocket\tool\controller;

use rocket\tool\backup\controller\BackupController;

use n2n\web\http\controller\ControllerAdapter;
use n2n\l10n\MessageContainer;
use rocket\tool\mail\controller\MailCenterController;
use n2n\reflection\annotation\AnnoInit;
use rocket\core\model\RocketState;
use rocket\core\model\Breadcrumb;
use n2n\l10n\DynamicTextCollection;
use n2n\web\http\annotation\AnnoPath;
use n2n\web\http\ResponseCacheStore;
use n2n\web\ui\view\ViewCacheStore;

class ToolController extends ControllerAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->m('backupOverview',new AnnoPath(self::ACTION_BACKUP_OVERVIEW . '/params*:*'));
		$ai->m('mailCenter', new AnnoPath(self::ACTION_MAIL_CENTER . '/params*:*'));
		$ai->m('clearCache', new AnnoPath(self::ACTION_CLEAR_CACHE));
	}
	
	const ACTION_BACKUP_OVERVIEW = 'backup-overview';
	const ACTION_MAIL_CENTER = 'mail-center';
	const ACTION_CLEAR_CACHE = 'clear-cache';
	
	private $rocketState;
	private $dtc;
	private $request;
	
	private function _init(RocketState $rocketState, DynamicTextCollection $dtc) {
		$this->rocketState = $rocketState;
		$this->dtc = $dtc;
	}
	
	public function index() {
		$this->applyBreadCrumbs();
		$this->forward('..\view\toolsOverview.html');
	}
	
	public function backupOverview(array $params = null) {
		$this->applyBreadCrumbs(self::ACTION_BACKUP_OVERVIEW);
		$this->delegate(new BackupController());
	}
	
	public function mailCenter(MailCenterController $mailCenterController, array $params = null) {
		$this->applyBreadCrumbs(self::ACTION_MAIL_CENTER);
		
		$this->delegate($mailCenterController);
	}
	
	public function clearCache(MessageContainer $mc, ResponseCacheStore $responseCacheStore = null, ViewCacheStore $viewCacheStore = null) {
		if ($responseCacheStore !== null) {
			$responseCacheStore->clear();
		}
		
		if ($viewCacheStore !== null) {
			$viewCacheStore->clear();
		}
				
		$mc->addInfoCode('tool_cache_cleared_info');
		$this->redirectToController();
	}
	
	private function applyBreadCrumbs($action = null) {
		$this->rocketState->addBreadcrumb(
				new Breadcrumb($this->getHttpContext()->getControllerContextPath($this->getControllerContext()),
						$this->dtc->translate('tool_title')));
		switch ($action) {
			case self::ACTION_MAIL_CENTER:
				$this->rocketState->addBreadcrumb(
						new Breadcrumb($this->getHttpContext()->getControllerContextPath(
								$this->getControllerContext())->ext($action),
								$this->dtc->translate('tool_mail_center_title')));
				break;
			case self::ACTION_BACKUP_OVERVIEW:
				$this->rocketState->addBreadcrumb(
						new Breadcrumb($this->getHttpContext()->getControllerContextPath(
								$this->getControllerContext())->ext($action),
								$this->dtc->translate('tool_backup_title')));
				break;
		}
	}
	
}
