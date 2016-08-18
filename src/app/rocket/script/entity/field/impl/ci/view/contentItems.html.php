<?php 
	use rocket\script\entity\manage\model\EntryFormInfo;
	use n2n\dispatch\PropertyPath;
use n2n\util\StringUtils;

	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$inTranslationMode = $view->getParam('inTranslationMode');

	$panelDataAttrs = $view->getParam('panelDataAttrs');
	
	$html->addJs('js/command.js');
?>
<div class="rocket-content-nested rocket-content-item-composer<?php $html->out($inTranslationMode ? ' rocket-frozen' : '') ?>" 
		data-content-item-panels="<?php $html->esc(StringUtils::jsonEncode($panelDataAttrs)) ?>" 
		data-text-append-content-item="<?php $html->l10nText('script_content_item_append')?>">
	<?php $formHtml->arrayProps($propertyPath, function() use ($view, $formHtml, $inTranslationMode) { ?>
		<div class="rocket-content-item">
			<?php $view->import('script\entity\manage\view\entryForm.html', 
					array('entryFormInfo' => new EntryFormInfo($formHtml->getValue()->getObject(), 
							$formHtml->createPropertyPath(), array(
									'id' => array('class' => 'rocket-content-item-id'),
									'panel' => array('class' => 'rocket-content-item-panel'),
									'orderIndex' => array('class' => 'rocket-content-item-order-index'))))) ?>	
			<?php if (!$inTranslationMode): ?>				
				<?php $formHtml->objectOptional(null, array('class' => 'rocket-content-item-enabler')) ?>
			<?php endif ?>
		</div>
	<?php }, ($inTranslationMode ? null : sizeof($formHtml->getValue($propertyPath)) + 10)) ?>
</div>

