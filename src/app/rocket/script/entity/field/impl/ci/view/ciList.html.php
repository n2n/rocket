<?php
	$groupedEntryInfos = $view->getParam('groupedEntryInfos');
?>
<div class="rocket-content-nested rocket-content-item-composer">
	<?php foreach ($groupedEntryInfos as $panelName => $entryInfos): ?>
		<div>
			<?php foreach ($entryInfos as $entryInfo): ?>
				<div class="rocket-content-item">
					<?php $view->import('script\entity\command\impl\common\view\inc\entryInfo.html', array('entryInfo' => $entryInfo)) ?>
				</div>
			<?php endforeach ?>
		</div>
	<?php endforeach ?>
</div>