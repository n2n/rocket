<?php
	$view->useTemplate('core\view\template.html',
			array('title' => $view->getL10nText('about')));
?>
<div class="rocket-grouped-panels">
	<section>
		<h2><?php $html->l10nText('about_credits') ?></h2>
		<p>Rocket basiert auf dem PHP Framework n2n. n2n ist ein Produkt von Hofmänner New Media, Winterthur.</p>
		<h3><?php $html->l10nText('about_credits_title')?></h3>
		<dl class="rocket-about-creators">
			<dt>Bert Hofmänner</dt>
			<dd>Idee, Frontend UX, Konzept</dd>
			<dt>Andreas von Burg</dt>
			<dd>Architektur, Lead Developer, Konzept</dd>
			<dt>Thomas Günther</dt>
			<dd>Developer, Frontend UI</dd>
			<dt>Yves Lüthi</dt>
			<dd>Frontend UI/UX</dd>
			<dt>Silvan Bauser</dt>
			<dd>Frontend UI</dd> 
		</dl>
	</section>
	<section>
		<h2><?php $html->l10nText('about_license') ?></h2>
		<h3><?php $html->l10nText('about_license_title')?></h3>
	</section>
</div>