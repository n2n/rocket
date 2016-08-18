<?php
	use rocket\script\entity\manage\ScriptHtmlBuilder;
	use rocket\script\entity\manage\model\EntryModel;
	use rocket\script\entity\manage\model\FieldOrderViewModel;

	$entryModel = $view->getParam('entryModel');
	$view->assert($entryModel instanceof EntryModel);
	
	$fieldOrderViewModel = $view->getParam('fieldOrderViewModel');
	$view->assert($fieldOrderViewModel instanceof FieldOrderViewModel);
		
	$scriptHtml = new ScriptHtmlBuilder($view, $entryModel);
?>
<ul class="rocket-properties<?php $html->out($fieldOrderViewModel->containsAsideGroup() ? ' rocket-aside-container' : '') ?>">
	<?php while ($fieldOrderViewModel->next()): ?>
		<?php if ($fieldOrderViewModel->isGroup()): ?>
			<li class="<?php $html->out($fieldOrderViewModel->getGroupCssClassName()) ?>">
				<label><?php $html->out($fieldOrderViewModel->getGroupTitle()) ?></label>
				<div class="rocket-controls">
					<?php $view->import('script\entity\manage\view\entry.html', array(
							'entryModel' => $entryModel, 'fieldOrderViewModel' => $fieldOrderViewModel->getGroupFieldOrderModel())) ?>
				</div>
			</li>
		<?php else: ?>
			<?php $scriptHtml->openOutputField('li', $fieldOrderViewModel->getFieldId()) ?>
				<?php $scriptHtml->label() ?>
				<div class="rocket-controls">
					<?php $scriptHtml->field() ?>
				</div>
			<?php $scriptHtml->closeField() ?>
		<?php endif ?>
	<?php endwhile ?>
</ul>