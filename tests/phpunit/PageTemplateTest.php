<?php

/**
 * Without theme overrides, regular pages and the 404 fall back to the
 * Pediment plugin's templates, whose pediment/footer pattern renders
 * placeholder content (hello@example.com etc.) instead of the site footer.
 */
class PageTemplateTest extends PatternTestCase {

	public function test_page_template_uses_theme_header_and_footer_parts() {
		$template = file_get_contents( $this->theme_dir() . '/templates/page.html' );

		$this->assertStringContainsString( '"slug":"header"', $template );
		$this->assertStringContainsString( 'wp:post-content {"align":"full"', $template );
		$this->assertStringContainsString( '"slug":"footer"', $template );
		$this->assertStringNotContainsString( 'pediment/footer', $template );
	}

	public function test_404_template_uses_theme_parts_and_not_found_pattern() {
		$template = file_get_contents( $this->theme_dir() . '/templates/404.html' );

		$this->assertStringContainsString( '"slug":"header"', $template );
		$this->assertStringContainsString( '"slug":"workation/not-found"', $template );
		$this->assertStringContainsString( '"slug":"footer"', $template );
	}

	public function test_not_found_pattern_renders_message_and_home_link() {
		$markup = $this->render_pattern( 'not-found.php' );

		$this->assertStringContainsString( 'Page not found', $markup );
		$this->assertStringContainsString( 'href="' . esc_url( home_url( '/' ) ) . '"', $markup );
	}

	public function test_footer_pattern_renders_the_site_footer() {
		$markup = $this->render_pattern( 'footer.php' );

		$this->assertStringContainsString( 'class="wc-footer"', $markup );
		$this->assertStringContainsString( 'wp:html', $markup );
	}
}
