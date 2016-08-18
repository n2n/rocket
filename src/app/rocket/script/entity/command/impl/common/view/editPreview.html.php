<?php 
	use rocket\script\entity\command\impl\common\model\EditModel;
	use rocket\script\entity\manage\ScriptHtmlBuilder;
	use rocket\script\entity\manage\EntryViewInfo;

	$detailModel = $view->params['commandEditEntryModel'];
	$view->assert($detailModel instanceof EditModel);
	
	$entryCommandViewModel = $view->params['entryViewInfo'];
	$view->assert($entryCommandViewModel instanceof EntryViewInfo);
 
	$entryHtml = new ScriptHtmlBuilder($view, $entryCommandViewModel->getScriptState());
	
	$view->useTemplate('core\view\template.html',
			array('title' => $entryCommandViewModel->getTitle(), 'tmplMode' => 'rocket-preview'));
	$html->addJs('js/preview.js');
	
	$currentHistoryDraft = $detailModel->getScriptSelection()->getDraft();
?>
<!-- 
<div class="rocket-select-view-toolbar">
	<input type="text" id="rocket-preview-draft-name-input"/>

	<?php if ($detailModel->isTranslatable()): ?>
		<?php $view->import('script\entity\command\impl\common\view\inc\langNav.html', array('entryViewInfo' => $entryCommandViewModel)) ?>
	<?php endif ?>
	
	<?php if ($entryCommandViewModel->hasPreviewTypeNav()): ?>
		<?php $view->import('script\entity\command\impl\common\view\inc\previewTypeNav.html', array('entryViewInfo' => $entryCommandViewModel)) ?>
	<?php endif ?>
</div>
 -->
 
<div class="rocket-panel">
	<h3>Detail</h3>
	<div class="rocket-edit-content rocket-preview-wrapper">
		<div id="rocket-preview-messages"></div>
		<iframe src="<?php $html->esc($view->params['iframeSrc']) ?>" id="rocket-preview-content"></iframe>
	</div>
	
	<div id="rocket-page-controls">
		<ul>
			<li>
				<button type="button" id="rocket-preview-save-command"
						data-rocket-confirm-msg="<?php $html->l10nText('script_cmd_edit_publish_draft_confirm_message') ?>"
						data-rocket-confirm-ok-label="<?php $html->l10nText('common_yes_label') ?>"
						data-rocket-confirm-cancel-label="<?php $html->l10nText('common_no_label') ?>">
					<?php $html->l10nText('common_save_label') ?>
				</button>
			</li>
					
			<li>
				<?php $html->link($entryCommandViewModel->getCancelPath($request), $view->getL10nText('common_cancel_label')) ?>
			</li>
		</ul>
	
		<?php if ($entryCommandViewModel->hasPreviewSwitch()): ?>
			<?php $view->import('script\entity\command\impl\common\view\inc\previewSwitch.html', array('entryViewInfo' => $entryCommandViewModel)) ?>
		<?php endif ?>
	</div>
</div>

<?php if ($detailModel->isDraftable()): ?>
	<?php $view->panelStart('additional') ?>
		<?php $view->import('script\entity\command\impl\common\view\inc\historyNav.html', array('entryViewInfo' => $entryCommandViewModel)) ?>
	<?php $view->panelEnd() ?>
<?php endif ?>