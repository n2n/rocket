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
	use n2nutil\jquery\JQueryLibrary;
	use rocket\core\model\TemplateModel;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\web\http\nav\Murl;

	$view = HtmlView::view($this);
	$request = HtmlView::request($this);
	$html = HtmlView::html($this);
	$httpContext = HtmlView::httpContext($this);
	
	$templateModel = $view->lookup(TemplateModel::class); 
	$view->assert($templateModel instanceof TemplateModel);

// 	$loginContext = $view->lookup('rocket\user\model\LoginContext'); 
// 	$view->assert($loginContext instanceof LoginContext);
	
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
	$htmlMeta->addLibrary(new JQueryLibrary(2, false));
	$htmlMeta->addCss('css/rocket.css');
	$htmlMeta->addCss('css/font-awesome.css');
	$htmlMeta->addJs('js/html5.js', null);
	$htmlMeta->addJs('js/respond.src.js', null);
// 	$htmlMeta->addJs('js/jquery-responsive-table.js', null, true);
	$htmlMeta->addJs('js/ajah.js', 'n2n\impl\web\ui');
	$htmlMeta->addJs('js/rocket-ts.js', null);
	
// 	$scriptManager = $rocket->getSpecManager();
// 	$menuGroups = $scriptManager->getMenuGroups();
// 	$selectedMenuItem = $manageState->getSelectedMenuItem();
// 	$breadcrumbs = $rocketState->getBreadcrumbs();
// 	$activeBreadcrumb = array_pop($breadcrumbs);
	$htmlMeta->addLink(array('rel' => 'shortcut icon', 'href' => $httpContext->getAssetsUrl('rocket')->ext(array('img', 'favicon.ico'))));
	$htmlMeta->addLink(array('rel' => 'apple-touch-icon', 'href' => $httpContext->getAssetsUrl('rocket')->ext(array('img', 'apple-touch-icon.png'))));
