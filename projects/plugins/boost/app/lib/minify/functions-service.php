<?php

use Automattic\Jetpack_Boost\Lib\Minify;
use Automattic\Jetpack_Boost\Lib\Minify\Config;
use Automattic\Jetpack_Boost\Lib\Minify\File_Paths;
use Automattic\Jetpack_Boost\Lib\Minify\Utils;

function jetpack_boost_handle_minify_request( $request_uri ) {
	// We handle the cache here, tell other caches not to.
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	$output  = jetpack_boost_build_minify_output( $request_uri );
	$content = $output['content'];
	$headers = $output['headers'];

	foreach ( $headers as $header ) {
		header( $header );
	}

	// Check if we're on Atomic and take advantage of the Atomic Edge Cache.
	if ( defined( 'ATOMIC_CLIENT_ID' ) ) {
		header( 'A8c-Edge-Cache: cache' );
	}

	header( 'X-Page-Optimize: uncached' );
	header( 'Cache-Control: max-age=' . 31536000 );
	header( 'ETag: "' . md5( $content ) . '"' );

	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- We need to trust this unfortunately.

	// Cache the generated data, if possible.
	$use_cache = Config::can_use_static_cache();
	if ( $use_cache ) {
		$file_parts = jetpack_boost_minify_get_file_parts( $request_uri );
		if ( is_array( $file_parts ) && isset( $file_parts['file_name'] ) && isset( $file_parts['file_extension'] ) ) {
			$cache_dir       = Config::get_static_cache_dir_path();
			$cache_file_path = $cache_dir . '/' . $file_parts['file_name'] . '.min.' . $file_parts['file_extension'];

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $cache_file_path, $content );
		}
	}
}

/**
 * Using a crafted request, we can check if is_404() is working in wp-content/
 */
