<?php 
	use rocket\script\entity\command\impl\common\model\EntryCommandViewModel;

	$entryCommandViewModel = $view->params['entryCommandViewModel']; 
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);
 ?>

<ul class="rocket-preview-switch">
	<li>
		<?php $html->linkToController($entryCommandViewModel->getInfoPathExt(),
				new n2n\ui\Raw('<i class="fa fa-list"></i>' 
						. $html->getL10nText('script_impl_entry_info_mode_label')), 
				array('class' => 'rocket-control rocket-control-dataview' . (!$entryCommandViewModel->isPreviewActivated() ? ' rocket-active' : null))) ?>
	</li>
	<li>
		<?php $html->linkToController($entryCommandViewModel->getPreviewPathExt(), 
				new n2n\ui\Raw('<i class="fa fa-eye"></i>' 
						. $html->getL10nText('script_impl_entry_preview_mode_label')), 
				array('class' => 'rocket-control rocket-control-preview' . ($entryCommandViewModel->isPreviewActivated() ? ' rocket-active' : null))) ?>
	</li>
</ul>