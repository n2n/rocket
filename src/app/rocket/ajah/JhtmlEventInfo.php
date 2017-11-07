<?php
namespace rocket\ajah;

class JhtmlEventInfo {
	private $resfreshMod;
	private $eventMap = array();

	public function groupChanged(string $groupId) {
		$this->eventMap[$groupId] = RocketJhtmlResponse::MOD_TYPE_CHANGED;
	}

	/**
	 * @param string $typeId
	 * @param string $entryId
	 * @return \rocket\ajah\JhtmlEventInfo
	 */
	public function itemChanged(string $typeId, string $entryId) {
		$this->item($typeId, $entryId, RocketJhtmlResponse::MOD_TYPE_CHANGED);
		return $this;
	}

	/**
	 * @param string $typeId
	 * @param string $entryId
	 * @return \rocket\ajah\JhtmlEventInfo
	 */
	public function itemRemoved(string $typeId, string $entryId) {
		$this->item($typeId, $entryId, RocketJhtmlResponse::MOD_TYPE_REMOVED);
		return $this;
	}

	/**
	 * @param string $typeId
	 * @param string $entryId
	 * @param string $modType
	 */
	public function item(string $typeId, string $entryId, string $modType) {
		if (!isset($this->eventMap[$typeId])) {
			$this->eventMap[$typeId] = array();
		} else if ($this->eventMap[$typeId] == RocketJhtmlResponse::MOD_TYPE_CHANGED) {
			return;
		}

		$this->eventMap[$typeId][$entryId] = $modType;
		return $this;
	}

	public function toAttrs(): array {
		return $this->eventMap;
	}
}
