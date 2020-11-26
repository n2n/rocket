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

			rocket-ui-structure-branch {
				display: block;
			}

			.rocket-highlighed {
			    animation: last-mod-transition 30s;
			    background: inherit;
			}
		
			.rocket-removed,
			.rocket-outdated {
				filter: blur(2px);
			}

			.rocket-locked {
				background-color:#d50000;
				background-image: 
				    repeating-linear-gradient(
				      45deg,
				      rgba(100,100,100,0.8),
				      rgba(100,100,100,0.8) 100px,
				      transparent 0px,
				      transparent 200px
				    ),
				    repeating-linear-gradient(
				      -45deg,
				      rgba(100,100,100,0.5),
				      rgba(100,100,100,0.5) 100px,
				      transparent 0px,
				      transparent 200px
				    );
			}

			.rocket-locked .rocket-group,
			.rocket-highlighed .rocket-group {
 				background: transparent !important;
			}

			.rocket-reloading {
				animation: reloadspin 0.4s ease-in-out infinite;
			}

			@keyframes reloadspin {
				0% { transform:rotate(0deg) }
				25% { transform:rotate(-3deg) }
				75% { transform:rotate(3deg) }
				100% { transform:rotate(0deg) }
			}
	');
	
	$anglTemplateModel = $view->lookup(AnglTemplateModel::class);
	$view->assert($anglTemplateModel instanceof AnglTemplateModel);
?>

<rocket-root data-rocket-angl-data="<?php $html->out(json_encode($anglTemplateModel->createData($view->getControllerContext()))) ?>"></rocket-root>