<?php
namespace rocket\tool\backup\controller;

use n2n\batch\NewDayListener;

use rocket\tool\backup\BackupManager;
use n2n\http\ControllerAdapter;
use n2n\util\DateUtils;
use n2n\util\DateParseException;

class BackupBatchController extends ControllerAdapter implements NewDayListener {
	const SUFFIX_FILE_NAME = 'full-daily.sql';
	
	public function onNewDay() {
		BackupManager::createBackup(implode('-', array(BackupManager::PREFIX_FILE_NAME, date(BackupManager::DATE_TIME_FORMAT), self::SUFFIX_FILE_NAME)));
		$this->cleanUpBackupDir();
	}
	
	private function cleanUpBackupDir() {
		$today = new \DateTime();
		$backupIndex = array();
		$children = BackupManager::getBackupDir()->getChildren();
		foreach (array_reverse(BackupManager::getBackupDir()->getChildren()) as $file) {
			//Just Regard automatically created Backups
			if (strpos($file, self::SUFFIX_FILE_NAME) === false) continue;
			$matches = array();
			if (!preg_match('/\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}/', (string) $file, $matches)) continue;
			try {
				$dateCreated = DateUtils::createDateTimeFromFormat(BackupManager::DATE_TIME_FORMAT, reset($matches));
				$diff = $today->diff($dateCreated);
				if (!$diff->invert) continue;
				$delete = false;
				if ($diff->y > 0) {
					if (!isset($backupIndex[$dateCreated->format('Y-M')])) {
						$backupIndex[$dateCreated->format('Y-M')] = true;
						continue;
					}
					$delete = true;
				}
				if (!$delete && $diff->m > 0) {
					if ($dateCreated->format('w') === '1' && !isset($backupIndex[$dateCreated->format('Y-M-d')])) {
						$backupIndex[$dateCreated->format('Y-M-d')] = true;
						continue;
					}
					$delete = true;
				}
				if ($delete) {
					BackupManager::deleteBackups($file->getName());
				}
			} catch (DateParseException $e) {
				continue;
			} 
		}
	}
}