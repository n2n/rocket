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

use n2n\l10n\Message;
use n2n\core\N2N;
use n2n\web\ui\Raw;
use rocket\core\model\TemplateModel;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\http\nav\Murl;
use n2nutil\jquery\JQueryUiLibrary;
use n2n\impl\web\ui\view\jhtml\JhtmlLibrary;
use rocket\user\model\LoginContext;

$view = HtmlView::view($this);
$request = HtmlView::request($view);
$html = HtmlView::html($view);
$httpContext = HtmlView::httpContext($view);

$templateModel = $view->lookup(TemplateModel::class);
$view->assert($templateModel instanceof TemplateModel);

/**
 * @var LoginContext $loginContext
 */
$loginContext = $view->lookup(LoginContext::class);
$view->assert($loginContext instanceof LoginContext);

// 	$rocket = $view->lookup('rocket\core\model\Rocket');
// 	$view->assert($rocket instanceof Rocket);

// 	$rocketState = $view->lookup('rocket\core\model\RocketState');
// 	$view->assert($rocketState instanceof RocketState);

// 	$manageState = $view->lookup('rocket\spec\ei\manage\ManageState');
// 	$view->assert($manageState instanceof ManageState);

$htmlMeta = $html->meta();

$htmlMeta->addMeta(array('charset' => N2N::CHARSET));
$htmlMeta->addMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
$htmlMeta->addMeta(array('content' => 'IE=edge', 'http-equiv' => 'x-ua-compatible'));
$htmlMeta->addMeta(array('name' => 'robots', 'content' => 'noindex, nofollow'));
$htmlMeta->addLibrary(new JQueryUiLibrary(3));
$htmlMeta->addLibrary(new JhtmlLibrary(false));
// new design (not ready yet):
$htmlMeta->addCss('css/rocket-12.css');
// old design:
//	$htmlMeta->addCss('css/rocket.css');
$htmlMeta->addCss('css/font-awesome.css');
// 	$htmlMeta->addJs('js/respond.src.js', null);
// 	$htmlMeta->addJs('js/jquery-responsive-table.js', null, true);
$htmlMeta->addJs('js/ajah.js', 'n2n\impl\web\ui');
$htmlMeta->addJs('js/rocket.js', null);

// 	$specManager = $rocket->getSpecManager();
// 	$menuGroups = $specManager->getMenuGroups();
// 	$selectedMenuItem = $manageState->getSelectedMenuItem();
// 	$breadcrumbs = $rocketState->getBreadcrumbs();
// 	$activeBreadcrumb = array_pop($breadcrumbs);
$htmlMeta->addLink(array('rel' => 'shortcut icon', 'href' => $httpContext->getAssetsUrl('rocket')->ext(array('img', 'favicon.ico'))));
$htmlMeta->addLink(array('rel' => 'apple-touch-icon', 'href' => $httpContext->getAssetsUrl('rocket')->ext(array('img', 'apple-touch-icon.png'))));
?>
<!DOCTYPE html>
<html lang="<?php $html->out($request->getN2nLocale()->getLanguage()->getShort()) ?>">
<?php $html->headStart() ?>

<style>

	/* pizza loader */
	.rocket-zone.rocket-loading {
		background: url('https://media.giphy.com/media/3o7bu8sRnYpTOG1p8k/giphy-downsized.gif') center center no-repeat;
		min-height: 80vh;
	}

	.rocket-zone-block {
		position: fixed;
		top: 0px;
		left: 0px;
		right: 0px;
		bottom: 0px;
		background: url('https://media.giphy.com/media/14fx8QIuKnp8Pu/giphy.gif') center center no-repeat rgba(0, 0, 0, 0.2);
	}

	form.rocket-impl-critmod .rocket-impl-critmod-blocker {
		position: absolute;
		top: 0px;
		left: 0px;
		right: 0px;
		bottom: 0px;
		background: url('https://media.giphy.com/media/jAYUbVXgESSti/giphy.gif') center center no-repeat;
	}
</style>
<?php $html->headEnd() ?>
<?php $html->bodyStart(array('data-refresh-path' => $view->buildUrl(Murl::controller('rocket')),
	'class' => (isset($view->params['tmplMode']) ? $view->params['tmplMode'] : null))) ?>
