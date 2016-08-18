<?php
	use rocket\script\entity\field\impl\relation\option\EntryFormWrapper;
	use rocket\script\entity\manage\model\EntryFormInfo;
	use n2n\dispatch\PropertyPath;
use n2n\ui\html\HtmlUtils;
use rocket\script\entity\command\control\IconType;
use rocket\script\entity\manage\model\EntryFormViewModel;
	
	$propertyPath = $view->getParam('propertyPath');
	$view->assert($propertyPath instanceof PropertyPath);
	
	$entryFormWrapper = $formHtml->getValue($propertyPath)->getObject();
	$view->assert($entryFormWrapper instanceof EntryFormWrapper);
	
	$enablableId = HtmlUtils::buildUniqueId('rocket-');
?>

<?php if (!$entryFormWrapper->isRequired()): ?>
	<label><?php $formHtml->inputCheckbox($propertyPath->ext('enabled'), true, 
			array('class' => 'rocket-oto-enabler', 'data-text-add' => $view->getL10nText('script_cmd_add_label'), 
					'data-icon-add' => IconType::ICON_PLUS_CIRCLE, 'data-icon-remove' => IconType::ICON_TIMES), $html->getL10nText('common_enable')) ?></label>
<?php endif ?>
<div class="rocket-oto-content">
	<?php $view->import('script\entity\manage\view\entryForm.html', array(
			'entryFormViewModel' => new EntryFormViewModel($entryFormWrapper->getEntryForm(), 
					$propertyPath->ext('entryForm')))) ?>
</div>