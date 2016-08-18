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

	use rocket\tool\xml\MailItem;	
	use rocket\tool\mail\model\MailCenter;
	use rocket\tool\mail\controller\MailArchiveBatchController;
	use n2n\log4php\appender\nn6\AdminMailCenter;
	use rocket\tool\mail\controller\MailCenterController;
	use n2n\mail\MailUtils;

	$mailCenter = $view->getParam('mailCenter');
	
	$view->assert($mailCenter instanceof MailCenter);
	
	$numPages = $mailCenter->getNumPages();
	$items = $mailCenter->getCurrentItems();
	$currentPageNum = $mailCenter->getCurrentPageNum();
	$numItems = $mailCenter->getNumItemsTotal();
	
	$currentFileName = $view->getParam('currentFileName');
	$view->useTemplate('~\core\view\template.html', 
			array('title' => $view->getL10nText('tool_mail_center_title')));
	
	$html->meta()->addJs('js/tools.js');
	
	$fileNames = MailCenter::getMailFileNames();
?>
<div id="rocket-tools-mail-center" class="rocket-panel">
	<h3><?php $html->text('tool_mail_center_title') ?></h3>
	<?php if (count($fileNames) > 1 || $numPages > 1): ?>
		<div class="rocket-tool-panel">
			<form>
				<dl>
					<?php if (count($fileNames) > 1): ?>
						<dt>
							<label><?php $html->text('tool_mail_center_archives_label') ?></label>
						</dt>
						<dd>
							<select class="rocket-paging">
								<?php foreach ($fileNames as $fileName) : ?>
									<?php if ($fileName == AdminMailCenter::DEFAULT_MAIL_FILE_NAME) : ?>
										<option value="<?php $html->out($request->getCurrentControllerContextPath()) ?>" 
												<?php $view->out(($fileName == $currentFileName) ? 'selected' : null) ?>>
												<?php $html->text('tool_mail_center_current_file_label') ?>
										</option>
									<?php else : ?>
										<?php if (null == ($date = MailArchiveBatchController::fileNameToDate($fileName))) continue ?>
										<option value="<?php $html->out($request->getCurrentControllerContextPath(array(MailCenterController::ACTION_ARCHIVE, $fileName))) ?>" 
												<?php $view->out(($fileName == $currentFileName) ? 'selected' : null) ?>>
												<?php $html->text('tool_mail_center_archive_file_label', array('month' => $date->format('m'), 'year' => $date->format('Y'))) ?>
												<?php $view->out(MailArchiveBatchController::fileNameToIndex($fileName)) ?>
										</option>
									<?php endif ?>
								<?php endforeach ?>
							</select>
						</dd>
					<?php endif ?>
					<?php if ($numPages > 1) : ?>
						<dt>
							<label>Seite</label>
						</dt>
						<dd>
							<select class="rocket-paging">
								<?php for ($i = 1; $i <= $numPages; $i++) : ?>
									<?php $params = ($currentFileName == AdminMailCenter::DEFAULT_MAIL_FILE_NAME) ? array() : array(MailCenterController::ACTION_ARCHIVE, $currentFileName) ?>
									<?php $params = ($i == 1) ? $params : array_merge($params, array($i)) ?>
									<option value="<?php $html->out($html->meta()->getControllerUrl($params)) ?>" 
											<?php $view->out(($i == $currentPageNum) ? 'selected' : null) ?>>
											<?php $html->out($i) ?>
									</option>
								<?php endfor ?>
							</select>
						</dd>
					<?php endif ?>
				</dl>
			</form>
		</div>
	<?php endif ?>
	<div>
		<?php foreach ((array) $items as $itemIndex => $mailItem) : $mailItem instanceof MailItem ?>
			<article class="rocket-mail">
				<header class="rocket-mail-header clearfix">
					<h4 class="rocket-mail-subject"><i class="fa fa-plus"></i> <?php $html->out($mailItem->getSubject())?></h4>
					<div class="rocket-mail-senddate">
						<?php $html->l10nDateTime($mailItem->getDateTime())?> 
					</div>
				</header>
				<dl class="rocket-mail-properties">
					<dt><?php $html->text('tool_mail_center_mail_to_label') ?></dt>
					<dd><?php $html->out($mailItem->getTo()) ?></dd>
					<dt><?php $html->text('tool_mail_center_mail_from_label') ?></dt>
					<dd><?php $html->out($mailItem->getFrom()) ?></dd>
					<?php if ($mailItem->hasReplyTo()): ?>
						<dt><?php $html->text('tool_mail_center_mail_replyto_label') ?></dt>
						<dd><?php $html->out($mailItem->getReplyTo()) ?></dd>
					<?php endif ?>

					<?php if ($mailItem->hasAttachments()) : ?>
						<dt>
							<?php $html->text('tool_mail_center_attatchments_label') ?>
						</dt>
						<dd>
							<ul>
								<?php foreach($mailItem->getAttachments() as $attachmentIndex => $attachment) : ?>
									<li>
										<?php $html->linkToController(array(MailCenterController::ACTION_ATTACHMENT, $currentFileName, 
												$itemIndex, $attachmentIndex, $attachment->getName()), $attachment->getName()) ?>
									</li>
								<?php endforeach ?>
							</ul>
						</dd>
					<?php endif ?>
					<dt class="rocket-mail-message-label"><?php $html->text('tool_mail_center_mail_message_label') ?></dt>
					<dd class="rocket-mail-message"><pre style="<?php $html->out('font-family: "Courier";') ?>"><?php $html->esc($mailItem->getMessage()) ?></pre></dd>
				</dl>
			</article>
		<?php endforeach ?>
	</div>
</div>
