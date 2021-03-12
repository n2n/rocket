<?php
	use n2n\impl\web\ui\view\html\HtmlView;
	use rocket\core\model\AnglTemplateModel;
use n2n\core\N2N;

	$view = HtmlView::view($this);
	$html = HtmlView::html($view);
	
	if (isset($_SERVER['ROCKET_DEV'])) {
		$html->meta()->bodyEnd()->addJs('angl-dev/runtime.js');
		$html->meta()->bodyEnd()->addJs('angl-dev/polyfills.js');
		$html->meta()->bodyEnd()->addJs('angl-dev/vendor.js');
		$html->meta()->bodyEnd()->addJs('angl-dev/main.js');
	} else {
		$html->meta()->bodyEnd()->addJs('angl/runtime.js', null, false, false, ['defer']);
		$html->meta()->bodyEnd()->addJs('angl/polyfills.js', null, false, false, ['defer']);
		$html->meta()->bodyEnd()->addJs('angl/main.js', null, false, false, ['defer']);
	}
	
	$html->meta()->bodyEnd()->addCssUrl('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.11/cropper.min.css');
	
	$view->useTemplate('boilerplate.html', $view->getParams());
	
	$html->meta()->addCssCode('
// 			.rocket-layer {
// 				animation: layertransform 0.2s;
// 			}
			
// 			@keyframes layertransform {
// 			    0% { transform: translateX(100vw); }
// 				100% { transform: translateX(0); }
// 			}

			rocket-ui-structure-branch {
				display: block;
			}

			.rocket-highlighed {
			    animation: last-mod-transition 30s;
			    background: inherit;
			}
		
// 			.rocket-removed,
// 			.rocket-outdated {
// 				filter: blur(2px);
// 			}

// 			.rocket-locked {
// 				background-color:#d50000;
// 				background-image: 
// 				    repeating-linear-gradient(
// 				      45deg,
// 				      rgba(100,100,100,0.8),
// 				      rgba(100,100,100,0.8) 100px,
// 				      transparent 0px,
// 				      transparent 200px
// 				    ),
// 				    repeating-linear-gradient(
// 				      -45deg,
// 				      rgba(100,100,100,0.5),
// 				      rgba(100,100,100,0.5) 100px,
// 				      transparent 0px,
// 				      transparent 200px
// 				    );
// 			}

			.rocket-locked .rocket-group,
			.rocket-highlighed .rocket-group {
 				background: transparent !important;
			}

// 			.rocket-reloading {
// 				animation: reloadspin 0.4s ease-in-out infinite;
// 			}

// 			@keyframes reloadspin {
// 				0% { transform:rotate(0deg) }
// 				25% { transform:rotate(-3deg) }
// 				75% { transform:rotate(3deg) }
// 				100% { transform:rotate(0deg) }
// 			}

			.rocket-marked {
				outline: 3px solid #dc3545;
				position: relative;
				background: rgba(220, 53, 69, 0.1);
			}
			
			.rocket-marked-remember {
				outline: 3px solid transparent;
				-webkit-transition: outline 1s;
				transition: outline 1s;
			}
	');
	
	$anglTemplateModel = $view->lookup(AnglTemplateModel::class);
	$view->assert($anglTemplateModel instanceof AnglTemplateModel);
?>

<rocket-root data-rocket-angl-data="<?php $html->out(json_encode($anglTemplateModel->createData($view->getControllerContext()))) ?>"
		data-rocket-assets-url="<?php $html->out($html->meta()->getAssetUrl(null, 'rocket')) ?>"
		data-locale-id="<?php $html->out($request->getN2nLocale()->toWebId(true)) ?>"></rocket-root>