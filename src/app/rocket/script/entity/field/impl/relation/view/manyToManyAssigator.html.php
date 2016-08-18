<?php
	use rocket\script\entity\field\impl\relation\command\model\ManyToManyAssignatorForm;
	use rocket\script\entity\command\control\IconType;
	
	$assignatorForm = $view->getParam('assignatorForm');
	$view->assert($assignatorForm instanceof ManyToManyAssignatorForm);

	$scriptField = $assignatorForm->getManyToManyScriptField();
	$entityScript = $scriptField->getEntityScript();
	$propertyLabel = $scriptField->getLabel();
	
	$view->useTemplate('core\view\template.html', array('title' => 
			$view->getL10nText('script_cmd_manytomany_assignator_title', array('label' => $entityScript->getLabel(), 
					'property_label' => $propertyLabel))));
?>

<?php $formHtml->open($assignatorForm) ?>
	<?php $formHtml->select('assignedIds', $assignatorForm->getAssignedIdOptions(), 
			array('id' => 'rocket-assigned-id-options', 
					'data-text-assigned-entities' => $view->getL10nText('script_cmd_manytomany_assigned_title', 
							array('objects' => $propertyLabel)), 
					'data-text-unassigned-entities' => $view->getL10nText('script_cmd_manytomany_not_assigned_title',
							array('objects' => $propertyLabel)),
					'data-class-name-icon-remove' => IconType::ICON_TIMES, 
					'data-class-name-icon-add' => IconType::ICON_PLUS_CIRCLE), true) ?>
	<div id="rocket-page-controls">
		<ul>
			<li class="rocket-control-warning">
				<?php $formHtml->inputSubmit('save', $view->getL10nText('common_save_label')) ?>
			</li>
		</ul>
	</div>
	
<?php $formHtml->close() ?>