function jetpack_boost_check_404_handler( $request_uri ) {
	if ( ! str_contains( strtolower( $request_uri ), 'wp-content/boost-cache/static/testing_404' ) ) {
		return;
	}

	if ( is_404() ) {
		if ( ! is_dir( Config::get_static_cache_dir_path() ) ) {
			mkdir( Config::get_static_cache_dir_path(), 0775, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
		}
		file_put_contents( Config::get_static_cache_dir_path() . '/404', '1' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		return true;
	} else {
		wp_delete_file( Config::get_static_cache_dir_path() . '/404' );
		return false;
	}
}

/**
 * This function is used to test if is_404() is working in wp-content/
 * It sends a request to a non-existent URL, that will execute the 404 handler
 * in jetpack_boost_check_404_handler().
 *
 * This function is called when the Minify_CSS or Minify_JS module is activated.
 */
function jetpack_boost_404_tester() {
	wp_remote_get( home_url( '/wp-content/boost-cache/static/testing_404' ) );
	if ( file_exists( Config::get_static_cache_dir_path() . '/404' ) ) {
		wp_delete_file( Config::get_static_cache_dir_path() . '/404' );
		update_site_option( 'jetpack_boost_static_minification', 1 );
	} else {
		update_site_option( 'jetpack_boost_static_minification', 0 );
	}
}
add_action( 'jetpack_boost_404_tester_cron', 'jetpack_boost_404_tester' );

/**
 * Setup the 404 tester.
 *
 * Schedule the 404 tester in three seconds if the concatenation modules
 * haven't been toggled since this feature was released.
 * Only run this in wp-admin to avoid excessive updates to the option.
 */
function jetpack_boost_404_setup() {
	if ( is_admin() && get_site_option( 'jetpack_boost_static_minification', 'na' ) === 'na' ) {
		update_site_option( 'jetpack_boost_static_minification', 0 ); // Add a default value if not set to avoid an extra SQL query.
	}
	jetpack_boost_page_optimize_schedule_404_tester();
}

/**
 * This function is used to clean up the static cache folder.
 * It removes files with the file extension passed in the $file_extension parameter.
 *
 * @param string $file_extension The file extension to clean up.
 */
function jetpack_boost_page_optimize_cleanup_cache( $file_extension ) {
	$files = glob( Config::get_static_cache_dir_path() . "/*.min.{$file_extension}" );
	foreach ( $files as $file ) {
		wp_delete_file( $file );
	}
}

/**
 * This function is used to clean up the static cache folder.
 * It removes files that are stale and no longer needed.
 * A file is considered stale if it's older than the files it depends on.
 */
function jetpack_boost_minify_remove_stale_static_files() {
	$files = glob( Config::get_static_cache_dir_path() . '/*.min.*' );
	foreach ( $files as $file ) {
		if ( ! file_exists( $file ) ) {
			continue;
		}

		$file_mtime = filemtime( $file );
		$file_parts = pathinfo( $file );
		$hash       = substr( $file_parts['basename'], 0, strpos( $file_parts['basename'], '.' ) );
		$paths      = File_Paths::get( $hash );
		if ( $paths ) {
			$args = $paths->get_paths();
			if ( ! is_array( $args ) ) {
				continue;
			}

			foreach ( $args as $filename ) {
				if ( ! file_exists( ABSPATH . $filename ) || filemtime( ABSPATH . $filename ) > $file_mtime ) {
					wp_delete_file( $file ); // remove the file from the cache because it's stale.
				}
			}
		}
	}
}

function jetpack_boost_build_minify_output( $request_uri ) {
	$utils                             = new Utils();
	$jetpack_boost_page_optimize_types = jetpack_boost_page_optimize_types();

	// Config
	$concat_max_files = 150;
	$concat_unique    = true;

	$file_parts = jetpack_boost_minify_get_file_parts( $request_uri );
	if ( ! $file_parts ) {
		jetpack_boost_page_optimize_status_exit( 404 );
	}

	$file_paths = jetpack_boost_page_optimize_get_file_paths( $file_parts['file_name'] );

	// file_paths contain something like array( '/foo/bar.css', '/foo1/bar/baz.css' )
	if ( count( $file_paths ) > $concat_max_files ) {
		jetpack_boost_page_optimize_status_exit( 400 );
	}

	// If we're in a subdirectory context, use that as the root.
	// We can't assume that the root serves the same content as the subdir.
	$subdir_path_prefix = '';
	$request_path       = $utils->parse_url( $request_uri, PHP_URL_PATH );
	$_static_index      = strpos( $request_path, jetpack_boost_get_static_prefix() );
	if ( $_static_index > 0 ) {
		$subdir_path_prefix = substr( $request_path, 0, $_static_index );
	}
	unset( $request_path, $_static_index );

	$last_modified = 0;
	$pre_output    = '';
	$output        = '';

	$mime_type = '';

	foreach ( $file_paths as $uri ) {
		$fullpath = jetpack_boost_page_optimize_get_path( $uri );

		if ( ! file_exists( $fullpath ) ) {
			jetpack_boost_page_optimize_status_exit( 404 );
		}

		$mime_type = jetpack_boost_page_optimize_get_mime_type( $fullpath );
		if ( ! in_array( $mime_type, $jetpack_boost_page_optimize_types, true ) ) {
			jetpack_boost_page_optimize_status_exit( 400 );
		}

		if ( $concat_unique ) {
			if ( ! isset( $last_mime_type ) ) {
				$last_mime_type = $mime_type;
			}

			if ( $last_mime_type !== $mime_type ) {
				jetpack_boost_page_optimize_status_exit( 400 );
			}
		}

		$stat = stat( $fullpath );
		if ( false === $stat ) {
			jetpack_boost_page_optimize_status_exit( 500 );
		}

		if ( $stat['mtime'] > $last_modified ) {
			$last_modified = $stat['mtime'];
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$buf = file_get_contents( $fullpath );
		if ( false === $buf ) {
			jetpack_boost_page_optimize_status_exit( 500 );
		}

		if ( 'text/css' === $mime_type ) {
			$dirpath = jetpack_boost_strip_parent_path( $subdir_path_prefix, dirname( $uri ) );

			// url(relative/path/to/file) -> url(/absolute/and/not/relative/path/to/file)
			$buf = jetpack_boost_page_optimize_relative_path_replace( $buf, $dirpath );

			// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
			// This regex changes things like AlphaImageLoader(...src='relative/path/to/file'...) to AlphaImageLoader(...src='/absolute/path/to/file'...)
			$buf = preg_replace(
				'/(Microsoft.AlphaImageLoader\s*\([^\)]*src=(?:\'|")?)([^\/\'"\s\)](?:(?<!http:|https:).)*)\)/isU',
				'$1' . ( $dirpath === '/' ? '/' : $dirpath . '/' ) . '$2)',
				$buf
			);

			// The @charset rules must be on top of the output
			if ( str_starts_with( $buf, '@charset' ) ) {
				$buf = preg_replace_callback(
					'/(?P<charset_rule>@charset\s+[\'"][^\'"]+[\'"];)/i',
					function ( $match ) use ( &$pre_output ) {
						if ( str_starts_with( $pre_output, '@charset' ) ) {
							return '';
						}

						$pre_output = $match[0] . "\n" . $pre_output;

						return '';
					},
					$buf
				);
			}

			// Move the @import rules on top of the concatenated output.
			// Only @charset rule are allowed before them.
			if ( str_contains( $buf, '@import' ) ) {
				$buf = preg_replace_callback(
					'/(?P<pre_path>@import\s+(?:url\s*\()?[\'"\s]*)(?P<path>[^\'"\s](?:https?:\/\/.+\/?)?.+?)(?P<post_path>[\'"\s\)]*;)/i',
					function ( $match ) use ( $dirpath, &$pre_output ) {
						if ( ! str_starts_with( $match['path'], 'http' ) && '/' !== $match['path'][0] ) {
							$pre_output .= $match['pre_path'] . ( $dirpath === '/' ? '/' : $dirpath . '/' ) .
											$match['path'] . $match['post_path'] . "\n";
						} else {
							$pre_output .= $match[0] . "\n";
						}

						return '';
					},
					$buf
				);
			}

			// If filename indicates it's already minified, don't minify it again.
			if ( ! preg_match( '/\.min\.css$/', $fullpath ) ) {
				// Minify CSS.
				$buf = Minify::css( $buf );
			}
			$output .= "$buf";
		} else {
			// If filename indicates it's already minified, don't minify it again.
			if ( ! preg_match( '/\.min\.js$/', $fullpath ) ) {
				// Minify JS
				$buf = Minify::js( $buf );
			}

			$output .= "$buf;\n";
		}
	}

	// Don't let trailing whitespace ruin everyone's day. Seems to get stripped by batcache
	// resulting in ns_error_net_partial_transfer errors.
	$output = rtrim( $output );

	$headers = array(
		'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $last_modified ) . ' GMT',
		"Content-Type: $mime_type",
	);

	return array(
		'headers' => $headers,
		'content' => $pre_output . $output,
	);
}

/**
 * Get the file name and extension from the request URI.
 *
 * @param string $request_uri The request URI.
 * @return array|false The file name and extension, or false if the request URI is invalid.
 */
function jetpack_boost_minify_get_file_parts( $request_uri ) {
	$utils       = new Utils();
	$request_uri = $utils->unslash( $request_uri );

	$file_path = $utils->parse_url( $request_uri, PHP_URL_PATH );
	if ( $file_path === false ) {
		return false;
	}

	$file_info = pathinfo( $file_path );
	$real_path = realpath( ABSPATH . $file_info['dirname'] );
	$cache_dir = realpath( WP_CONTENT_DIR . '/boost-cache/static' );

	// Security check: Ensure requested file is strictly within the designated cache directory
	// by comparing the resolved absolute paths.
	if ( $real_path === false || $cache_dir === false || stripos( $real_path, $cache_dir ) !== 0 ) {
		return false;
	}

	$allowed_extensions = array_keys( jetpack_boost_page_optimize_types() );
	if ( ! isset( $file_info['extension'] ) || ! in_array( $file_info['extension'], $allowed_extensions, true ) ) {
		return false;
	}

	// The base name (without the extension) might contain ".min".
	// Example - 777873a36e.min
	$file_name_parts = explode( '.', $file_info['basename'] );
	$file_name       = $file_name_parts[0];

	return array(
		'file_name'      => $file_name,
		'file_extension' => $file_info['extension'] ?? '',
	);
}
