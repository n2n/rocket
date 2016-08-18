<?php 
	use rocket\script\entity\manage\EntryViewInfo;
	use rocket\script\entity\adaptive\draft\Draft;

	$entryCommandViewModel = $view->params['entryViewInfo']; 
	$view->assert($entryCommandViewModel instanceof EntryViewInfo);
 
	$selectedDraft = $entryCommandViewModel->getScriptSelection()->getDraft();
?>
<h2><?php $html->l10nText('script_draft_nav_title') ?></h2>
<div class="rocket-panel rocket-collapsable rocket-history">
	
	<?php if (null !== ($currentDraft = $entryCommandViewModel->getCurrentDraft())): ?>
		<h3><?php $html->l10nText('script_draft_current_title') ?></h3>
		<ul>
			<li<?php $view->out($currentDraft->equals($selectedDraft) ? ' class="rocket-active"' : '') ?>>
				<div class="rocket-history-entry-content">
					<p><?php $html->l10nDateTime($currentDraft->getLastMod()) ?></p>
					<p><?php $html->linkToController($entryCommandViewModel->buildPathToDraft($currentDraft), $html->getL10nText('script_draft_show_draft_label')) ?></p>
				</div>
				<?php $html->linkToController($entryCommandViewModel->buildPathToDraft($currentDraft), new n2n\ui\Raw('<i class="fa fa-inbox"></i>'), 
						array('class' => 'rocket-single-command rocket-control', 'title' => $html->getL10nText('script_draft_load_label'))) ?>
			</li>
		</ul>
	<?php endif ?>
	<h3><?php $html->l10nText('script_draft_live_entry_title') ?></h3>
	<ul>
		<li<?php $view->out($selectedDraft === null ? ' class="rocket-active"' : '') ?>>
				<div class="rocket-history-entry-content">
					<p>activation date</p>
					<p><?php $html->linkToController($entryCommandViewModel->getLiveEntryPathExt(), 
							$html->getL10nText('script_draft_show_live_entry_label')) ?></p>
				</div>
				<?php $html->linkToController($entryCommandViewModel->getLiveEntryPathExt(), new n2n\ui\Raw('<i class="fa fa-inbox"></i>'),
						array('class' => 'rocket-single-command rocket-control', 'title' => $html->getL10nText('script_draft_show_live_entry_label'))) ?>
		</li>
	</ul>
	<h3><?php $html->l10nText('script_draft_history_title') ?></h3>
	<ul>
		<?php foreach ($entryCommandViewModel->getHistoricizedDrafts() as $draft): $view->assert($draft instanceof Draft) ?>
			<li<?php $view->out($draft->equals($selectedDraft) ? ' class="rocket-active"' : '') ?>>
				<div class="rocket-history-entry-content">
					<p><?php $html->l10nDateTime($draft->getLastMod()) ?></p>
					<p class="rocket-history-status">
						<?php if ($draft->isPublished()): ?>
							<?php $html->linkToController($entryCommandViewModel->buildPathToDraft($draft), $html->getL10nText('script_draft_show_history_label')) ?>
						<?php else: ?>
							<?php $html->linkToController($entryCommandViewModel->buildPathToDraft($draft), $html->getL10nText('script_draft_show_draft_label')) ?>
						<?php endif ?>
						<?php // TODO: script_draft_show_backup_label einbauen ?>
					</p>
				</div>
				<?php $html->linkToController($entryCommandViewModel->buildPathToDraft($draft), new n2n\ui\Raw('<i class="fa fa-inbox"></i>'), 
						array('class' => 'rocket-single-command rocket-control', 'title' => $html->getL10nText('script_draft_load_label'))) ?>
			</li>
		<?php endforeach ?>
	</ul>
</div>