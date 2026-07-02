<?php

class PrimaryNavSeedTest extends WP_UnitTestCase {

	private function primary_menu() {
		$posts = get_posts(
			array(
				'post_type'        => 'wp_navigation',
				'name'             => 'primary',
				'post_status'      => 'any',
				'numberposts'      => -1,
				'suppress_filters' => false,
			)
		);
		return $posts;
	}

	public function test_blocks_markup_lists_expected_items() {
		$markup = pediment_child_primary_nav_blocks();
		$this->assertStringContainsString( '"label":"Activities"', $markup );
		$this->assertStringContainsString( '"url":"/photos/"', $markup );
		$this->assertStringContainsString( 'wp:navigation-submenu', $markup );
		$this->assertStringContainsString( '"url":"/ways-to-stay/"', $markup );
		$this->assertStringContainsString( 'https://workationcastle.com/guide/waste-disposal/', $markup );
	}

	public function test_seed_creates_primary_menu_when_absent() {
		\PedimentChild\Seed::seed_primary_nav();
		$menus = $this->primary_menu();
		$this->assertCount( 1, $menus );
		$this->assertSame( 'wp_navigation', $menus[0]->post_type );
		$this->assertSame( 'publish', $menus[0]->post_status );
		$this->assertStringContainsString( 'wp:navigation-link', $menus[0]->post_content );
	}

	public function test_reseed_is_create_if_absent_and_preserves_edits() {
		\PedimentChild\Seed::seed_primary_nav();
		$menu = $this->primary_menu()[0];
		wp_update_post(
			array(
				'ID'           => $menu->ID,
				'post_content' => '<!-- wp:navigation-link {"label":"Edited","url":"/x/","kind":"custom"} /-->',
			)
		);
		\PedimentChild\Seed::seed_primary_nav();
		$menus = $this->primary_menu();
		$this->assertCount( 1, $menus, 'Re-seed must not duplicate the menu' );
		$this->assertStringContainsString( 'Edited', $menus[0]->post_content, 'Re-seed must not overwrite edits' );
	}
}
