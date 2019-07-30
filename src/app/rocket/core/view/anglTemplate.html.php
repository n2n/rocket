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
			}

			.cdk-drag-preview {
				box-sizing: border-box;
				border-radius: 4px;
				box-shadow: 0 5px 5px -3px rgba(0, 0, 0, 0.2),
				0 8px 10px 1px rgba(0, 0, 0, 0.14),
				0 3px 14px 2px rgba(0, 0, 0, 0.12);
			}
			
			.cdk-drag-placeholder {
				opacity: 0;
			}
			
			.cdk-drag-animating {
				transition: transform 250ms cubic-bezier(0, 0, 0.2, 1);
			}
			
			.rocket-draggable-list.cdk-drop-list-dragging .rocket-draggable:not(.cdk-drag-placeholder) {
				transition: transform 250ms cubic-bezier(0, 0, 0.2, 1);
			}');
	
?>

<div id="rocket-content-container">
	<rocket-root></rocket-root>
</div>