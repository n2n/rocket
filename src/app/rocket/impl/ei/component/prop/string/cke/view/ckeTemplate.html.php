<?php
    use n2n\impl\web\dispatch\ui\FormHtmlBuilder;
    use n2n\impl\web\ui\view\html\HtmlView;
	use n2n\core\N2N;
    use n2nutil\jquery\JQueryLibrary;
    use rocket\impl\ei\component\prop\string\cke\conf\CkeEditorConfig;
    use rocket\impl\ei\component\prop\string\cke\ui\CkeComposer;
    use rocket\impl\ei\component\prop\string\cke\ui\CkeHtmlBuilder;
    use rocket\impl\ei\component\prop\string\cke\ui\CkeLibrary;
	use rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig;

$view = HtmlView::view($this);
	$html = htmlView::html($view);
	$request = HtmlView::request($view);

	$formHtml = new FormHtmlBuilder($view);
    $ckeHtml = new CkeHtmlBuilder($view);

    /**
     * @var CkeComposer $ckeInField
     */
    $ckeComposer = $view->getParam('composer');
    $view->assert($ckeComposer instanceof CkeComposer);

    /**
     * @var CkeEditorConfig $config
     */
	$ckeCssConfig = $view->getParam('ckeCssConfig');
	$view->assert($ckeCssConfig === null || $ckeCssConfig instanceof CkeCssConfig);

	$ckeLinkProviders = $view->getParam('ckeLinkProviders');
	$view->assert(is_array($ckeLinkProviders));

    $html->meta()->addLibrary(new JQueryLibrary(3));
    $html->meta()->addLibrary(new CkeLibrary());

    $attrs = array('name' => 'content');
?>
<!DOCTYPE html>
<html lang="<?php $html->out($request->getN2nLocale()->getLanguage()->getShort()) ?>">
	<?php $html->headStart() ?>
		<meta charset="<?php $html->out(N2N::CHARSET) ?>" />
	<?php $html->headEnd() ?>
	<?php $html->bodyStart(array('style' => 'margin: 0')) ?>

        <style>
            /* fix cke collapse on focus */
            .cke_contents {
                height: 100% !important;
            }
        </style>

        <script>
            CKEDITOR.config.removeButtons = 'Maximize'; // maximize causes cke height = 0. Needs to be fixed

            CKEDITOR.on('instanceReady', function(e) {
                var cke = CKEDITOR.instances['content'];
                cke.on('change', function() {
                    cke.updateElement();
                    var element = cke.element.$;
                    if ('createEvent' in document) {
                        var event = document.createEvent('HTMLEvents');
                        event.initEvent('change', false, true);
                        element.dispatchEvent(event);
                    } else {
                        element.fireEvent('onchange');
                    }
                });

                // CKEDITOR.on('dialogDefinition', function (e) {
                //     var dialog = e.data.definition.dialog;
                //     dialog.on('show', function () {
                //         iframeJq.css('height', this.getSize().height + 50);
                //     });
                //     dialog.on('hide', function () {
                //         iframeJq.css('height', initialHeight);
                //     });
                // });

                var focusHeightIncreasePx = 300;

                var initialEditorHeight;
                var initialEditorIframeHeight;
                e.editor.on('focus', function(event) {
                    var editorJq = $(this.element.$.parentElement.parentElement);
                    var editorIframeJq = $(this.element.$.parentElement).find('iframe');

                    initialEditorHeight = editorJq.height();
                    initialEditorIframeHeight = editorIframeJq.height();

                    editorJq.css('height', (initialEditorHeight + focusHeightIncreasePx));
                    editorIframeJq.height((initialEditorIframeHeight + focusHeightIncreasePx));
                });

                e.editor.on('blur', function(event) {
                    $(this.element.$.parentElement.parentElement).css('height', initialEditorHeight);
                    $(this.element.$.parentElement).find('iframe').height(initialEditorIframeHeight);
                });

            });
        </script>

        <?php $html->out($ckeHtml->getTextarea('', $ckeComposer, $ckeCssConfig, $ckeLinkProviders, $attrs)) ?>
    <?php $html->bodyEnd() ?>
</html>
