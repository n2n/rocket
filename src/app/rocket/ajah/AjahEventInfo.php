<?php
namespace rocket\ajah;

class AjahEventInfo {
	private $resfreshMod;
	private $eventMap = array();

	public function groupChanged(string $groupId) {
		$this->eventMap[$groupId] = RocketAjahResponse::MOD_TYPE_CHANGED;
	}

	/**
	 * @param string $typeId
	 * @param string $entryId
	 * @return \rocket\spec\ei\manage\util\model\AjahModInfoAdapter
	 */
	public function itemChanged(string $typeId, string $entryId) {
		$this->item($typeId, $entryId, RocketAjahResponse::MOD_TYPE_CHANGED);
		return $this;
	}

	/**
	 * @param string $typeId
	 * @param string $entryId
	 * @return \rocket\spec\ei\manage\util\model\AjahModInfoAdapter
	 */
	public function itemRemoved(string $typeId, string $entryId) {
		$this->item($typeId, $entryId, RocketAjahResponse::MOD_TYPE_REMOVED);
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
		} else if ($this->eventMap[$typeId] == RocketAjahResponse::MOD_TYPE_CHANGED) {
			return;
		}

		$this->eventMap[$typeId][$entryId] = $modType;
		return $this;
	}

	public function toAttrs(): array {
		return $this->eventMap;
	}
}
