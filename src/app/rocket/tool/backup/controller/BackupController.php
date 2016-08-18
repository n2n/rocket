<?php
namespace rocket\tool\backup\controller;

use rocket\tool\backup\BackupManager;

use n2n\http\ControllerAdapter;

class BackupController extends ControllerAdapter {
	
	public function index() {
		$this->forward('tool\backup\view\backupOverview.html', 
				array('files' => array_reverse(BackupManager::getBackupDir()->getChildren('*.sql'))));
	}
	
	public function doCreate() {
		BackupManager::createBackup();
		$this->redirectToController();
	}
	
	public function doDownload($fileName) {
		$this->getResponse()->send(BackupManager::requestBackupFile($fileName));
	}
	
	public function doDelete($pattern) {
		BackupManager::deleteBackups($pattern);
		$this->redirectToController();
	}
}