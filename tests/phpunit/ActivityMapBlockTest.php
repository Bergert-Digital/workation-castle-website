<?php

class ActivityMapBlockTest extends WP_UnitTestCase {

	public function test_renders_destination_and_pins() {
		$markup = '<!-- wp:workation/activity-map {"lat":"46.1497","lng":"9.3346","title":"Soul Surfer Domaso"} -->'
			. '<!-- wp:workation/activity-map-pin {"lat":"46.0303","lng":"9.1236","title":"Circolo Nautico Porlezza"} /-->'
			. '<!-- /wp:workation/activity-map -->';

		$html = do_blocks( $markup );

		$this->assertStringContainsString( 'wc-activity-map', $html );
		$this->assertStringContainsString( 'data-lat="46.1497"', $html );
		$this->assertStringContainsString( 'Circolo Nautico Porlezza', $html );
		$this->assertStringContainsString( '9.1236', $html );
	}

	public function test_self_closing_block_has_no_pins() {
		$html = do_blocks( '<!-- wp:workation/activity-map {"lat":"46.0315","lng":"9.1501","title":"La Rivetta Beach"} /-->' );

		$this->assertStringContainsString( 'data-pins="[]"', $html );
	}
}
