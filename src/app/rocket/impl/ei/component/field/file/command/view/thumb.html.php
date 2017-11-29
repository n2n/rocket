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

	use rocket\impl\ei\component\field\file\command\model\ThumbModel;
	use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\impl\web\ui\view\html\img\UiComponentFactory;
	use n2n\web\ui\Raw;

	$view = HtmlView::view($this);
	$html = HtmlView::html($this);
	$formHtml = HtmlView::formHtml($this);
	
	$view->useTemplate('~\core\view\template.html');
	
	$thumbModel = $view->getParam('thumbModel');
	$view->assert($thumbModel instanceof ThumbModel);
	
	$imageFile = $thumbModel->getImageFile();
	
	$html->meta()->addJs('impl/js/image-resizer.js');
	$html->meta()->addJs('impl/js/thumbs.js');
	$html->meta()->addCss('impl/css/image-resizer.css');
?>


<div class="rocket-panel">
	<h3><?php $html->out($imageFile->getFile()->getOriginalName()) ?></h3>
	<div class="rocket-edit-content">
		<?php $formHtml->open($thumbModel) ?>
			<?php $formHtml->select('imageDimensionStr', $thumbModel->getImageDimensionOptions(), 
					array('id' => 'rocket-thumb-dimension-select')) ?>
					
			<div id="rocket-image-resizer"
					data-img-src="<?php $html->esc(UiComponentFactory::createImgSrc($imageFile)) ?>"
					data-text-fixed-ratio="<?php $html->l10nText('ei_impl_thumb_keep_aspect_ratio_label') ?>"
					data-text-low-resolution="<?php $html->l10nText('ei_impl_thumb_low_resolution_label') ?>" data-text-zoom="Zoom"></div>
			
			<?php $formHtml->input('x', array('id' => 'rocket-thumb-pos-x')) ?>
			<?php $formHtml->input('y', array('id' => 'rocket-thumb-pos-y')) ?>
			<?php $formHtml->input('width', array('id' => 'rocket-thumb-width')) ?>
			<?php $formHtml->input('height', array('id' => 'rocket-thumb-height')) ?>
			
			<div class="rocket-zone-commands">
				<?php $formHtml->buttonSubmit('save', new Raw('<i class="fa fa-save"></i><span>' 
								. $html->getL10nText('common_save_label') . '</span>'), 
						array('class' => 'btn btn-primary')) ?>
				<?php $html->link($view->getParam('cancelUrl'), 
						new Raw('<i class="fa fa-times-circle"></i><span>' 
								. $html->getL10nText('common_cancel_label') . '</span>'),
						array('class' => 'btn btn-secondary')) ?>
			</div>
		<?php $formHtml->close() ?>
	</div>
</div>
<div class="rocket-panel">
	<h3><?php $html->l10nText('ei_impl_thumb_preview_title') ?></h3>
	<div class="rocket-thumbs">
		<?php foreach ($thumbModel->getImageDimensions() as $imageDimension): ?>
			<?php if (null !== ($thumbFile = $imageFile->getThumbFile($imageDimension))): ?>
				<figure class="rocket-thumbnail">
					<?php $html->image($thumbFile, null, null, false, false)?>
					<figcaption>
						<?php $html->l10nText('ei_impl_thumb_preview_label', array(
								'width' => $imageDimension->getWidth(), 
								'height' => $imageDimension->getHeight())) ?>
					</figcaption>
				</figure>
			<?php else: ?>
				<div class="rocket-thumbnail">
					<?php $html->l10nText('ei_impl_thumb_not_yet_created_label', array(
							'width' => $imageDimension->getWidth(), 
							'height' => $imageDimension->getHeight())) ?>
				</div>
			<?php endif ?>
		<?php endforeach ?>
	</div>
</div>
