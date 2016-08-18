<?php 
	use rocket\script\config\model\EntityScriptForm;
	use rocket\script\entity\command\control\IconType;
	
	$entityScriptForm = $view->getParam('entityScriptForm');
	$view->assert($entityScriptForm instanceof EntityScriptForm);
?>
<section id="rocket-script-config-fields" class="rocket-panel">
	<h2><?php $html->l10nText('script_fields_title') ?></h2>
	<table class="rocket-list">
		<thead>
			<tr>
				<th><?php $html->text('common_id_label') ?></th>
				<th><?php $html->text('common_name_label') ?></th>
				<th><?php $html->text('common_label_label') ?></th>
				<th><?php $html->text('common_list_tools_label') ?></th>
			</tr>
		</thead>
		<tbody id="rocket-config-assigned-script-fields" 
				data-ban-title="<?php $html->text('script_fields_ban_script_field_title') ?>"
				data-assign-title="<?php $html->text('script_fields_assign_script_field_title') ?>"
				data-add-label="<?php $html->text('script_fields_add_script_field_label') ?>" 
				data-known-fields="<?php $html->out(json_encode($entityScriptForm->getKnownFieldDataAttrs())) ?>"
				data-known-properties="<?php $html->out(json_encode($entityScriptForm->getKnownPropertiesDataAttrs())) ?>">
			<?php foreach ($formHtml->getValue('fieldClassNames') as $key => $commandClassName): ?>
				<tr class="rocket-config-table-row">
					<td>
						<?php $formHtml->inputField('fieldIds[' . $key . ']', array("class" => "rocket-script-fields-config-id", "place")) ?>
						<?php $formHtml->inputField('fieldPropertyNames[' . $key . ']', array("class" => "rocket-script-fields-config-property-name")) ?>
						<?php $formHtml->inputField('fieldEntityPropertyNames[' . $key . ']', array("class" => "rocket-script-fields-config-entity-property-name")) ?>
					</td>
					<td>
						<?php $formHtml->inputField('fieldClassNames[' . $key . ']', array("class" => "rocket-script-fields-config-class-name")) ?>
					</td>
					<td>
						<?php $formHtml->inputField('fieldLabels[' . $key . ']', array("class" => "rocket-script-fields-config-label")) ?>
					</td>
					<td></td>
				</tr>
			<?php endforeach ?>
			<tr>
				<td>
					<?php $formHtml->inputField('fieldIds[]', array("class" => "rocket-script-fields-config-id")) ?>
					<?php $formHtml->inputField('fieldPropertyNames[]', array("class" => "rocket-script-fields-config-property-name")) ?>
					<?php $formHtml->inputField('fieldEntityPropertyNames[]', array("class" => "rocket-script-fields-config-entity-property-name")) ?>
				</td>
				<td>
					<?php $formHtml->inputField('fieldClassNames[]', array("class" => "rocket-script-fields-config-class-name")) ?>
				</td>
				<td>
					<?php $formHtml->inputField('fieldLabels[]', array("class" => "rocket-script-fields-config-label")) ?>
				</td>
				<td></td>
			</tr>
		</tbody>
	</table>
</section>