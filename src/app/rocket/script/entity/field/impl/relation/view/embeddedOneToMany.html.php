<?php
	$entryViews = $view->getParam('entryViews');
?>
<div class="rocket-to-many">
	<ul class="rocket-option-array">
		<?php foreach ($entryViews as $entryView): ?>
			<li class="rocket-controls">
				<?php $view->import($entryView)?>
			</li>
		<?php endforeach ?>
	</ul>
</div>
