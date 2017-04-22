<?php
namespace rocket\spec\ei\manage\veto;

use rocket\spec\ei\manage\EiObject;
use rocket\spec\ei\manage\veto\VetoableRemoveQueue;
use n2n\util\ex\IllegalStateException;
use n2n\l10n\Message;

class VetoableRemoveAction {
	private $eiObject;
	private $vetoableRemoveQueue;
	private $approved = null;
	private $vetoReasonMessage = null;
	private $whenApprovedClosures = array();
	
	public function __construct(EiObject $eiObject, VetoableRemoveQueue $vetoableRemoveQueue) {
		$this->eiObject = $eiObject;
		$this->vetoableRemoveQueue = $vetoableRemoveQueue;
	}
	
	public function getQueue() {
		return $this->vetoableRemoveQueue;
	}
	
	public function getEiObject() {
		return $this->eiObject;
	}
	
	public function isInitialized() {
		return $this->approved !== null;
	}
	
	public function prevent(Message $reasonMessage) {
		$this->approved = false;
		$this->vetoReasonMessage = $reasonMessage;
	}
	
	public function approve() {
		$this->approved = true;
		$this->vetoReasonMessage = null;
		
		foreach ($this->whenApprovedClosures as $whenApprovedClosure) {
			$whenApprovedClosure();
		}
		$this->whenApprovedClosures = array();
	}
	
	public function hasVeto(): bool {
		return null !== $this->vetoReasonMessage;
	}
	
	public function getReasonMessage() {
		if ($this->vetoReasonMessage !== null) {
			return $this->vetoReasonMessage;
		}
		
		throw new IllegalStateException('Remove action was not vetoed.');
	}
	
	public function executeWhenApproved(\Closure $whenApprovedClosure) {
		$this->whenApprovedClosures[] = $whenApprovedClosure;
	}
}