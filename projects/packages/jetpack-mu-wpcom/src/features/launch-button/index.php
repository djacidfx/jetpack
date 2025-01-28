<?php
/**
 * Adds a "launch site" button to the admin bar.
 *
 * @package automattic/jetpack-mu-wpcom
 */

/**
 * Adds a "launch site" button to the admin bar.
 *
 * @param WP_Admin_Bar $admin_bar The WordPress admin bar.
 */
function wpcom_add_launch_button_to_admin_bar( WP_Admin_Bar $admin_bar ) {
	$current_blog_id = get_current_blog_id();

	if ( function_exists( 'is_graylisted' ) && is_graylisted( $current_blog_id ) ) {
		return false;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( function_exists( 'has_blog_sticker' ) && has_blog_sticker( 'difm-lite-in-progress' ) ) {
		return false;
	}

	// No button for agency-managed sites.
	if ( ! empty( get_option( 'is_fully_managed_agency_site' ) ) ) {
		return false;
	}

	$is_launched = get_option( 'launch-status' ) !== 'unlaunched';
	if ( $is_launched ) {
		return;
	}
	$blog_domain = wp_parse_url( home_url(), PHP_URL_HOST );
	$admin_bar->add_menu(
		array(
			'id'     => 'menu-id',
			'parent' => null,
			'group'  => null,
			'title'  => __( 'Launch site', 'jetpack-mu-wpcom' ),
			'href'   => 'https://wordpress.com/start/launch-site?siteSlug=' . $blog_domain,
			'meta'   => array(
				'class' => 'launch-site',
			),
		)
	);
}

/**
 * Enqueue the necessary styles for the admin bar button.
 */
function wpcom_enqueue_launch_button_styles() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$version = filemtime( __DIR__ . '/style.css' );
	wp_enqueue_style( 'launch-banner', plugins_url( 'style.css', __FILE__ ), array(), $version );
}

add_action( 'admin_bar_menu', 'wpcom_add_launch_button_to_admin_bar', 500 );
add_action( 'wp_enqueue_scripts', 'wpcom_enqueue_launch_button_styles' );
add_action( 'admin_enqueue_scripts', 'wpcom_enqueue_launch_button_styles' );
