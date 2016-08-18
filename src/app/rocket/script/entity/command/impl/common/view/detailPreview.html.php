<?php
	use rocket\script\entity\manage\CommandEntryModel;
	use rocket\script\entity\manage\ScriptHtmlBuilder;
	use rocket\script\entity\manage\EntryViewInfo;
use rocket\script\entity\command\impl\common\model\EntryCommandViewModel;
use rocket\script\entity\command\impl\common\model\DetailModel;

	$detailModel = $view->getParam('detailModel');
	$view->assert($detailModel instanceof DetailModel);
	
	$entryCommandViewModel = $view->getParam('entryCommandViewModel');
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);
 
	$scriptHtml = new ScriptHtmlBuilder($view, $detailModel->getEntryModel());
	
	$view->useTemplate('core\view\template.html',
			array('title' => $entryCommandViewModel->getTitle(), 'tmplMode' => 'rocket-preview'));
	
?>

<div class="rocket-select-view-toolbar">
	<?php if ($entryCommandViewModel->isTranslationEnabled()): ?>
		<?php $view->import('script\entity\command\impl\common\view\inc\langNav.html', 
				array('entryCommandViewModel' => $entryCommandViewModel)) ?>
	<?php endif ?>
</div>
	
<div class="rocket-panel">
	<h3 class="rocket-preview-iframe-title">Detail</h3>
	<div class="rocket-detail-content rocket-preview-wrapper">
		<iframe src="<?php $html->esc($view->params['iframeSrc']) ?>" id="rocket-preview-content"></iframe>
	</div>
</div>


<?php if ($entryCommandViewModel->isDraftEnabled()): ?>
	<?php $view->panelStart('additional') ?>
		<?php $view->import('script\entity\command\impl\common\view\inc\historyNav.html', 
				array('entryCommandViewModel' => $entryCommandViewModel)) ?>
	<?php $view->panelEnd() ?>
<?php endif ?>


<div id="rocket-page-controls">
	<?php $scriptHtml->entryControlList() ?>
	
	<?php if ($entryCommandViewModel->hasPreviewSwitch()): ?>
		<?php $view->import('script\entity\command\impl\common\view\inc\previewSwitch.html', 
				array('entryCommandViewModel' => $entryCommandViewModel)) ?>
	<?php endif ?>
</div>