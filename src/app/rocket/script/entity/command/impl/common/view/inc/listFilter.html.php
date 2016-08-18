<?php
	use rocket\script\entity\command\impl\common\model\ListFilterForm;
	use rocket\script\entity\command\impl\common\model\ListQuickSearchModel;
	use n2n\ui\Raw;
	use rocket\script\entity\command\control\IconType;
	
	$filterModel = $view->getParam('listFilterForm'); 
	$view->assert($filterModel instanceof ListFilterForm);
	$listQuickSearchModel = $view->getParam('listQuickSearchModel'); 
	$view->assert($listQuickSearchModel instanceof ListQuickSearchModel); 
	
	$html->addJs('js/script/list-filter.js');
?>
<div id="rocket-filter"<?php $view->out($filterModel->isActive() ? ' class="rocket-active"' : '') ?>
		data-rocket-script-id="<?php $html->out($filterModel->getScriptId()) ?>">
	<?php $formHtml->open($filterModel, null, null, array('id' => 'rocket-filter-list', 'class' => 'rocket-edit-form')) ?>
		<fieldset>
			<dl class="rocket-filter-selection">
				<dt><?php $formHtml->label('selectedFilterId', $view->getL10nText('script_impl_select_filter_label')) ?></dt>
				<dd><?php $formHtml->select('selectedFilterId', $filterModel->getSelectedFilterIdOptions()) ?></dd>
			</dl>
		</fieldset>
		<div class="rocket-filter-configuration">
			<fieldset>
				<h4><?php $html->l10nText('script_impl_filter_title') ?></h4>
				<?php $view->import('script\entity\filter\view\filterForm.html', 
						array('propertyPath' => $formHtml->createPropertyPath('filterForm'))) ?>
			</fieldset>
			<fieldset>
				<h4><?php $html->l10nText('script_impl_sort_title') ?></h4>
				<?php $view->import('script\entity\filter\view\sortForm.html', 
						array('propertyPath' => $formHtml->createPropertyPath('sortForm'))) ?>
			</fieldset>
			<div  class="rocket-form-actions clearfix">
				<ul class="rocket-filter-commands">
					<li>
						<?php $formHtml->inputSubmit('apply', $view->getL10nText('common_apply_label'),
								array('class' => 'rocket-control-warning rocket-important')) ?>
					</li>
					<?php if ($filterModel->isActive()) : ?>
						<li>
							<?php $formHtml->inputSubmit('clear', $view->getL10nText('common_clear_label'),
									array('class' => 'rocket-control')) ?>
						</li>
					<?php endif ?>
					<?php if ($filterModel->hasSelectedFilter()): ?>
						<li>
							<?php $formHtml->inputSubmit('saveFilter', $view->getL10nText('common_save_label'),
									array('class' => 'rocket-control-warning')) ?>
						</li>
					<?php endif ?>
					<li class="rocket-textable-control">
						<?php $formHtml->inputSubmit('createFilter', $view->getL10nText('common_save_as_label'), 
								array('data-after-label' => $view->getL10nText('script_filter_save_label'))) ?>
						<?php $formHtml->inputField('newFilterName', array('maxlength' => '32', 'class' => 'rocket-control-warning')) ?>
					</li>
					<?php if ($filterModel->hasSelectedFilter()): ?>
						<li>
							<?php $formHtml->inputSubmit('deleteFilter', $view->getL10nText('common_delete_label'),
									array('class' => 'rocket-control-danger')) ?>
						</li>
					<?php endif ?>
				</ul>
			</div>
			<?php $formHtml->inputSubmit('selectFilter', 'Select Filter') ?>
		</div>
	<?php $formHtml->close() ?>
</div>
<div id="rocket-quicksearch"<?php $view->out($listQuickSearchModel->isActive() ? ' class="rocket-active"' : '') ?>>
	<?php $formHtml->open($listQuickSearchModel, null, null, array('class' => 'rocket-edit-form')) ?>
		<dl class="rocket-quicksearch-selection">
			<dt>
				<?php $formHtml->label('searchStr', $html->getL10nText('common_search_label')) ?>
			</dt>
			<dd class="rocket-search-input">
				<?php $formHtml->inputField('searchStr', null, 'search') ?>
			</dd>
			<dd>
				<ul class="rocket-quicksearch-command rocket-simple-controls">
					<li>
						<?php $formHtml->buttonSubmit('search', new Raw('<i class="fa fa-search"></i>'),
								array('class' => 'rocket-control rocket-command-lonely-appended',
										'title' => $view->getL10nText('script_cmd_list_quicksearch_tooltip'))) ?>
					</li>
					<li>
						<?php if ($listQuickSearchModel->isActive()) : ?>
							<?php $formHtml->buttonSubmit('clear', new Raw('<i class="fa fa-eraser"></i>'),
									array('class' => 'rocket-control rocket-command-lonely-appended',
											'title' => $view->getL10nText('script_cmd_list_quicksearch_erase_tooltip'))) ?>
						<?php endif ?>
					</li>
				</ul>
			</dd>
		</dl>
	<?php $formHtml->close() ?>
</div>