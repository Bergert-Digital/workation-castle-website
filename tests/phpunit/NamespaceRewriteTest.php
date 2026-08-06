<?php
// tests/phpunit/NamespaceRewriteTest.php

use Workation\NamespaceRewrite;

class NamespaceRewriteTest extends WP_UnitTestCase {

	private const LEGACY = '<!-- wp:pediment-child/workation-hero {"headline":"Hi"} -->'
		. '<div class="wp-block-pediment-child-workation-hero">Hi</div>'
		. '<!-- /wp:pediment-child/workation-hero -->';

	private const REWRITTEN = '<!-- wp:workation/workation-hero {"headline":"Hi"} -->'
		. '<div class="wp-block-workation-workation-hero">Hi</div>'
		. '<!-- /wp:workation/workation-hero -->';

	private function page( string $content ): int {
		return self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => $content,
			)
		);
	}

	public function test_a_plan_counts_without_writing() {
		$id = $this->page( self::LEGACY );

		$plan = NamespaceRewrite::plan();

		$this->assertSame( 1, $plan['posts'] );
		$this->assertSame( self::LEGACY, get_post( $id )->post_content, 'a plan never writes' );
	}

	public function test_apply_rewrites_openers_closers_and_classes() {
		$id = $this->page( self::LEGACY );

		$this->assertSame( 1, NamespaceRewrite::apply() );
		$this->assertSame( self::REWRITTEN, get_post( $id )->post_content );
	}

	public function test_plugin_blocks_are_never_touched() {
		$content = '<!-- wp:pediment/prose --><p>x</p><!-- /wp:pediment/prose -->';
		$id      = $this->page( $content );

		NamespaceRewrite::apply();

		$this->assertSame( $content, get_post( $id )->post_content );
	}

	public function test_it_is_idempotent() {
		$id = $this->page( self::LEGACY );

		NamespaceRewrite::apply();
		$this->assertSame( 0, NamespaceRewrite::apply(), 'nothing left to rewrite' );
		$this->assertSame( self::REWRITTEN, get_post( $id )->post_content );
	}

	public function test_the_trash_is_left_alone() {
		$id = $this->page( self::LEGACY );
		wp_trash_post( $id );

		NamespaceRewrite::apply();

		$this->assertStringContainsString( 'wp:pediment-child/', get_post( $id )->post_content );
	}
}
