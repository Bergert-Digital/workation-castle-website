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
		$this->assertStringContainsString( '"url":"/guide/faq/"', $markup );
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

	/**
	 * An unpublished menu used to deadlock the site: the seed skipped it as
	 * "exists" while the header ignored it for not being published, and because
	 * WordPress keeps slugs unique any replacement was inserted as `primary-2`,
	 * which the header cannot resolve either. Re-seeding must adopt the post
	 * that owns the slug so the navigation comes back.
	 */
	public function test_reseed_publishes_an_unpublished_menu_instead_of_forking_the_slug() {
		\PedimentChild\Seed::seed_primary_nav();
		$menu = $this->primary_menu()[0];
		wp_update_post(
			array(
				'ID'          => $menu->ID,
				'post_status' => 'draft',
			)
		);

		\PedimentChild\Seed::seed_primary_nav();

		$menus = $this->primary_menu();
		$this->assertCount( 1, $menus, 'Re-seed must adopt the slug owner, not insert a primary-2 rival' );
		$this->assertSame( $menu->ID, $menus[0]->ID, 'Re-seed must reuse the existing menu post' );
		$this->assertSame( 'primary', $menus[0]->post_name, 'The menu must keep the slug the header resolves' );
		$this->assertSame( 'publish', $menus[0]->post_status, 'Re-seed must publish it so the header binds to it' );
		$this->assertNotNull( pediment_child_get_primary_nav_menu(), 'The header lookup must find the menu again' );
	}

	public function test_reseed_refills_an_emptied_menu() {
		\PedimentChild\Seed::seed_primary_nav();
		$menu = $this->primary_menu()[0];
		wp_update_post(
			array(
				'ID'           => $menu->ID,
				'post_content' => '   ',
			)
		);

		\PedimentChild\Seed::seed_primary_nav();

		$menus = $this->primary_menu();
		$this->assertCount( 1, $menus );
		$this->assertStringContainsString( 'wp:navigation-link', $menus[0]->post_content, 'An empty menu must be refilled' );
	}
}
