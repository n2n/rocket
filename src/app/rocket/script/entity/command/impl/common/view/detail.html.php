<?php 	
	use rocket\script\entity\command\impl\common\model\EntryCommandViewModel;
	use rocket\script\entity\command\impl\common\model\DetailModel;
	use rocket\script\entity\manage\ScriptHtmlBuilder;
	
	$detailModel = $view->getParam('detailModel');
	$view->assert($detailModel instanceof DetailModel);
	
	$entryCommandViewModel = $view->getParam('entryCommandViewModel');
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);
 
	$scriptHtml = new ScriptHtmlBuilder($view, $detailModel->getEntryModel());
	
	$html->addJs('js/script/display.js');
	
	$view->useTemplate('core\view\template.html',
			array('title' => $entryCommandViewModel->getTitle()));
	
?>

<div class="rocket-select-view-toolbar">
	<?php if ($entryCommandViewModel->isTranslationEnabled()): ?>
		<?php $view->import('script\entity\command\impl\common\view\inc\langNav.html', 
				array('entryCommandViewModel' => $entryCommandViewModel)) ?>
	<?php endif ?>
</div>

 
<div class="rocket-panel">
	<h3><?php $html->l10nText('common_properties_title') ?></h3>
	
	<?php $view->import($entryCommandViewModel->createEntryView()) ?>
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