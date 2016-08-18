<?php 
	use rocket\script\entity\command\impl\common\model\AddModel;
	use rocket\script\entity\manage\model\EntryFormViewModel;
	use rocket\script\entity\command\impl\common\model\EntryCommandViewModel;
use n2n\ui\Raw;

	$addModel = $view->params['addModel'];
	$view->assert($addModel instanceof AddModel);
	
	$entryCommandViewModel = $view->params['entryViewInfo'];
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);
 
	$view->useTemplate('core\view\template.html',
			array('title' => $entryCommandViewModel->getTitle()));
?>

<?php $formHtml->open($addModel, 'multipart/form-data', 'post', array('class' => 'rocket-edit-form rocket-unsaved-check-form')) ?>
	<div class="rocket-panel">
		<h3><?php $html->l10nText('common_properties_title') ?></h3>
		
		<?php $view->import('script\entity\manage\view\entryForm.html', 
				array('entryFormViewModel' => new EntryFormViewModel($addModel->getEntryForm(), 
						$formHtml->createPropertyPath(array('entryForm'))))) ?>
			
		<div id="rocket-page-controls">
			<ul>
				<li>
					<?php $formHtml->buttonSubmit('create', new Raw('<i class="fa fa-save"></i><span>' 
									. $html->getL10nText('common_save_label') . '</span>'),
							array('class' => 'rocket-control-warning rocket-important')) ?>
				</li>
				<li>
					<?php $html->link($entryCommandViewModel->getCancelPath($request), 
							new n2n\ui\Raw('<i class=" icon-remove-circle"></i><span>'
									. $html->getL10nText('common_cancel_label') . '</span>'),
							array('class' => 'rocket-control')) ?>
				</li>
			</ul>
		</div>
	</div>
<?php $formHtml->close() ?>