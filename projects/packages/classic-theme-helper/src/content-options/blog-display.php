<?php
/**
 * Theme Tools: the functions to display Content or Excerpt in a theme.
 *
 * @package automattic/jetpack-classic-theme-helper
 */

/**
 * If the theme doesn't support 'jetpack-content-options', don't continue.
 */
if ( ! current_theme_supports( 'jetpack-content-options' ) ) {
	return;
}

/**
 * Get the Blog Display setting.
 * If theme is using both 'Content' and 'Excerpt' then this setting will be called 'Mixed'.
 */
$options      = get_theme_support( 'jetpack-content-options' );
$blog_display = ( ! empty( $options[0]['blog-display'] ) ) ? $options[0]['blog-display'] : null;
$blog_display = preg_grep( '/^(content|excerpt)$/', (array) $blog_display );
sort( $blog_display );
$blog_display = implode( ', ', $blog_display );
$blog_display = ( 'content, excerpt' === $blog_display ) ? 'mixed' : $blog_display;

/**
 * If the theme doesn't support 'jetpack-content-options[ 'blog-display' ]', don't continue.
 */
if ( ! in_array( $blog_display, array( 'content', 'excerpt', 'mixed' ), true ) ) {
	return;
}

if ( ! function_exists( 'jetpack_blog_display_custom_excerpt' ) ) {

	/**
	 * Apply Content filters.
	 *
	 * @since 9.7.0 Deprecated $content parameter.
	 *
	 * @param string $content Post content. Deprecated.
	 */
	function jetpack_blog_display_custom_excerpt( $content = '' ) {
		if ( ! empty( $content ) ) {
			_doing_it_wrong(
				'jetpack_blog_display_custom_excerpt',
				esc_html__( 'You do not need to pass a $content parameter anymore.', 'jetpack-classic-theme-helper' ),
				'jetpack-9.7.0'
			);
		}
		$had_content_to_the_excerpt_filter = false;
		$had_content_customizer_filter     = false;
		// Avoid infinite loop where the content depends on the excerpt and
		// the excerpt depends on the content.
		if ( has_filter( 'the_content', 'jetpack_the_content_to_the_excerpt' ) ) {
			remove_filter( 'the_content', 'jetpack_the_content_to_the_excerpt' );
			$had_content_to_the_excerpt_filter = true;
		}
		if ( has_filter( 'the_content', 'jetpack_the_content_customizer' ) ) {
			remove_filter( 'the_content', 'jetpack_the_content_customizer' );
			$had_content_customizer_filter = true;
		}
		$result = '';
		try {
			$result = apply_filters( 'the_excerpt', get_the_excerpt() );
		} finally {
			if ( $had_content_to_the_excerpt_filter ) {
				add_filter( 'the_content', 'jetpack_the_content_to_the_excerpt' );
			}
			if ( $had_content_customizer_filter ) {
				add_filter( 'the_content', 'jetpack_the_content_customizer' );
			}
		}
		return $result;
	}

}

if ( ! function_exists( 'jetpack_the_content_to_the_excerptt' ) ) {

	/**
	 * Display Excerpt instead of Content.
	 *
	 * @param string $content Post content.
	 */
	function jetpack_the_content_to_the_excerpt( $content ) {
		if ( ( is_home() || is_archive() ) && ! is_post_type_archive( array( 'jetpack-testimonial', 'jetpack-portfolio', 'product' ) ) ) {
			$excerpt = jetpack_blog_display_custom_excerpt();
		}
		if ( empty( $excerpt ) ) {
			return $content;
		} else {
			return $excerpt;
		}
	}

}

