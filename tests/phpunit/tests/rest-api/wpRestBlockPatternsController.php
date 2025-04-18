<?php
/**
 * Unit tests covering WP_REST_Block_Patterns_Controller functionality.
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 6.0.0
 *
 * @ticket 55505
 *
 * @covers WP_REST_Block_Patterns_Controller
 *
 * @group restapi
 */
class Tests_REST_WpRestBlockPatternsController extends WP_Test_REST_Controller_Testcase {

	/**
	 * Admin user ID.
	 *
	 * @since 6.0.0
	 *
	 * @var int
	 */
	protected static $admin_id;

	/**
	 * Original instance of WP_Block_Patterns_Registry.
	 *
	 * @since 6.0.0
	 *
	 * @var WP_Block_Patterns_Registry
	 */
	protected static $orig_registry;

	/**
	 * Instance of the reflected `instance` property.
	 *
	 * @since 6.0.0
	 *
	 * @var ReflectionProperty
	 */
	private static $registry_instance_property;

	/**
	 * The REST API route.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	const REQUEST_ROUTE = '/wp/v2/block-patterns/patterns';

	/**
	 * Set up class test fixtures.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_UnitTest_Factory $factory WordPress unit test factory.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$admin_id = $factory->user->create( array( 'role' => 'administrator' ) );

		// Setup an empty testing instance of `WP_Block_Patterns_Registry` and save the original.
		self::$orig_registry              = WP_Block_Patterns_Registry::get_instance();
		self::$registry_instance_property = new ReflectionProperty( 'WP_Block_Patterns_Registry', 'instance' );
		self::$registry_instance_property->setAccessible( true );
		$test_registry = new WP_Block_Pattern_Categories_Registry();
		self::$registry_instance_property->setValue( null, $test_registry );

		// Register some patterns in the test registry.
		$test_registry->register(
			'test/one',
			array(
				'title'         => 'Pattern One',
				'content'       => '<!-- wp:heading {"level":1} --><h1>One</h1><!-- /wp:heading -->',
				'viewportWidth' => 1440,
				'categories'    => array( 'test' ),
				'templateTypes' => array( 'page' ),
				'source'        => 'theme',
			)
		);

		$test_registry->register(
			'test/two',
			array(
				'title'         => 'Pattern Two',
				'content'       => '<!-- wp:paragraph --><p>Two</p><!-- /wp:paragraph -->',
				'categories'    => array( 'test' ),
				'templateTypes' => array( 'single' ),
				'source'        => 'core',
			)
		);

		$test_registry->register(
			'test/three',
			array(
				'title'      => 'Pattern Three',
				'content'    => '<!-- wp:paragraph --><p>Three</p><!-- /wp:paragraph -->',
				'categories' => array( 'test', 'buttons', 'query' ),
				'source'     => 'pattern-directory/featured',
			)
		);
	}

	public static function wpTearDownAfterClass() {
		self::delete_user( self::$admin_id );

		// Restore the original registry instance.
		self::$registry_instance_property->setValue( null, self::$orig_registry );
		self::$registry_instance_property->setAccessible( false );
		self::$registry_instance_property = null;
		self::$orig_registry              = null;
	}

	public function set_up() {
		parent::set_up();

		switch_theme( 'emptytheme' );
	}

	public function test_register_routes() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( static::REQUEST_ROUTE, $routes );
	}

	/**
	 * @group external-http
	 */
	public function test_get_items() {
		wp_set_current_user( self::$admin_id );

		$request            = new WP_REST_Request( 'GET', static::REQUEST_ROUTE );
		$request['_fields'] = 'name,content,source,template_types';
		$response           = rest_get_server()->dispatch( $request );
		$data               = $response->get_data();

		$this->assertIsArray( $data, 'WP_REST_Block_Patterns_Controller::get_items() should return an array' );
		$this->assertGreaterThanOrEqual( 2, count( $data ), 'WP_REST_Block_Patterns_Controller::get_items() should return at least 2 items' );
		$this->assertSame(
			array(
				'name'           => 'test/one',
				'content'        => '<!-- wp:heading {"level":1} --><h1>One</h1><!-- /wp:heading -->',
				'template_types' => array( 'page' ),
				'source'         => 'theme',
			),
			$data[0],
			'WP_REST_Block_Patterns_Controller::get_items() should return test/one'
		);
		$this->assertSame(
			array(
				'name'           => 'test/two',
				'content'        => '<!-- wp:paragraph --><p>Two</p><!-- /wp:paragraph -->',
				'template_types' => array( 'single' ),
				'source'         => 'core',
			),
			$data[1],
			'WP_REST_Block_Patterns_Controller::get_items() should return test/two'
		);
	}

	/**
	 * Verify capability check for unauthorized request (not logged in).
	 */
	public function test_get_items_unauthorized() {
		// Ensure current user is logged out.
		wp_logout();

		$request  = new WP_REST_Request( 'GET', static::REQUEST_ROUTE );
		$response = rest_do_request( $request );

		$this->assertWPError( $response->as_error() );
		$this->assertSame( 401, $response->get_status() );
	}

	/**
	 * Verify capability check for forbidden request (insufficient capability).
	 */
	public function test_get_items_forbidden() {
		// Set current user without `edit_posts` capability.
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$request  = new WP_REST_Request( 'GET', static::REQUEST_ROUTE );
		$response = rest_do_request( $request );

		$this->assertWPError( $response->as_error() );
		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Tests the proper migration of old core pattern categories to new ones.
	 *
	 * @since 6.2.0
	 *
	 * @ticket 57532
	 * @group external-http
	 *
	 * @covers WP_REST_Block_Patterns_Controller::get_items
	 */
	public function test_get_items_migrate_pattern_categories() {
		wp_set_current_user( self::$admin_id );

		$request            = new WP_REST_Request( 'GET', static::REQUEST_ROUTE );
		$request['_fields'] = 'name,categories';
		$response           = rest_get_server()->dispatch( $request );
		$data               = $response->get_data();

		$this->assertIsArray( $data, 'WP_REST_Block_Patterns_Controller::get_items() should return an array' );
		$this->assertGreaterThanOrEqual( 3, count( $data ), 'WP_REST_Block_Patterns_Controller::get_items() should return at least 3 items' );
		$this->assertSame(
			array(
				'name'       => 'test/one',
				'categories' => array( 'test' ),
			),
			$data[0],
			'WP_REST_Block_Patterns_Controller::get_items() should return test/one'
		);
		$this->assertSame(
			array(
				'name'       => 'test/two',
				'categories' => array( 'test' ),
			),
			$data[1],
			'WP_REST_Block_Patterns_Controller::get_items() should return test/two'
		);
		$this->assertSame(
			array(
				'name'       => 'test/three',
				'categories' => array( 'test', 'call-to-action', 'posts' ),
			),
			$data[2],
			'WP_REST_Block_Patterns_Controller::get_items() should return test/three'
		);
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_context_param() {
		// Controller does not use get_context_param().
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_get_item() {
		// Controller does not implement get_item().
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_create_item() {
		// Controller does not implement create_item().
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_update_item() {
		// Controller does not implement update_item().
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_delete_item() {
		// Controller does not implement delete_item().
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_prepare_item() {
		// Controller does not implement prepare_item().
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_get_item_schema() {
		// Controller does not implement get_item_schema().
	}
}
