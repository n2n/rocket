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
	
	use n2n\core\N2N;
	use n2n\web\ui\Raw;
	use rocket\core\model\TemplateModel;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\web\http\nav\Murl;
	use rocket\user\model\LoginContext;
	use rocket\core\controller\RocketController;
	
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
	
	// 	$manageState = $view->lookup('rocket\ei\manage\ManageState');
	// 	$view->assert($manageState instanceof ManageState);
	
	$htmlMeta = $html->meta();
	
	$htmlMeta->addMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
	$htmlMeta->addMeta(array('content' => 'IE=edge', 'http-equiv' => 'x-ua-compatible'));
	$htmlMeta->addMeta(array('name' => 'robots', 'content' => 'noindex, nofollow'));
	
	// new design (not ready yet):
	$htmlMeta->addCss('css/rocket-20.css');
	// old design:
	//	$htmlMeta->addCss('css/rocket.css');
	$htmlMeta->addCss('css/font-awesome.css');
	// 	$htmlMeta->addJs('js/respond.src.js', null);
	// 	$htmlMeta->addJs('js/jquery-responsive-table.js', null, true);
	
	
	// 	$spec = $rocket->getSpec();
	// 	$menuGroups = $spec->getMenuGroups();
	// 	$selectedLaunchPad = $manageState->getSelectedLaunchPad();
	// 	$breadcrumbs = $rocketState->getBreadcrumbs();
	// 	$activeBreadcrumb = array_pop($breadcrumbs);
	$htmlMeta->addLink(array('rel' => 'shortcut icon', 'href' => $httpContext->getAssetsUrl('rocket')->ext(array('img', 'favicon.ico'))));
	$htmlMeta->addLink(array('rel' => 'apple-touch-icon', 'href' => $httpContext->getAssetsUrl('rocket')->ext(array('img', 'apple-touch-icon.png'))));
?>
<!DOCTYPE html>
<html lang="<?php $html->out($request->getN2nLocale()->getLanguage()->getShort()) ?>">
<?php $html->headStart() ?>
	<meta charset="<?php $html->out(N2n::CHARSET) ?>" />
	<base href="<?php $html->out($view->buildUrl(Murl::controller(RocketController::class))->getPath()->chEndingDelimiter(true)) ?>" />
<?php $html->headEnd() ?>
<?php $html->bodyStart(array('data-refresh-path' => $view->buildUrl(Murl::controller('rocket')),
		'class' => (isset($view->params['tmplMode']) ? $view->params['tmplMode'] : null))) ?>
	<div data-jhtml-container="rocket-template" data-jhtml-browsable="true" 
			data-rocket-url="<?php $html->out($view->buildUrlStr(Murl::controller('rocket'))) ?>">
		<header id="rocket-header">
			<div id="rocket-logo">
				<?php $html->link(Murl::controller('rocket'), $html->getImageAsset('img/rocket-logo.svg', 'logo'),
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
				<ul class="nav rocket-meta-nav justify-content-end">
					<?php if ($templateModel->getCurrentUser()->isAdmin()): ?>
						<li class="nav-item">
							<?php $html->linkStart(Murl::controller('rocket')->pathExt('tools'), array('class' => 'nav-link')) ?>
							<i class="fa fa-wrench mr-2"></i><span><?php $html->text('tool_title') ?></span>
							<?php $html->linkEnd() ?>
						</li>
						<li class="nav-item">
							<?php $html->linkStart(Murl::controller('rocket')->pathExt('users'), array('class' => 'nav-link')) ?>
							<i class="fa fa-user mr-2"></i><span><?php $html->text('user_title') ?></span>
							<?php $html->linkEnd() ?>
						</li>
						<li class="nav-item">
							<?php $html->linkStart(Murl::controller('rocket')->pathExt('usergroups'), array('class' => 'nav-link')) ?>
							<i class="fa fa-group mr-2"></i><span><?php $html->text('user_groups_title') ?></span>
							<?php $html->linkEnd() ?>
						</li>
					<?php endif ?>
					<li class="nav-item">
						<?php $html->linkStart(Murl::controller('rocket')->pathExt('users', 'profile'), array('class' => 'nav-link rocket-conf-user')) ?>
						<i class="fa fa-user mr-2"></i><span><?php $html->out((string) $templateModel->getCurrentUser()) ?></span>
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
				<div class="rocket-nav-group<?php $html->esc($navArray['open'] ? ' rocket-nav-group-open': '') ?>"
						data-nav-group-id="<?php $html->out(str_replace(' ', '-', strtolower($navArray['menuGroup']->getLabel()))) ?>">
					<h3 class="rocket-global-nav-group-title">
						<a><?php $html->esc($navArray['menuGroup']->getLabel()) ?></a>
						<i class="fa <?php $html->esc($navArray['open'] ? 'fa-minus': 'fa-plus') ?>"></i>
					</h3>
					<ul class="nav flex-column">
						<?php foreach ($navArray['launchPads'] as $launchPad): ?>
							<li class="nav-item">
								<?php $html->link(
										$view->buildUrl(Murl::controller('rocket')->pathExt('manage', $launchPad->getId()))
											->ext($launchPad->determinePathExt($view->getN2nContext())),
									new Raw($html->getEsc($navArray['menuGroup']->determineLabel($launchPad))
										. '<span></span>'),
									array('data-jhtml' => 'true', 'class' => 'nav-link'
										. ($templateModel->isLaunchPadActive($launchPad) ? ' active' : null))) ?></li>
						<?php endforeach ?>
					</ul>
				</div>
			<?php endforeach ?>
		</nav>
		
		<?php $view->importContentView() ?>
		
	<?php $html->bodyEnd() ?>
</html>