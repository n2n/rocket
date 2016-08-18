<?php
namespace rocket\tool\backup;

use n2n\io\fs\File;
use n2n\io\FileResourceStream;
use n2n\core\VarStore;
use n2n\N2N;

class BackupManager {
	const PREFIX_FILE_NAME = 'backup';
	const SUFFIX_FILE_NAME = 'full-manual.sql';
	const DATE_TIME_FORMAT = 'Y-m-d-H-i-s';
	const MODULE_DIR = 'rocket';
	
	public static function createBackup($fileName = null) {
		$backuper = N2N::getDbhPool()->getDbh()->getMetaData()->getDatabase()->createBackuper();
		$backuper->setBackupDataEnabled(true);
		$backuper->setReplaceTableEnabled(true);
		$backuper->setOutputStream(new FileResourceStream(self::generateFile($fileName), 'w'));
		$backuper->start();
	}
	
	public static function deleteBackups($pattern) {
		foreach (self::getBackupDir()->getChildren($pattern) as $abstractPath) {
			$abstractPath->delete();
		}
	}
	
	public static function requestBackupFile($fileName) {
		return new File(N2N::getVarStore()->requestFilePath(VarStore::CATEGORY_BAK, self::MODULE_DIR, null,
				$fileName));
	}
	/**
	 * @return \n2n\io\fs\AbstractPath
	 */
	public static function getBackupDir() {
		return N2N::getVarStore()->requestDirectoryPath(VarStore::CATEGORY_BAK, self::MODULE_DIR, null, true);
	}
	
	private static function generateFile($fileName = null) {
		if (is_null($fileName)) {
			$fileName = implode('-', array(self::PREFIX_FILE_NAME, date(self::DATE_TIME_FORMAT), self::SUFFIX_FILE_NAME));
		}
		return N2N::getVarStore()->requestFilePath(VarStore::CATEGORY_BAK, self::MODULE_DIR, null,
				$fileName, true, true);
	}
}