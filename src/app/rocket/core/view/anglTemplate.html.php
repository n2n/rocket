<?php
	use n2n\impl\web\ui\view\html\HtmlView;

	$view = HtmlView::view($this);
	$html = HtmlView::html($view);
	
	$html->meta()->bodyEnd()->addJs('angl-dev/runtime.js');
	$html->meta()->bodyEnd()->addJs('angl-dev/polyfills.js');
	$html->meta()->bodyEnd()->addJs('angl-dev/styles.js');
	$html->meta()->bodyEnd()->addJs('angl-dev/vendor.js');
	$html->meta()->bodyEnd()->addJs('angl-dev/main.js');
	
	$view->useTemplate('boilerplate.html', $view->getParams());
	
	$html->meta()->addCssCode('
			body {
				scroll-behavior: smooth;
			}
			
			.rocket-layer {
				animation: layertransform 0.2s;
			}
			
			@keyframes layertransform {
			    from { transform: translateX(100vw); }
			    to { transform: translateX(0); }
			}');
?>

<div id="rocket-content-container">
	<rocket-root></rocket-root>
</div>