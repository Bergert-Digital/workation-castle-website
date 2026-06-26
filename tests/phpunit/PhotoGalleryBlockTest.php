<?php

class PhotoGalleryBlockTest extends WP_UnitTestCase {

	public function test_block_is_registered() {
		$this->assertTrue(
			WP_Block_Type_Registry::get_instance()->is_registered( 'pediment-child/photo-gallery' )
		);
	}

	public function test_block_renders_section() {
		$att = self::factory()->attachment->create_object(
			'p.jpg', 0,
			array( 'post_mime_type' => 'image/jpeg', 'post_type' => 'attachment' )
		);
		update_post_meta( $att, '_wp_attached_file', '2024/01/p.jpg' );
		$id = self::factory()->post->create(
			array( 'post_type' => 'wc_photo', 'post_status' => 'publish', 'post_title' => 'X' )
		);
		set_post_thumbnail( $id, $att );

		$html = do_blocks( '<!-- wp:pediment-child/photo-gallery /-->' );
		$this->assertStringContainsString( 'photo-grid', $html );
		$this->assertStringContainsString( 'photo-tabs', $html );
	}
}
