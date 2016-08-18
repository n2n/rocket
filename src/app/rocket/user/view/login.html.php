<?php 
use n2n\ui\Raw;
use rocket\user\model\LoginContext;
use util\jquery\JQueryLibrary;

$loginContext = $view->params['loginContext']; $view->assert($loginContext instanceof LoginContext);
$html->addMeta(array('charset' => n2n\N2N::CHARSET));
$html->addMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
$html->addMeta(array('name' => 'robots', 'content' => 'noindex'));
$html->addCss('css/style.css');
$html->addCss('css/font-awesome.css');
$html->addJs('js/html5.js');
$html->addJs('js/respond.src.js');
$html->addLibrary(new JQueryLibrary());
$html->addJs('js/rocket.js');
?>
<!DOCTYPE html>
<html> 
	<?php $html->headStart() ?>
	<?php $html->headEnd() ?>
	<body id="rocket-login">
		<div id="rocket-login-container">
			<div id="rocket-login-form-container">
				<div id="rocket-logo-container">
					<?php $html->imageAsset('img/login-logo-06.png', '', array('id' => 'rocket-login-logo')) ?>
					<?php $html->linkToContext('', new Raw('<i class="fa fa-home"></i> ' . $html->getL10nText('user_back_to_website_label')), array('class' => 'rocket-user-back-link' , 'target' => '_blank'))?>
				</div>
					<?php $html->messageList(null, null, array('class' => 'rocket-message-error')) ?>
					<?php $formHtml->open($loginContext, null, null, array('class' => 'rocket-login-form')) ?>
					<ul>
						<li>
							<label for="nick">
								<i class="fa fa-user"></i>
								<span><?php $html->l10nText('user_password_label') ?></span>
							</label>
							<div class="rocket-controls">
								<?php $formHtml->inputField('nick', array('placeholder' => $view->getL10nText('user_nick_label'), 'class' => 'rocket-login-input')) ?>
							</div>
						</li>
						<li>
							<label for="rawPassword">
								<i class="fa fa-lock"></i>
								<span><?php $html->l10nText('user_nick_label') ?></span>
							</label>
							<div class="rocket-controls">
								<?php $formHtml->inputField('rawPassword', array('placeholder' => $view->getL10nText('user_password_label'), 'class' => 'rocket-login-input'), 'password', true) ?>
							</div>
						</li>
					</ul>
					<div class="rocket-form-actions">
						<?php $formHtml->inputSubmit('login', $view->getL10nText('user_login_label')) ?>
					</div>
				<?php $formHtml->close() ?>
			</div>
		</div>
	</body>
</html>