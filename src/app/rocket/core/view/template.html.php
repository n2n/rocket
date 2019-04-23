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
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\core\model\TemplateModel;
	use n2nutil\jquery\JQueryUiLibrary;
	
	$view = HtmlView::view($this);
	$request = HtmlView::request($view);
	$html = HtmlView::html($view);
	$httpContext = HtmlView::httpContext($view);
	
	$view->useTemplate('boilerplate.html', $view->getParams());
	
	$htmlMeta->addLibrary(new JQueryUiLibrary(3));
	
	$templateModel = $view->lookup(TemplateModel::class);
	$view->assert($templateModel instanceof TemplateModel);
	
	$html->meta()->addCssCode('
			.rocket-layer {
				animation: layertransform 0.2s;
			}
			
			.rocket-layer,
			.rocket-main-layer {
				visibility: hidden;
			}
			
			.rocket-layer.rocket-active,
			.rocket-main-layer.rocket-active {
				visibility: visible;
			}
			
			@keyframes layertransform {
			    from { transform: translateX(100vw); }
			    to { transform: translateX(0); }
			}');
?>


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

			<?php $html->messageList(null, Message::SEVERITY_ERROR, array('class' => 'rocket-messages alert alert-danger list-unstyled')) ?>
			<?php $html->messageList(null, Message::SEVERITY_INFO, array('class' => 'rocket-messages alert alert-info list-unstyled')) ?>
			<?php $html->messageList(null, Message::SEVERITY_WARN, array('class' => 'rocket-messages alert alert-warn list-unstyled')) ?>
			<?php $html->messageList(null, Message::SEVERITY_SUCCESS, array('class' => 'rocket-messages alert alert-success list-unstyled')) ?>

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
