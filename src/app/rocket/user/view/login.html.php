<?php
	/*
	 * Copyright (c) 2012-2016, Hofmänner New Media.
	 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
	 *
	 * This file is part of the n2n module ROCKET.
	 *
	 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
	 * GNU Lesser General Public License as published by the Free Software Foundation, either
	 * version 2.1 of the License, or (at your option) any later version.
	 *
	 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
	 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
	 *
	 * The following people participated in this project:
	 *
	 * Andreas von Burg...........:	Architect, Lead Developer, Concept
	 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
	 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
	 */

	use n2n\web\ui\Raw;
	use rocket\user\model\LoginContext;
	use n2nutil\jquery\JQueryLibrary;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\view\View;
	
	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$loginContext = $view->params['loginContext']; $view->assert($loginContext instanceof LoginContext);
	$html->meta()->addMeta(array('charset' => n2n\core\N2N::CHARSET));
	$html->meta()->addMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
	$html->meta()->addMeta(array('name' => 'robots', 'content' => 'noindex'));
	$html->meta()->addCss('css/rocket.css');
	$html->meta()->addCss('css/font-awesome.css');
	$html->meta()->addJs('js/html5.js');
	$html->meta()->addJs('js/respond.src.js');
	$html->meta()->addLibrary(new JQueryLibrary(2));
	$html->meta()->addJs('js/ajah.js', 'n2n\web', true);
	$html->meta()->addJs('js/rocket-ts.js');
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
								<?php $formHtml->input('nick', array('placeholder' => $view->getL10nText('user_nick_label'), 'class' => 'rocket-login-input input-lg')) ?>
							</div>
						</li>
						<li>
							<label for="rawPassword">
								<i class="fa fa-lock"></i>
								<span><?php $html->l10nText('user_nick_label') ?></span>
							</label>
							<div class="rocket-controls rocket-control-danger">
								<?php $formHtml->input('rawPassword', array('placeholder' => $view->getL10nText('user_password_label'), 'class' => 'rocket-login-input input-lg'), 'password', true) ?>
							</div>
						</li>
					</ul>
					<div class="rocket-form-actions">
						<?php $formHtml->inputSubmit('login', $view->getL10nText('user_login_label'), array('class' => 'input-lg rocket-control-warning rocket-control-important')) ?>
					</div>
				<?php $formHtml->close() ?>
			</div>
		</div>
	</body>
</html>
