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
?>

<div id="rocket-content-container">
	<rocket-root></rocket-root>
</div>