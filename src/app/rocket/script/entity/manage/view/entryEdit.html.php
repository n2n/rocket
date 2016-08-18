<?php
	use rocket\script\entity\manage\model\EditEntryModel;
	use n2n\dispatch\PropertyPath;
	use rocket\script\entity\manage\ScriptHtmlBuilder;
	use rocket\script\entity\manage\model\FieldOrderViewModel;
	
	$entryModel = $view->getParam('editEntryModel');
	$view->assert($entryModel instanceof EditEntryModel);

	$fieldOrderViewModel = $view->getParam('fieldOrderViewModel');
	$view->assert($fieldOrderViewModel instanceof FieldOrderViewModel);
	
	$basePropertyPath = $view->getParam('basePropertyPath', false);
	$view->assert($basePropertyPath === null || $basePropertyPath instanceof PropertyPath);

	$scriptHtml = new ScriptHtmlBuilder($view, $entryModel, $basePropertyPath);
?>
<ul class="rocket-properties<?php $html->out($fieldOrderViewModel->containsAsideGroup() ? ' rocket-aside-container' : '') ?>">
	<?php while ($fieldOrderViewModel->next()): ?>
		<?php if ($fieldOrderViewModel->isGroup()): ?>
			<li class="<?php $html->out($fieldOrderViewModel->getGroupCssClassName() . ($formHtml->hasErrors($basePropertyPath) ? ' rocket-has-error' : '')) ?>">
				<label><?php $html->out($fieldOrderViewModel->getGroupTitle()) ?></label>
				<div class="rocket-controls">
					<?php $view->import('script\entity\manage\view\entryEdit.html', array('editEntryModel' => $entryModel, 
							'fieldOrderViewModel' => $fieldOrderViewModel->getGroupFieldOrderModel(),
							'basePropertyPath' => $basePropertyPath)) ?>
				</div>
			</li>
		<?php else: ?>
			<?php $scriptHtml->openInputField('li', $fieldOrderViewModel->getFieldId()) ?>
				<?php $scriptHtml->label() ?>
				<div class="rocket-controls">
					<?php $scriptHtml->field() ?>
				</div>
			<?php $scriptHtml->closeField() ?>
		<?php endif ?>
	<?php endwhile ?>
</ul>