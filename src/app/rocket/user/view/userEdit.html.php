<?php 
	use rocket\user\model\UserForm;
use n2n\ui\Raw;
	
	$userGroupForm = $view->getParam('userForm'); 
	$view->assert($userGroupForm instanceof UserForm);

	$user = $userGroupForm->getUser();
	
	$view->useTemplate('core\view\template.html', array('title' => ($userGroupForm->isNew() 
			? $view->getL10nText('user_add_title') : $view->getL10nText('user_edit_title', 
					array('user' => (string) $user)))));
?>

<?php $formHtml->open($userGroupForm, null, 'post', array('class' => 'rocket-edit-form'))?>
	<div class="rocket-panel">
		<h3><?php $html->l10nText('common_properties_title') ?></h3>
			
		<ul class="rocket-properties">
			<li>
				<?php $formHtml->label('user.nick', $html->getL10nText('user_nick_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->inputField('user.nick', array('maxlength' => 128)) ?>
				</div>
			</li>
			<li>
				<?php $formHtml->label('rawPassword', $html->getL10nText('user_password_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->inputField('rawPassword', null, 'password', true) ?>
				</div>
			</li>
			<li>
				<?php $formHtml->label('rawPassword2', $html->getL10nText('user_password_confirmation_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->inputField('rawPassword2', null, 'password', true) ?>
				</div>
			</li>
			<li>
				<?php $formHtml->label('user.firstname', $html->getL10nText('user_firstname_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->inputField('user.firstname', array('maxlength' => 32)) ?>
				</div>
			</li>
			<li>
				<?php $formHtml->label('user.lastname', $html->getL10nText('user_lastname_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->inputField('user.lastname', array('maxlength' => 32)) ?>
				</div>
			</li>
			<li>
				<?php $formHtml->label('user.email', $html->getL10nText('user_email_label')) ?>
				<div class="rocket-controls">
					<?php $formHtml->inputField('user.email', array('maxlength' => 128), 'email') ?>
				</div>
			</li>
			
			<?php if ($userGroupForm->isModifyTypeAllowed()): ?>
				<li>
					<?php $formHtml->label('type', $html->getL10nText('user_access_type_label')) ?>
					<div class="rocket-controls">
						<?php $formHtml->select('type', $userGroupForm->getTypeOptions()) ?>
					</div>
				</li>
			<?php endif ?>
			
			<li>
				<?php $formHtml->label('userGroupIds', $html->getL10nText('user_assigned_groups_label')) ?>
				<div class="rocket-controls">
					<?php if ($userGroupForm->areGroupsReadOnly()): ?>
						<ul>
							<?php foreach ($user->getUserGroups() as $userGroup): ?>
								<li><?php $html->out($userGroup->getName()) ?></li>
							<?php endforeach ?>
						</ul>
					<?php else: ?>
						<ul>
							<?php foreach ($userGroupForm->getAvaialableUserGroups() as $id => $userGroup): ?>
								<li><?php $formHtml->inputCheckbox('userGroupIds[' . $id . ']', $id, null, $userGroup->getName())?></li>
							<?php endforeach ?>
						</ul>
					<?php endif ?>
				</div>
			</li>
		</ul>
	</div>
	<div id="rocket-page-controls">	
		<ul>
			<li>
				<?php $formHtml->buttonSubmit('save', 
						new Raw('<i class="fa fa-save"></i>' . $html->getL10nText('common_save_label')), 
						array('class' => 'rocket-control-warning rocket-important')) ?>
			</li>
		</ul>
	</div>
<?php $formHtml->close() ?>