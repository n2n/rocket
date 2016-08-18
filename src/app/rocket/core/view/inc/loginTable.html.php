<?php
	use rocket\user\model\UserDao;
	
	$userDao = $view->lookup('rocket\user\model\UserDao'); $userDao instanceof UserDao;
	$useSuccessfullLogins = $view->getParam('useSuccessfull', false, true);
	$logins = array();
	if ($useSuccessfullLogins) {
		$logins = $userDao->getSuccessfullLogins(0, 5);
	} else {
		$logins = $userDao->getFailedLogins();
	}
?>
<table class="rocket-list">
	<thead>
		<tr>
			<th><?php $html->l10nText('user_nick_label') ?></th>
			<th><?php $html->l10nText('core_ip_label') ?></th>
			<?php if ($useSuccessfullLogins) : ?>
				<th><?php $html->l10nText('user_access_type_label') ?></th>
			<?php endif ?>
			<th><?php $html->l10nText('common_date_label') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ((array) $logins as $login ) : ?>
			<tr>
				<td><?php $html->out($login->getNick()) ?></td>
				<td><?php $html->out($login->getIp()) ?></td>
				<?php if ($useSuccessfullLogins) : ?>
					<td><?php $html->out($login->getType()) ?></td>
				<?php endif ?>
				<td><?php $html->out($html->getL10nDateTime($login->getDateTime())) ?></td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>