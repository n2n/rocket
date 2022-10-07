<?php

namespace rocket\spec\result;

use rocket\ei\component\command\EiCmdNature;
use rocket\ei\EiCmdPath;
use rocket\spec\TypePath;

class EiCommandError {
	private $eiTypePath;
	private $eiCmdPath;
	private $eiCmd;
	private $t;
	
	public function __construct(TypePath $eiTypePath, EiCmdPath $eiCmdPath, \Throwable $t,
			EiCmdNature $eiCmd = null) {
		$this->eiTypePath = $eiTypePath;
		$this->eiCmdPath = $eiCmdPath;
		$this->eiCmd = $eiCmd;
		$this->t = $t;
	}
	
	public function getEiCmdPath() {
		return $this->eiCmdPath;
	}
	
	public function getEiCommand() {
		return $this->eiCmd;
	}
	
	public function getEiTypePath() {
		return $this->eiTypePath;
	}
	
	/**
	 * @return \Throwable
	 */
	public function getThrowable() {
		return $this->t;
	}
	
	public static function fromEiCommand(EiCmdNature $eiCmd, \Throwable $t) {
		$wrapper = $eiCmd->getWrapper();
		return new EiCommandError($wrapper->getEiCommandCollection()->getEiMask()->getEiTypePath(),
				$wrapper->getEiCmdPath(), $t, $eiCmd);
	}
}