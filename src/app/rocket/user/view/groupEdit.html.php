<?php 
	use rocket\user\model\UserGroupForm;
use n2n\ui\Raw;
use rocket\user\model\UserScriptGrantForm;
use n2n\dispatch\map\MappingResult;

	$userGroupForm = $view->getParam('userGroupForm'); 
	$view->assert($userGroupForm instanceof UserGroupForm);
 
	$view->useTemplate('core\view\template.html', array('title' => $view->getL10nText('user_groups_title')));
	$html->addJs('js\user-group.js');
?>

<?php $formHtml->open($userGroupForm, null, 'post', array('class' => 'rocket-edit-form'))?>
	<div class="rocket-panel">
		<h3><?php $html->l10nText('common_properties_title') ?></h3>
		<ul class="rocket-properties">
			<li>
				<?php $formHtml->label('name', $html->getL10nText('common_name_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->inputField('name', array('maxlength' => 64)) ?>
				</div>
			</li>
			<li>
				<label><?php $html->text('user_accessable_menu_items_label') ?></label>
				<div class="rocket-controls rocket-user-group-menu-items"
					data-accessable-items-title="<?php $html->text('user_accessable_menu_items_label') ?>"
					data-unaccessable-items-title="<?php $html->text('user_unaccessable_menu_items_title') ?>"
					data-assign-title="<?php $html->text('common_assign_label') ?>"
					data-unassign-title="<?php $html->text('common_unassign_label') ?>">
					<?php $formHtml->inputCheckbox('menuItemRestrictionEnabled', true, null, 'MenuItemRestrictionEnabled') ?>
					<ul>
						<?php foreach ($userGroupForm->getAccessableMenuItemIdOptions() as $id => $label): ?>
							<li><?php $formHtml->inputCheckbox('accessableMenuItemIds[' . $id . ']', $id, null, $label)?></li>
						<?php endforeach ?>
					</ul>
				</div>
			</li>
		</ul>
	</div>
	<div id="rocket-page-controls">	
		<ul>
			<li>
				<?php $formHtml->buttonSubmit('save', new Raw('<i class="fa fa-save"></i><span>' 
								. $html->getL10nText('common_save_label') . '</span>'),
						array('class' => 'rocket-control-warning rocket-important')) ?>
			</li>
		</ul>
	</div>
<?php $formHtml->close() ?>