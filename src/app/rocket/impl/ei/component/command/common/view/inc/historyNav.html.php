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

	use rocket\ei\manage\draft\Draft;
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\impl\ei\component\command\common\model\EntryCommandViewModel;
	use rocket\user\model\RocketUserDao;
	use rocket\ei\manage\ControlEiuHtmlBuilder;
	use rocket\ei\manage\gui\ViewMode;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$request = HtmlView::request($this);

	$entryCommandViewModel = $view->getParam('entryCommandViewModel');
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);
 
	$rocketUserDao = $view->lookup(RocketUserDao::class);
	$view->assert($rocketUserDao instanceof RocketUserDao);
	
	$selectedDraft = $entryCommandViewModel->getSelectedDraft();
	
	$controlEiHtml = new ControlEiuHtmlBuilder($view, $entryCommandViewModel->getEiFrame());
?>
<h2><?php $html->l10nText('ei_impl_draft_nav_title') ?></h2>
<div class="rocket-panel rocket-collapsable rocket-history">
	
	<?php if (null !== ($latestDraft = $entryCommandViewModel->getLatestDraft())): ?>
		<h3><?php $html->l10nText('ei_impl_draft_current_title') ?></h3>
		<ul>
			<li<?php $view->out($latestDraft->equals($selectedDraft) ? ' class="rocket-active"' : '') ?>>
				<div class="rocket-history-entry-content">
					<p>
						<?php $html->l10nDateTime($latestDraft->getLastMod()) ?>
						<?php if (null !== ($user = $rocketUserDao->getUserById($latestDraft->getUserId()))): ?> 
							<?php $html->out($user) ?>
						<?php endif ?>
					</p>
					<?php if ($latestDraft->isNew()): ?>
						<p>New draft</p>
					<?php else: ?>
						<?php $controlEiHtml->entryControlList($latestDraft, ViewMode::LIST_READ, true) ?>
					<?php endif ?>
				</div>
			</li>
		</ul>
	<?php endif ?>
	
	<h3><?php $html->l10nText('ei_impl_draft_live_entry_title') ?></h3>
	<ul>
		<li<?php $view->out($selectedDraft === null ? ' class="rocket-active"' : '') ?>>
				<div class="rocket-history-entry-content">
					<p>activation date</p>
				</div>
				<?php $controlEiHtml->entryControlList($entryCommandViewModel->getEiObject()->getEiEntityObj(), 
						ViewMode::LIST_READ, true) ?>
		</li>
	</ul>
	
	<h3><?php $html->l10nText('ei_impl_draft_history_title') ?></h3>
	<ul>
		<?php foreach ($entryCommandViewModel->getHistoricizedDrafts() as $draft): $view->assert($draft instanceof Draft) ?>
			<li<?php $view->out($draft->equals($selectedDraft) ? ' class="rocket-active"' : '') ?>>
				<div class="rocket-history-entry-content">
					<p><?php $html->l10nDateTime($draft->getLastMod()) ?></p>
				</div>
				<?php $controlEiHtml->entryControlList($draft, ViewMode::LIST_READ, true) ?>
			</li>
		<?php endforeach ?>
	</ul>
</div>
