<?php
	use rocket\script\entity\manage\model\EntryListModel;
	use rocket\script\entity\manage\ScriptHtmlBuilder;
	use n2n\dispatch\PropertyPath;
	
	$entryModel = $view->getParam('entryListModel');
	$view->assert($entryModel instanceof EntryListModel);
	
	$fieldIds = $view->getParam('fieldIds');
	$view->assert(is_array($fieldIds));
	
	$selectPropertyPath = $view->getParam('selectPropertyPath', false);
	$view->assert($selectPropertyPath === null || $selectPropertyPath instanceof PropertyPath);
	
	$scriptHtml = new ScriptHtmlBuilder($view, $entryModel);
?>
<table class="rocket-list">
	<thead>
		<tr>
			<?php if ($selectPropertyPath !== null): ?>
				<th>&nbsp;</th>
			<?php endif ?>
			<?php foreach ($fieldIds as $fieldId): ?>
				<th><?php $scriptHtml->simpleLabel($fieldId) ?></th>
			<?php endforeach ?>
			<th><?php $html->l10nText('common_list_tools_label') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php while (null !== ($id = $scriptHtml->meta()->next())): ?>
			<tr>
				<?php if ($selectPropertyPath !== null): ?>
					<td><?php $formHtml->inputCheckbox($selectPropertyPath->fieldExt($id), $id) ?></td>
				<?php endif ?>
				<?php foreach ($fieldIds as $fieldId): ?>
					<?php $scriptHtml->openOutputField('td', $fieldId) ?>
						<?php $scriptHtml->field() ?>
					<?php $scriptHtml->closeField() ?>
				<?php endforeach ?>
				<td>
					<?php $scriptHtml->entryControlList(true) ?>
				</td>
			</tr>
		<?php endwhile ?>
	</tbody>
</table>