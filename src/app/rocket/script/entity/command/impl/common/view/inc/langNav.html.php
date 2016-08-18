<?php 
	use rocket\script\entity\command\impl\common\model\EntryCommandViewModel;
	
	$entryCommandViewModel = $view->params['entryCommandViewModel'];
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel); 
 
?>

<select class="rocket-paging rocket-language-navigation">
	<?php foreach ($entryCommandViewModel->getLangNavPoints() as $navPoint): ?>
		<option value="<?php $html->out($request->getControllerContextPath($view->getControllerContext(), $navPoint['pathExt'])) ?>"
				<?php $view->out($navPoint['active'] ? ' selected="selected"' : '') ?>>
			<?php $html->out($navPoint['label']); ?>
		</option>
	<?php endforeach ?>
</select>