if ( ! function_exists( 'jetpack_the_excerpt_to_the_content' ) ) {

	/**
	 * Display Content instead of Excerpt.
	 *
	 * @param string $content The post excerpt.
	 */
	function jetpack_the_excerpt_to_the_content( $content ) {
		if ( ( is_home() || is_archive() ) && ! is_post_type_archive( array( 'jetpack-testimonial', 'jetpack-portfolio', 'product' ) ) ) {
			ob_start();
			the_content(
				sprintf(
					wp_kses(
						/* translators: %s: Name of current post. Only visible to screen readers */
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'jetpack-classic-theme-helper' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					get_the_title()
				)
			);
			$content = ob_get_clean();
		}
		return $content;
	}

}

if ( ! function_exists( 'jetpack_the_content_customizer' ) ) {

	/**
	 * Display both Content and Excerpt instead of Content in the Customizer so live preview can switch between them.
	 *
	 * @param string $content The post content.
	 */
	function jetpack_the_content_customizer( $content ) {
		$class = jetpack_the_content_customizer_class();
		if ( ( is_home() || is_archive() ) && ! is_post_type_archive( array( 'jetpack-testimonial', 'jetpack-portfolio', 'product' ) ) ) {
			if ( post_password_required() ) {
				$excerpt = sprintf( '<p>%s</p>', esc_html__( 'There is no excerpt because this is a protected post.', 'jetpack-classic-theme-helper' ) );
			} else {
				$excerpt = jetpack_blog_display_custom_excerpt();
			}
		}
		if ( empty( $excerpt ) ) {
			return $content;
		} else {
			return sprintf( '<div class="jetpack-blog-display %s jetpack-the-content">%s</div><div class="jetpack-blog-display %s jetpack-the-excerpt">%s</div>', $class, $content, $class, $excerpt );
		}
	}

}

if ( ! function_exists( 'jetpack_the_excerpt_customizer' ) ) {

	/**
	 * Display both Content and Excerpt instead of Excerpt in the Customizer so live preview can switch between them.
	 *
	 * @param string $excerpt The post excerpt.
	 */
	function jetpack_the_excerpt_customizer( $excerpt ) {
		if ( ( is_home() || is_archive() ) && ! is_post_type_archive( array( 'jetpack-testimonial', 'jetpack-portfolio', 'product' ) ) ) {
			ob_start();
			the_content(
				sprintf(
					wp_kses(
						/* translators: %s: Name of current post. Only visible to screen readers */
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'jetpack-classic-theme-helper' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					get_the_title()
				)
			);
			$content = ob_get_clean();
		}
		if ( empty( $content ) ) {
			return $excerpt;
		} else {
			return sprintf( '<div class="jetpack-blog-display jetpack-the-content">%s</div><div class="jetpack-blog-display jetpack-the-excerpt">%s</div>', $content, $excerpt );
		}
	}

}

if ( ! function_exists( 'jetpack_the_excerpt_mixed_customizer' ) ) {

	/**
	 * Display Content instead of Excerpt in the Customizer when theme uses a 'Mixed' display.
	 *
	 * @param string $content The post excerpt.
	 */
	function jetpack_the_excerpt_mixed_customizer( $content ) {
		if ( ( is_home() || is_archive() ) && ! is_post_type_archive( array( 'jetpack-testimonial', 'jetpack-portfolio', 'product' ) ) ) {
			jetpack_the_content_customizer_class( 'output-the-excerpt' );
			ob_start();
			the_content();
			$content = ob_get_clean();
		}
		return $content;
	}

}

if ( ! function_exists( 'jetpack_the_content_customizer_class' ) ) {

	/**
	 * Returns a class value, `output-the-content` by default.
	 * Used for themes with a 'Mixed' Blog Display so we can tell which output is by default.
	 *
	 * @param string|null $new_class CSS class added to content container.
	 */
	function jetpack_the_content_customizer_class( $new_class = null ) {
		static $class;
		if ( isset( $new_class ) ) {
			// Assign a new class and return.
			$class = $new_class;
		} elseif ( isset( $class ) ) {
			// Reset the class after getting value.
			$prev_class = $class;
			$class      = null;
			return $prev_class;
		} else {
			// Return default class value.
			return 'output-the-content';
		}
	}

}

if ( is_customize_preview() ) {
	/*
	 * Display Content and Excerpt if the default Blog Display is 'Content'
	 * and we are in the Customizer.
	 */
	if ( 'content' === $blog_display ) {
		add_filter( 'the_content', 'jetpack_the_content_customizer' );
	}

	/*
	 * Display Content and Excerpt if the default Blog Display is 'Excerpt'
	 * and we are in the Customizer.
	 */
	if ( 'excerpt' === $blog_display ) {
		add_filter( 'the_excerpt', 'jetpack_the_excerpt_customizer' );
	}

	/*
	 * Display Content and Excerpt if the default Blog Display is 'Mixed'
	 * and we are in the Customizer.
	 */
	if ( 'mixed' === $blog_display ) {
		add_filter( 'the_content', 'jetpack_the_content_customizer' );
		add_filter( 'the_excerpt', 'jetpack_the_excerpt_mixed_customizer' );
	}
} else {
	$display_option = get_option( 'jetpack_content_blog_display', $blog_display );

	/*
	 * Display Excerpt if the default Blog Display is 'Content'
	 * or default Blog Display is 'Mixed'
	 * and the Option picked is 'Post Excerpt'
	 * and we aren't in the Customizer.
	 */
	if ( ( 'content' === $blog_display || 'mixed' === $blog_display ) && 'excerpt' === $display_option ) {
		add_filter( 'the_content', 'jetpack_the_content_to_the_excerpt' );
	}

	/*
	 * Display Content if the default Blog Display is 'Excerpt'
	 * or default Blog Display is 'Mixed'
	 * and the Option picked is 'Full Post'
	 * and we aren't in the Customizer.
	 */
	if ( ( 'excerpt' === $blog_display || 'mixed' === $blog_display ) && 'content' === $display_option ) {
		add_filter( 'the_excerpt', 'jetpack_the_excerpt_to_the_content' );
	}
}
