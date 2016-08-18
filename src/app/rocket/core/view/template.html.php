<?php 
	use n2n\core\Message;
	use n2n\N2N;
	use n2n\ui\Raw;
	use util\jquery\JQueryLibrary;
	use rocket\core\model\TemplateModel;
	
	$templateModel = $view->lookup('rocket\core\model\TemplateModel'); 
	$view->assert($templateModel instanceof TemplateModel);

// 	$loginContext = $view->lookup('rocket\user\model\LoginContext'); 
// 	$view->assert($loginContext instanceof LoginContext);
	
// 	$rocket = $view->lookup('rocket\core\model\Rocket'); 
// 	$view->assert($rocket instanceof Rocket);
	
// 	$rocketState = $view->lookup('rocket\core\model\RocketState'); 
// 	$view->assert($rocketState instanceof RocketState);
	
// 	$manageState = $view->lookup('rocket\script\core\ManageState'); 
// 	$view->assert($manageState instanceof ManageState);
	
	$rocketControllerContext = $request->getControllerContextByKey('rocket\core\controller\RocketController');
 
	$html->addMeta(array('charset' => N2N::CHARSET));
	$html->addMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
	$html->addMeta(array('content' => 'IE=edge,chrome=1', 'http-equiv' => 'X-UA-Compatible'));
	$html->addMeta(array('name' => 'robots', 'content' => 'noindex, nofollow'));
	$html->addLibrary(new JQueryLibrary());
	$html->addCss('css/style.css');
	$html->addCss('css/font-awesome.css');
	$html->addJs('js/html5.js');
	$html->addJs('js/respond.src.js');
// 	$html->addJs('js/jquery-responsive-table.js');
	$html->addJs('js/rocket.js');
	
// 	$scriptManager = $rocket->getScriptManager();
// 	$menuGroups = $scriptManager->getMenuGroups();
// 	$selectedMenuItem = $manageState->getSelectedMenuItem();
// 	$breadcrumbs = $rocketState->getBreadcrumbs();
// 	$activeBreadcrumb = array_pop($breadcrumbs);
	$html->addLink(array('rel' => 'shortcut icon', 'href' => $request->getAssetsPath('rocket', array('img', 'favicon.ico'))));
	$html->addLink(array('rel' => 'apple-touch-icon', 'href' => $request->getAssetsPath('rocket', array('img', 'apple-touch-icon.png'))));
