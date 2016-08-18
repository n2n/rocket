<?php
namespace rocket\tool\mail\controller;

use n2n\batch\NewMonthListener;
use n2n\http\ControllerAdapter;
use n2n\util\DateUtils;
use n2n\io\InvalidPathException;
use rocket\tool\mail\model\MailCenter;
use n2n\N2N;
use n2n\core\VarStore;
use n2n\log4php\appender\nn6\AdminMailCenter;
use rocket\tool\controller\ToolController;

class MailArchiveBatchController extends ControllerAdapter implements NewMonthListener {
	
	const FILE_NAME_PREFIX = 'mail';
	const FILE_NAME_PARTS_SEPERATOR = '-';
	const FILE_EXTENSION = 'xml';
	
	public function index() {
		$this->createMailArchive();
		$path = $this->getRequest()->getControllerContextPath(
				$this->getRequest()->getControllerContextByKey('rocket\core\controller\RocketController'),
				array('tools', ToolController::ACTION_MAIL_CENTER));
		$this->redirect($path);
	}
	
	public function onNewMonth() {
		$this->createMailArchive();
	}
	
	public static function dateToFileName(\DateTime $date, $index = null) {
		$nameParts = array(self::FILE_NAME_PREFIX, $date->format('Y'), $date->format('m'));
		if (null !== $index)  {
			$nameParts[] = $index;
		}
		return implode(self::FILE_NAME_PARTS_SEPERATOR, $nameParts) . '.' . self::FILE_EXTENSION;
	}
	/**
	 * @param string $fileName
	 * @return \DateTime
	 */
	public static function fileNameToDate($fileName) {
		$fileNameParts = explode(self::FILE_NAME_PARTS_SEPERATOR, self::removeFileExtension($fileName));
		if (count($fileNameParts) < 3) return null;
		return DateUtils::createDateTimeFromFormat('Ym',  $fileNameParts[1] . $fileNameParts[2]);
	}
	
	public static function fileNameToIndex($fileName) {
		$fileNameParts = explode(self::FILE_NAME_PARTS_SEPERATOR, self::removeFileExtension($fileName));
		if (count($fileNameParts) < 4) return null;
		return $fileNameParts[3];
	}
	
	public static function removeFileExtension($fileName) {
		return str_replace('.' . self::FILE_EXTENSION, '', $fileName);
	}
	
	private function createMailArchive() {
		$currentMailPath = MailCenter::requestMailLogFile(AdminMailCenter::DEFAULT_MAIL_FILE_NAME, false);
		if (!$currentMailPath->isFile()) return;
		
		$date = new \DateTime();
		$date->setDate($date->format('Y'), $date->format('m'), $date->format('d') - 1);
		$fileName = self::dateToFileName($date);
		for ($i = 1; ; $i++) {
			try {
				MailCenter::requestMailLogFile($fileName);
				$fileName = self::dateToFileName($date, $i);
			} catch (InvalidPathException $e){
				break;
			}
		}
		
		$archiveFilePath = N2N::getVarStore()->requestFilePath(VarStore::CATEGORY_LOG, N2N::N2N_NAMESPACE,
				AdminMailCenter::LOG_FOLDER, $fileName, true, true);
		$currentMailPath->copyFile($archiveFilePath, N2N::getAppConfig()->io()->getPrivateFilePermission());
		$currentMailPath->delete();
	}
}
