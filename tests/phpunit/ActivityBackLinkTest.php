<?php

/**
 * The "Back to activities" link under an activity single must point at the
 * activities page in the visitor's language, not at a hardcoded /activities/.
 */
class ActivityBackLinkTest extends PatternTestCase {

	private function make_activities_page(): int {
		return self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_name'   => 'activities',
				'post_title'  => 'Activities',
				'post_status' => 'publish',
			)
		);
	}

	public function test_url_helper_links_to_the_activities_page() {
		$page_id = $this->make_activities_page();

		$this->assertSame( get_permalink( $page_id ), workation_activities_page_url() );
	}

	public function test_url_helper_falls_back_to_the_path_when_no_page_exists() {
		$this->assertSame( home_url( '/activities/' ), workation_activities_page_url() );
	}

	public function test_pattern_renders_the_page_link_and_label() {
		$page_id = $this->make_activities_page();

		$markup = $this->render_pattern( 'back-to-activities.php' );

		$this->assertStringContainsString( 'wp:paragraph', $markup );
		$this->assertStringContainsString( 'class="back-to-activities', $markup );
		$this->assertStringContainsString( 'href="' . esc_url( get_permalink( $page_id ) ) . '"', $markup );
		$this->assertStringContainsString( 'Back to activities', $markup );
	}

	public function test_single_template_references_the_pattern_not_a_hardcoded_url() {
		$template = file_get_contents( $this->theme_dir() . '/templates/single-wc_activity.html' );

		$this->assertStringContainsString( '"slug":"workation/back-to-activities"', $template );
		$this->assertStringNotContainsString( 'href="/activities/"', $template );
	}
}
