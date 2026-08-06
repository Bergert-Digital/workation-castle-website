<?php
/**
 * Title: Header
 * Slug: workation/header
 * Categories: workation
 * Description: The branded site header — logo, navigation, language switcher.
 * Inserter: no
 */
// phpcs:ignoreFile -- block pattern content (verbatim block markup).
?>
<!-- wp:group {"tagName":"header","className":"site-header","anchor":"siteHeader","layout":{"type":"default"}} -->
<header class="wp-block-group site-header" id="siteHeader">
	<!-- wp:group {"className":"wc-wrap header-inner","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"nowrap"}} -->
	<div class="wp-block-group wc-wrap header-inner">
		<!-- wp:html -->
		<a class="brand" href="/" aria-label="Workation Castle home">
			<img src="%WORKATION_THEME_URI%/assets/images/logo-wordmark.svg" alt="Workation Castle">
		</a>
		<!-- /wp:html -->
		<!-- wp:navigation {"overlayMenu":"mobile","className":"main-nav"} -->
		<!-- /wp:navigation -->
		<!-- wp:html -->
		<a class="wc-btn wc-btn-yellow header-cta" href="https://workationcastle.holiduhost.com/">Check availability <span class="arr">→</span></a>
		<!-- /wp:html -->
	</div>
	<!-- /wp:group -->
</header>
<!-- /wp:group -->
