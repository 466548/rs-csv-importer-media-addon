<?php

class Import_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		add_filter( 'import_allow_create_users', '__return_true' );

		global $wpdb;
		// crude but effective: make sure there's no residual data in the main tables
		foreach ( array('posts', 'postmeta', 'comments', 'terms', 'term_taxonomy', 'term_relationships', 'users', 'usermeta') as $table)
			$wpdb->query("DELETE FROM {$wpdb->$table}");

		$result = $this->import_csv();
	}

	public function import_csv() {

		$rs_csv_importer = new RS_CSV_Importer();
		$h = new RSCSV_Import_Post_Helper();
		$rs_csv_importer->id = 0;
		$rs_csv_importer->file = dirname( __FILE__ ) . '/testdata.csv';
		ob_start();
		$result = $rs_csv_importer->process_posts();
		ob_end_clean();

		return $result;
	}

	/**
	 *
	 * @test
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */
	public function test_url_convert() {

		$posts = get_posts();
		$post = reset($posts);
		$image = get_post_meta( $post->ID, 'png', true );
		$attachment = get_post( $image );
		$this->assertEquals( 'attachment', $attachment->post_type );

	}

	/**
	 *
	 * @test
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */
	public function test_local_path_convert() {

		$posts = get_posts();
		$post = reset($posts);
		$image = get_post_meta( $post->ID, 'local_png', true );
		$attachment = get_post( $image );
		$this->assertEquals( 'attachment', $attachment->post_type );

	}



	/**
	 *
	 * @test
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 */
	public function test_url_no_convert() {
		$posts = get_posts();
		$post = reset($posts);
		$url = get_post_meta( $post->ID, 'url', true );
		$this->assertEquals( 'https://github.com/jawordpressorg/wapuu/', $url );
		$svg = get_post_meta( $post->ID, 'svg', true );
		$this->assertEquals( 'https://github.com/jawordpressorg/wapuu/blob/gh-pages/wapuu-original/wapuu-original.svg', $svg );
		$local_svg = get_post_meta( $post->ID, 'local_svg', true );
		$this->assertEquals( '/tmp/wapuu-original.svg', $local_svg );
	}


	public function tearDown() {
		remove_filter( 'import_allow_create_users', '__return_true' );

		parent::tearDown();
	}
}

