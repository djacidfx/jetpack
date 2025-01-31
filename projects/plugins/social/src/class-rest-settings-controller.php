<?php
/**
 * The Social Rest Controller class.
 * Registers the REST routes for Social.
 *
 * @package automattic/jetpack-social-plugin
 */

namespace Automattic\Jetpack\Social;

use Jetpack_Social;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Registers the REST routes for Social.
 */
class REST_Settings_Controller extends WP_REST_Controller {
	/**
	 * Registers the REST routes for Social.
	 *
	 * @access public
	 * @static
	 */
	public function register_rest_routes() {
		register_rest_route(
			'jetpack/v4',
			'/social/review-dismiss',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_review_dismissed' ),
					'permission_callback' => array( $this, 'require_publish_posts_permission_callback' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
			)
		);
	}

	/**
	 * Only administrators can access the API.
	 *
	 * @return bool|WP_Error True if a blog token was used to sign the request, WP_Error otherwise.
	 */
	public function require_admin_privilege_callback() {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return new WP_Error(
			'rest_forbidden',
			esc_html__( 'You are not allowed to perform this action.', 'jetpack-social' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * Check to see if a user is able to publish posts
	 *
	 * @return bool|WP_Error
	 */
	public function require_publish_posts_permission_callback() {
		if ( current_user_can( 'publish_posts' ) ) {
			return true;
		}

		return new WP_Error(
			'rest_forbidden',
			esc_html__( 'You are not allowed to perform this action.', 'jetpack-social' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * Updates the boolean value that dismisses the request to review the plugin
	 *
	 * @param WP_REST_Request $request - REST Request.
	 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function update_review_dismissed( $request ) {
		$params    = $request->get_params();
		$dismissed = $params['dismissed'];

		if ( ! update_option( Jetpack_Social::JETPACK_SOCIAL_REVIEW_DISMISSED_OPTION, (bool) $dismissed ) ) {
			return new WP_Error(
				'rest_cannot_edit',
				__( 'Failed to update the review_request_dismiss', 'jetpack-social' ),
				array( 'status' => 500 )
			);
		}

		return rest_ensure_response( true );
	}

	/**
	 * Prepares the settings data to return from the endpoint.
	 * Includes checking the values against the schema.
	 *
	 * @param array           $settings  The settings data to prepare.
	 * @param WP_REST_Request $request   REST request.
	 * @return array|WP_Error The prepared settings or a WP_Error on failure.
	 */
	public function prepare_item_for_response( $settings, $request ) {
		$args   = $this->get_endpoint_args_for_item_schema( $request->get_method() );
		$return = array();
		foreach ( $settings as $name => $value ) {
			if ( empty( $args[ $name ] ) ) {
				// This setting shouldn't be returned.
				continue;
			}
			$is_valid = rest_validate_value_from_schema( $value, $args[ $name ], $name );
			if ( is_wp_error( $is_valid ) ) {
				return $is_valid;
			}
			$sanitized = rest_sanitize_value_from_schema( $value, $args[ $name ] );
			if ( is_wp_error( $sanitized ) ) {
				return $sanitized;
			}
			$return[ $name ] = $sanitized;
		}
		return rest_ensure_response( $return );
	}
}