?>
<!DOCTYPE html>
<html lang="<?php $html->out($request->getLocale()->getLanguage()->getShort()) ?>"> 
	<?php $html->headStart() ?>
	<?php $html->headEnd() ?>
	<?php $html->bodyStart(array('data-refresh-path' => $request->getControllerContextPath($rocketControllerContext), 
			'class' => (isset($view->params['tmplMode']) ? $view->params['tmplMode'] : null))) ?>
		<header id="rocket-header">
			<?php $html->linkToController(null, $html->getImageAsset('img/nav-logo-05.png', 'logo'), 
					array('id' => 'rocket-branding'), null, null, $rocketControllerContext) ?>
			<h2 id="rocket-customer-name"><?php $html->out(N2N::getAppConfig()->general()->getPageName()) ?></h2>
			<a href="#" id="rocket-conf-nav-toggle" title="<?php $html->text('mobile_nav_title') ?>">
				<i class="fa fa-navicon"></i>
			</a>
			<nav id="rocket-conf-nav">
				<h2 class="sr-only"><?php $html->l10nText('conf_nav_title') ?></h2>
				<ul class="rocket-level-1">
					<?php if (N2N::isDevelopmentModeOn()): ?>
						<li class="rocket-conf-modules"><?php $html->linkToController('modules', new Raw('<i class="fa fa-puzzle-piece"></i>' . $html->getL10nText('module_title')), 
								null, null, null, 'rocket\core\controller\RocketController') ?>
							<ul class="rocket-level-2">
								<li><?php $html->linkToController('modules', $html->getL10nText('module_title'), 
										null, null, null, 'rocket\core\controller\RocketController') ?></li>
								<li><?php $html->linkToController('modules', $html->getL10nText('module_title'), 
										null, null, null, 'rocket\core\controller\RocketController') ?></li>
							</ul>		
						</li>
						<li class="rocket-conf-scripts"><?php $html->linkToController('scripts', 
								new Raw('<i class="rocket-conf-nav-icon fa fa-gear"></i>' . $html->getL10nText('script_title')), 
								null, null, null, 'rocket\core\controller\RocketController') ?></li>
					<?php endif ?>
					<?php if ($templateModel->getCurrentUser()->isAdmin()): ?>
						<li class="rocket-conf-users"><?php $html->linkToController('users', 
								new Raw('<i class="fa fa-user"></i>' . $html->getL10nText('user_title')), 
									null, null, null, 'rocket\core\controller\RocketController') ?></li>
						<li class="rocket-conf-users"><?php $html->linkToController('usergroups', 
								new Raw('<i class="fa fa-group"></i>' . $html->getL10nText('user_groups_title')), 
								null, null, null, 'rocket\core\controller\RocketController') ?></li>
						<li class="rocket-conf-tools"><?php $html->linkToController('tools', 
								new Raw('<i class="fa fa-wrench"></i>' . $html->getL10nText('tool_title')), 
								null, null, null, 'rocket\core\controller\RocketController') ?></li>
					<?php endif ?>
					<li class="rocket-conf-about"><?php $html->linkToController('about', 
							new Raw('<i class="fa fa-info-circle"></i>' . $html->getL10nText('about_title')), 
							null, null, null, 'rocket\core\controller\RocketController') ?></li>
					<li class="rocket-conf-about"><?php $html->linkToController(
							array('users', 'edit', $templateModel->getCurrentUser()->getId()), 
							new Raw('<i class="fa fa-key"></i>' . $html->getL10nText('user_edit_profile_label')), 
							array(''), null, null, 'rocket\core\controller\RocketController') ?></li>
				</ul>
			</nav>
			<?php $html->linkToController(array('logout'), new Raw('<i class="fa fa-sign-out"></i><span class="rocket-logout-text">' 
						. $html->getL10nText('user_logout_label', 
					array('user' => (string) $templateModel->getCurrentUser())) . '</span>'), 
					array('id' => 'rocket-logout'), null, null, $rocketControllerContext) ?>
		</header>
		<nav id="rocket-global-nav">
			<h2 class="sr-only"><?php $html->l10nText('manage_nav_title') ?></h2>
			<?php foreach ($templateModel->getNavArray() as $navArray): ?>
				<div class="rocket-nav-group<?php $html->esc($navArray['open'] ? ' rocket-nav-group-open': '') ?>">
					<h3><a><i class="fa <?php $html->esc($navArray['open'] ? 'fa-minus': 'fa-plus') ?>"></i> 
							<?php $html->esc($navArray['label']) ?></a></h3>
					<ul>
						<?php foreach ($navArray['menuItems'] as $menuItem): ?>
							<li<?php $view->out($templateModel->isMenuItemSelected($menuItem) 
									? ' class="rocket-nav-group-list-item-active"' : null) ?>>
								<?php $html->linkToController(array('manage', $menuItem->getId()), 
										new Raw($html->getEsc($menuItem->getLabel()) . '<span></span>'), null, 
										null, null, $rocketControllerContext) ?></li>
						<?php endforeach ?>
					</ul>
				</div>
			<?php endforeach ?>
		</nav>
		<nav id="rocket-global-mobile-nav">
			<select onchange="location.href = this.value">
				<option value="<?php $html->out($request->getControllerContextPath($rocketControllerContext)) ?>">
					<?php $html->text('common_select_label') ?>
				</option>
				<?php foreach ($templateModel->getNavArray() as $navArray): ?>
					<optgroup label="<?php $html->out($navArray['label']) ?>">
						<?php foreach ($navArray['menuItems'] as $menuItem): ?>
							<option value="<?php $html->out($request->getControllerContextPath($rocketControllerContext, array('manage', $menuItem->getId()))) ?>" 
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
			
			<div class="<?php $html->esc($view->hasPanel('additional') ? ' rocket-contains-additional' : '') ?>">
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