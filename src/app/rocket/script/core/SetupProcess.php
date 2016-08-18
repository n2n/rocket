<?php
namespace rocket\script\core;

use rocket\script\entity\EntityScript;
use rocket\script\entity\ScriptElement;
use rocket\script\entity\field\InvalidScriptElementConfigurationException;

class SetupProcess {
	private $scriptManager;
	private $entityScript;
	private $failEs = null;
	
	public function __construct(ScriptManager $scriptManager, EntityScript $entityScript) {
		$this->scriptManager = $scriptManager;
		$this->entityScript = $entityScript;
	}
	/**
	 * @return \rocket\script\core\ScriptManager
	 */
	public function getScriptManager() {
		return $this->scriptManager;
	}
	/**
	 * @return EntityScript
	 */
	public function getEntityScript() {
		return $this->entityScript;
	}
		
	public function failedE(ScriptElement $scriptElement, \Exception $e) {
		$this->failed($scriptElement, $e->getMessage(), $e);
	}
	
	public function failed(ScriptElement $scriptElement, $reason = null, \Exception $previous = null) {
		$this->failEs[] = $this->createInvalidScriptElementConfigurationException($scriptElement, $reason, $previous);
	}
	
	public function hasFailed() {
		return (boolean) sizeof($this->failEs);
	}
	
	public function getFailE() {
		if (!$this->hasFailed()) return null;
		return reset($this->failEs);
	}
	
	public function getFailEs() {
		return $this->failEs;
	}
	
	private function createInvalidScriptElementConfigurationException(ScriptElement $scriptElement, $reason = null, \Exception $previous = null) {
		return new InvalidScriptElementConfigurationException(get_class($scriptElement) . ' with id \'' . $scriptElement->getId() . '\' (EntityScript: \''
						. $this->entityScript->getId() . '\') is invalid configurated.' . (isset($reason) ? ' Reason: ' . $reason : ''),
				0, $previous);
	}
}