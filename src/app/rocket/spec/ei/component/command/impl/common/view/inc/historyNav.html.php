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

	use rocket\spec\ei\manage\draft\Draft;
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\spec\ei\component\command\impl\common\model\EntryCommandViewModel;
use rocket\user\model\RocketUserDao;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$request = HtmlView::request($this);

	$entryCommandViewModel = $view->getParam('entryCommandViewModel');
	$view->assert($entryCommandViewModel instanceof EntryCommandViewModel);
 
	$rocketUserDao = $view->lookup(RocketUserDao::class);
	$view->assert($rocketUserDao instanceof RocketUserDao);
	
	$selectedDraft = $entryCommandViewModel->getSelectedDraft();
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
						<?php if (null !== ($user = $rocketUserDao->getUserById($latestDraft->getId()))): ?> 
							<?php $html->out($user) ?>
						<?php endif ?>
					</p>
					<?php if ($latestDraft->isNew()): ?>
						<p>New draft</p>
					<?php else: ?>
						<p><?php $html->linkToController($entryCommandViewModel->getPathToDraft($latestDraft),
								$html->getL10nText('spec_draft_show_draft_label')) ?></p>
					<?php endif ?>
				</div>
				
				
				<?php $html->linkToController($entryCommandViewModel->buildPathToDraft($latestDraft), 
						new n2n\web\ui\Raw('<i class="fa fa-inbox"></i>'), 
						array('class' => 'rocket-single-command rocket-control', 
								'title' => $html->getL10nText('spec_draft_load_label'))) ?>
			</li>
		</ul>
	<?php endif ?>
	
	<h3><?php $html->l10nText('ei_impl_draft_live_entry_title') ?></h3>
	<ul>
		<li<?php $view->out($selectedDraft === null ? ' class="rocket-active"' : '') ?>>
				<div class="rocket-history-entry-content">
					<p>activation date</p>
					<p><?php $html->linkToController($entryCommandViewModel->getLiveEntryUrl($request), 
							$html->getL10nText('spec_draft_show_live_entry_label')) ?></p>
				</div>
				<?php $html->link($entryCommandViewModel->getLiveEntryUrl($request), new n2n\web\ui\Raw('<i class="fa fa-inbox"></i>'),
						array('class' => 'rocket-single-command rocket-control', 'title' => $html->getL10nText('spec_draft_show_live_entry_label'))) ?>
		</li>
	</ul>
	
	<h3><?php $html->l10nText('ei_impl_draft_history_title') ?></h3>
	<ul>
		<?php foreach ($entryCommandViewModel->getHistoricizedDrafts() as $draft): $view->assert($draft instanceof Draft) ?>
			<li<?php $view->out($draft->equals($selectedDraft) ? ' class="rocket-active"' : '') ?>>
				<div class="rocket-history-entry-content">
					<p><?php $html->l10nDateTime($draft->getLastMod()) ?></p>
					<p class="rocket-history-status">
						<?php if ($draft->isPublished()): ?>
							<?php $html->linkToController($entryCommandViewModel->buildPathToDraft($draft), $html->getL10nText('spec_draft_show_history_label')) ?>
						<?php else: ?>
							<?php $html->linkToController($entryCommandViewModel->buildPathToDraft($draft), $html->getL10nText('spec_draft_show_draft_label')) ?>
						<?php endif ?>
						<?php // TODO: spec_draft_show_backup_label einbauen ?>
					</p>
				</div>
				<?php $html->linkToController($entryCommandViewModel->buildPathToDraft($draft), new n2n\web\ui\Raw('<i class="fa fa-inbox"></i>'), 
						array('class' => 'rocket-single-command rocket-control', 'title' => $html->getL10nText('spec_draft_load_label'))) ?>
			</li>
		<?php endforeach ?>
	</ul>
</div>
