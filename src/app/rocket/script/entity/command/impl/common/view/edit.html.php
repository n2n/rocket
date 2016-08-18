<?php 
	use rocket\script\entity\command\impl\common\model\EditModel;
	use rocket\script\entity\command\impl\common\model\EntryCommandViewModel;
	use rocket\script\entity\manage\model\EntryFormViewModel;
	use n2n\ui\Raw;
	
	$detailModel = $view->getParam('editModel');
	$view->assert($detailModel instanceof EditModel);
	
	$entryCommandViewModel = $view->getParam('entryCommandViewModel');
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);
 
	$view->useTemplate('core\view\template.html',
			array('title' => $entryCommandViewModel->getTitle()));
	
?>

<?php if ($entryCommandViewModel->isTranslationEnabled()): ?>
	<ul class="rocket-toolbar">
		<li><?php $view->import('script\entity\command\impl\common\view\inc\langNav.html', 
				array('entryCommandViewModel' => $entryCommandViewModel)) ?></li>
	</ul>
<?php endif ?>

<?php $formHtml->open($detailModel, 'multipart/form-data', 'post', array('class' => 'rocket-unsaved-check-form')) ?>
	<div class="rocket-panel">
		<h3><?php $html->l10nText('common_properties_title') ?></h3>
		
		<?php if ($entryCommandViewModel->isDraftEnabled() && $entryCommandViewModel->isPublishAllowed()): ?>
			<div class="rocket-save-mode">
				<?php $formHtml->inputRadio('saveMode', EditModel::SAVE_MODE_DRAFT, null, 
						$html->getL10nText('script_cmd_edit_save_as_draft_label')) ?>
				<?php $formHtml->inputRadio('saveMode', EditModel::SAVE_MODE_LIVE, null, 
						$html->getL10nText('script_cmd_edit_save_and_publish_label')) ?>
			</div>
		<?php endif ?>
		
		<?php $view->import('script\entity\manage\view\entryForm.html', 
				array('entryFormViewModel' => new EntryFormViewModel($detailModel->getEntryForm(), 
						$formHtml->createPropertyPath(array('entryForm'))))) ?>
						
		<div id="rocket-page-controls">
			<ul>
				<li>
					<?php $formHtml->buttonSubmit('save', new Raw('<i class="fa fa-save"></i><span>' 
									. $html->getL10nText('common_save_label') . '</span>'), 
							array('class' => 'rocket-control-warning rocket-important')) ?>
				</li>
				<li>
					<?php $html->link($entryCommandViewModel->getCancelPath($request), 
							new n2n\ui\Raw('<i class="fa fa-times-circle"></i><span>' 
									. $html->getL10nText('common_cancel_label') . '</span>'),
							array('class' => 'rocket-control')) ?>
				</li>
			</ul>
			
			<div class="rocket-additional-controls">
				<?php if ($entryCommandViewModel->hasPreviewSwitch()): ?>
					<?php $view->import('script\entity\command\impl\common\view\inc\previewSwitch.html', 
							array('entryCommandViewModel' => $entryCommandViewModel)) ?>
				<?php endif ?>
			</div>
		</div>
	</div>
<?php $formHtml->close() ?>

<?php if ($entryCommandViewModel->isDraftEnabled()): ?>
	<?php $view->panelStart('additional') ?>
		<?php $view->import('script\entity\command\impl\common\view\inc\historyNav.html', array('entryViewInfo' => $entryViewInfo)) ?>
	<?php $view->panelEnd() ?>
<?php endif ?>
