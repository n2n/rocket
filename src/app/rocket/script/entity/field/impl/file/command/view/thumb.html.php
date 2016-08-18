<?php
	use rocket\script\entity\field\impl\file\command\model\ThumbModel;
	
	$view->useTemplate('core\view\template.html');
	
	$thumbModel = $view->getParam('thumbModel');
	$view->assert($thumbModel instanceof ThumbModel);
	
	$imageFile = $thumbModel->getImageFile();
	$file = $imageFile->getFile();
	$dimensions = $thumbModel->getDimensions();
	
	$html->addJs('/js/image-resizer.js');
	$html->addJs('/js/thumbs.js');
	$html->addCss('/css/image-resizer.css');
?>


<div class="rocket-panel">
	<h3><?php $html->out($file->getOriginalName()) ?></h3>
	<div class="rocket-edit-content">
		<?php $formHtml->open($thumbModel) ?>
			<?php $formHtml->select('dimensionId', $thumbModel->getDimensionOptions(), 
					array('id' => 'rocket-thumb-dimension-select')) ?>
					
			<div id="rocket-image-resizer"
					data-img-src="<?php $html->esc($file->getHttpPath()) ?>"
					data-text-fixed-ratio="<?php $html->l10nText('script_impl_thumb_keep_aspect_ratio_label') ?>"
					data-text-low-resolution="<?php $html->l10nText('script_impl_thumb_low_resolution_label') ?>" data-text-zoom="Zoom"></div>
			
			<?php $formHtml->inputField('x', array('id' => 'rocket-thumb-pos-x')) ?>
			<?php $formHtml->inputField('y', array('id' => 'rocket-thumb-pos-y')) ?>
			<?php $formHtml->inputField('width', array('id' => 'rocket-thumb-width')) ?>
			<?php $formHtml->inputField('height', array('id' => 'rocket-thumb-height')) ?>
			
			<div id="rocket-page-controls">
				<ul>
					<li class="rocket-control-warning"><?php $formHtml->inputSubmit('save', $view->getL10nText('common_save_label')) ?></li>
				</ul>
			</div>
		<?php $formHtml->close() ?>
	</div>
</div>
<div class="rocket-panel">
	<h3><?php $html->l10nText('script_impl_thumb_preview_title') ?></h3>
	<div class="rocket-detail-content">
		<ul class="rocket-detail-content-entries">
		<?php foreach ($thumbModel->getDimensions() as $dimension): ?>
			<li>
				<figure class="rocket-thumbnail">
					<?php $html->image($file, $dimension, null, false, false)?>
					<figcaption><?php $html->l10nText('script_impl_thumb_preview_label', 
						array('width' => $dimension->getWidth(), 'height' => $dimension->getHeight())) ?></figcaption>
				</figure>
			</li>
		<?php endforeach ?>
		</ul>
	</div>
</div>