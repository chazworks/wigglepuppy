<?php

/**
 * @group post
 * @covers ::get_body_class
 */
class Tests_Post_GetBodyClass extends WP_UnitTestCase {
	protected $post_id;

	public function set_up() {
		parent::set_up();
		$this->post_id = self::factory()->post->create();
	}

	/**
	 * @ticket 30883
	 */
	public function test_with_utf8_category_slugs() {
		$cat_id1 = self::factory()->category->create( array( 'name' => 'Первая рубрика' ) );
		$cat_id2 = self::factory()->category->create( array( 'name' => 'Вторая рубрика' ) );
		$cat_id3 = self::factory()->category->create( array( 'name' => '25кадр' ) );
		wp_set_post_terms( $this->post_id, array( $cat_id1, $cat_id2, $cat_id3 ), 'category' );

		$this->go_to( home_url( "?cat=$cat_id1" ) );
		$this->assertContains( "category-$cat_id1", get_body_class() );

		$this->go_to( home_url( "?cat=$cat_id2" ) );
		$this->assertContains( "category-$cat_id2", get_body_class() );

		$this->go_to( home_url( "?cat=$cat_id3" ) );
		$this->assertContains( "category-$cat_id3", get_body_class() );
	}

	/**
	 * @ticket 30883
	 */
	public function test_with_utf8_tag_slugs() {
		$tag_id1 = self::factory()->tag->create( array( 'name' => 'Первая метка' ) );
		$tag_id2 = self::factory()->tag->create( array( 'name' => 'Вторая метка' ) );
		$tag_id3 = self::factory()->tag->create( array( 'name' => '25кадр' ) );
		wp_set_post_terms( $this->post_id, array( $tag_id1, $tag_id2, $tag_id3 ), 'post_tag' );

		$tag1 = get_term( $tag_id1, 'post_tag' );
		$tag2 = get_term( $tag_id2, 'post_tag' );
		$tag3 = get_term( $tag_id3, 'post_tag' );

		$this->go_to( home_url( "?tag={$tag1->slug}" ) );
		$this->assertContains( "tag-$tag_id1", get_body_class() );

		$this->go_to( home_url( "?tag={$tag2->slug}" ) );
		$this->assertContains( "tag-$tag_id2", get_body_class() );

		$this->go_to( home_url( "?tag={$tag3->slug}" ) );
		$this->assertContains( "tag-$tag_id3", get_body_class() );
	}

	/**
	 * @ticket 30883
	 */
	public function test_with_utf8_term_slugs() {
		register_taxonomy( 'wptests_tax', 'post' );
		$term_id1 = self::factory()->term->create(
			array(
				'taxonomy' => 'wptests_tax',
				'name'     => 'Первая метка',
			)
		);
		$term_id2 = self::factory()->term->create(
			array(
				'taxonomy' => 'wptests_tax',
				'name'     => 'Вторая метка',
			)
		);
		$term_id3 = self::factory()->term->create(
			array(
				'taxonomy' => 'wptests_tax',
				'name'     => '25кадр',
			)
		);
		wp_set_post_terms( $this->post_id, array( $term_id1, $term_id2, $term_id3 ), 'wptests_tax' );

		$term1 = get_term( $term_id1, 'wptests_tax' );
		$term2 = get_term( $term_id2, 'wptests_tax' );
		$term3 = get_term( $term_id3, 'wptests_tax' );

		$this->go_to( home_url( "?wptests_tax={$term1->slug}" ) );
		$this->assertContains( "term-$term_id1", get_body_class() );

		$this->go_to( home_url( "?wptests_tax={$term2->slug}" ) );
		$this->assertContains( "term-$term_id2", get_body_class() );

		$this->go_to( home_url( "?wptests_tax={$term3->slug}" ) );
		$this->assertContains( "term-$term_id3", get_body_class() );
	}

	/**
	 * @ticket 35164
	 * @ticket 36510
	 */
	public function test_singular_body_classes() {
		$post_id = self::factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );

