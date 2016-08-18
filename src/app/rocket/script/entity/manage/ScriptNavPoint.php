<?php
namespace rocket\script\entity\manage;

use n2n\l10n\Locale;

class ScriptNavPoint {
	private $id;
	private $draftId;
	private $translationLocale;
	private $previewType;
	
	public function __construct($id, $draftId = null, Locale $translationLocale = null, $previewType = null) {
		$this->id = $id;
		$this->draftId = $draftId;
		$this->translationLocale = $translationLocale;
		$this->previewType = $previewType;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getDraftId() {
		return $this->draftId;
	}
	
	public function getTranslationLocale() {
		return $this->translationLocale;
	}
	
	public function getPreviewType() {
		return $this->previewType;
	}
	/**
	 * @param string $removeDraftId
	 * @param string $removeTranslationLocale
	 * @param string $removePreviewType
	 * @return \rocket\script\entity\manage\ScriptNavPoint
	 */
	public function copy($removeDraftId = false, $removeTranslationLocale = false, $removePreviewType = false) {
		return new ScriptNavPoint($this->id, 
				($removeDraftId ? null : $this->draftId), 
				($removeTranslationLocale ? null : $this->translationLocale),
				($removePreviewType ? null : $this->previewType));
	}
}