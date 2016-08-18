<?php
namespace rocket\tool\controller;

use rocket\tool\backup\controller\BackupController;

use n2n\http\ControllerAdapter;
use n2n\ui\ViewFactory;
use n2n\core\MessageContainer;
use rocket\tool\mail\controller\MailCenterController;
use n2n\reflection\annotation\AnnotationSet;
use n2n\http\ControllerAnnotations;
use rocket\core\model\RocketState;
use rocket\core\model\Breadcrumb;
use n2n\core\DynamicTextCollection;

class ToolController extends ControllerAdapter {
	
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
	
	private static function _annotations(AnnotationSet $as) {
		$as->m('backupOverview', ControllerAnnotations::PATH_METHOD, array('pattern' => self::ACTION_BACKUP_OVERVIEW . '/params*:*'));
		$as->m('mailCenter', ControllerAnnotations::PATH_METHOD, array('pattern' => self::ACTION_MAIL_CENTER . '/params*:*') );
		$as->m('clearCache', ControllerAnnotations::PATH_METHOD, array('pattern' => self::ACTION_CLEAR_CACHE));
	}
	
	public function index() {
		$this->applyBreadCrumbs();
		$this->forward('tool\view\toolsOverview.html');
	}
	
	public function backupOverview(array $contextCmds, array $cmds, array $params) {
		array_push($contextCmds, array_shift($cmds));
		$backupController = new BackupController($this->getRequest(), $this->getResponse());
		$this->applyBreadCrumbs(self::ACTION_BACKUP_OVERVIEW);
		$backupController->execute($cmds, $contextCmds, $this->getN2nContext());
	}
	
	public function mailCenter(array $contextCmds, array $cmds, array $params) {
		array_push($contextCmds, array_shift($cmds));
		$mailCenterController = new MailCenterController($this->getRequest(), $this->getResponse());
		$this->applyBreadCrumbs(self::ACTION_MAIL_CENTER);
		$mailCenterController->execute($cmds, $contextCmds, $this->getN2nContext());
	}
	
	public function clearCache(MessageContainer $mc) {
		ViewFactory::getCacheStore()->clear();
		$mc->addInfoCode('tool_cache_cleared_info');
		$this->redirectToController();
	}
	
	private function applyBreadCrumbs($action = null) {
		$this->rocketState->addBreadcrumb(
				new Breadcrumb($this->getRequest()->getCurrentControllerContextPath(),
						$this->dtc->translate('tool_title')));
		switch ($action) {
			case self::ACTION_MAIL_CENTER:
				$this->rocketState->addBreadcrumb(
						new Breadcrumb($this->getRequest()->getCurrentControllerContextPath($action),
								$this->dtc->translate('tool_mail_center_title')));
				break;
			case self::ACTION_BACKUP_OVERVIEW:
				$this->rocketState->addBreadcrumb(
						new Breadcrumb($this->getRequest()->getCurrentControllerContextPath($action),
								$this->dtc->translate('tool_backup_title')));
				break;
		}
	}
	
}