?>
<!DOCTYPE html>
<html lang="<?php $html->out($request->getN2nLocale()->getLanguage()->getShort()) ?>"> 
	<?php $html->headStart() ?>
	<?php $html->headEnd() ?>
	<?php $html->bodyStart(array('data-refresh-path' => $view->buildUrl(Murl::controller('rocket')), 
			'class' => (isset($view->params['tmplMode']) ? $view->params['tmplMode'] : null))) ?>
		<header id="rocket-header">
			<?php $html->link(Murl::controller('rocket'), $html->getImageAsset('img/nav-logo-05.png', 'logo'),
					array('id' => 'rocket-branding')) ?>
					
			<h2 id="rocket-customer-name"><?php $html->out(N2N::getAppConfig()->general()->getPageName()) ?></h2>
			<a href="#" id="rocket-conf-nav-toggle" title="<?php $html->text('mobile_nav_title') ?>">
				<i class="fa fa-navicon"></i>
			</a>
			<nav id="rocket-conf-nav">
				<h2 class="sr-only"><?php $html->l10nText('conf_nav_title') ?></h2>
				<ul class="rocket-level-1">
					<?php if ($templateModel->getCurrentUser()->isAdmin()): ?>
						<li class="rocket-conf-users">
							<?php $html->linkStart(Murl::controller('rocket')->pathExt('users')) ?>
								<i class="fa fa-user"></i><?php $html->text('user_title') ?>
							<?php $html->linkEnd() ?> 
						</li>
						<li class="rocket-conf-users">
							<?php $html->linkStart(Murl::controller('rocket')->pathExt('usergroups')) ?>
								<i class="fa fa-group"></i><?php $html->text('user_groups_title') ?>
							<?php $html->linkEnd() ?> 
						</li>
						<li class="rocket-conf-tools">
							<?php $html->linkStart(Murl::controller('rocket')->pathExt('tools')) ?>
								<i class="fa fa-wrench"></i><?php $html->text('tool_title') ?>
							<?php $html->linkEnd() ?>
						</li>
					<?php endif ?>
					<li class="rocket-conf-about">
						<?php $html->linkStart(Murl::controller('rocket')->pathExt('about')) ?>
							<i class="fa fa-wrench"></i><?php $html->text('about_title') ?>
						<?php $html->linkEnd() ?>
					</li>
					<li class="rocket-conf-profile">
						<?php $html->linkStart(Murl::controller('rocket')->pathExt('users', 'profile')) ?> 
							<i class="fa fa-key"></i><?php $html->text('user_profile_title') ?>
						<?php $html->linkEnd() ?>
					</li>
				</ul>
			</nav>
			<?php $html->linkStart(Murl::controller('rocket')->pathExt('logout'), array('id' => 'rocket-logout')) ?>
				<i class="fa fa-sign-out"></i><span class="rocket-logout-text"><?php $html->text('user_logout_label', 
						array('user' => (string) $templateModel->getCurrentUser())) ?></span>
			<?php $html->linkEnd() ?>
		</header>
		<nav id="rocket-global-nav">
			<h2 class="sr-only"><?php $html->l10nText('manage_nav_title') ?></h2>
			<?php foreach ($templateModel->getNavArray() as $navArray): ?>
				<div class="rocket-nav-group<?php $html->esc($navArray['open'] ? ' rocket-nav-group-open': '') ?>">
					<h3><a><i class="fa <?php $html->esc($navArray['open'] ? 'fa-minus': 'fa-plus') ?>"></i> 
							<?php $html->esc($navArray['label']) ?></a></h3>
					<ul>
						<?php foreach ($navArray['menuItems'] as $menuItem): ?>
							<li<?php $view->out($templateModel->isMenuItemActive($menuItem) 
									? ' class="rocket-nav-group-list-item-active"' : null) ?>>
								<?php $html->link(Murl::controller('rocket')->pathExt('manage', $menuItem->getId(), $menuItem->determinePathExt($view->getN2nContext())), 
										new Raw($html->getEsc($menuItem->getLabel()) . '<span></span>')) ?></li>
						<?php endforeach ?>
					</ul>
				</div>
			<?php endforeach ?>
		</nav>
		<nav id="rocket-global-mobile-nav">
			<select onchange="location.href = this.value">
				<option value="<?php $html->out($view->buildUrl(Murl::controller('rocket'))) ?>">
					<?php $html->text('common_select_label') ?>
				</option>
				<?php foreach ($templateModel->getNavArray() as $navArray): ?>
					<optgroup label="<?php $html->out($navArray['label']) ?>">
						<?php foreach ($navArray['menuItems'] as $menuItem): ?>
							<option value="<?php $html->out($view->buildUrl(Murl::controller('rocket')->pathExt('manage', $menuItem->getId()))) ?>" 
									<?php $view->out(isset($selectedMenuItem) && $scriptId == $selectedMenuItem->getId() ? 'selected="selected"' : null) ?>>
								<?php $html->out($menuItem->getLabel())?>
							</option>
						<?php endforeach ?>
					</optgroup>
				<?php endforeach ?>
			</select>
		</nav>
		
		
		<div id="rocket-content-container">
			<?php if (null !== ($activeBreadcrumb = $templateModel->getActiveBreadcrumb())): ?>
				<ul id="rocket-breadcrumb">
					<?php foreach ($templateModel->getBreadcrumbs() as $breadcrumb): ?>
						<li><?php $html->link($breadcrumb->getUrl(), (string) $breadcrumb->getLabel()) ?></li>
					<?php endforeach ?>
					<li class="rocket-breadcrumb-active">
						<?php $html->link($activeBreadcrumb->getUrl(), (string) $activeBreadcrumb->getLabel()) ?>
					</li>
				</ul>
			<?php endif ?>
			
			<!-- WICHTIG -->
			
			<?php if (isset($view->params['title'])): ?>
				<h1 class="rocket-main-title"><?php $html->out($view->params['title']) ?></h1>
			<?php else: ?>
				<h1 class="rocket-main-title">Rocket</h1>
			<?php endif ?>
			
			<?php $html->messageList(null, Message::SEVERITY_ERROR, array('class' => 'rocket-message-error')) ?>
			<?php $html->messageList(null, Message::SEVERITY_INFO, array('class' => 'rocket-message-info')) ?>
			<?php $html->messageList(null, Message::SEVERITY_WARN, array('class' => 'rocket-message-warn')) ?>
			<?php $html->messageList(null, Message::SEVERITY_SUCCESS, array('class' => 'rocket-message-success')) ?>
			
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
	<?php $html->bodyEnd() ?>
</html>
