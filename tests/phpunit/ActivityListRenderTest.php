<?php

class ActivityListRenderTest extends WP_UnitTestCase {

	private function make_activity( string $title, string $excerpt, int $order ): int {
		$id  = self::factory()->post->create(
			array(
				'post_type'    => 'wc_activity',
				'post_title'   => $title,
				'post_excerpt' => $excerpt,
				'post_status'  => 'publish',
				'menu_order'   => $order,
			)
		);
		$att = self::factory()->attachment->create_object(
			'act.jpg',
			$id,
			array( 'post_mime_type' => 'image/jpeg', 'post_type' => 'attachment' )
		);
		update_post_meta( $att, '_wp_attached_file', '2024/01/act.jpg' );
		set_post_thumbnail( $id, $att );
		return $id;
	}

	public function test_renders_grid_with_card_per_activity() {
		$this->make_activity( 'Varenna', 'A romantic escape.', 10 );
		$this->make_activity( 'Bellagio', 'The jewel of the lake.', 20 );
		$html = workation_activity_list_chrome( array() );
		$this->assertStringContainsString( 'activity-grid', $html );
		$this->assertSame( 2, substr_count( $html, 'class="activity-card"' ) );
		$this->assertStringContainsString( 'Varenna', $html );
		$this->assertStringContainsString( 'A romantic escape.', $html );
	}

	public function test_card_links_to_permalink() {
		$id   = $this->make_activity( 'Varenna', 'Blurb.', 10 );
		$html = workation_activity_list_chrome( array() );
		$this->assertStringContainsString( 'href="' . esc_url( get_permalink( $id ) ) . '"', $html );
	}

	public function test_orders_activities_by_menu_order() {
		$this->make_activity( 'Second', 'b', 20 );
		$this->make_activity( 'First', 'a', 10 );
		$html = workation_activity_list_chrome( array() );
		$this->assertLessThan( strpos( $html, 'Second' ), strpos( $html, 'First' ) );
	}

	public function test_renders_eyebrow_and_headline() {
		$this->make_activity( 'X', 'y', 10 );
		$html = workation_activity_list_chrome( array( 'eyebrow' => 'Things to do', 'headline' => 'Activities' ) );
		$this->assertStringContainsString( 'Things to do', $html );
		$this->assertStringContainsString( 'Activities', $html );
	}
}