		$class = get_body_class();
		$this->assertContains( 'single-post', $class );
		$this->assertContains( "postid-{$post_id}", $class );
		$this->assertContains( 'single-format-standard', $class );
		$this->assertContains( 'wp-singular', $class );
	}

	public function test_page_template_body_classes_no_template() {
		$post_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
			)
		);
		$this->go_to( get_permalink( $post_id ) );

		$class = get_body_class();

		$this->assertNotContains( 'page-template', $class );
		$this->assertContains( 'page-template-default', $class );
	}

	public function test_page_template_body_classes() {
		$post_id = self::factory()->post->create(
			array(
				'post_type' => 'page',
			)
		);

		add_post_meta( $post_id, '_wp_page_template', 'templates/cpt.php' );

		$this->go_to( get_permalink( $post_id ) );

		$class = get_body_class();

		$this->assertContains( 'page-template', $class );
		$this->assertContains( 'page-template-templates', $class );
		$this->assertContains( 'page-template-cpt', $class );
		$this->assertContains( 'page-template-templatescpt-php', $class );
	}

	/**
	 * @ticket 18375
	 */
	public function test_page_template_body_classes_attachment() {
		$post_id = self::factory()->post->create(
			array(
				'post_type' => 'attachment',
			)
		);

		add_post_meta( $post_id, '_wp_page_template', 'templates/cpt.php' );

		$this->go_to( get_permalink( $post_id ) );

		$class = get_body_class();

		$this->assertContains( 'attachment-template', $class );
		$this->assertContains( 'attachment-template-templates', $class );
		$this->assertContains( 'attachment-template-cpt', $class );
		$this->assertContains( 'attachment-template-templatescpt-php', $class );
	}

	/**
	 * @ticket 18375
	 */
	public function test_page_template_body_classes_post() {
		$post_id = self::factory()->post->create();

		add_post_meta( $post_id, '_wp_page_template', 'templates/cpt.php' );

		$this->go_to( get_permalink( $post_id ) );

		$class = get_body_class();

		$this->assertContains( 'post-template', $class );
		$this->assertContains( 'post-template-templates', $class );
		$this->assertContains( 'post-template-cpt', $class );
		$this->assertContains( 'post-template-templatescpt-php', $class );
	}

	/**
	 * @ticket 38225
	 */
	public function test_attachment_body_classes() {
		$post_id = self::factory()->post->create();

		$attachment_id = self::factory()->attachment->create_object(
			'image.jpg',
			$post_id,
			array(
				'post_mime_type' => 'image/jpeg',
			)
		);

		$this->go_to( get_permalink( $attachment_id ) );

		$class = get_body_class();

		$this->assertContains( 'attachment', $class );
		$this->assertContains( "attachmentid-{$attachment_id}", $class );
		$this->assertContains( 'attachment-jpeg', $class );
	}

	/**
	 * @ticket 38168
	 */
	public function test_custom_background_class_is_added_when_theme_supports_it() {
		add_theme_support( 'custom-background', array( 'default-color', '#ffffff' ) );
		set_theme_mod( 'background_color', '#000000' );

		$class                     = get_body_class();
		$theme_supports_background = current_theme_supports( 'custom-background' );

		remove_theme_mod( 'background_color' );
		remove_theme_support( 'custom-background' );

		$this->assertTrue( $theme_supports_background );
		$this->assertContains( 'custom-background', $class );
	}

	/**
	 * @ticket 38168
	 */
	public function test_custom_background_class_is_not_added_when_theme_support_is_missing() {
		set_theme_mod( 'background_color', '#000000' );

		$class                     = get_body_class();
		$theme_supports_background = current_theme_supports( 'custom-background' );

		remove_theme_mod( 'background_color' );

		$this->assertFalse( $theme_supports_background );
		$this->assertNotContains( 'custom-background', $class );
	}

	/**
	 * @ticket 44005
	 * @group privacy
	 */
	public function test_privacy_policy_body_class() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Privacy Policy',
			)
		);
		update_option( 'wp_page_for_privacy_policy', $page_id );

		$this->go_to( get_permalink( $page_id ) );

		$class = get_body_class();

		$this->assertContains( 'privacy-policy', $class );
		$this->assertContains( 'page-template-default', $class );
		$this->assertContains( 'page', $class );
		$this->assertContains( "page-id-{$page_id}", $class );
	}

	/**
	 * Test theme-related body classes.
	 *
	 * @ticket 19736
	 */
	public function test_theme_body_classes() {
		$original_theme = wp_get_theme();

		switch_theme( 'block-theme' );
		do_action( 'setup_theme' );
		do_action( 'after_setup_theme' );

		$classes = get_body_class();
		$this->assertContains( 'wp-theme-block-theme', $classes, 'Parent theme body class not found' );

		switch_theme( 'block-theme-child' );
		do_action( 'setup_theme' );
		do_action( 'after_setup_theme' );

		$classes = get_body_class();
		$this->assertContains( 'wp-theme-block-theme', $classes, 'Parent theme body class not found in child theme context' );
		$this->assertContains( 'wp-child-theme-block-theme-child', $classes, 'Child theme body class not found' );

		switch_theme( $original_theme->get_stylesheet() );
		do_action( 'setup_theme' );
		do_action( 'after_setup_theme' );
	}
}
