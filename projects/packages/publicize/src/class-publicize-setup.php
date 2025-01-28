<?php
/**
 * Main Publicize class.
 *
 * @package automattic/jetpack
 */

namespace Automattic\Jetpack\Publicize;

use Automattic\Jetpack\Status\Host;

/**
 * The class to configure and initialize the publicize package.
 */
class Publicize_Setup {

	/**
	 * Whether to update the plan information from WPCOM when initialising the package.
	 *
	 * @var bool
	 */
	public static $refresh_plan_info = false;

	/**
	 * To configure the publicize package, when called via the Config package.
	 */
	public static function configure() {
		add_action( 'jetpack_feature_publicize_enabled', array( __CLASS__, 'on_jetpack_feature_publicize_enabled' ) );
	}

	/**
	 * Initialization of publicize logic that should always be loaded.
	 */
	public static function pre_initialization() {

		$is_wpcom = ( new Host() )->is_wpcom_simple();

		// Assets are to be loaded in all cases.
		Publicize_Assets::configure();

		$rest_controllers = array(
			REST_API\Connections_Controller::class,
		);

		// Load the REST controllers.
		foreach ( $rest_controllers as $controller ) {
			if ( $is_wpcom ) {
				wpcom_rest_api_v2_load_plugin( $controller );
			} else {
				new $controller();
			}
		}

		Social_Admin_Page::init();
	}

	/**
	 * To configure the publicize package, when called via the Config package.
	 */
	public static function on_jetpack_feature_publicize_enabled() {
		global $publicize_ui;

		if ( ! isset( $publicize_ui ) ) {
			$publicize_ui = new Publicize_UI();

		}

		// Adding on a higher priority to make sure we're the first field registered.
		// The priority parameter can be removed once we deprecate WPCOM_REST_API_V2_Post_Publicize_Connections_Field
		add_action( 'rest_api_init', array( new REST_API\Connections_Post_Field(), 'register_fields' ), 5 );
		add_action( 'rest_api_init', array( new REST_Controller(), 'register_rest_routes' ) );
		add_action( 'current_screen', array( static::class, 'init_sharing_limits' ) );

		add_action( 'rest_api_init', array( static::class, 'register_core_options' ) );
		add_action( 'admin_init', array( static::class, 'register_core_options' ) );

		( new Social_Image_Generator\Setup() )->init();
	}

	/**
	 * Registers the core options for the Publicize package.
	 */
	public static function register_core_options() {
		( new Jetpack_Social_Settings\Settings() )->register_settings();
		( new Jetpack_Social_Settings\Dismissed_Notices() )->register();
	}

	/**
	 * Retrieves the blog ID based on the environment we're running in.
	 *
	 * @return int The WPCOM blog ID.
	 */
	public static function get_blog_id() {
		return defined( 'IS_WPCOM' ) && IS_WPCOM ? get_current_blog_id() : \Jetpack_Options::get_option( 'id' );
	}

	/**
	 * Initialise share limits if they should be enabled.
	 */
	public static function init_sharing_limits() {
		$current_screen = get_current_screen();

		if ( empty( $current_screen ) || $current_screen->base !== 'post' ) {
			return;
		}

		global $publicize;

		if ( $publicize->has_paid_plan( self::$refresh_plan_info ) ) {
			return;
		}

		$info = $publicize->get_publicize_shares_info( self::get_blog_id() );

		if ( is_wp_error( $info ) ) {
			return;
		}

		if ( empty( $info['is_share_limit_enabled'] ) ) {
			return;
		}

		$connections      = $publicize->get_filtered_connection_data();
		$shares_remaining = $info['shares_remaining'];

		$share_limits = new Share_Limits( $connections, $shares_remaining, ! $current_screen->is_block_editor() );
		$share_limits->enforce_share_limits();
	}
}
