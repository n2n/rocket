<?php
	use rocket\script\entity\command\control\IconType;
	use rocket\script\entity\manage\ScriptState;
	use n2n\core\DynamicTextCollection;
use rocket\script\entity\field\impl\file\command\controller\MultiUploadScriptController;
	
	$scriptState = $view->getParam('scriptState');
	$view->assert($scriptState instanceof ScriptState);

	$view->useTemplate('\rocket\core\view\template.html',
			array('title' => $view->getL10nText('script_impl_multi_upload_title', 
					array('plural_label' => $scriptState->getContextEntityScript()->getPluralLabel())))); 
	
	$html->addJs('js/script/impl/multiupload/jquery.knob.js');
	$html->addJs('js/script/impl/multiupload/jquery.ui.widget.js');
	$html->addJs('js/script/impl/multiupload/jquery.iframe-transport.js');
	$html->addJs('js/script/impl/multiupload/jquery.fileupload.js');
	$html->addJs('js/script/impl/multiupload/multiupload.js');
	$html->addCss('css/script/impl/multiupload/multiupload.css');
	
	$rocketDtc = new DynamicTextCollection('rocket');
?>
<div class="rocket-panel">
	<h3><?php $html->text('script_impl_multi_upload_label', array('plural_label' => 
					$scriptState->getContextEntityScript()->getPluralLabel())) ?></h3>
	<form id="rocket-multi-upload-form" method="post" 
			action="<?php $html->out($request->getCurrentControllerContextPath(array(MultiUploadScriptController::ACTION_UPLOAD))) ?>" 
			enctype="multipart/form-data">
		<div id="rocket-multi-upload-drop">
			Drop Here
			<a>Browse</a>
			<input type="file" name="prop-upl" multiple />
		</div>
		<ul>
			<!-- The file uploads will be shown here -->
		</ul>
	</form>
</div>
<div id="rocket-page-controls">
	<ul>
		<li>
			<a id="rocket-multi-upload-submit" href="#" class="rocket-control">
				<i class="<?php $view->out(IconType::ICON_UPLOAD)?>"></i>
				<span><?php $html->text('script_impl_multi_upload_label')?></span>
			</a>
		</li>
		<li>
			<?php $html->link($scriptState->getOverviewPath($request),
					new n2n\ui\Raw('<i class="fa fa-times-circle"></i><span>' . $rocketDtc->translate('common_cancel_label') . '</span>'),
							array('class' => 'rocket-control')) ?>
		</li>
	</ul>
</div>