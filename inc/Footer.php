<?php
/**
 * Server-rendered site footer.
 *
 * The footer is one language-less template part shared by every language
 * (Polylang's template-part translation is deliberately disabled), so it is
 * rendered in PHP: labels come from the pages' own translated titles, URLs
 * from the localized page resolver, and the remaining strings from the
 * theme's textdomain. The markup lives here rather than in the pattern file
 * because i18n:pot excludes patterns/ from extraction.
 *
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL of a page (by its English path) in the visitor's language.
 *
 * @param string $path Page path in the default language, e.g. "guide/arrival".
 * @return string
 */
function workation_localized_page_url( $path ) {
	$page = get_page_by_path( $path );
	if ( $page && function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $page->ID );
		if ( $translated ) {
			$page = get_post( $translated );
		}
	}
	if ( $page ) {
		return (string) get_permalink( $page );
	}
	return home_url( '/' . trim( $path, '/' ) . '/' );
}

/**
 * Anchor to a page in the visitor's language, labelled with the translated
 * page title (or the given fallback when the page does not exist).
 *
 * @param string $path     Page path in the default language.
 * @param string $fallback Label when no page resolves.
 * @return string
 */
function workation_footer_page_link( $path, $fallback ) {
	$page  = get_page_by_path( $path );
	$label = $fallback;
	if ( $page && function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $page->ID );
		if ( $translated ) {
			$page = get_post( $translated );
		}
	}
	if ( $page ) {
		$label = get_the_title( $page );
	}
	return sprintf(
		'<a href="%s">%s</a>',
		esc_url( $page ? get_permalink( $page ) : home_url( '/' . trim( $path, '/' ) . '/' ) ),
		esc_html( $label )
	);
}

/**
 * Language switcher links for the footer, pointing at the current page's
 * translations. Empty string when Polylang (or its frontend) is absent.
 *
 * @return string
 */
function workation_footer_language_links() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return '';
	}
	$languages = pll_the_languages(
		array(
			'raw'           => 1,
			'hide_if_empty' => 0,
		)
	);
	if ( ! is_array( $languages ) || ! $languages ) {
		return '';
	}
	$links = array();
	foreach ( $languages as $language ) {
		$links[] = sprintf(
			'<a href="%s" hreflang="%s"%s>%s</a>',
			esc_url( $language['url'] ),
			esc_attr( $language['slug'] ),
			! empty( $language['current_lang'] ) ? ' class="is-current" aria-current="true"' : '',
			esc_html( $language['name'] )
		);
	}
	return implode( "\n\t\t\t\t\t", $links );
}

/**
 * The 404 message markup, for the workation/not-found pattern.
 *
 * Lives here rather than in the pattern file so the strings are scanned into
 * the POT (i18n:pot excludes patterns/).
 *
 * @return string
 */
function workation_not_found_markup() {
	return sprintf(
		'<div class="wc-wrap not-found">
	<h1>%s</h1>
	<p><a class="text-link" href="%s"><span class="arr">←</span>%s</a></p>
</div>',
		esc_html__( 'Page not found', 'workation' ),
		esc_url( home_url( '/' ) ),
		esc_html__( 'Back to the homepage', 'workation' )
	);
}

/**
 * The complete footer markup.
 *
 * @return string
 */
function workation_footer_markup() {
	$languages = workation_footer_language_links();

	ob_start();
	?>
<footer class="wc-footer" id="footer">
	<div class="wc-wrap">
		<div class="foot-top">
			<div class="foot-brand">
				<img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo-wordmark.svg' ); ?>" alt="Workation Castle">
				<p><?php esc_html_e( 'Vacation homes and co-working between Lake Lugano and Lake Como.', 'workation' ); ?></p>
			</div>
			<div class="foot-cols">
				<div class="foot-col">
					<h4><?php esc_html_e( 'Explore', 'workation' ); ?></h4>
					<?php echo workation_footer_page_link( 'ways-to-stay', 'Ways to Stay' ); ?>
					<?php echo workation_footer_page_link( 'ways-to-stay/workations', 'Workations' ); ?>
					<?php echo workation_footer_page_link( 'photos', 'Photos' ); ?>
					<?php echo workation_footer_page_link( 'reviews', 'Reviews' ); ?>
				</div>
				<div class="foot-col">
					<h4><?php esc_html_e( 'Visit', 'workation' ); ?></h4>
					<?php echo workation_footer_page_link( 'guide/arrival', 'Arrival' ); ?>
					<?php echo workation_footer_page_link( 'activities', 'Activities' ); ?>
					<a href="https://workationcastle.holiduhost.com/"><?php esc_html_e( 'Check availability', 'workation' ); ?></a>
					<a href="<?php echo esc_url( workation_localized_page_url( 'contact-us' ) ); ?>"><?php esc_html_e( 'Ask for a custom offer', 'workation' ); ?></a>
				</div>
				<?php if ( $languages ) : ?>
				<div class="foot-col">
					<h4><?php esc_html_e( 'Languages', 'workation' ); ?></h4>
					<?php echo $languages; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="foot-bottom">
			<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Workation Castle</span>
			<span class="links"><?php echo workation_footer_page_link( 'contact-us', 'Contact' ); ?><?php echo workation_footer_page_link( 'feedback', 'Feedback' ); ?><?php echo workation_footer_page_link( 'imprint', 'Imprint' ); ?><?php echo workation_footer_page_link( 'privacy-policy', 'Privacy Policy' ); ?><button type="button" class="wc-consent-settings-link"><?php esc_html_e( 'Cookie settings', 'workation' ); ?></button></span>
		</div>
	</div>
</footer>
	<?php
	return (string) ob_get_clean();
}