<div data-jhtml-container="rocket-template">
	<header id="rocket-header">
		<div id="rocket-logo">
			<?php $html->link(Murl::controller('rocket'), $html->getImageAsset('img/nav-logo-05.png', 'logo'),
				array('id' => 'rocket-branding')) ?>
		</div>
		<h2 id="rocket-customer-name"><?php $html->out(N2N::getAppConfig()->general()->getPageName()) ?></h2>
		<nav id="rocket-conf-nav" class="navbar-expand-lg" data-jhtml-comp="rocket-conf-nav">
			<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
					data-target="#rocket-conf-nav" aria-controls="navbarText" aria-expanded="false"
					aria-label="Toggle navigation">
				<i class="fa fa-navicon"></i>
			</button>
			<h2 class="sr-only"><?php $html->l10nText('conf_nav_title') ?></h2>
			<ul class="nav justify-content-end">
				<?php if ($templateModel->getCurrentUser()->isAdmin()): ?>
					<li class="nav-item">
						<?php $html->linkStart(Murl::controller('rocket')->pathExt('tools'), array('class' => 'nav-link')) ?>
						<i class="fa fa-wrench mr-2"></i><?php $html->text('tool_title') ?>
						<?php $html->linkEnd() ?>
					</li>
					<li class="nav-item">
						<?php $html->linkStart(Murl::controller('rocket')->pathExt('users'), array('class' => 'nav-link')) ?>
						<i class="fa fa-user mr-2"></i><?php $html->text('user_title') ?>
						<?php $html->linkEnd() ?>
					</li>
					<li class="nav-item">
						<?php $html->linkStart(Murl::controller('rocket')->pathExt('usergroups'), array('class' => 'nav-link')) ?>
						<i class="fa fa-group mr-2"></i><?php $html->text('user_groups_title') ?>
						<?php $html->linkEnd() ?>
					</li>
				<?php endif ?>
				<li class="nav-item">
					<?php $html->linkStart(Murl::controller('rocket')->pathExt('users', 'profile'), array('class' => 'nav-link rocket-conf-user')) ?>
					<i class="fa fa-user mr-2"></i><?php $html->out((string) $templateModel->getCurrentUser()) ?>
					<?php $html->linkEnd() ?>
				</li>
				<li class="nav-item">
					<?php $html->linkStart(Murl::controller('rocket')->pathExt('logout'), array('class' => 'nav-link rocket-conf-logout')) ?>
					<i class="fa fa-sign-out"></i>
					<?php $html->linkEnd() ?>
				</li>
				<li class="nav-item">
					<?php $html->linkStart(Murl::controller('rocket')->pathExt('about'), array('class' => 'nav-link')) ?>
					<i class="fa fa-info"></i>
					<?php $html->linkEnd() ?>
				</li>
			</ul>
		</nav>
	</header>
	<nav id="rocket-global-nav" data-jhtml-comp="rocket-global-nav">
		<h2 class="sr-only" data-rocket-user-id="<?php $html->out($loginContext->getCurrentUser()->getId()) ?>"><?php $html->l10nText('manage_nav_title') ?></h2>
		<?php foreach ($templateModel->getNavArray() as $navArray): ?>
			<div class="rocket-nav-group<?php $html->esc($navArray['open'] ? ' rocket-nav-group-open': '') ?>">
				<h3 class="rocket-global-nav-group-title">
					<a><?php $html->esc($navArray['menuGroup']->getLabel()) ?></a>
					<i class="fa <?php $html->esc($navArray['open'] ? 'fa-minus': 'fa-plus') ?>"></i>
				</h3>
				<ul class="nav flex-column">
					<?php foreach ($navArray['menuItems'] as $menuItem): ?>
						<li class="nav-item">
							<?php $html->link(Murl::controller('rocket')->pathExt('manage', $menuItem->getId(),
								$menuItem->determinePathExt($view->getN2nContext())),
								new Raw($html->getEsc($navArray['menuGroup']->determineLabel($menuItem))
									. '<span></span>'),
								array('data-jhtml' => 'true', 'class' => 'nav-link'
									. ($templateModel->isMenuItemActive($menuItem) ? ' active' : null))) ?></li>
					<?php endforeach ?>
				</ul>
			</div>
		<?php endforeach ?>
	</nav>

	<div id="rocket-content-container" data-error-tab-title="<?php $html->text('ei_error_list_title') ?>"
		 data-display-error-label="<?php $html->text('core_display_error_label') ?>">
		<div class="rocket-main-layer">
			<div class="rocket-zone" data-jhtml-comp="rocket-page">
				<header>
					<?php if (null !== ($activeBreadcrumb = $templateModel->getActiveBreadcrumb())): ?>
						<ol class="breadcrumb">
							<?php foreach ($templateModel->getBreadcrumbs() as $breadcrumb): ?>
								<li class="breadcrumb-item"><?php $html->link($breadcrumb->getUrl(), (string) $breadcrumb->getLabel(),
										($breadcrumb->isJhtml() ? array('data-jhtml' => 'true', 'data-jhtml-use-page-scroll-pos' => 'true') : null)) ?></li>
							<?php endforeach ?>
							<li class="breadcrumb-item active">
								<?php $html->link($activeBreadcrumb->getUrl(), (string) $activeBreadcrumb->getLabel(),
									($activeBreadcrumb->isJhtml() ? array('data-jhtml' => 'true', 'data-jhtml-use-page-scroll-pos' => 'true') : null)) ?>
							</li>
						</ol>
					<?php endif ?>

					<!-- WICHTIG -->

					<?php if (isset($view->params['title'])): ?>
						<h1><?php $html->out($view->params['title']) ?></h1>
					<?php else: ?>
						<h1>Rocket</h1>
					<?php endif ?>
				</header>

				<?php $html->messageList(null, Message::SEVERITY_ERROR, array('class' => 'alert alert-danger list-unstyled')) ?>
				<?php $html->messageList(null, Message::SEVERITY_INFO, array('class' => 'alert alert-info list-unstyled')) ?>
				<?php $html->messageList(null, Message::SEVERITY_WARN, array('class' => 'alert alert-warn list-unstyled')) ?>
				<?php $html->messageList(null, Message::SEVERITY_SUCCESS, array('class' => 'alert alert-success list-unstyled')) ?>

				<div class="rocket-content <?php $html->esc($view->hasPanel('additional') ? ' rocket-contains-additional' : '') ?>"
					 data-error-list-label="<?php $html->text('ei_error_list_title') ?>">
					<?php $view->importContentView() ?>
				</div>

				<?php if ($view->hasPanel('additional')): ?>
					<div id="rocket-additional">
						<?php $view->importPanel('additional') ?>
					</div>
				<?php endif ?>

				<!-- NICHT MEHR WICHTIG -->
			</div>
		</div>
	</div>
</div>
<?php $html->bodyEnd() ?>
</html>
