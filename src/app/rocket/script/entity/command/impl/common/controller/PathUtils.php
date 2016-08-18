<?php
namespace rocket\script\entity\command\impl\common\controller;

use rocket\script\entity\manage\ScriptNavPoint;
use n2n\l10n\Locale;
use n2n\http\Path;

class PathUtils {


	public static function createPathExtFromScriptNavPoint($commandId, ScriptNavPoint $scriptNavPoint) {
		$objectId = $scriptNavPoint->getId();
		$draftId = $scriptNavPoint->getDraftId();
		$translationLocale = $scriptNavPoint->getTranslationLocale();
		$previewType = $scriptNavPoint->getPreviewType();
	
		if (isset($draftId)) {
			return self::createDraftPathExt($commandId, $objectId, $draftId, $translationLocale, $previewType);
		}
	
		return self::createPathExt($commandId, $objectId, $translationLocale, $previewType);
	}
	
	
	public static function createPathExt($commandId, $objectId, Locale $translationLocale = null, $previewType = null) {
		$pathParts = array($commandId);
		if (isset($previewType)) {
			$pathParts[] = 'preview';
			$pathParts[] = (string) $previewType;
		}
		
		$pathParts[] = $objectId;
		
		if (isset($translationLocale)) {
			$pathParts[] = $translationLocale->toHttpId();
		}
		
		return new Path($pathParts);
	}
	
	public static function createDraftPathExt($commandId, $objectId, $draftId = null, Locale $translationLocale = null, $previewType = null) {
		$pathParts = array((string) $commandId);
		if (isset($previewType)) {
			$pathParts[] = 'draftpreview';
			$pathParts[] = (string) $previewType;
		} else {
			$pathParts[] = 'draft';
		}
		
		$pathParts[] = $objectId;
		$pathParts[] = $draftId;
		
		if (isset($translationLocale)) {
			$pathParts[] = $translationLocale->toHttpId();
		}
		
		return new Path($pathParts);
	}
	
// 	public static function createDetailPathExtFromScriptNavPoint($commandId, ScriptNavPoint $scriptNavPoint) {
// 		$objectId = $scriptNavPoint->getId();
// 		$draftId = $scriptNavPoint->getDraftId();
// 		$translationLocale = $scriptNavPoint->getTranslationLocale();
// 		$previewType = $scriptNavPoint->getPreviewType();
		
// 		if (isset($draftId)) {
// 			return self::createDraftDetailPathExt($commandId, $objectId, $draftId, $translationLocale, $previewType);
// 		}
		
// 		return self::createDetailPathExt($commandId, $objectId, $translationLocale, $previewType);
// 	}
	
// 	public static function createDetailPathExtFromScriptSelection($commandId, ScriptSelection $scriptSelection, $previewType) {
// 		$objectId = $scriptSelection->getId();
// 		$draftId = $scriptSelection->getDraftId();
// 		$translationLocale = $scriptSelection->getTranslationLocale();
	
// 		if (isset($draftId)) {
// 			return self::createDraftDetailPathExt($commandId, $objectId, $draftId, $translationLocale, $previewType);
// 		}
	
// 		return self::createDetailPathExt($commandId, $objectId, $translationLocale, $previewType);
// 	}
	
// 	public static function createDetailPathExtFromScriptState($commandId, ScriptState $scriptState, $includeDraft = false, 
// 			$includeTranslation = false, $includePreview = false) {
// 		$scriptSelection = $scriptState->getScriptSelection();
// 		$objectId = $scriptSelection->getId();
// 		$translationLocale = null;
// 		if ($scriptSelection->hasTranslation() && $includeTranslation) {
// 			$translationLocale = $scriptSelection->getTranslationLocale();
// 		}
		
// 		$previewType = $scriptState->getPreviewType();
		
// 		if ($scriptSelection->hasDraft() && $includeDraft) {
// 			return self::createDraftDetailPathExt($commandId, $objectId, $scriptSelection->getDraft()->getId(), $translationLocale, $previewType);
// 		} else {
// 			return self::createDetailPathExt($commandId, $objectId, $translationLocale, $previewType);
// 		}
// 	}
}