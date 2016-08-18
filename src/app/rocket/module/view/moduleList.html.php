<?php
	$view->useTemplate('core\view\template.html', 
			array('title' => $view->getL10nText('module_title')));
?>
<section class="rocket-panel">
	<h2><?php $html->text('module_installed_title')?></h2>
	<table class="rocket-list">
		<thead>
			<tr>
				<th><?php $html->l10nText('module_namespace_label') ?></th>
				<th><?php $html->l10nText('common_name_label') ?></th>
				<th><?php $html->l10nText('module_version_label') ?></th>
				<th><?php $html->l10nText('module_dependencies_label') ?></th>
				<th><?php $html->l10nText('module_author_label') ?></th>
				<th><?php $html->l10nText('module_website_label') ?></th>
				<th><?php $html->l10nText('common_list_tools_label') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach (n2n\N2N::getModules() as $module): $view->assert($module instanceof n2n\core\Module) ?>
				<?php $moduleConfiguration = $module->getModuleConfiguration() ?>
				<?php $encodedNamespace = n2n\reflection\ReflectionUtils::encodeNamespace($module->getNamespace()) ?>
				<tr>
					<td><?php $html->esc($module->getNamespace()) ?></td>
					<td><?php $html->esc($moduleConfiguration->getName())?></td>
					<td><?php $html->esc($moduleConfiguration->getVersion()) ?></td>
					<td>
						<?php if (sizeof($dependencies = $moduleConfiguration->getDependencies())): ?>
							<ul>
								<?php foreach ($dependencies as $namespace => $version): ?>
									<li>
										<span><?php $html->esc($namespace) ?></span>
										<span><?php $html->esc($version) ?></span>
									</li>
								<?php endforeach ?>
							</ul>
						<?php endif ?>
					</td>
					<td><?php $html->esc($moduleConfiguration->getAuthor()) ?></td>
					<td><?php $html->esc($moduleConfiguration->getWebsite()) ?></td>
					<td>
						<ul class="rocket-simple-controls">
							<?php if ($module->getModuleConfiguration()->hasDescriber()): ?>
								<li>
									<?php $html->linkToController(array('customconfig', $encodedNamespace),
		 									new n2n\ui\Raw('<i class="fa fa-wrench"></i><span>' . $html->getText('common_config_label') . '</span>'), 
											array('class' => 'rocket-control')) ?>
		 						</li>
	 						<?php endif ?>
							<li>
								<?php $html->linkToController(array('scriptelements', $encodedNamespace), 
										new n2n\ui\Raw('<i class="fa fa-cog"></i><span>' . $html->getText('module_manage_script_elements_label') . '</span>'), 
										array('class' => 'rocket-control')) ?>
							</li>
						</ul>
					</td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</section>
