<?php
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\core\model\AnglTemplateModel;

	$view = HtmlView::view($this);
	$html = HtmlView::html($view);
	
	$html->meta()->bodyEnd()->addJs('angl-dev/runtime.js');
	$html->meta()->bodyEnd()->addJs('angl-dev/polyfills.js');
	$html->meta()->bodyEnd()->addJs('angl-dev/styles.js');
	$html->meta()->bodyEnd()->addJs('angl-dev/vendor.js');
	$html->meta()->bodyEnd()->addJs('angl-dev/main.js');
	
	$view->useTemplate('boilerplate.html', $view->getParams());
	
	$html->meta()->addCssCode('
			.rocket-layer {
				animation: layertransform 0.2s;
			}
			
			@keyframes layertransform {
			    0% { transform: translateX(100vw); }
				100% { transform: translateX(0); }
			}
	');
	
	$anglTemplateModel = $view->lookup(AnglTemplateModel::class);
	$view->assert($anglTemplateModel instanceof AnglTemplateModel);
?>

<rocket-root data-rocket-angl-data="<?php $html->out(json_encode($anglTemplateModel->createData($view->getControllerContext()))) ?>"></rocket-root>