<?php 
	use rocket\script\entity\manage\EntryViewInfo;

	$entryCommandViewModel = $view->params['entryViewInfo']; 
	$view->assert($entryCommandViewModel instanceof EntryViewInfo);
 
?>
<select class="rocket-paging">
	<?php foreach ($entryCommandViewModel->getPreviewTypeNavInfos() as $previewType => $navPoint): ?>
		<option value="<?php $html->out($request->getControllerContextPath($view->getControllerContext(), $navPoint['pathExt'])) ?>"
				<?php $html->out($navPoint['active'] ? ' selected="selected"' : '') ?>>
			<?php $html->out($navPoint['label']) ?>
		</option>
	<?php endforeach ?>
</select>