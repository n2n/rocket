<?php
	use n2n\dispatch\PropertyPath;
	use rocket\script\entity\filter\SortForm;
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$sortForm = $formHtml->getValue($propertyPath)->getObject();
	$view->assert($sortForm instanceof SortForm);
	
	$itemIdOptions = $sortForm->getItemIdOptions();
	$directionsOptions = $sortForm->getSortDirectionOptions($request->getLocale());
	
	$html->addJs('js/sort-form.js');
?>
<ul class="rocket-sort-items"
	data-add-sort-label="<?php $html->l10nText('script_impl_add_sort_label') ?>">
	<?php foreach ($formHtml->getValue($propertyPath->createExtendedPath(array('itemIds'))) as $id => $itemId): ?>
		<li>
			<?php $formHtml->select($propertyPath->createExtendedPath(array('itemIds[' . $id . ']')), $itemIdOptions) ?>
			<?php $formHtml->select($propertyPath->createExtendedPath(array('directions[' . $id . ']')), $directionsOptions) ?>
		</li>
	<?php endforeach ?>
	<li>
		<?php $formHtml->select($propertyPath->createExtendedPath(array('itemIds[]')), $itemIdOptions) ?>
		<?php $formHtml->select($propertyPath->createExtendedPath(array('directions[]')), $directionsOptions) ?>
	</li>
</ul>